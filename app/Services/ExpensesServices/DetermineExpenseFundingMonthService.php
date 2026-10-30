<?php

namespace App\Services\ExpensesServices;

use Illuminate\Support\Collection;
use InvalidArgumentException;

class DetermineExpenseFundingMonthService
{
    public function determineFundingMonths(
        Collection $plannedExpenses,
        callable $evaluatePlan
    ): array {

        // Start with the funding choices already saved.
        $choices = [];

        foreach ($plannedExpenses as $expense) {

            $detail = $expense->plannedExpenseDetails
                ->sortBy([
                    ['effective_date', 'asc'],
                    ['id', 'asc'],
                ])
                ->last();

            if (!$detail) {
                continue;
            }

            $choices[$expense->id] =
                (bool) $detail->assign_to_following_month;
        }

        $originalChoices = $choices;

        // Evaluate the current arrangement first.
        $bestResult = $this->evaluate(
            $choices,
            $originalChoices,
            $evaluatePlan
        );

        /*
         * STEP 1
         *
         * Try changing one expense at a time.
         *
         * Keep a change only when it improves the
         * overall projected budget.
         */

        do {

            $improved = false;
            $bestChoices = $choices;
            $bestCandidate = $bestResult;

            foreach (array_keys($choices) as $expenseId) {

                $candidateChoices = $choices;

                $candidateChoices[$expenseId] =
                    !$candidateChoices[$expenseId];

                $candidateResult = $this->evaluate(
                    $candidateChoices,
                    $originalChoices,
                    $evaluatePlan
                );

                if ($this->isBetter(
                    $candidateResult,
                    $bestCandidate
                )) {

                    $bestCandidate = $candidateResult;
                    $bestChoices = $candidateChoices;
                    $improved = true;
                }
            }

            if ($improved) {
                $choices = $bestChoices;
                $bestResult = $bestCandidate;
            }

        } while ($improved);

        /*
         * STEP 2
         *
         * Sometimes changing one expense alone
         * doesn't improve the budget, but changing
         * two together does.
         *
         * Try pairs of changes.
         */

        do {

            $improved = false;
            $bestChoices = $choices;
            $bestCandidate = $bestResult;

            $expenseIds = array_keys($choices);

            for ($i = 0; $i < count($expenseIds); $i++) {

                for ($j = $i + 1; $j < count($expenseIds); $j++) {

                    $firstId = $expenseIds[$i];
                    $secondId = $expenseIds[$j];

                    $candidateChoices = $choices;

                    $candidateChoices[$firstId] =
                        !$candidateChoices[$firstId];

                    $candidateChoices[$secondId] =
                        !$candidateChoices[$secondId];

                    $candidateResult = $this->evaluate(
                        $candidateChoices,
                        $originalChoices,
                        $evaluatePlan
                    );

                    if ($this->isBetter(
                        $candidateResult,
                        $bestCandidate
                    )) {

                        $bestCandidate = $candidateResult;
                        $bestChoices = $candidateChoices;
                        $improved = true;
                    }
                }
            }

            if ($improved) {
                $choices = $bestChoices;
                $bestResult = $bestCandidate;
            }

        } while ($improved);

        /*
         * STEP 3
         *
         * After changing pairs, check individual
         * expenses again.
         *
         * A pair change might make another
         * individual change beneficial.
         */

        do {

            $improved = false;

            foreach (array_keys($choices) as $expenseId) {

                $candidateChoices = $choices;

                $candidateChoices[$expenseId] =
                    !$candidateChoices[$expenseId];

                $candidateResult = $this->evaluate(
                    $candidateChoices,
                    $originalChoices,
                    $evaluatePlan
                );

                if ($this->isBetter(
                    $candidateResult,
                    $bestResult
                )) {

                    $choices = $candidateChoices;
                    $bestResult = $candidateResult;
                    $improved = true;

                    // Restart because our choices changed.
                    break;
                }
            }

        } while ($improved);

        return [
            'choices' => $choices,
            'evaluation' => $bestResult,
        ];
    }

    private function evaluate(
        array $choices,
        array $originalChoices,
        callable $evaluatePlan
    ): array {

        // The callback will use our projection services
        // to calculate the results for these choices.
        $result = $evaluatePlan($choices);

        $requiredMetrics = [
            'hard_violations',
            'regular_shortfall',
            'cashflow_shortfall',
            'backup_used',
            'balance_penalty',
        ];

        foreach ($requiredMetrics as $metric) {

            if (
                !array_key_exists($metric, $result)
                || !is_numeric($result[$metric])
                || !is_finite((float) $result[$metric])
                || (float) $result[$metric] < 0
            ) {
                throw new InvalidArgumentException(
                    "Invalid evaluation metric: {$metric}"
                );
            }
        }

        // Prefer retaining existing funding choices
        // when two arrangements perform equally well.
        $changes = 0;

        foreach ($choices as $expenseId => $followingMonth) {

            if (
                $followingMonth !==
                $originalChoices[$expenseId]
            ) {
                $changes++;
            }
        }

        $result['changes'] = $changes;

        return $result;
    }

    private function isBetter(
        array $candidate,
        array $current
    ): bool {

        /*
         * Compare in order of importance.
         *
         * A lower value is better.
         *
         * A more important metric always takes
         * priority over less important metrics.
         */

        $priorities = [
            'hard_violations',
            'regular_shortfall',
            'cashflow_shortfall',
            'backup_used',
            'changes',
            'balance_penalty',
        ];

        foreach ($priorities as $metric) {

            $difference =
                (float) $candidate[$metric]
                - (float) $current[$metric];

            if (abs($difference) < 0.000001) {
                continue;
            }

            return $difference < 0;
        }

        return false;
    }
}
