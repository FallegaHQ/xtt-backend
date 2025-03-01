<?php

namespace App\Models;

use App\Enums\TransactionType;
use App\QueryFilters\TransactionQueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static where(string $string, int $id)
 */
class Transaction extends Model{
    /** @use HasFactory<\Database\Factories\TransactionFactory> */
    use HasFactory, HasTimestamps;

    protected $fillable = [
        'type',
        'amount',
        'description',
        'date',
    ];

    /**
     * @return BelongsTo<Balance, Transaction>
     */
    public function balance(): BelongsTo{
        return $this->belongsTo(Balance::class);
    }

    public function scopeFilter(Builder $query, array $filters = []): Builder{
        return (new TransactionQueryFilter($this->getTable(), $this->fillable))->apply($query, $filters);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array{
        return [
            'date' => 'datetime',
            'type' => TransactionType::class,
        ];
    }
}
