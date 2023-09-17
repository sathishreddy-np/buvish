<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = ['user_id','name', 'email'];


    protected static function booted(): void
    {
        if (auth()->check()) {
            static::addGlobalScope('user', function (Builder $query) {
                $query->where('user_id', auth()->user()->id);
            });
        }
    }
}
