<?php

namespace App\QueryFilters;

final class TransactionQueryFilter extends AbstractQueryFilter{
    protected array $dateFilterFields = ['date'];
    protected array $likeFilterFields = ['description'];
    protected array $boolFilterFields = [];
}