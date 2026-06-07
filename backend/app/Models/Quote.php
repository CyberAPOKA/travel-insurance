<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Quote extends Model
{
    protected $fillable = [
        'user_id',
        'destination',
        'start_date',
        'end_date',
        'charged_days',
        'group_discount_percentage',
        'final_total',
        'warnings',
        'calculation_breakdown',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'final_total' => 'decimal:2',
            'warnings' => 'array',
            'calculation_breakdown' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function travelers(): HasMany
    {
        return $this->hasMany(QuoteTraveler::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(QuotePayment::class);
    }

    /**
     * @param  Builder<Quote>  $query
     * @return Builder<Quote>
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * @param  Builder<Quote>  $query
     * @return Builder<Quote>
     */
    public function scopeLatestForUser(Builder $query): Builder
    {
        return $query->orderByDesc('created_at');
    }
}
