<?php

namespace App\Http\Controllers;

use App\Models\reservations;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorereservationsRequest;
use App\Http\Requests\UpdatereservationsRequest;
use App\Models\Cautions;
use App\Models\CustomerController;
use App\Models\ServicesController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterprise)
    {
        $list=collect(reservations::where('enterprise_id','=',$enterprise)->orderby('created_at','asc')->get());
        $listdata=$list->map(function ($item){
            return $this->show($item);
        });
        return $listdata;
    }  
    
    /**
     * Display a listing of the resource by filter.
     *
     * @return \Illuminate\Http\Response
     */
    public function reservationsfilter(Request $request)
    {
        $list=reservations::where('enterprise_id','=',$request['enterprise_id'])
        ->whereIn('status',$request['filters'])
        ->orderby('created_at','asc')->paginate(20);
        $listdata=$list->getCollection()->map(function ($item){
            return $this->show($item);
        });

        return $listdata;
    }

    /**
     * update reservation or change status
     */
    public function changestatus(Request $request){
        //if exists
        $ifexists=reservations::find($request['id']);
        if($ifexists){
            try {
                // $ifexists->update($request);
                DB::update('update reservations set status = ? where id= ?',[$request['status'],$request['id']]);
                $ifexists=reservations::find($request['id']);
                return response()->json([
                    "message"=>"success",
                    "status"=>200,
                    "error"=>null,
                    "data"=>$this->show($ifexists)
                ]);
            } catch (\Throwable $th) {
                return response()->json([
                    "message"=>"error",
                    "status"=>500,
                    "error"=>$th,
                    "data"=>null
                ]);
            }
           
        }else{
            return response()->json([
                "message"=>"not found",
                "status"=>404,
                "error"=>null,
                "data"=>null
            ]);
        }
    }

    /**
     * search 
     */
    public function searchreservations(Request $request){

        $list=collect(reservations::join('customer_controllers as C','reservations.customer_id','=','C.id')
        ->where('reservations.enterprise_id','=',$request['enterprise_id'])
        ->where('C.customerName','LIKE',"%$request->keywor%")
        ->orderby('reservations.created_at','desc')
        ->limit(20)
        ->get('reservations.*'));

        $listdata=$list->map(function ($item){
            return $this->show($item);
        });

        return $listdata;
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
     * @param  \App\Http\Requests\StorereservationsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorereservationsRequest $request)
    {
        $servicectrl = new ServicesControllerController ();
        if (!isset($request['done_at']) || empty($request['done_at'])) {
            $request['done_at']=date('Y-m-d');
        }

        try {
            $request['status']="pending";
            $request['nbr_days']=Carbon::parse($request['from'])->floatDiffInDays($request['to']);
            $request['nbr_days']=$request['nbr_days']+1;
            $request['total']=$request['nbr_days']*$request['price'];
            $result=$this->show(reservations::create($request->all()));
            DB::update('update services_controllers set status = "unavailable" where id= ?',[$request['service_id']]);

            if(isset($request['caution']) && !empty($request['caution']) && $request['caution']>0){
                $caution=Cautions::create([
                    'customer_id'=>$request['customer_id'],
                    'user_id'=>$request['user_id'],
                    'amount'=>$request['caution'],
                    'money_id'=>$this->defaultmoney($request['enterprise_id'])['id'],
                    'amount_used'=>0,
                    'enterprise_id'=>$request['enterprise_id'],
                    'uuid'=>$this->getUuId('CA','C'),
                    'sync_status'=>true,
                    'done_at'=>$request['done_at']
                ]);

                if($caution){
                    //update the caution in customer model
                    DB::update('update customer_controllers set totalcautions = totalcautions + ? where id = ?',[$caution['amount'],$caution['customer_id']]);
                }
            }

           return  response()->json([
                "data"=>$result,
                "service"=>$servicectrl->show(ServicesController::find($request['service_id'])),
                "message"=>"success",
                "status"=>200,
                "error"=>null
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "data"=>null,
                "service"=>$servicectrl->show(ServicesController::find($request['service_id'])),
                "message"=>"error",
                "status"=>500,
                "error"=>$th
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\reservations  $reservations
     * @return \Illuminate\Http\Response
     */
    public function show(reservations $reservations)
    {
        $servicectrl= new ServicesControllerController();
        $customerctrl= new CustomerControllerController();
        $service=$servicectrl->show(ServicesController::find($reservations['service_id']));
        $customer=$customerctrl->show(CustomerController::find($reservations['customer_id']));
        $reservations['customer']=$customer;
        $reservations['service']=$service;
        return $reservations;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\reservations  $reservations
     * @return \Illuminate\Http\Response
     */
    public function edit(reservations $reservations)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatereservationsRequest  $request
     * @param  \App\Models\reservations  $reservations
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatereservationsRequest $request, reservations $reservations)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\reservations  $reservations
     * @return \Illuminate\Http\Response
     */
    public function destroy(reservations $reservations)
    {
        //
    }
}
