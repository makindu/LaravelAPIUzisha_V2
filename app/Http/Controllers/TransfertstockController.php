<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\transfertstock;
use App\Models\DepositServices;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoretransfertstockRequest;
use App\Http\Requests\UpdatetransfertstockRequest;
use App\Models\StockHistoryController;
use Illuminate\Http\JsonResponse;

class TransfertstockController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterprise_id)
    {
        //collect all transferts
        $list=collect(transfertstock::where('enterprise_id','=',$enterprise_id)->get());
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
     * @param  \App\Http\Requests\StoretransfertstockRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoretransfertstockRequest $request)
    {  $request['uuid']=$this->getUuId('C','T').$request['id'];
        return $this->show(transfertstock::create($request->all()));
    }

    /**
     * change status for a specific resource
     */
    public function statusChange(Request $request){
        $funded=transfertstock::find($request['id']);
        $request['validate_at']=date('Y-m-d');
        $funded->update($request->all());
        return transfertstock::find($request['id']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\transfertstock  $transfertstock
     * @return \Illuminate\Http\Response
     */
    public function show(transfertstock $transfertstock)
    {
        //
        return transfertstock::leftjoin('deposit_controllers as DS','transfertstocks.deposit_sender_id','=','DS.id')
        ->leftjoin('deposit_controllers as DR','transfertstocks.deposit_receiver_id','=','DR.id')
        ->leftjoin('users as US','transfertstocks.sender_id','=','US.id')
        ->leftjoin('users as UR','transfertstocks.receiver_id','=','UR.id')
        ->leftjoin('services_controllers as S','transfertstocks.service_id','=','S.id')
        ->leftjoin('unit_of_measure_controllers as UM','S.uom_id','=','UM.id')
        ->where('transfertstocks.id','=',$transfertstock['id'])->get(['transfertstocks.*','UM.name as uom_name','UM.symbol as uom_symbol','S.name as service_name','S.description as service_description','DS.name as deposit_sender_name','DR.name as  deposit_receiver_name','US.user_name as sender_name'])[0];
    }

    public function validation(Request $request){
        //return $request;
        $qty=$request->quantity_sent;
        if(isset($request->quantity_received) && $request->quantity_received>0){
            $qty=$request->quantity_received;
        }
       $request['status']='1';
       $finded=transfertstock::find($request['id']);
       $updated=$finded->update($request->all());
       if($updated){
        //making stock-history and approve the deposit receiver
        $checkdeposit= DepositServices::where('deposit_id','=',$request->deposit_receiver_id)->where('service_id','=',$request->service_id)->get('available_qte');
        if(count($checkdeposit)){
            $qty_before=$checkdeposit[0]['available_qte'];
            $available_qty =$checkdeposit[0]['available_qte']+$qty;
            //update the stock
            $setquantity=DB::update('update deposit_services set available_qte = ? where deposit_id = ? and service_id= ? ',[$available_qty,$request->deposit_receiver_id,$request->service_id]);
            if($setquantity){
                //making stock-history for the deposit
                StockHistoryController::create([
                    'depot_id'=>$request->deposit_receiver_id,	
                    'service_id'=>$request->service_id,
                    'user_id'=>$request->user_id,
                    'quantity'=>$qty,
                    'quantity_before'=>$qty_before,
                    'price'=>0,
                    'total'=>0,
                    'motif'=>'transfert stock',
                    'note'=>$request->note,
                    'type'=>'entry',
                    'type_approvement'=>'cash',
                    'uuid'=>$this->getUuId('C','T'),
                    'enterprise_id'=>$request->enterprise_id
                ]);
            }
        }else{
            //if the deposit does'nt have the article, we affect it right here
            DepositServices::create([
                'deposit_id'=>$request->deposit_receiver_id,
                'service_id'=>$request->service_id,
                'available_qte'=>$qty
            ]);
        }
       }

       return $this->show(transfertstock::find($request->id));
    }

    /**
     * 
     */

     public function canceling(Request $request){
        $message='';
        //return $request;
        $qty=$request->quantity_sent;
       
        $finded=transfertstock::find($request['id']);
       
       if($finded){
        //if already received
        if($finded->status=='1'){
            $message='received';
        }else{
            $request['status']='2';
            $updated=$finded->update($request->all());  
            //if updated return the stock of the sender deposit
            if($updated){
                $message='canceled';
                $checkdeposit= DepositServices::where('deposit_id','=',$request->deposit_sender_id)->where('service_id','=',$request->service_id)->get('available_qte');
                $qty_before=$checkdeposit[0]['available_qte'];
                $available_qty =$checkdeposit[0]['available_qte']+$qty;
                DB::update('update deposit_services set available_qte = ? where deposit_id = ? and service_id= ? ',[$available_qty,$request->deposit_sender_id,$request->service_id]);
                //making stock history for the sender deposit for returning
                StockHistoryController::create([
                    'depot_id'=>$request->deposit_sender_id,	
                    'service_id'=>$request->service_id,
                    'user_id'=>$request->user_id,
                    'quantity'=>$qty,
                    'quantity_before'=>$qty_before,
                    'price'=>0,
                    'total'=>0,
                    'motif'=>'remboursement transfert stock',
                    'note'=>$request->note,
                    'type'=>'entry',
                    'type_approvement'=>'cash',
                    'uuid'=>$this->getUuId('C','T'),
                    'enterprise_id'=>$request->enterprise_id
                ]);
            }  
        }
    } 

    return ['message'=>$message,'data'=>$this->show(transfertstock::find($request->id))];
}
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\transfertstock  $transfertstock
     * @return \Illuminate\Http\Response
     */
    public function edit(transfertstock $transfertstock)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatetransfertstockRequest  $request
     * @param  \App\Models\transfertstock  $transfertstock
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatetransfertstockRequest $request, transfertstock $transfertstock)
    {
        return $this->show(transfertstock::find($transfertstock)->update($request->all()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\transfertstock  $transfertstock
     * @return \Illuminate\Http\Response
     */
    public function destroy(transfertstock $transfertstock)
    {
        //
    }
}
