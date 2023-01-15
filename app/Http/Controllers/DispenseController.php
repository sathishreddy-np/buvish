<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDispenseRequest;
use App\Http\Requests\UpdateDispenseRequest;
use App\Models\Dispense;
use App\Models\Machine;
use Illuminate\Http\Request;

class DispenseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Machine $machine)
    {
        $dispense = Dispense::where('machine_id', $machine->id)->first();

        return response()->json([
            'status' => 200,
            'data' => $dispense,
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
     * @param  \App\Http\Requests\StoreDispenseRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDispenseRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Dispense  $dispense
     * @return \Illuminate\Http\Response
     */
    public function show(Dispense $dispense)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Dispense  $dispense
     * @return \Illuminate\Http\Response
     */
    public function edit(Dispense $dispense)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDispenseRequest  $request
     * @param  \App\Models\Dispense  $dispense
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDispenseRequest $request, Dispense $dispense)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Dispense  $dispense
     * @return \Illuminate\Http\Response
     */
    public function destroy(Dispense $dispense)
    {
    }

    public function dispenseDelete(Request $request)
    {
        $dispense = Dispense::where('machine_id', $request->machine_id)->first();

        if ($dispense) {
            $dispense->delete();

            return response()->json([
                'status' => 200,
                'data' => true,
            ]);
        }
    }
}
