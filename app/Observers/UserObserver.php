<?php

namespace App\Observers;

use App\Models\Company;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Http\Client\Request;
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
        $company = Company::create(['name' => 'Main Branch', 'user_id' => $user->id]);
        if ($company) {
            $user->company_id = $company->id;
            $user->update(['company_id' => $company->id, 'email_verified_at' => now()]);

            $admin_role = Role::create(
                [
                    'name' => 'Admin',
                    'guard_name' => 'web',
                    'company_id' => $company->id
                ]
            );

            $all_permissions = Permission::all()->pluck('name');

            $admin_role->syncPermissions($all_permissions);

            $user->assignRole($admin_role);
            if (url()->current('/admin/register')){
                // $user->sendEmailVerificationNotification();

                Notification::make()
                ->title("Email invite sent. Please verify email your email.")
                ->success()
                ->send();
            }else{
                Notification::make()
                ->title("Email invite sent. Ask $user->name to verify their email to log in.")
                ->success()
                ->send();
            }
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
