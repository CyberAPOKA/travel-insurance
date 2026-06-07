<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Cancelled = 'cancelled';

    public static function fromAsaasStatus(string $status): self
    {
        return match (strtoupper($status)) {
            'RECEIVED', 'CONFIRMED' => self::Paid,
            'OVERDUE' => self::Overdue,
            'REFUNDED', 'DELETED', 'CANCELLED' => self::Cancelled,
            default => self::Pending,
        };
    }

    public function isPaid(): bool
    {
        return $this === self::Paid;
    }
}
