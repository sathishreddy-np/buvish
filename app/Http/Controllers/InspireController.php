<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInspireRequest;
use App\Http\Requests\UpdateInspireRequest;
use App\Models\Inspire;
use Illuminate\Support\Facades\Artisan;

class InspireController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Artisan::call('inspire');
        $quote = Artisan::output();

        return response()->json([
            'status' => 200,
            'data' => $quote,
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
     * @param  \App\Http\Requests\StoreInspireRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreInspireRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Inspire  $inspire
     * @return \Illuminate\Http\Response
     */
    public function show(Inspire $inspire)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Inspire  $inspire
     * @return \Illuminate\Http\Response
     */
    public function edit(Inspire $inspire)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateInspireRequest  $request
     * @param  \App\Models\Inspire  $inspire
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateInspireRequest $request, Inspire $inspire)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Inspire  $inspire
     * @return \Illuminate\Http\Response
     */
    public function destroy(Inspire $inspire)
    {
        //
    }
}
