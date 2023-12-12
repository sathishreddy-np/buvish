<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = ['user_id', 'branch_id', 'name', 'email', 'is_active'];

    // Show Only User Created Records. You Can Check By Using Role.
    protected static function booted(): void
    {
        if (auth()->check()) {
            static::addGlobalScope('user', function (Builder $query) {
                $query->where('user_id', auth()->user()->id);
            });
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function notificationTypes(): BelongsToMany
    {
        return $this->belongsToMany(NotificationType::class);
    }

    public function bookings(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class);
    }
}
