<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UnitOfMeasureController;
use App\Http\Requests\StoreUnitOfMeasureControllerRequest;
use App\Http\Requests\UpdateUnitOfMeasureControllerRequest;

class UnitOfMeasureControllerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterprise_id)
    {
        return UnitOfMeasureController::where('enterprise_id','=',$enterprise_id)->get();
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
     * @param  \App\Http\Requests\StoreUnitOfMeasureControllerRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUnitOfMeasureControllerRequest $request)
    {
        return UnitOfMeasureController::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UnitOfMeasureController  $unitOfMeasureController
     * @return \Illuminate\Http\Response
     */
    public function show(UnitOfMeasureController $unitOfMeasureController)
    {
        return UnitOfMeasureController::find($unitOfMeasureController);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UnitOfMeasureController  $unitOfMeasureController
     * @return \Illuminate\Http\Response
     */
    public function edit(UnitOfMeasureController $unitOfMeasureController)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateUnitOfMeasureControllerRequest  $request
     * @param  \App\Models\UnitOfMeasureController  $unitOfMeasureController
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUnitOfMeasureControllerRequest $request, UnitOfMeasureController $unitOfMeasureController)
    {
        return $this->show(unitOfMeasureController::find($unitOfMeasureController->update($request->all())));
    }

    public function update2(Request $request,$id)
    {
        $uom=UnitOfMeasureController::find($id);
        $uom->update($request->all());
        return $uom;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UnitOfMeasureController  $unitOfMeasureController
     * @return \Illuminate\Http\Response
     */
    public function destroy(UnitOfMeasureController $unitOfMeasureController)
    {
        return UnitOfMeasureController::destroy($unitOfMeasureController);
    }
}
