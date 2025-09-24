<?php

namespace App\Policies;

use App\Models\ClientPickupAddress;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClientPickupAddressPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'CLIENT';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ClientPickupAddress $clientPickupAddress): bool
    {
        return $user->role === 'CLIENT' && $user->id === $clientPickupAddress->client_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'CLIENT';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ClientPickupAddress $clientPickupAddress): bool
    {
        return $user->role === 'CLIENT' && $user->id === $clientPickupAddress->client_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ClientPickupAddress $clientPickupAddress): bool
    {
        return $user->role === 'CLIENT' && $user->id === $clientPickupAddress->client_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ClientPickupAddress $clientPickupAddress): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ClientPickupAddress $clientPickupAddress): bool
    {
        return false;
    }
}
