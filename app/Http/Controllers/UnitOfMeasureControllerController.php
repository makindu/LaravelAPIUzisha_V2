<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UnitOfMeasureController;
use App\Http\Requests\StoreUnitOfMeasureControllerRequest;
use App\Http\Requests\UpdateUnitOfMeasureControllerRequest;
use App\Models\ServicesController;
use Exception;
use Illuminate\Support\Facades\DB;

class UnitOfMeasureControllerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterprise_id)
    {
        $list=collect(UnitOfMeasureController::where('enterprise_id','=',$enterprise_id)->get());
        $list->transform(function ($uom){
            return $this->show($uom);
        });

        return $list;
    }

    /**
     * services by UOM
     */
    public function servicesbyuom($uomid){
        try {
            $servicectrl = new ServicesControllerController();
            $list=ServicesController::where('uom_id',$uomid)->orderby('name')->paginate(20);
            $list->getCollection()->transform(function ($service) use ($servicectrl){
                return $servicectrl->show($service);
            });

            return $list;
        } catch (Exception $th) {
            return response()->json([
                'message'=>'error',
                'status'=>500,
                'error'=>$th->getMessage(),
                'data'=>null
            ]);
        }
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
        $services=ServicesController::select(DB::raw('count(id) as nbrservices'))
        ->where('uom_id','=',$unitOfMeasureController->id)
        ->get()->first();

        $unitOfMeasureController['nbrservices']=$services['nbrservices'];
        return $unitOfMeasureController;
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
