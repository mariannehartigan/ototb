<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Income;

class InsertIncomesService
{
    public function __construct(
        private GetIncomesService $getIncomesService
    ) {}

    public function insertIncomes(Carbon $date): void
    {
        $incomes = $this->getIncomesService->getIncomes($date);

        foreach ($incomes as $income) {

            // The detail ID is useful for generation,
            // but is not necessarily a column in incomes.
            unset($income['planned_income_detail_id']);

            Income::create($income);
        }
    }
}
