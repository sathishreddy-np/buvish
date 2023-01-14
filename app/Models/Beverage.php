<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Beverage extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = ['beverage_name', 'beverage_price', 'beverage_image_url'];

    public function machines()
    {
        return $this->belongsToMany(Machine::class);
    }
}
