<?php
namespace App\Consts;

use App\Models\Budget;
use App\Models\Income;
use App\Models\SaveBox;
use App\Models\Investment;
use Illuminate\Database\Eloquent\Model;

class ModelConsts {
    const BUDGET = Budget::class;
    const INCOME = Income::class;
    const INVESTMENT = Investment::class;
    const SAVEBOX = SaveBox::class;

    const MODELS = [
        'budget',
        'income',
        'investment',
        'save_box'
    ];

    public static $modelMap = [
        'budget' => self::BUDGET,
        'income' => self::INCOME,
        'investment' => self::INVESTMENT,
        'save_box' => self::SAVEBOX,
    ];

    private static $persianModel = [
        self::BUDGET => 'بودجه',
        self::INCOME => 'درآمد',
        self::INVESTMENT => 'سرمایه گذاری',
        self::SAVEBOX => 'باکس ذخیره'
    ];

    private static $reverseModelMap = [
        self::BUDGET => 'budget',
        self::INCOME => 'income',
        self::INVESTMENT => 'investment',
        self::SAVEBOX => 'save_box',
    ];

    public static function modelToPersian(string $model) {
        if(mb_detect_encoding($model, 'ASCII', true) === 'ASCII') {
            return self::$persianModel[$model] ?? null;
        }
    }

    public static function findModel(string $model): ?Model {
        if(mb_detect_encoding($model, 'ASCII', true) === 'ASCII') {
            return new self::$modelMap[$model]() ?? null;
        }

        return null;
    }

    public static function findLowerCaseModelName(Model $model): string {
        $className = get_class($model);

        $modelKey = self::$reverseModelMap[$className] ?? null;

        return $modelKey ? $modelKey : null;
    }

    public static function getTableName(string $model): string {
        $tableName = self::findModel($model)->getTable();

        return $tableName;
    }
}