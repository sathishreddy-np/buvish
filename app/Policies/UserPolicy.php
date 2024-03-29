<?php

namespace App\Policies;

use App\Models\User;
use App\Services\UserLimitService;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('Users :: viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->hasPermissionTo('Users :: view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if (auth()->check()) {
            $resource_limit = UserLimitService::userLimits($user);

            return $user->hasPermissionTo('Users :: create') && $resource_limit;

        }
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return $user->hasPermissionTo('Users :: update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->hasPermissionTo('Users :: delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->hasPermissionTo('Users :: restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->hasPermissionTo('Users :: forceDelete');
    }
}
