<?php

namespace App\Policies;

use App\Models\BookingTiming;
use App\Models\User;

class BookingTimingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('BookingTimings :: viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BookingTiming $bookingTiming): bool
    {
        return $user->hasPermissionTo('BookingTimings :: view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('BookingTimings :: create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BookingTiming $bookingTiming): bool
    {
        return $user->hasPermissionTo('BookingTimings :: update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BookingTiming $bookingTiming): bool
    {
        return $user->hasPermissionTo('BookingTimings :: delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, BookingTiming $bookingTiming): bool
    {
        return $user->hasPermissionTo('BookingTimings :: restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, BookingTiming $bookingTiming): bool
    {
        return $user->hasPermissionTo('BookingTimings :: forceDelete');
    }
}
