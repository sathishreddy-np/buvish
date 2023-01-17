<?php

namespace App\Http\Controllers\Api\Razorpay;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRazorpayRequest;
use App\Http\Requests\UpdateRazorpayRequest;
use App\Models\Beverage;
use App\Models\Contact;
use App\Models\Machine;
use App\Models\Razorpay;
use App\Services\Razorpay\RazorpayService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RazorpayController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $payments = Razorpay::all();

        return response()->json([
            'status' => 200,
            'data' => $payments,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreRazorpayRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRazorpayRequest $request, RazorpayService $razorpayService, Machine $machine, Beverage $beverage)
    {
        $params = [
            'type' => 'upi_qr',
            'name' => 'Buvish',
            'usage' => 'single_use',
            'fixed_amount' => true,
            'payment_amount' => ($beverage->beverage_price) * 100, // in paise
            'description' => $beverage->id,
            'customer_id' => 'cust_KprnIQzbtuO1m7',
            'close_by' => Carbon::now()->timestamp + 600,
            'notes' => [
                'purpose' => $machine->id,
            ],
        ];

        // return $beverage;

        $response = $razorpayService->createQr($params);

        // return $response;

        $ice = $request->ice == 1 ? 1 : 0;

        $params = [
            'qr_code_id' => $response['id'],
            'machine_id' => $machine->id,
            'beverage_id' => $beverage->id,
            'amount' => $response['payment_amount'],
            'qr_code_image' => $response['image_url'],
            'status' => 0,
            'response' => json_encode($response),
            'straw' => $request->straw,
            'lid' => $request->lid,
            'sugar' => $request->sugar,
            'ice' => $ice,
        ];

        // return ($params);

        Razorpay::create($params);

        $payment = Razorpay::where('machine_id', $machine->id)->orderByDesc('id')->first();

        return response()->json([
            'status' => 201,
            'data' => $payment,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Razorpay  $razorpay
     * @return \Illuminate\Http\Response
     */
    public function show(Razorpay $razorpay)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Razorpay  $razorpay
     * @return \Illuminate\Http\Response
     */
    public function edit(Razorpay $razorpay)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateRazorpayRequest  $request
     * @param  \App\Models\Razorpay  $razorpay
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRazorpayRequest $request, Razorpay $razorpay, RazorpayService $razorpayService)
    {
        $razorpay = Razorpay::where('id', $razorpay->id)->orderByDesc('id')->first();

        $params = ['qr_code_id' => $razorpay->qr_code_id];
        $response = $razorpayService->fetchQr($params);

        if ($response['payments_count_received'] > 0) {
            Razorpay::where('qr_code_id', $razorpay->qr_code_id)
                ->orderByDesc('id')
                ->update(['status' => 1]);

            $razorpay = Razorpay::select('machine_id', 'beverage_id', 'status', 'straw', 'lid', 'sugar', 'ice')
                ->where('qr_code_id', $razorpay->qr_code_id)
                ->orderByDesc('id')->first();

            $razorpayService->storeDispenseDetails($razorpay);

            return response()->json([
                'status' => 200,
                'data' => true,
                'details' => $razorpay,
            ]);
        }

        return response()->json([
            'status' => 200,
            'data' => false,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Razorpay  $razorpay
     * @return \Illuminate\Http\Response
     */
    public function destroy(Razorpay $razorpay)
    {
        //
    }

    public function rewardsPayment(Request $request, RazorpayService $razorpayService)
    {
        $contact = Contact::where('mobile_number', $request->mobile_number)->first();

        if ($request->mobile_number != '' && $request->password == '') {
            if (! $contact) {
                return response()->json([
                    'status' => 200,
                    'data' => 'Not registered. Pay by using QR',
                ]);
            } else {
                return response()->json([
                    'status' => 200,
                    'data' => 'Candidate Registered',
                ]);
            }
        }

        if ($request->mobile_number != '' && $request->password != '') {
            if ($contact) {
                if ($contact->points >= 70) {
                    $params = [
                        'machine_id' => $request->machine_id,
                        'beverage_id' => $request->beverage_id,
                        'status' => 1,
                        'straw' => $request->straw,
                        'lid' => $request->lid,
                        'sugar' => $request->sugar,
                        'ice' => $request->ice,
                    ];

                    $dispense = $razorpayService->storeDispenseDetails($params);

                    $contact->decrement('points', 70);
                    $contact->save();

                    return response()->json([
                        'status' => 200,
                        'data' => true,
                        'details' => $dispense,
                    ]);
                } else {
                    return response()->json([
                        'status' => 200,
                        'data' => 'You dont have sufficient points. Minimum 70 points required.',
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 200,
                    'data' => 'Not registered. Pay by using QR',
                ]);
            }
        }
    }
}
