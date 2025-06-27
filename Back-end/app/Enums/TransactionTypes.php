<?php
namespace App\Enums;

enum TransactionTypes: string{
    case INCRIMENT = 'incriment';
    case DECRIMENT = 'decriment';

    public function getPersianType(): string {
        return match($this) {
            self::INCRIMENT => 'افزایش',
            self::DECRIMENT => 'کاهش'
        };
    }
}