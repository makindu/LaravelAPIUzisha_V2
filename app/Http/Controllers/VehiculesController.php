<?php

namespace App\Http\Controllers;

use App\Models\vehicules;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorevehiculesRequest;
use App\Http\Requests\UpdatevehiculesRequest;
use Illuminate\Http\Request;

class VehiculesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterpriseid)
    {
          //take all the invoices for a single enterprise
          try {
            $vehicules=vehicules::where("enterprise_id","=",$enterpriseid)->paginate(20);
            $vehicules->getCollection()->transform(function ($vehicule){
                return $this->show($vehicule);
            });
            return  response()->json([
                "status"=>200,
                "message"=>"success",
                "error"=>null,
                "data"=>$vehicules
            ]);
        } catch (\Throwable $th) {
            return  response()->json([
                "status"=>500,
                "message"=>"error occured",
                "error"=>$th,
                "data"=>null
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

    public function searchbywords(Request $request){
         //take all the invoices for a single enterprise
         try {
            $invoices=vehicules::where("enterprise_id","=",$request['enterpriseid'])->orWhere('numero_immatriculation','LIKE',"%$request->word%")->orWhere('marque','LIKE',"%$request->word%")->paginate(20);
            return  response()->json([
                "status"=>200,
                "message"=>"success",
                "error"=>null,
                "data"=>$invoices
            ]);
        } catch (\Throwable $th) {
            return  response()->json([
                "status"=>500,
                "message"=>"error occured",
                "error"=>$th,
                "data"=>null
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorevehiculesRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorevehiculesRequest $request)
    {
        if (!isset($request['marque']) && !isset($request['customer_id']) && !isset($request['numero_immatriculation'])) {
            return  response()->json([
                "status"=>404,
                "message"=>"fields required",
                "error"=>null,
                "data"=>null
            ]);
        }

        try {
            $request['uuid']=$this->getUuId("C","VH");
            $result = vehicules::create($request->all());
            return  response()->json([
                "status"=>200,
                "message"=>"success",
                "error"=>null,
                "data"=>$this->show($result)
            ]);
        } catch (\Throwable $th) {
            return  response()->json([
                "status"=>500,
                "message"=>"error occured",
                "error"=>$th,
                "data"=>null
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\vehicules  $vehicules
     * @return \Illuminate\Http\Response
     */
    public function show(vehicules $vehicules)
    {
        return vehicules::join('customer_controllers as C','vehicules.customer_id','=','C.id')->where('vehicules.id','=',$vehicules->id)->get(['C.uuid as customer_uuid','C.customerName as customer_name','C.phone as customer_phone','vehicules.*'])->first();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\vehicules  $vehicules
     * @return \Illuminate\Http\Response
     */
    public function edit(vehicules $vehicules)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatevehiculesRequest  $request
     * @param  \App\Models\vehicules  $vehicules
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatevehiculesRequest $request, vehicules $vehicules)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\vehicules  $vehicules
     * @return \Illuminate\Http\Response
     */
    public function destroy(vehicules $vehicules)
    {
        //
    }
}
