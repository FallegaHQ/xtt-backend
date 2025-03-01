<?php

namespace App\QueryFilters;

final class BalanceQueryFilter extends AbstractQueryFilter{
    protected array $dateFilterFields = [''];
    protected array $likeFilterFields = ['currncy'];
    protected array $boolFilterFields = [];
}