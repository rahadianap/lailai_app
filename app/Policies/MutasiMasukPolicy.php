<?php

namespace App\Policies;

use App\Models\User;

class MutasiMasukPolicy
{
    public function view(User $user)
    {
        return in_array($user->role, ['admin', 'manager']);
    }

    public function create(User $user)
    {
        return in_array($user->role, ['admin', 'manager']);
    }

    public function update(User $user)
    {
        return $user->role === 'admin';
    }

    public function delete(User $user)
    {
        return $user->role === 'admin';
    }

    public function approve(User $user)
    {
        return $user->role === 'admin';
    }
}
