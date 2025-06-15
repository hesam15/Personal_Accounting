<?php
namespace App\Enums;

enum TransactionTypes: string{
    case INCRIMENT = 'incriment';
    case DECRIMENT = 'decriment';
}