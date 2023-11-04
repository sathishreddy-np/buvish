<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;

class UserLimitService
{
    public static function userLimits($user)
    {
        $company_id = auth()->user()->company_id;
        $users = User::where('company_id', $company_id)->get();
        $existing_users_count = $users->count();
        if ($user->limit) {
            $can_have_users = $user->limits['users'];

        } else {
            $can_have_users = 5;
        }
        if ($existing_users_count < $can_have_users) {
            $with_in_user_limit = true;
        } else {
            $with_in_user_limit = false;
        }

        return $with_in_user_limit;

    }

    public static function roleLimits($user)
    {
        $existing_roles_count = $user->roles()->count();
        if ($user->limit) {
            $can_have_roles = $user->limits['roles'];

        } else {
            $can_have_roles = 5;
        }
        if ($existing_roles_count < $can_have_roles) {
            $with_in_role_limit = true;
        } else {
            $with_in_role_limit = false;
        }

        return $with_in_role_limit;

    }

    public static function companyLimits($user)
    {
        $existing_companies_count = $user->companies()->count();
        if ($user->limit) {
            $can_have_companies = $user->limits['companies'];

        } else {
            $can_have_companies = 1;
        }
        if ($existing_companies_count < $can_have_companies) {
            $with_in_company_limit = true;
        } else {
            $with_in_company_limit = false;
        }

        return $with_in_company_limit;

    }

    public static function branchLimits($user)
    {
        $company = Company::where('user_id', $user->id)->first();
        if ($company) {
            $existing_branches_count = $company->branches()->count();
            if ($user->limits) {
                $can_have_branches = $user->limits['branches'];
            } else {
                $can_have_branches = 5;
            }
            if ($existing_branches_count < ($can_have_branches)) {
                $with_in_branch_limit = true;
            } else {
                $with_in_branch_limit = false;
            }
        }

        return $with_in_branch_limit;

    }
}
