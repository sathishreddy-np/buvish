<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RolePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('Roles :: viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('Roles :: view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        $existing_roles_count = $user->roles()->count();
        if($user->limit){
            $can_have_roles = $user->limits['roles'];

        }else{
            $can_have_roles = 5;
        }
        if($existing_roles_count < $can_have_roles){
            $with_in_role_limit = true;
        }else{
            $with_in_role_limit = false;
        }

        return $user->hasPermissionTo('Roles :: create') && $with_in_role_limit;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('Roles :: update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('Roles :: delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('Roles :: restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('Roles :: forceDelete');
    }
}
