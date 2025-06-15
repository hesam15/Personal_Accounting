<?php
namespace App\Consts;

use App\Models\Budget;
use App\Models\Income;
use App\Models\SaveBox;
use App\Models\Investment;
use App\Models\DailyExpense;
use Illuminate\Database\Eloquent\Model;

class ModelConsts {
    const BUDGET = Budget::class;
    const DAILYEXPENSE = DailyExpense::class;
    const INCOME = Income::class;
    const INVESTMENT = Investment::class;
    const SAVEBOX = SaveBox::class;

    private static $modelMap = [
        'BUDGET' => self::BUDGET,
        'DAILY_EXPENSE' => self::DAILYEXPENSE,
        'INCOME' => self::INCOME,
        'INVESTMENT' => self::INVESTMENT,
        'SAVE_BOX' => self::SAVEBOX,
    ];

    private static $reverseModelMap = [
        self::BUDGET => 'BUDGET',
        self::DAILYEXPENSE => 'DAILY_EXPENSE',
        self::INCOME => 'INCOME',
        self::INVESTMENT => 'INVESTMENT',
        self::SAVEBOX => 'SAVE_BOX',
    ];

    public static function findModel(string $model): ?Model {
        if(mb_detect_encoding($model, 'ASCII', true) === 'ASCII') {
            return new self::$modelMap[strtoupper($model)]() ?? null;
        }

        return null;
    }

    public static function findLowerCaseModelName(Model $model): string {
        $className = get_class($model);

        $modelKey = self::$reverseModelMap[$className] ?? null;

        return $modelKey ? strtolower($modelKey) : null;
    }
}