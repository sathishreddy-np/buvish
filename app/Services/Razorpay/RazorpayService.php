<?php

namespace App\Services\Razorpay;

use Illuminate\Support\Facades\Http;

class RazorpayService
{
    public function createQr($params)
    {
        $response = Http::withBasicAuth(config('app.razorpay_key_id'), config('app.razorpay_key_secret'))
            ->post('https://api.razorpay.com/v1/payments/qr_codes', $params)
            ->json();

        return $response;
    }

    public function fetchQr($params)
    {
        $response = Http::withBasicAuth(config('app.razorpay_key_id'), config('app.razorpay_key_secret'))
            ->get('https://api.razorpay.com/v1/payments/qr_codes/'.$params['qr_code_id'].'')
            ->json();

        return $response;
    }
}
