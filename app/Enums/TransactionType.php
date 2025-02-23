<?php

namespace App\Enums;

enum TransactionType: string{
    case Other          = 'other';
    case Groceries      = 'groceries';
    case Housing        = 'housing';
    case Utilities      = 'utilities';
    case Transportation = 'transportation';
    case Entertainment  = 'entertainment';
    case Healthcare     = 'healthcare';
    case Gifts          = 'gifts';
    case Debt           = 'debt';
    case Subscriptions  = 'subscriptions';
}
