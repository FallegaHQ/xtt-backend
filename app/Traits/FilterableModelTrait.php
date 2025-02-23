<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use function is_array;

trait FilterableModelTrait{
    /**
     * add filtering.
     *
     * @param Builder $builder : query builder.
     * @param array   $filters : array of filters.
     *
     * @return Builder query builder.
     */
    public function scopeFilter(Builder $builder, array $filters = []): Builder{
        if(!$filters){
            return $builder;
        }
        $tableName             = $this->getTable();
        $defaultFillableFields = $this->fillable;
        foreach($filters as $field => $value){
            if(is_array($this->boolFilterFields)){
                if(in_array($field, $this->boolFilterFields) && $value != null){
                    $builder->where($field, (bool) $value);
                    continue;
                }
            }

            if(is_array($this->dateFilterFields)){
                if(is_array($this->dateFilterFields) && in_array($field, $this->dateFilterFields)){
                     $this->handleDateFilter($builder, $field, $value);
                    continue;
                }
            }

            if(!in_array($field, $defaultFillableFields) || !$value){
                continue;
            }

            if(is_array($this->likeFilterFields)){
                if(in_array($field, $this->likeFilterFields)){
                    $builder->where($tableName . '.' . $field, 'LIKE', "%$value%");
                }
                else if(is_array($value)){
                    $builder->whereIn($field, $value);
                }
                else{
                    $builder->where($field, $value);
                }
            }
        }

        return $builder;
    }

    /**
     * Handle date filtering with various operations:
     * // 1. Exact date match
     *
     *  // 2. Date range using start and end
     *
     *  // 3. Using operators
     *
     *  // After a specific date
     *
     *  // Before a specific date
     *
     *  // On a specific date
     *
     *  // 4. Combining with other filters
     *
     *
     * @param Builder $builder
     * @param string  $field
     * @param mixed   $value
     *
     * @return Builder
     * @example
     * {
     *      "date": "2024-02-08"
     * }
     *
     * @example
     * {
     *      "date": {
     *      "start": "2024-01-01",
     *      "end": "2024-02-08"
     * }
     * }
     *
     * @example
     * {
     *      "date": "after:2024-01-01"
     * }
     *
     * @example
     * {
     *      "date": "before:2024-02-08"
     * }
     *
     * @example
     * {
     *      "date": "on:2024-02-08"
     * }
     *
     * @example
     * {
     *      "date": "after:2024-01-01",
     *      "description": "groceries",
     *      "type": "expense"
     * }
     *
     */
    protected function handleDateFilter(Builder $builder, string $field, mixed $value): void
    {
        if (is_string($value)) {
            if (str_contains($value, ':')) {
                [$operator, $date] = explode(':', $value, 2);
                $this->applyDateOperator($builder, $field, $operator, $date);
            } else {
                $builder->whereDate($field, Carbon::parse($value));
            }
            return;
        }

        // Handle array/object of conditions
        if (is_array($value)) {
            // Handle date range with start/end
            if (isset($value['start']) && isset($value['end'])) {
                $builder->whereBetween($field, [
                    Carbon::parse($value['start'])->startOfDay(),
                    Carbon::parse($value['end'])->endOfDay()
                ]);
                return;
            }

            // Handle individual operators
            foreach ($value as $operator => $date) {
                $this->applyDateOperator($builder, $field, $operator, $date);
            }
        }
    }
    /**
     * Apply a specific date operator to the query
     *
     * @param Builder $builder
     * @param string $field
     * @param string $operator
     * @param string $date
     * @return void
     */
    protected function applyDateOperator(Builder $builder, string $field, string $operator, string $date): void
    {
        $date = Carbon::parse($date);

        switch (strtolower($operator)) {
            case 'before':
            case 'until':
                $builder->whereDate($field, '<=', $date);
                break;

            case 'after':
            case 'since':
                $builder->whereDate($field, '>=', $date);
                break;

            case 'on':
                $builder->whereDate($field, $date);
                break;
        }
    }
}
