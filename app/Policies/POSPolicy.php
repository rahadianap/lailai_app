<?php

namespace App\Policies;

use App\Models\User;

class POSPolicy
{
    public function view(User $user)
    {
        return in_array($user->role, ['admin', 'manager']);
    }
}
