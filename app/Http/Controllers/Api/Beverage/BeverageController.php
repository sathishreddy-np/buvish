<?php

namespace App\Http\Controllers\Api\Beverage;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBeverageRequest;
use App\Http\Requests\UpdateBeverageRequest;
use App\Models\Beverage;

class BeverageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $beverages = Beverage::all();

        return response()->json([
            'status' => 200,
            'data' => $beverages,
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
     * @param  \App\Http\Requests\StoreBeverageRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBeverageRequest $request)
    {
        // return $request->all();
        $beverage = Beverage::firstOrCreate([
            'beverage_name' => $request->beverage_name,
            'beverage_price' => $request->beverage_price,
            'beverage_points' => $request->beverage_points,
            'beverage_image_url' => $request->beverage_image_url,
        ]);

        return response()->json([
            'status' => 201,
            'data' => $beverage,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Beverage  $beverage
     * @return \Illuminate\Http\Response
     */
    public function show(Beverage $beverage)
    {
        return response()->json([
            'status' => 200,
            'data' => $beverage,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Beverage  $beverage
     * @return \Illuminate\Http\Response
     */
    public function edit(Beverage $beverage)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateBeverageRequest  $request
     * @param  \App\Models\Beverage  $beverage
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBeverageRequest $request, Beverage $beverage)
    {
        $beverage->beverage_name = $request->beverage_name;
        $beverage->beverage_price = $request->beverage_price;
        $beverage->beverage_points = $request->beverage_points;
        $beverage->beverage_image_url = $request->beverage_image_url;
        $beverage->save();

        return response()->json([
            'status' => 200,
            'data' => $beverage,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Beverage  $beverage
     * @return \Illuminate\Http\Response
     */
    public function destroy(Beverage $beverage)
    {
        $beverage = $beverage->delete();

        return response()->json([
            'status' => 200,
            'data' => $beverage,
        ]);
    }
}
