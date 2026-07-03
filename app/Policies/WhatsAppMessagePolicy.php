<?php

namespace App\Policies;

use App\Models\User;

class WhatsAppMessagePolicy
{
    public function create(User $user): bool
    {
        return true;
    }
}
