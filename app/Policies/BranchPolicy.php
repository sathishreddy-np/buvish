<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BranchPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('Branches :: viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Branch $branch): bool
    {
        return $user->hasPermissionTo('Branches :: view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        $company = Company::where('user_id', $user->id)->first();
        if($company){
            $existing_branches_count = $company->branches()->count();
            if($user->limits){
                $can_have_branches = $user->limits['branches'];
            }else{
                $can_have_branches = 5;
            }
            if($existing_branches_count < ($can_have_branches)){
                $with_in_branch_limit = true;
            }else{
                $with_in_branch_limit = false;
            }
        }


        return $user->hasPermissionTo('Branches :: create') && $with_in_branch_limit;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Branch $branch): bool
    {
        return $user->hasPermissionTo('Branches :: update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Branch $branch): bool
    {
        return $user->hasPermissionTo('Branches :: delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Branch $branch): bool
    {
        return $user->hasPermissionTo('Branches :: restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Branch $branch): bool
    {
        return $user->hasPermissionTo('Branches :: forceDelete');
    }
}
