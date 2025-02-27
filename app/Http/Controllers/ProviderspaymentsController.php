<?php

namespace App\Http\Controllers;

use App\Models\providerspayments;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreproviderspaymentsRequest;
use App\Http\Requests\UpdateproviderspaymentsRequest;
use App\Models\funds;
use App\Models\ProviderController;
use App\Models\requestHistory;
use App\Models\StockHistoryController;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProviderspaymentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \App\Http\Requests\StoreproviderspaymentsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreproviderspaymentsRequest $request)
    {
        try {
            $stock = StockHistoryController::find($request['stock_history_id']);
            if ($stock) {
                //checking debts and advances
                $advances=providerspayments::select(DB::raw('sum(amount) as total_advances'))
                ->where('stock_history_id','=',$stock['id'])
                ->get()->first();
                 $stock['totaladvances']=$advances['total_advances']?$advances['total_advances']:0;   
                 $stock['solddebts']=$stock['total']?$stock['total']:(($stock['price']?$stock['price']:0)*$stock['quantity'])-$stock['advances']; 

                 if ($stock['solddebts']>=0) {
                    if ($request['amount']>$stock['solddebts']) {
                        $request['amount']=$request['amount']-$stock['solddebts'];
                    }
                    $request['uuid']=$this->getUuId('C','PP');
                    $newpayment=providerspayments::create($request->all());
                    $provider=ProviderController::find($newpayment['provider_id']);
                    $ese=$this->getEse($newpayment['done_by']);
                    $actualfund=funds::where('enterprise_id',$ese->id)->where('principal',true)->first();
                    $sold=$actualfund['sold']-$request['amount'];
                    //passer l'ecriture de sortie dans la caisse principale
                    if ($newpayment) {
                        DB::update('update funds set sold =sold + ? where id = ? ',[$request->amount,$actualfund->id]);
                        requestHistory::create([
                            'user_id'=>$newpayment['done_by'],
                            'fund_id'=>$actualfund['id'],
                            'amount'=>$request['amount'],
                            'motif'=>"paiement dette fournisseur ".$provider['providerName'],
                            'type'=>"withdraw",
                            'enterprise_id'=>$ese->id,
                            'sold'=>$sold,
                            'done_at'=>date('Y-m-d'),
                            'status'=>"validated",
                            'beneficiary'=>$provider['providerName'],
                            'provenance'=>$ese->name,
                            'uuid'=>$this->getUuId('RH','C')
                        ]);
                    }

                    return response()->json([
                        'message'=>'success',
                        'status'=>200,
                        'error'=>null,
                        'data'=>$this->show($newpayment)
                    ]);
                 }else{
                    return response()->json([
                        'message'=>'error',
                        'status'=>400,
                        'error'=>'already payed',
                        'data'=>null
                    ]);
                 }
            }else{
                return response()->json([
                    'message'=>'error',
                    'status'=>400,
                    'error'=>'not fund',
                    'data'=>null
                ]); 
            }
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
     * Display payments list by provider
     */
    public function paymentsbyprovider(Request $request){
        try {
            if ($request['id']) {
                $provider=ProviderController::find($request['id']);
                if ($provider) {
                   
                    $payments =collect(providerspayments::where('provider_id',$provider['id'])->get());
                    $payments=$payments->transform(function ($payment){
                        return $this->show($payment);
                    });

                    return response()->json([
                        'message'=>'success',
                        'status'=>200,
                        'error'=>null,
                        'data'=>$payments
                    ]);
                }else{
                    return response()->json([
                        'message'=>'error',
                        'status'=>400,
                        'error'=>'no provider find',
                        'data'=>null
                    ]);    
                }
            }else{
                return response()->json([
                    'message'=>'error',
                    'status'=>400,
                    'error'=>'no provider sent',
                    'data'=>null
                ]); 
            }
        } catch (Exception $th) {
            return response()->json([
                'message'=>'error',
                'status'=>500,
                'error'=>$th->getMessage(),
                'data'=>null
            ]); 
        }
        return $request;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\providerspayments  $providerspayments
     * @return \Illuminate\Http\Response
     */
    public function show(providerspayments $providerspayments)
    {
        $stockctrl = new StockHistoryControllerController();
        $payment =providerspayments::find($providerspayments['id']);
        $doneby=User::find($payment['done_by']);
        $stockhistory=StockHistoryController::find($payment['stock_history_id']);
        $stockhistory=$stockctrl->show($stockhistory);
        $payment['service_name']=$stockhistory['service_name'];
        $payment['quantity']=$stockhistory['quantity'];
        $payment['price']=$stockhistory['price'];
        $payment['total']=$stockhistory['total'];
        $payment['uom_symbol']=$stockhistory['uom_symbol'];
        $payment['motif']=$stockhistory['motif'];
        $payment['service_id']=$stockhistory['service_id'];
        $payment['done_by_name']=$doneby['user_name'];
        $payment['ref_buy']=$stockhistory['uuid'];

        $advances=providerspayments::select(DB::raw('sum(amount) as total_advances'))
            ->where('stock_history_id','=',$payment['stock_history_id'])
            ->get()->first();

        $stockhistory['advances']=$advances['total_advances']?$advances['total_advances']:0;   
        $payment['sold']=$stockhistory['total']-$stockhistory['advances'];   

        return $payment;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\providerspayments  $providerspayments
     * @return \Illuminate\Http\Response
     */
    public function edit(providerspayments $providerspayments)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateproviderspaymentsRequest  $request
     * @param  \App\Models\providerspayments  $providerspayments
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateproviderspaymentsRequest $request, providerspayments $providerspayments)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\providerspayments  $providerspayments
     * @return \Illuminate\Http\Response
     */
    public function destroy(providerspayments $providerspayments)
    {
        //
    }
}
