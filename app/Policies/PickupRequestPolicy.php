<?php

namespace App\Policies;

use App\Models\PickupRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PickupRequestPolicy
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
    public function view(User $user, PickupRequest $pickupRequest): bool
    {
        if ($user->role === 'CLIENT') {
            return $user->id === $pickupRequest->client_id;
        }

        if ($user->role === 'DELIVERER') {
            // Deliverers can view:
            // 1. Pending pickups (available for assignment)
            // 2. Pickups assigned to them
            return $pickupRequest->status === 'pending' ||
                   $pickupRequest->assigned_deliverer_id === $user->id;
        }

        if (in_array($user->role, ['COMMERCIAL', 'SUPERVISOR'])) {
            // Commercial and supervisors can view all pickup requests
            return true;
        }

        return false;
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
    public function update(User $user, PickupRequest $pickupRequest): bool
    {
        if ($user->role === 'CLIENT') {
            return $user->id === $pickupRequest->client_id &&
                   in_array($pickupRequest->status, ['pending', 'assigned']);
        }

        if ($user->role === 'DELIVERER') {
            return $pickupRequest->assigned_deliverer_id === $user->id &&
                   $pickupRequest->status === 'assigned';
        }

        if (in_array($user->role, ['COMMERCIAL', 'SUPERVISOR'])) {
            // Commercial and supervisors can update pickup requests
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PickupRequest $pickupRequest): bool
    {
        return $user->role === 'CLIENT' &&
               $user->id === $pickupRequest->client_id &&
               in_array($pickupRequest->status, ['pending', 'assigned']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PickupRequest $pickupRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PickupRequest $pickupRequest): bool
    {
        return false;
    }
}
