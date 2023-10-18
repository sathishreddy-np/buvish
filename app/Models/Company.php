<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    // assign Admin role when creating company
    public static function boot()
    {
        parent::boot();

        self::created(function ($model) {
            $session_team_id = getPermissionsTeamId();
            setPermissionsTeamId($model);
            User::find(auth()->user()->id)->assignRole('admin');
            setPermissionsTeamId($session_team_id);
        });
    }
}
