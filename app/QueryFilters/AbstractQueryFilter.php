<?php

namespace App\QueryFilters;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use function explode;
use function in_array;
use function is_array;
use function is_string;
use function strtolower;

class AbstractQueryFilter{
    /**
     * Fields that should be treated as dates for filtering
     *
     * @var array
     */
    protected array $dateFilterFields = [];

    /**
     * Fields that should be treated as boolean for filtering
     *
     * @var array
     */
    protected array $boolFilterFields = [];

    /**
     * Fields that should use LIKE operator for filtering
     *
     * @var array
     */
    protected array $likeFilterFields = [];

    /**
     * Fields that are fillable/filterable
     *
     * @var array
     */
    protected array $fillableFields = [];

    /**
     * Table name for the model
     *
     * @var string
     */
    protected string $tableName;

    public function __construct(string $tableName, array $fillableFields){
        $this->tableName      = $tableName;
        $this->fillableFields = $fillableFields;
    }

    /**
     * Apply filters to the query builder
     *
     * @param Builder $builder
     * @param array   $filters
     *
     * @return Builder
     */
    public function apply(Builder $builder, array $filters = []): Builder{
        if(!$filters){
            return $builder;
        }
        foreach($filters as $field => $value){
            if(!in_array($field, $this->fillableFields) || !$value){
                continue;
            }

            if(in_array($field, $this->boolFilterFields) && $value != null){
                $builder->where($this->tableName . '.' . $field, (bool) $value);
                continue;
            }

            if(in_array($field, $this->dateFilterFields)){
                $this->handleDateFilter($builder, $field, $value);
                continue;
            }

            if(in_array($field, $this->likeFilterFields)){
                $builder->where("$this->tableName.$field", 'LIKE', "%$value%");
            }
            else if(is_array($value)){
                $builder->whereIn($field, $value);
            }
            else{
                $builder->where($field, $value);
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
    protected function handleDateFilter(Builder $builder, string $field, mixed $value): void{
        if(is_string($value)){
            if(str_contains($value, ':')){
                [
                    $operator,
                    $date,
                ] = explode(':', $value, 2);
                $this->applyDateOperator($builder, $field, $operator, $date);
            }
            else{
                $builder->whereDate("$this->tableName.$field", Carbon::parse($value));
            }

            return;
        }

        // Handle array/object of conditions
        if(is_array($value)){
            // Handle date range with start/end
            if(isset($value['start'], $value['end'])){
                $builder->whereBetween($this->tableName . '.' . $field, [
                    Carbon::parse($value['start'])
                          ->startOfDay(),
                    Carbon::parse($value['end'])
                          ->endOfDay(),
                ]);

                return;
            }

            // Handle individual operators
            foreach($value as $operator => $date){
                $this->applyDateOperator($builder, $field, $operator, $date);
            }
        }
    }

    /**
     * Apply a specific date operator to the query
     *
     * @param Builder $builder
     * @param string  $field
     * @param string  $operator
     * @param string  $dateIn
     *
     * @return void
     */
    protected function applyDateOperator(Builder $builder, string $field, string $operator, string $dateIn): void{
        $date = Carbon::parse($dateIn);

        switch(strtolower($operator)){
            case 'before':
            case 'until':
                $builder->whereDate($this->tableName . '.' . $field, '<=', $date);
                break;

            case 'after':
            case 'since':
                $builder->whereDate($this->tableName . '.' . $field, '>=', $date);
                break;

            case 'on':
                $builder->whereDate($this->tableName . '.' . $field, $date);
                break;
        }
    }
}