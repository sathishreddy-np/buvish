<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Razorpay extends Model
{
    use HasFactory;

    protected $fillable = ['qr_code_id', 'machine_id', 'beverage_id', 'amount', 'qr_code_image', 'status', 'response'];
}
