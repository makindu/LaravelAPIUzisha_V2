<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\ProviderController;
use Illuminate\Support\Facades\DB;
use App\Models\StockHistoryController;
use App\Http\Requests\StoreProviderControllerRequest;
use App\Http\Requests\UpdateProviderControllerRequest;
use App\Models\providerspayments;
use Carbon\Carbon;

class ProviderControllerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterpriseid)
    {
        $list=collect(ProviderController::where('enterprise_id','=',$enterpriseid)->get());
        $listdata=$list->map(function ($item,$key){
            return $this->show($item);
        });
        return $listdata;
    }  
    
    public function financialsituation($enterpriseid)
    {
        try {
            $list=collect(ProviderController::where('enterprise_id','=',$enterpriseid)->get());
            $list=$list->map(function ($provider){
                 $provider=$this->show($provider);
                 $cash=StockHistoryController::select(DB::raw('sum(total) as total_cash'))
                                ->where('type','=','entry')
                                ->where('provider_id','=',$provider['id'])
                                ->where('type_approvement','=','cash')
                                ->get()->first();
                $debts=StockHistoryController::select(DB::raw('sum(total) as total_debts'))
                                ->where('type','=','entry')
                                ->where('provider_id','=',$provider['id'])
                                ->where('type_approvement','=','credit')
                                ->get()->first();

                $advances=providerspayments::select(DB::raw('sum(amount) as total_advances'))
                ->where('provider_id','=',$provider['id'])
                ->get()->first();
                    
                 $provider['cash']=$cash['total_cash']?$cash['total_cash']:0;   
                 $provider['debts']=$debts['total_debts']?$debts['total_debts']:0;   
                 $provider['advances']=$advances['total_advances']?$advances['total_advances']:0;   
                 $provider['sold']=$provider['debts']-$provider['advances'];   
                 $provider['totalca']=$provider['debts']+$provider['cash'];   
                 return $provider;
            });
            return response()->json([
                'message'=>'success',
                'status'=>200,
                'error'=>null,
                'totalcash'=>$list->sum('cash'),
                'totaldebts'=>$list->sum('debts'),
                'totaladvances'=>$list->sum('advances'),
                'totalsold'=>$list->sum('sold'),
                'totalca'=>$list->sum('totalca'),
                'data'=>$list
            ]); 
        } catch (Exception $th) {
            return response()->json([
                'message'=>'error',
                'status'=>500,
                'error'=>$th->getMessage(),
                'data'=>null
            ]); 
        }
       
    }  
    
    public function debtsprovider($providerId)
    {
        try {
            $provider=ProviderController::find($providerId);
            $stockhistoryctrl= new StockHistoryControllerController();
            if ($provider) {

                $debts=collect(StockHistoryController::where('provider_id','=',$provider['id'])
                ->where('type_approvement','=','credit')
                ->get());

                $debts->transform(function ($stock) use ($stockhistoryctrl){
                    $stock=$stockhistoryctrl->show($stock);
                    $advances=providerspayments::select(DB::raw('sum(amount) as total_advances'))
                    ->where('stock_history_id','=',$stock['id'])
                    ->get()->first();
       
                     $stock['totaladvances']=$advances['total_advances']?$advances['total_advances']:0;   
                     $stock['solddebts']=($stock['total']?$stock['total']:(($stock['price']?$stock['price']:0)*$stock['quantity']))-$stock['totaladvances']; 
                     return $stock;
                });
                
                return response()->json([
                    'message'=>'success',
                    'status'=>200,
                    'error'=>null,
                    'data'=>$debts
                ]);

            }else{
                return response()->json([
                    'message'=>'error',
                    'status'=>400,
                    'error'=>'provider not fund',
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
     * @param  \App\Http\Requests\StoreProviderControllerRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProviderControllerRequest $request)
    {
        return ProviderController::create($request->all());
    }

    public function importation(Request $request){
        $list=[];
        if (isset($request['data']) && count($request['data'])>0) {
            foreach ($request['data'] as $provider) {
                //if exists
                $ifexists=ProviderController::where('providerName',$provider['providerName'])->first();
                if(!$ifexists){
                    $newone=ProviderController::create($provider);
                    if ($newone) {
                        $provider['message']="success";
                    }else{
                        $provider['message']="failed";
                    }
                }else{
                    $provider['message']="duplicated";
                }
                array_push($list,$provider);
            }
        }
        return $list;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProviderController  $providerController
     * @return \Illuminate\Http\Response
     */
    public function show(ProviderController $providerController)
    {
        $provider=ProviderController::leftjoin('enterprises as E', 'provider_controllers.enterprise_id','=','E.id')
        ->leftjoin('users as U', 'provider_controllers.created_by_id','=','U.id')
        ->where('provider_controllers.id', '=', $providerController->id)
        ->get(['provider_controllers.*','E.name as enterprise_name','U.user_name'])->first();

            $stocktotal=StockHistoryController::select(DB::raw('count(id) as total_stock'),DB::raw('sum(total) as total_ca'))
            ->where('provider_id','=',$provider['id'])
            ->get()->first();
            
            $cash=StockHistoryController::select(DB::raw('sum(total) as total_cash'))
            ->where('type','=','entry')
            ->where('provider_id','=',$provider['id'])
            ->where('type_approvement','=','cash')
            ->get()->first();

            $debts=StockHistoryController::select(DB::raw('sum(total) as total_debts'))
                    ->where('type','=','entry')
                    ->where('provider_id','=',$provider['id'])
                    ->where('type_approvement','=','credit')
                    ->get()->first();

            $advances=providerspayments::select(DB::raw('sum(amount) as total_advances'))
            ->where('provider_id','=',$provider['id'])
            ->get()->first();

            $provider['cash']=$cash['total_cash']?$cash['total_cash']:0;   
            $provider['debts']=$debts['total_debts']?$debts['total_debts']:0;   
            $provider['advances']=$advances['total_advances']?$advances['total_advances']:0;   
            $provider['sold']=$provider['debts']-$provider['advances'];   
            $provider['totalstockprovided']=$stocktotal['total_stock'];   
            $provider['totalca']=$stocktotal['total_ca'];   

        return $provider;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ProviderController  $providerController
     * @return \Illuminate\Http\Response
     */
    public function edit(ProviderController $providerController)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateProviderControllerRequest  $request
     * @param  \App\Models\ProviderController  $providerController
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProviderControllerRequest $request, ProviderController $providerController)
    {
        //
    }

    public function update2(Request $request,$id)
    {
        $provider=ProviderController::find($id);
        $provider->update($request->all());
        return $this->show($provider);
    }

    /**
     * Gettings stock history by provider
     */
    public function stockhistory($providerid){
        $stockhistoryctrl= new StockHistoryControllerController();
        $list=collect(StockHistoryController::where('provider_id','=',$providerid)->get());
        $list=$list->map(function($history) use($stockhistoryctrl){
            return $stockhistoryctrl->show($history);
        });

       return $list;
    }  
    
    /**
     * Gettings periodic stock history by provider
     */
    public function periodicstockhistory(Request $request){
        if (isset($request['criteria']) && !empty($request['criteria'])) {
            if (isset($request['provider_id']) && !empty($request['provider_id'])){
                try {
                    $provider=ProviderController::find($request['provider_id']);
                    if ($provider) {
                        switch ($request['criteria']) {
                            case 'monthly':
                                Carbon::setLocale('fr');
                                $period=Carbon::now()->translatedFormat('F Y');
                                $startOfMonth = Carbon::now()->startOfMonth(); // Début du mois
                                $endOfMonth = Carbon::now()->endOfMonth();
                                break;
                            
                            default:
                                # code...
                                break;
                        }
    
                        $stockhistoryctrl= new StockHistoryControllerController();
                        $list=collect(StockHistoryController::where('provider_id','=',$request['provider_id'])
                        ->whereBetween('done_at',[$startOfMonth.' 00:00:00', $endOfMonth.' 23:59:59'])
                        ->get());
                        $list=$list->map(function($history) use($stockhistoryctrl){
                            return $stockhistoryctrl->show($history);
                        });

                        return response()->json([
                            'message'=>'success',
                            'status'=>200,
                            'error'=>null,
                            'data'=>$list,
                            'period'=>$period
                        ]);

                    }else{
                        return response()->json([
                            'message'=>'error',
                            'status'=>400,
                            'error'=>'provider not fund',
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
               
            }else{
                return response()->json([
                    'message'=>'error',
                    'status'=>400,
                    'error'=>'no provider sent',
                    'data'=>null
                ]); 
            }
           
        }else{
            return response()->json([
                'message'=>'error',
                'status'=>400,
                'error'=>'no criteria sent',
                'data'=>null
            ]); 
        }
    }
    
    /**
     * Gettings debts stock history by provider
     */
    public function debtstockhistory($providerid){

       return StockHistoryController::leftjoin('deposit_controllers as D','stock_history_controllers.depot_id','=','D.id')
       ->leftjoin('services_controllers as S','stock_history_controllers.service_id','=','S.id')
       ->leftjoin('users as U','stock_history_controllers.user_id','=','U.id')
       ->where('provider_id','=',$providerid)
       ->where('stock_history_controllers.type','=','debt')
       ->get(['stock_history_controllers.*','S.name as service_name','D.name as deposit_name','U.user_name as done_by_name']);
    } 
    
    /**
     * Gettings cash stock history by provider
     */
    public function cashstockhistory($providerid){

       return StockHistoryController::leftjoin('deposit_controllers as D','stock_history_controllers.depot_id','=','D.id')
       ->leftjoin('services_controllers as S','stock_history_controllers.service_id','=','S.id')
       ->leftjoin('users as U','stock_history_controllers.user_id','=','U.id')
       ->where('provider_id','=',$providerid)
       ->where('stock_history_controllers.type','=','cash')
       ->get(['stock_history_controllers.*','S.name as service_name','D.name as deposit_name','U.user_name as done_by_name']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProviderController  $providerController
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProviderController $providerController)
    {
        return ProviderController::destroy($providerController);
    }

    public function delete($provider){
        $provid=ProviderController::find($provider);
        return $provid->delete();
    }
}
