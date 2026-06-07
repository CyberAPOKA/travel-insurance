<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteTraveler extends Model
{
    protected $fillable = [
        'quote_id',
        'user_id',
        'name',
        'birth_date',
        'add_ons',
        'age',
        'subtotal',
        'applied_add_ons',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'add_ons' => 'array',
            'subtotal' => 'decimal:2',
            'applied_add_ons' => 'array',
        ];
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
