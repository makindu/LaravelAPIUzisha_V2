<?php


namespace App\Http\Controllers;

use App\Models\requestapprovments;
use App\Http\Requests\StorerequestapprovmentsRequest;
use App\Http\Requests\UpdaterequestapprovmentsRequest;
use App\Models\DepositServices;
use App\Models\StockHistoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequestapprovmentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterprise_id)
    {
        //collect all transferts
        $list=collect(requestapprovments::where('enterprise_id','=',$enterprise_id)->get());
        $listdata=$list->map(function ($item,$key){
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
     * @param  \App\Http\Requests\StorerequestapprovmentsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorerequestapprovmentsRequest $request)
    {
        return $this->show(requestapprovments::create($request->all()));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\requestapprovments  $requestapprovments
     * @return \Illuminate\Http\Response
     */
    public function show(requestapprovments $requestapprovments)
    {
        //
        return requestapprovments::leftjoin('deposit_controllers as DS','requestapprovments.deposit_sender_id','=','DS.id')
        ->leftjoin('deposit_controllers as DR','requestapprovments.deposit_receiver_id','=','DR.id')
        ->leftjoin('users as US','requestapprovments.sender_id','=','US.id')
        ->leftjoin('users as UR','requestapprovments.receiver_id','=','UR.id')
        ->leftjoin('services_controllers as S','requestapprovments.service_id','=','S.id')
        ->leftjoin('unit_of_measure_controllers as UM','S.uom_id','=','UM.id')
        ->where('requestapprovments.id','=',$requestapprovments['id'])->get(['requestapprovments.*','UM.name as uom_name','UM.symbol as uom_symbol','S.name as service_name','S.description as service_description','DS.name as deposit_sender_name','DR.name as  deposit_receiver_name','US.user_name as sender_name'])[0];
    }

    public function validation(Request $request){
       
        //update stock of the deposit sender
        $qty=$request->quantity_sent;
        $request['status']='0';

        //making stock-history and approve the deposit receiver
        $checkdeposit= DepositServices::where('deposit_id','=',$request->deposit_sender_id)->where('service_id','=',$request->service_id)->get('available_qte');
        if(count($checkdeposit)){
            $qty_before=$checkdeposit[0]['available_qte'];
            $available_qty =$checkdeposit[0]['available_qte']-$qty;
            //check if the sent qty >= available qty
            if($qty_before>=$available_qty){
                $setquantity=DB::update('update deposit_services set available_qte = ? where deposit_id = ? and service_id = ? ',[$available_qty,$request->deposit_sender_id,$request->service_id]);
                 
                //update the stock
                if($setquantity){
                    //making stock-history for the deposit
                    StockHistoryController::create([
                        'depot_id'=>$request->deposit_sender_id,	
                        'service_id'=>$request->service_id,
                        'user_id'=>$request->sender_id,
                        'quantity'=>$qty,
                        'quantity_before'=>$qty_before,
                        'price'=>0,
                        'total'=>0,
                        'motif'=>'validation demande approvisionnement',
                        'note'=>$request->note,
                        'type'=>'withdraw',
                        'type_approvement'=>'cash',
                        'uuid'=>$this->getUuId(),
                        'enterprise_id'=>$request->enterprise_id
                    ]);
                }
            }
          }
  
        return $this->show(requestapprovments::create($request->all()));

    //     //return $request;
    //     $qty=$request->quantity_sent;
    //     if(isset($request->quantity_received) && $request->quantity_received>0){
    //         $qty=$request->quantity_received;
    //     }
    //    $request['status']='1';
    //    $finded=requestapprovments::find($request['id']);
    //    $updated=$finded->update($request->all());
    //    if($updated){
    //     //making stock-history and approve the deposit receiver
    //     $checkdeposit= DepositServices::where('deposit_id','=',$request->deposit_receiver_id)->where('service_id','=',$request->service_id)->get('available_qte');
    //     if(count($checkdeposit)){
    //         $qty_before=$checkdeposit[0]['available_qte'];
    //         $available_qty =$checkdeposit[0]['available_qte']+$qty;
    //         //update the stock
    //         $setquantity=DB::update('update deposit_services set available_qte = ? where deposit_id = ? and service_id= ? ',[$available_qty,$request->deposit_receiver_id,$request->service_id]);
    //         if($setquantity){
    //             //making stock-history for the deposit
    //             StockHistoryController::create([
    //                 'depot_id'=>$request->deposit_receiver_id,	
    //                 'service_id'=>$request->service_id,
    //                 'user_id'=>$request->user_id,
    //                 'quantity'=>$qty,
    //                 'quantity_before'=>$qty_before,
    //                 'price'=>0,
    //                 'total'=>0,
    //                 'motif'=>'validation demande approvisionnement',
    //                 'note'=>$request->note,
    //                 'type'=>'entry',
    //                 'type_approvement'=>'cash',
    //                 'uuid'=>$this->getUuId(),
    //                 'enterprise_id'=>$request->enterprise_id
    //             ]);
    //         }
    //     }else{
    //         //if the deposit does'nt have the article, we affect it right here
    //         DepositServices::create([
    //             'deposit_id'=>$request->deposit_receiver_id,
    //             'service_id'=>$request->service_id,
    //             'available_qte'=>$qty
    //         ]);
    //     }
    //    }

    //    return $this->show(requestapprovments::find($request->id));
    }

    /**
     * 
     */

     public function canceling(Request $request){
        $message='';
        //return $request;
        $qty=$request->quantity_sent;
       
        $finded=requestapprovments::find($request['id']);
       
       if($finded){
        //if already received
        if($finded->status=='1'){
            $message='received';
        }else{
            $message='denied';
            $request['status']='3';
            $finded->update($request->all());    
        }
    } 

    return ['message'=>$message,'data'=>$this->show(requestapprovments::find($request->id))];
}
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\requestapprovments  $requestapprovments
     * @return \Illuminate\Http\Response
     */
    public function edit(requestapprovments $requestapprovments)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdaterequestapprovmentsRequest  $request
     * @param  \App\Models\requestapprovments  $requestapprovments
     * @return \Illuminate\Http\Response
     */
    public function update(UpdaterequestapprovmentsRequest $request, requestapprovments $requestapprovments)
    {
        return $this->show(requestapprovments::find($requestapprovments)->update($request->all()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\requestapprovments  $requestapprovments
     * @return \Illuminate\Http\Response
     */
    public function destroy(requestapprovments $requestapprovments)
    {
        //
    }
}
