<?php
namespace App\Enums;

enum BudgetsPeriod: string {
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';
}