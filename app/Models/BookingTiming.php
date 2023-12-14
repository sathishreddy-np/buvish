<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingTiming extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = ['timings' => 'json'];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}
