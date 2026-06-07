<?php

namespace App\Policies;

use App\Models\Quote;
use App\Models\User;

class QuotePolicy
{
    public function view(User $user, Quote $quote): bool
    {
        return $quote->user_id === $user->id;
    }

    public function update(User $user, Quote $quote): bool
    {
        return $quote->user_id === $user->id;
    }

    public function pay(User $user, Quote $quote): bool
    {
        return $quote->user_id === $user->id;
    }
}
