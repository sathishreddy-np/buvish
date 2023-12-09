<?php

namespace App\Policies;

use App\Models\NotificationType;
use App\Models\User;

class NotificationTypePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('NotificationTypes :: viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, NotificationType $notificationType): bool
    {
        return $user->hasPermissionTo('NotificationTypes :: view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('NotificationTypes :: create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, NotificationType $notificationType): bool
    {
        return $user->hasPermissionTo('NotificationTypes :: update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, NotificationType $notificationType): bool
    {
        return $user->hasPermissionTo('NotificationTypes :: delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, NotificationType $notificationType): bool
    {
        return $user->hasPermissionTo('NotificationTypes :: restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, NotificationType $notificationType): bool
    {
        return $user->hasPermissionTo('NotificationTypes :: forceDelete');
    }
}
