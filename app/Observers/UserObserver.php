<?php

namespace App\Observers;

use App\Models\Company;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
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
        $url= url()->previous();
        if (str_contains($url,'admin/register')) {
            $company = Company::create(['name' => 'Main Branch', 'user_id' => $user->id]);
            if ($company) {
                $user->company_id = $company->id;
                $user->update(['company_id' => $company->id]);

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

                // $user->sendEmailVerificationNotification();

                Notification::make()
                ->title("Email invite sent. Please verify email your email.")
                ->success()
                ->send();

                if(Cookie::has('buvish_session')){
                    Cookie::queue(Cookie::forget('buvish_session'));
                }
            }
        } else if (str_contains($url,'admin/users/create')) {
            try {
                $user->update(['company_id' => auth()->user()->company_id]);

                // $email_sent = $user->sendEmailVerificationNotification();
            } catch (\Throwable $th) {
                Notification::make()
                    ->title("Email invite not sent.Please contact info@buvish.com .")
                    ->success()
                    ->send();
            }

            // if($email_sent){
            //     Notification::make()
            //     ->title("Email invite sent. Ask $user->name to verify their email to log in.")
            //     ->success()
            //     ->send();
            // }
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
