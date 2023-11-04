<?php

namespace App\Observers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cookie;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function creating(User $user): void
    {
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $url = url()->previous();
        if (str_contains($url, 'admin/register')) {
            $company = Company::create(
                [
                    'name' => 'My Company',
                    'user_id' => $user->id,
                ]
            );
            if ($company) {
                Branch::create(
                    [
                        'name' => 'Main Branch',
                        'company_id' => $company->id,
                    ]
                );
                $user->company_id = $company->id;
                $user->update(['company_id' => $company->id]);

                $admin_role = Role::create(
                    [
                        'name' => 'Admin',
                        'guard_name' => 'web',
                        'company_id' => $company->id,
                    ]
                );

                $all_permissions = Permission::all()->pluck('name');

                $admin_role->syncPermissions($all_permissions);
                $user->assignRole($admin_role);

                // $user->sendEmailVerificationNotification();

                if (Cookie::has('buvish_session')) {
                    Cookie::queue(Cookie::forget('buvish_session'));
                }
            }
        } elseif (str_contains($url, 'admin/users/create')) {
            $user->update(['company_id' => auth()->user()->company_id]);

            $user->sendEmailVerificationNotification();
            Notification::make()
                ->title("An email invite has been sent to $user->email. Please verify email.")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title("$user->name :: Please contact info@buvish.com")
                ->success()
                ->send();
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
