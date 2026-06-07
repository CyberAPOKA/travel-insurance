<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotePayment extends Model
{
    protected $fillable = [
        'quote_id',
        'user_id',
        'asaas_payment_id',
        'status',
        'value',
        'due_date',
        'pix_encoded_image',
        'pix_payload',
        'pix_expiration_date',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'value' => 'decimal:2',
            'due_date' => 'date',
            'pix_expiration_date' => 'datetime',
            'paid_at' => 'datetime',
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
