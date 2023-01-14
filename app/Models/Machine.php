<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Machine extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = ['machine_name'];

    public function beverages()
    {
        return $this->belongsToMany(Beverage::class);
    }
}
