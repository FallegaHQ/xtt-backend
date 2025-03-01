<?php

namespace App\Models;

use App\Enums\Currencies;
use App\QueryFilters\BalanceQueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Balance extends Model{
    /** @use HasFactory<\Database\Factories\BalanceFactory> */
    use HasFactory, HasTimestamps;

    protected $fillable = [
        'user_id',
        'balance',
        'description',
        'currency',
    ];

    /**
     * @return BelongsTo<User, Balance>
     */
    public function user(): BelongsTo{
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Transaction, Balance>
     */
    public function transactions(): HasMany{
        return $this->hasMany(Transaction::class);
    }

    public function scopeFilter(Builder $query, array $filters = []): Builder{
        return (new BalanceQueryFilter($this->getTable(), $this->fillable))->apply($query, $filters);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array{
        return [
            'currency' => Currencies::class,
        ];
    }
}
