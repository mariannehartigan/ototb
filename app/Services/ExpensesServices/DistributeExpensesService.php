<?php

namespace App\Services\ExpensesServices;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class DistributeExpensesService
{
    private const EPSILON = 0.000001;

    public function distributeExpenses(Collection $incomes, Collection $expenses): array
    {
        $tithingEnabled = (bool) (Setting::where('user_id', Auth::id())->first()?->tithe ?? true);
        $balances = [];
        $assignments = [];
        $unassigned = [];

        foreach ($incomes as $income) {
            $obligation = $tithingEnabled && $income->tithe
                && !($income->tithe_already_accounted ?? false)
                ? round((float) $income->amount * 0.10, 2) : 0.0;

            $balances[$income->id] = [
                'income' => $income,
                'opening' => (float) ($income->opening_balance ?? $income->amount),
                'tithe_obligation' => $obligation,
                'manual_tithe_assigned' => 0.0,
                'automatic_tithe' => $obligation,
                'remaining' => 0.0,
                'expenses' => [],
            ];

            $this->recalculate($balances[$income->id]);
        }

        // Due date is the first priority. Within a day, honour explicit
        // income selections and tithes before ordinary expenses.
        $ordered = $expenses->sort(function ($a, $b) {
            $dateComparison = strcmp(
                Carbon::parse($a->date)->toDateString(),
                Carbon::parse($b->date)->toDateString()
            );

            if ($dateComparison !== 0) {
                return $dateComparison;
            }

            $priority = fn ($e) => $e->planned_income_id !== null
                ? 0
                : ($e->tithe ? 1 : 2);

            return $priority($a) <=> $priority($b)
                ?: ((float) $b->amount <=> (float) $a->amount)
                ?: ($a->id <=> $b->id);
        });

        foreach ($ordered as $expense) {
            $regular = $this->eligible($expense, $balances, false);
            $candidates = $regular ?: $this->eligible($expense, $balances, true);

            if (!$candidates) {
                $unassigned[] = [
                    'expense' => $expense,
                    'reason' => 'No income received on or before the due date satisfies the restrictions',
                ];

                continue;
            }

            $id = $this->bestIncome($expense, $candidates, $balances);

            $this->assign($expense, $id, $balances, $assignments);
        }

        // Move whole expenses among eligible regular incomes to reduce the
        // worst negative balance and then improve percentage balance.
        // Never change the due date or an explicit income selection.
        for ($iteration = 0; $iteration < 300; $iteration++) {
            $current = $this->penalty($balances);
            $best = null;
            $bestScore = $current;

            foreach ($ordered as $expense) {
                if (
                    !isset($assignments[$expense->id])
                    || $expense->planned_income_id !== null
                ) {
                    continue;
                }

                $sourceId = $assignments[$expense->id];

                if ($balances[$sourceId]['income']->classification !== 'regular') {
                    continue;
                }

                foreach ($this->eligible($expense, $balances, false) as $targetId) {
                    if ($targetId === $sourceId) {
                        continue;
                    }

                    $trial = $balances;

                    $this->unassign($expense, $sourceId, $trial);
                    $this->put($expense, $targetId, $trial);

                    $score = $this->penalty($trial);

                    if ($score < $bestScore - self::EPSILON) {
                        $bestScore = $score;
                        $best = [$expense, $sourceId, $targetId];
                    }
                }
            }

            if ($best === null) {
                break;
            }

            [$expense, $sourceId, $targetId] = $best;

            $this->unassign($expense, $sourceId, $balances);
            $this->assign($expense, $targetId, $balances, $assignments);
        }

        // Backup is a last resort: move ordinary expenses from overdrawn
        // regular incomes onto eligible backup incomes when it improves
        // the regular-income shortfall. Negative balances remain permitted.
        foreach ($ordered as $expense) {
            $sourceId = $assignments[$expense->id] ?? null;

            if (
                $sourceId === null
                || $expense->tithe
                || $expense->planned_income_id !== null
            ) {
                continue;
            }

            if ($balances[$sourceId]['income']->classification !== 'regular') {
                continue;
            }

            if ($balances[$sourceId]['remaining'] >= -self::EPSILON) {
                continue;
            }

            $backupIds = array_values(array_filter(
                $this->eligible($expense, $balances, true),
                fn ($id) => $balances[$id]['income']->classification === 'backup'
            ));

            if (!$backupIds) {
                continue;
            }

            $targetId = $this->bestIncome($expense, $backupIds, $balances);

            $this->unassign($expense, $sourceId, $balances);
            $this->assign($expense, $targetId, $balances, $assignments);
        }

        return [
            'assignments' => $assignments,
            'unassigned' => $unassigned,
            'income_balances' => $balances,
            'backup_decisions' => [],
        ];
    }

    private function eligible(
        object $expense,
        array $balances,
        bool $allowBackup
    ): array {

        $ids = [];
        $due = Carbon::parse($expense->date)->startOfDay();

        foreach ($balances as $id => $balance) {
            $income = $balance['income'];

            if (Carbon::parse($income->date)->startOfDay()->gt($due)) {
                continue;
            }

            if ($income->assign_no_expenses_except_tithe && !$expense->tithe) {
                continue;
            }

            if (
                $expense->planned_income_id !== null
                && $expense->planned_income_id != $income->planned_income_id
            ) {
                continue;
            }

            if (
                !$allowBackup
                && $income->classification === 'backup'
                && $expense->planned_income_id === null
            ) {
                continue;
            }

            $ids[] = $id;
        }

        return $ids;
    }

    private function bestIncome(
        object $expense,
        array $ids,
        array $balances
    ): int|string {

        $bestId = $ids[0];
        $bestScore = -INF;

        foreach ($ids as $id) {
            $trial = $balances[$id];
            $trialBalances = [$id => $trial];

            $this->put($expense, $id, $trialBalances);

            // Score favours the highest percentage left after assignment.
            // Negative balances are allowed.
            $after = $trialBalances[$id]['remaining'];

            $score = $after / max(
                0.01,
                abs((float) $trial['income']->amount)
            );

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestId = $id;
            }
        }

        return $bestId;
    }

    private function assign(
        object $expense,
        int|string $id,
        array &$balances,
        array &$assignments
    ): void {

        $this->put($expense, $id, $balances);
        $assignments[$expense->id] = $id;
    }

    private function put(
        object $expense,
        int|string $id,
        array &$balances
    ): void {

        $balances[$id]['expenses'][$expense->id] = $expense;
        $this->recalculate($balances[$id]);
    }

    private function unassign(
        object $expense,
        int|string $id,
        array &$balances
    ): void {

        unset($balances[$id]['expenses'][$expense->id]);
        $this->recalculate($balances[$id]);
    }

    private function recalculate(array &$balance): void
    {
        $manual = 0.0;
        $ordinary = 0.0;

        foreach ($balance['expenses'] as $expense) {
            if ($expense->tithe) {
                $manual += (float) $expense->amount;
            } else {
                $ordinary += (float) $expense->amount;
            }
        }

        $balance['manual_tithe_assigned'] = round($manual, 2);

        $balance['automatic_tithe'] = round(
            max(0, $balance['tithe_obligation'] - $manual),
            2
        );

        $balance['remaining'] = round(
            $balance['opening']
            - $ordinary
            - $manual
            - $balance['automatic_tithe'],
            2
        );
    }

    private function penalty(array $balances): float
    {
        $regular = array_filter(
            $balances,
            fn ($b) =>
                $b['income']->classification === 'regular'
                && !$b['income']->assign_no_expenses_except_tithe
                && (float) $b['income']->amount > 0
        );

        if (!$regular) {
            return 0.0;
        }

        $percentages = array_map(
            fn ($b) =>
                $b['remaining'] / (float) $b['income']->amount,
            $regular
        );

        // Strongly prefer eliminating negative balances;
        // then equalize percentages.
        $negative = array_sum(array_map(
            fn ($b) => max(0, -$b['remaining']),
            $regular
        ));

        return $negative * 1000000
            + max($percentages)
            - min($percentages);
    }
}
