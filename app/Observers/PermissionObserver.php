<?php

namespace App\Observers;

use Spatie\Permission\Models\Permission;

class PermissionObserver
{
    /**
     * Handle the Role "creating" event.
     */
    public function creating(Permission $permission): void
    {
        if (auth()->check()) {
            $permission->guard_name = 'web';
        }
    }

    /**
     * Handle the Permission "created" event.
     */
    public function created(Permission $permission): void
    {
        //
    }

    /**
     * Handle the Permission "updated" event.
     */
    public function updated(Permission $permission): void
    {
        //
    }

    /**
     * Handle the Permission "deleted" event.
     */
    public function deleted(Permission $permission): void
    {
        //
    }

    /**
     * Handle the Permission "restored" event.
     */
    public function restored(Permission $permission): void
    {
        //
    }

    /**
     * Handle the Permission "force deleted" event.
     */
    public function forceDeleted(Permission $permission): void
    {
        //
    }
}
