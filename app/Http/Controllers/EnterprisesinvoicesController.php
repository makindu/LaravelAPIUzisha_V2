<?php

namespace App\Http\Controllers;

use App\Models\enterprisesinvoices;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreenterprisesinvoicesRequest;
use App\Http\Requests\UpdateenterprisesinvoicesRequest;
use Exception;
use PhpParser\Node\Stmt\TryCatch;

class EnterprisesinvoicesController extends Controller
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
            $invoices=enterprisesinvoices::where("enterprise_id","=",$enterpriseid)->get();
            return  response()->json([
                "status"=>200,
                "message"=>"success",
                "error"=>null,
                "data"=>$invoices
            ]);
        } catch (Exception $th) {
            return  response()->json([
                "status"=>500,
                "message"=>"error occured",
                "error"=>$th->getMessage(),
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreenterprisesinvoicesRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreenterprisesinvoicesRequest $request)
    {
        try {
            $request['uuid']=$this->getUuId("C","EI");
            
            $request['from']=date("Y-m-d");
            $datefromtimestamp=strtotime($request['from']);
            $datefin=date('Y-m-d',strtotime('+'.$request['nbrmonth'].'month',$datefromtimestamp));
            $request['to']=$datefin;
            $result = enterprisesinvoices::create($request->all());
            return  response()->json([
                "status"=>200,
                "message"=>"success",
                "error"=>null,
                "data"=>$result
            ]);
        } catch (Exception $th) {
            return  response()->json([
                "status"=>500,
                "message"=>"error occured",
                "error"=>$th->getMessage(),
                "data"=>null
            ]);
        }
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\enterprisesinvoices  $enterprisesinvoices
     * @return \Illuminate\Http\Response
     */
    public function show(enterprisesinvoices $enterprisesinvoices)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\enterprisesinvoices  $enterprisesinvoices
     * @return \Illuminate\Http\Response
     */
    public function edit(enterprisesinvoices $enterprisesinvoices)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateenterprisesinvoicesRequest  $request
     * @param  \App\Models\enterprisesinvoices  $enterprisesinvoices
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateenterprisesinvoicesRequest $request, enterprisesinvoices $enterprisesinvoices)
    {
        try {
            $find=enterprisesinvoices::find($enterprisesinvoices['1']);
            if ($find) {
                $result =$find->update($request->all());
                return  response()->json([
                    "status"=>200,
                    "message"=>"success",
                    "error"=>null,
                    "data"=>$result
                ]);
            }else{
                return  response()->json([
                    "status"=>404,
                    "message"=>"not find",
                    "error"=>null,
                    "data"=>$enterprisesinvoices
                ]);
            }
           
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\enterprisesinvoices  $enterprisesinvoices
     * @return \Illuminate\Http\Response
     */
    public function destroy(enterprisesinvoices $enterprisesinvoices)
    {
        //
    }
}
