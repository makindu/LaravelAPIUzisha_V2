<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreproviderspaymentsRequest;
use App\Models\funds;
use App\Models\requestHistory;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StorerequestHistoryRequest;
use App\Http\Requests\UpdaterequestHistoryRequest;
use App\Models\images;
use App\Models\libraries;
use App\Models\ProviderController;
use App\Models\providerspayments;
use App\Models\ServicesController;
use App\Models\StockHistoryController;
use Exception;
use Illuminate\Http\Request;

class RequestHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $list =requestHistory::join('users','request_histories.user_id','=','users.id')->get(['request_histories.*','users.user_name']);
        return $list;
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
     * update 
     */
    public function operationsupdate(Request $request){
        $data=[];
        if ($request['criteria']) {
            if ($request['user'] && $this->getEse($request['user']['id'])) {
                if ($request['data'] && count($request['data'])>0) {
                    try {
                        $status="";
                        switch ($request['criteria']) {
                            case 'validate':
                                $status="validated";
                                break;
                            case 'pending':
                                $status="pending";
                                break;
                            case 'cancel':
                                $status="cancelled";
                                break;
                            
                            default:
                                # code...
                                break;
                        }

                        foreach ($request['data'] as $expenditure) {
                            $finded=requestHistory::find($expenditure['id']);
                            if ($finded){
                                $finded['status']=$status;
                                // if ($status=="validated") {
                                //     $finded['is_validated']=true;
                                // }else{
                                //     $finded['is_validated']=false;
                                // } 
                                
                                $finded->save();
                                array_push($data,$finded);
                            }
                         }
                            return response()->json([
                            "status"=>200,
                            "message"=>"success",
                            "error"=>null,
                            "data"=>$data
                        ]);
                    } catch (Exception $th) {
                        return response()->json([
                            "status"=>500,
                            "message"=>"error",
                            "error"=>$th->getMessage(),
                            "data"=>null
                        ]);
                    }
                  
                }else{
                    return response()->json([
                        "status"=>400,
                        "message"=>"error",
                        "error"=>"no data sent",
                        "data"=>null
                    ]); 
                }
            }else{
                //unauthorized user
                return response()->json([
                    "status"=>400,
                    "message"=>"error",
                    "error"=>"unauthrized sent",
                    "data"=>null
                ]); 
            }
        }else{
            //no criteria sent
            return response()->json([
                "status"=>400,
                "message"=>"error",
                "error"=>"no criteria sent",
                "data"=>null
            ]); 
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorerequestHistoryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorerequestHistoryRequest $request)
    {
        if($request->type=='entry'){
            $fund=funds::find($request->fund_id);
            $request['sold']=$fund->sold+$request->amount;
            $newvalue=requestHistory::create($request->all());
            DB::update('update funds set sold =sold + ? where id = ? ',[$request->amount,$request->fund_id]);
            if ($request['provider_id'] && $request['service_id'] && $request['amount_provided']) {
                $provider=ProviderController::find($request['provider_id']);
                $service=ServicesController::find($request['service_id']);
                StockHistoryController::create([
                    'provider_id'=>$request['provider_id'],
                    'service_id'=>$request['service_id'],
                    'user_id'=>$request['user_id'],
                    'quantity'=>$request['quantity_provided']?$request['quantity_provided']:1,
                    'price'=>$request['quantity_provided']?($request['amount_provided']/$request['quantity_provided']):$request['amount_provided'],
                    'type'=>'entry',
                    'type_approvement'=>'credit',
                    'enterprise_id'=>$request['enterprise_id'],
                    'motif'=>$request['motif']?$request['motif']:'Location '.$service->name.' auprès du fournisseur '.$provider->providerName,
                    'done_at'=>$request['done_at'],
                    'date_operation'=>$request['done_at'],
                    'uuid'=>$this->getUuId('C','ST'),
                    'depot_id'=>$this->defaultdeposit($request['enterprise_id'])['id'],
                    'quantity_before'=>0,
                    'total'=>$request['amount_provided']?$request['amount_provided']:$request['amount'],
                    'requesthistory_id'=>$newvalue->id
                ]);
            }

            if ($request['attachments'] && count($request['attachments'])>0) {
                foreach ($request['attachments'] as $key => $attachment) {
                    $libraryfind=libraries::find($attachment['id']);
                    if ($libraryfind) {
                        $newimage=images::create([
                            'doc_link'=>$libraryfind['id'],
                            'description'=>$newvalue['motif'],
                            'type_operation'=>'request_history',
                            'ref_operation'=>$newvalue['id'],
                            'done_by'=>$request['user_id'],
                            'enterprise_id'=>$request['enterprise_id'],
                            'size'=>$libraryfind['size'],
                            'principal'=>$key==0?true:false
                        ]);
                    }
                }
            }
            return  $this->show($newvalue);
        }else{
            //checking sold
            $gettingsold=funds::find($request->fund_id);
            $sold=$gettingsold['sold'];

            if($sold>=$request->amount){
                $request['sold']=$sold-$request->amount;
                $newvalue=requestHistory::create($request->all());
                DB::update('update funds set sold =sold - ? where id = ? ',[$request->amount,$request->fund_id]);
                $operationdone=$this->show($newvalue);
                //payments to do
                if ($request['provider_id']) {
                    $amountSent=$request['amount'];
                    //select all his debts
                    $stockhistories=collect(StockHistoryController::leftjoin('providerspayments as P','stock_history_controllers.id','=','P.stock_history_id')
                    ->select(DB::raw('stock_history_controllers.id as stock_history'),DB::raw('sum(P.amount) as totalpayed'),DB::raw('sum(stock_history_controllers.total) as totaldebts'))
                    ->where('stock_history_controllers.type','entry')
                    ->where('stock_history_controllers.provider_id',$request['provider_id'])
                    ->where('stock_history_controllers.type_approvement','credit')
                    ->groupByRaw('stock_history_controllers.id')
                    ->havingRaw('totaldebts > totalpayed')
                    ->orHavingRaw('totalpayed IS NULL')
                    ->orderBy('stock_history_controllers.done_at','ASC')->get());
                    while ($amountSent > 0) {
                        foreach ($stockhistories as  $stock) {
                            $amountToPaye=0;
                            $soldDebt=($stock->totaldebts)-($stock->totalpayed?$stock->totalpayed:0);
                            if ($soldDebt>=$amountSent) {
                                $amountToPaye=$amountSent;
                            }
                            
                            if ($soldDebt<$amountSent) {
                                $amountToPaye=$soldDebt;   
                            }
    
                            $newrequest=new StoreproviderspaymentsRequest([
                                'done_by'=>$request['user_id'],
                                'provider_id'=>$request['provider_id'],
                                'stock_history_id'=>$stock['stock_history'],
                                'enterprise_id'=>$request['enterprise_id'],
                                'status'=>'pending',
                                'note'=>$request['motif'],
                                'amount'=>$amountToPaye,
                                'uuid'=>$this->getUuId('C','PP'),
                                'done_at'=>$request['done_at']
                            ]);
                            providerspayments::create($newrequest->all());
                            $amountSent=$amountSent-$amountToPaye;
                        }
                    }
                } 
                if ($request['attachments'] && count($request['attachments'])>0) {
                    foreach ($request['attachments'] as $key => $attachment) {
                        $libraryfind=libraries::find($attachment['id']);
                        if ($libraryfind) {
                            $newimage=images::create([
                                'doc_link'=>$libraryfind['id'],
                                'description'=>$newvalue['motif'],
                                'type_operation'=>'request_history',
                                'ref_operation'=>$newvalue['id'],
                                'done_by'=>$request['user_id'],
                                'enterprise_id'=>$request['enterprise_id'],
                                'size'=>$libraryfind['size'],
                                'principal'=>$key==0?true:false
                            ]);
                        }
                    }
                }
                // $operationdone['stockhistories']=$stockhistories;
                return  $operationdone;
            }
            else{
                return response()->json([
                    "message"=>"error",
                    "error"=>"no type operation",
                    "data"=>null
                ]);
            }
        }
    }

    private function makingnewstockhistoryforprovider($request){

        $provider=ProviderController::find($request['provider_id']);
        $service=ServicesController::find($request['service_id']);
        StockHistoryController::create([
            'provider_id'=>$request['provider_id'],
            'service_id'=>$request['service_id'],
            'user_id'=>$request['user_id'],
            'quantity'=>$request['quantity_provided']?$request['quantity_provided']:1,
            'price'=>$request['quantity_provided']?($request['amount_provided']/$request['quantity_provided']):$request['amount_provided'],
            'type'=>'entry',
            'type_approvement'=>'credit',
            'enterprise_id'=>$request['enterprise_id'],
            'motif'=>$request['motif']?$request['motif']:'Location '.$service->name.' auprès du fournisseur '.$provider->providerName,
            'done_at'=>$request['done_at'],
            'date_operation'=>$request['done_at'],
            'uuid'=>$this->getUuId('C','ST'),
            'depot_id'=>$this->defaultdeposit($request['enterprise_id'])['id'],
            'quantity_before'=>0,
            'total'=>$request['quantity_provided']?($request['amount_provided']*$request['quantity_provided']):$request['amount_provided'],
            'requesthistory_id'=>$request->id
        ]);
    }

    private function makingproviderpayments($request){
         //payments to do
         if ($request['provider_id']) {
            $amountSent=$request['amount'];
            //select all his debts
            $stockhistories=collect(StockHistoryController::leftjoin('providerspayments as P','stock_history_controllers.id','=','P.stock_history_id')
            ->select(DB::raw('stock_history_controllers.id as stock_history'),DB::raw('sum(P.amount) as totalpayed'),DB::raw('sum(stock_history_controllers.total) as totaldebts'))
            ->where('stock_history_controllers.type','entry')
            ->where('stock_history_controllers.provider_id',$request['provider_id'])
            ->where('stock_history_controllers.type_approvement','credit')
            ->groupByRaw('stock_history_controllers.id')
            ->havingRaw('totaldebts > totalpayed')
            ->orHavingRaw('totalpayed IS NULL')
            ->orderBy('stock_history_controllers.done_at','ASC')->get());
            while ($amountSent > 0) {
                foreach ($stockhistories as  $stock) {
                    $amountToPaye=0;
                    $soldDebt=($stock->totaldebts)-($stock->totalpayed?$stock->totalpayed:0);
                    if ($soldDebt>=$amountSent) {
                        $amountToPaye=$amountSent;
                    }
                    
                    if ($soldDebt<$amountSent) {
                        $amountToPaye=$soldDebt;   
                    }

                    $newrequest=new StoreproviderspaymentsRequest([
                        'done_by'=>$request['user_id'],
                        'provider_id'=>$request['provider_id'],
                        'stock_history_id'=>$stock['stock_history'],
                        'enterprise_id'=>$request['enterprise_id'],
                        'status'=>'pending',
                        'note'=>$request['motif'],
                        'amount'=>$amountToPaye,
                        'uuid'=>$this->getUuId('C','PP'),
                        'done_at'=>$request['done_at']
                    ]);
                    providerspayments::create($newrequest->all());
                    $amountSent=$amountSent-$amountToPaye;
                }
            }
        }
    }
    /**
     * save multiples
     */
    public function savemultiple(Request $request){
        $data=[];
        // return $request;
        if ($request->data && count($request->data)>0) {
            try {
                foreach ($request->data as  $item) {
                    array_push($data,$this->store(new StorerequestHistoryRequest($item)));
                }

                return response()->json([
                    "status"=>200,
                    "message"=>"success",
                    "error"=>null,
                    "data"=>$data
                ]);
            } catch (Exception $th) {
                return response()->json([
                    "status"=>500,
                    "message"=>"error occured",
                    "error"=>$th->getMessage(),
                    "data"=>null
                ]);
            }
          
        }else{
            return response()->json([
                "status"=>500,
                "message"=>"error occured",
                "error"=>"no data sent",
                "data"=>null
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\requestHistory  $requestHistory
     * @return \Illuminate\Http\Response
     */
    public function show(requestHistory $requestHistory)
    {
        $attachments=[];
        $data=requestHistory::join('users','request_histories.user_id','=','users.id')
                            ->join('funds as F','request_histories.fund_id','F.id')
                            ->join('moneys as M','F.money_id','M.id')
                            ->leftjoin('accounts as A','request_histories.account_id','A.id')
                            ->leftjoin('stock_history_controllers as SH','request_histories.id','SH.requesthistory_id')
                            ->leftjoin('provider_controllers as P','SH.provider_id','P.id')
                            ->leftjoin('services_controllers as S','SH.service_id','S.id')
                            ->leftjoin('unit_of_measure_controllers as UOM','S.uom_id','UOM.id')
                            ->where('request_histories.id','=',$requestHistory->id)
                            ->get(['UOM.name as uom_name','UOM.symbol as uom_symbol','SH.quantity as quantity_provided','SH.provider_id','SH.total as amount_provided','P.providerName','S.name as servicename','request_histories.*','A.name as account_name','F.money_id','F.description as fund_name','M.abreviation','users.user_name'])->first();
        if ($data) {
            $attachments=images::join('libraries as L','images.doc_link','=','L.id')->where('images.type_operation','request_history')->where('images.ref_operation',$requestHistory->id)->get('L.*');
        }
        $data['attachments']=$attachments;
        return $data;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\requestHistory  $requestHistory
     * @return \Illuminate\Http\Response
     */
    public function edit(requestHistory $requestHistory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdaterequestHistoryRequest  $request
     * @param  \App\Models\requestHistory  $requestHistory
     * @return \Illuminate\Http\Response
     */
    public function update(UpdaterequestHistoryRequest $request, requestHistory $requestHistory)
    {
        $finded=requestHistory::find($requestHistory->id);
        if ($finded) {
            if ($finded->status=='cancelled' && $finded->status=='validated') {
                return response()->json([
                    "status"=>400,
                    "message"=>"error",
                    "error"=>'closed',
                    "data"=>$this->show($finded)
                ]);
            }else{
                try {
                    $linefind=$this->show($finded);
                    $finded->update($request->only([
                        'motif',
                        'account_id',
                        'done_at',
                        'provenance',
                        'beneficiary',
                        'status'
                    ]));
                    if ($request['provider_id'] || $request['service_id']) {
                        $stockhistory=StockHistoryController::where('requesthistory_id', $linefind['id'])->first();
                            if ($stockhistory) {
                                $stockhistory->update($request->only([
                                    'provider_id','service_id'
                                ]));
                            }else{
                                $linefind['service_id']=$request['service_id'];
                                $linefind['provider_id']=$request['provider_id'];
                                $linefind['amount_provided']=$linefind['amount'];
                                //creating debt or making payment for the provider
                                if ($finded->type=='entry') {
                                   $this->makingnewstockhistoryforprovider($linefind);
                                }else{
                                    $this->makingproviderpayments($linefind);
                                }
                            }
                    }

                    if ($request['attachments'] && count($request['attachments'])>0) {
                        foreach ($request['attachments'] as $key => $attachment) {
                            $libraryfind=libraries::find($attachment['id']);
                            if ($libraryfind) {
                                $newimage=images::create([
                                    'doc_link'=>$libraryfind['id'],
                                    'description'=>$linefind['motif'],
                                    'type_operation'=>'request_history',
                                    'ref_operation'=>$linefind['id'],
                                    'done_by'=>$linefind['user_id'],
                                    'enterprise_id'=>$linefind['enterprise_id'],
                                    'size'=>$libraryfind['size'],
                                    'principal'=>$key==0?true:false
                                ]);
                            }
                        }
                    }

                    return response()->json([
                        "status"=>200,
                        "message"=>"success",
                        "error"=>null,
                        "data"=>$this->show($finded)
                    ]);
                } catch (Exception $th) {
                    return response()->json([
                        "status"=>500,
                        "message"=>"error",
                        "error"=>$th->getMessage(),
                        "data"=>null
                    ]);
                }
            }
           
        }else{
            return response()->json([
                "status"=>500,
                "message"=>"error",
                "error"=>"no data finded",
                "data"=>null
            ]);
        }
    }

    public function getbyfund($fund){
        $list =requestHistory::join('users','request_histories.user_id','=','users.id')->where('fund_id','=',$fund)->get(['request_histories.*','users.user_name']);
        return $list;
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\requestHistory  $requestHistory
     * @return \Illuminate\Http\Response
     */
    public function destroy(requestHistory $requestHistory)
    {
        //
    }
}
