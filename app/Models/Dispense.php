<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispense extends Model
{
    use HasFactory;

    protected $fillable = ['machine_id', 'beverage_id', 'status', 'straw', 'lid'];
}
