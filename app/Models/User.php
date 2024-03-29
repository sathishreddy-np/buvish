<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'branch_id',
        'name',
        'email',
        'password',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'limits' => 'json',
    ];

    // Only These Can Access Admin Panel
    public function canAccessPanel(Panel $panel): bool
    {
        // If email not verified then this will send email
        if (! $this->hasVerifiedEmail()) {
            $this->sendEmailVerificationNotification();
            Notification::make()
                ->title('Email sent. Please verify the email with in 60 minutes.')
                ->success()
                ->send();

            return false;
        }

        if (! $this->is_active) {

            Notification::make()
                ->title('Your account is inactive. Please contact administrator.')
                ->warning()
                ->send();

            return false;
        }

        return $this->hasVerifiedEmail();
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class)->withTrashed();
    }

    public function superAdminEmail()
    {
        $super_admin = $this->where('company_id', auth()->user()->company_id)
            ->where('branch_id', null)
            ->role('Super Admin')
            ->first();

        return ($super_admin && ($super_admin->email != auth()->user()->email)) ? $super_admin->email : null;
    }

    public function adminEmail()
    {
        $admin = $this->where('branch_id', auth()->user()->branch_id)
            ->role('Admin')
            ->first();

        return ($admin && ($admin->email != auth()->user()->email)) ? $admin->email : null;
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
