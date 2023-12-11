<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingTiming extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = ['timings' => 'json'];
}
