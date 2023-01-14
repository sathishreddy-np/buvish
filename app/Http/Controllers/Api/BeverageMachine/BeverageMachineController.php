<?php

namespace App\Http\Controllers\Api\BeverageMachine;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBeverageMachineRequest;
use App\Http\Requests\UpdateBeverageMachineRequest;
use App\Models\BeverageMachine;
use App\Models\Machine;
use Illuminate\Http\Request;

class BeverageMachineController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Machine $machine)
    {
        return $machine->beverages()->get();
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
     * @param  \App\Http\Requests\StoreBeverageMachineRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBeverageMachineRequest $request,Machine $machine)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BeverageMachine  $beverageMachine
     * @return \Illuminate\Http\Response
     */
    public function show(BeverageMachine $beverageMachine)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BeverageMachine  $beverageMachine
     * @return \Illuminate\Http\Response
     */
    public function edit(BeverageMachine $beverageMachine)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateBeverageMachineRequest  $request
     * @param  \App\Models\BeverageMachine  $beverageMachine
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBeverageMachineRequest $request, BeverageMachine $beverageMachine, Machine $machine)
    {
        $beverage_ids = explode(',',$request->beverage_ids);
        return $machine->beverages()->sync($beverage_ids);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BeverageMachine  $beverageMachine
     * @return \Illuminate\Http\Response
     */
    public function destroy(BeverageMachine $beverageMachine, Machine $machine, Request $request)
    {

    }
}
