<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\DepositsUsers;
use App\Models\DepositController;
use Illuminate\Support\Facades\DB;
use App\Models\StockHistoryController;
use App\Http\Requests\StoreStockHistoryControllerRequest;
use App\Http\Requests\UpdateStockHistoryControllerRequest;
use App\Models\DepositServices;
use App\Models\PricesCategories;
use App\Models\ServicesController;
use Exception;
use stdClass;

class StockHistoryControllerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterprise)
    {
        $list=collect(StockHistoryController::where('enterprise_id','=',$enterprise)->get());
        $list_data=$list->map(function ($item,$key){
            return $this->show($item);
        });
        return $list_data;

        // $service['details']=$this->stockhistorybyarticleforaspecifiperiod(
        //     new Request([
        //         "from"=>$request['from'],
        //         "to"=>$request['to'],
        //         "service_id"=>$service['id']
        //     ])
        // );
        
    }

    public function getStockHistoryById($mouvementId){
        try{
            return response()->json([
                "message"=>"success",
                "status"=>200,
                "error"=>null,
                "data"=>$this->show(StockHistoryController::find($mouvementId))
            ]);
            
        }catch(Exception $e){
            return response()->json([
                "message"=>"error",
                "status"=>200,
                "error"=>$e->getMessage(),
                "data"=>null
            ]);
        }  
    }

     /**
     * searching stock histories by done paginated
     */
    public function searchingstockhistorybydoneby(Request $request){
        $searchTerm = $request->query('keyword', '');
        $enterpriseId = $request->query('enterprise_id', 0);  
        $actualuser=$this->getinfosuser($request->query('user_id'));
        if ($actualuser['user_type']=='super_admin') {
        
            $list =StockHistoryController::join('services_controllers', 'stock_history_controllers.service_id', '=', 'services_controllers.id')
                ->leftjoin('provider_controllers', 'stock_history_controllers.provider_id', '=', 'provider_controllers.id')
                ->where('stock_history_controllers.enterprise_id', '=', $enterpriseId)
                ->where(function($query) use ($searchTerm) {
                    $query->where('stock_history_controllers.quantity', 'LIKE', "%$searchTerm%")
                    ->orWhere('stock_history_controllers.total', 'LIKE', "%$searchTerm%")
                    ->orWhere('stock_history_controllers.price', 'LIKE', "%$searchTerm%")
                    ->orWhere('stock_history_controllers.motif', 'LIKE', "%$searchTerm%")
                    ->orWhere('stock_history_controllers.note', 'LIKE', "%$searchTerm%")
                    ->orWhere('stock_history_controllers.type', 'LIKE', "%$searchTerm%")
                    ->orWhere('stock_history_controllers.type_approvement', 'LIKE', "%$searchTerm%")
                    ->orWhere('stock_history_controllers.uuid', 'LIKE', "%$searchTerm%")
                    ->orWhere('stock_history_controllers.done_at', 'LIKE', "%$searchTerm%")
                    ->orWhere('provider_controllers.providerName', 'LIKE', "%$searchTerm%")
                    ->orWhere('provider_controllers.adress', 'LIKE', "%$searchTerm%")
                    ->orWhere('provider_controllers.phone', 'LIKE', "%$searchTerm%")
                    ->orWhere('provider_controllers.type', 'LIKE', "%$searchTerm%")
                    ->orWhere('provider_controllers.mail', 'LIKE', "%$searchTerm%")
                    ->orWhere('services_controllers.name', 'LIKE', "%$searchTerm%")
                    ->orWhere('services_controllers.description', 'LIKE', "%$searchTerm%")
                    ->orWhere('services_controllers.codebar', 'LIKE', "%$searchTerm%");
                })
                ->select('stock_history_controllers.*')
                ->paginate(10)
                ->appends($request->query());

            $list->getCollection()->transform(function ($item){
                return $this->show($item);
            });
            return $list;

        } else {
                $list =StockHistoryController::join('services_controllers', 'stock_history_controllers.service_id', '=', 'services_controllers.id')
                ->leftjoin('provider_controllers', 'stock_history_controllers.provider_id', '=', 'provider_controllers.id')
                ->where('stock_history_controllers.user_id', '=', $actualuser['id'])
                ->where(function($query) use ($searchTerm) {
                    $query->where('stock_history_controllers.quantity', 'LIKE', "%$searchTerm%")
                    ->orWhere('stock_history_controllers.total', 'LIKE', "%$searchTerm%")
                    ->orWhere('stock_history_controllers.price', 'LIKE', "%$searchTerm%")
                    ->orWhere('stock_history_controllers.motif', 'LIKE', "%$searchTerm%")
                    ->orWhere('stock_history_controllers.note', 'LIKE', "%$searchTerm%")
                    ->orWhere('stock_history_controllers.type', 'LIKE', "%$searchTerm%")
                    ->orWhere('stock_history_controllers.type_approvement', 'LIKE', "%$searchTerm%")
                    ->orWhere('stock_history_controllers.uuid', 'LIKE', "%$searchTerm%")
                    ->orWhere('stock_history_controllers.done_at', 'LIKE', "%$searchTerm%")
                    ->orWhere('provider_controllers.providerName', 'LIKE', "%$searchTerm%")
                    ->orWhere('provider_controllers.adress', 'LIKE', "%$searchTerm%")
                    ->orWhere('provider_controllers.phone', 'LIKE', "%$searchTerm%")
                    ->orWhere('provider_controllers.type', 'LIKE', "%$searchTerm%")
                    ->orWhere('provider_controllers.mail', 'LIKE', "%$searchTerm%")
                    ->orWhere('services_controllers.name', 'LIKE', "%$searchTerm%")
                    ->orWhere('services_controllers.description', 'LIKE', "%$searchTerm%")
                    ->orWhere('services_controllers.codebar', 'LIKE', "%$searchTerm%");
                })
                ->select('stock_history_controllers.*')
                ->paginate(10)
                ->appends($request->query());

            $list->getCollection()->transform(function ($item){
                return $this->show($item);
            });

            return $list;
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
     * multiple save
     */
    public function multiplestore(Request $request){
        $data=[];
        $failed=[];
       if (isset($request['data']) && !empty($request['data']) && count($request['data'])>0) {
            foreach ($request['data'] as $value) {
                $new = $this->store(new StoreStockHistoryControllerRequest($value));
                if ($new) {
                   array_push($data,$new);
                }else{
                   array_push($failed,$new);
                }
            }
       } 
        
       return response()->json([
        "data"=>$data,
        "failed"=>$failed
       ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreStockHistoryControllerRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreStockHistoryControllerRequest $request)
    {
        $response=['message'=>'fail','data'=>[]];

        if(!$request['done_at']){
            $request['done_at']=date('Y-m-d');
        }
        
        if(isset($request->depot_id) && ($request->depot_id)>=1){
           
        }else{
            //looking for it deposit
            $deposit=DepositsUsers::where('user_id','=',$request['user_id'])->get()[0];
            $request['depot_id']=$deposit->deposit_id;
        }
       
        $stockbefore=DepositServices::where('deposit_id','=',$request['depot_id'])->where('service_id','=',$request['service_id'])->get();
        if (count($stockbefore)>0) {
            $request['quantity_before']=$stockbefore[0]->available_qte;
        } else {
            //affect service to the deposit with the qty sent
            DepositServices::create([
                'deposit_id'=>$request['depot_id'],
                'service_id'=>$request['service_id'],
                'available_qte'=>0
            ]);
        }

        if($request['type']=='entry'){
            DB::update('update deposit_services set available_qte = available_qte + ? where service_id = ? and deposit_id = ?',[$request['quantity'],$request['service_id'],$request['depot_id']]);
            //calcul stock used by FIFO or LIFO method par ici... avant d'enregistrer le stock history
                if($request['price']){
                    $request['total']=$request['quantity']*$request['price'];
                }
                //find product
                $product=ServicesController::find($request['service_id']);
                if ($product['coast']<>$request['price']) {
                    $product->update(['coast'=>$request['price']]);
                }
                $request['sold']=$request['quantity'];
                $request['uuid']=$this->getUuId('C','SH');
            return $this->show(StockHistoryController::create($request->all()));
        }else if($request['type']=='withdraw'){

            if($request['quantity_before']>=$request['quantity']){
                //looking for method for the deposit
                $actualdeposit=DepositController::find($request['depot_id']);
                if ($actualdeposit) {
                    if ($actualdeposit['withdrawing_method']=="fifo") {
                        # code...
                    }else if($actualdeposit['withdrawing_method']=="lifo"){

                    }else{

                    }
                }
                DB::update('update deposit_services set available_qte = available_qte - ? where service_id = ? and deposit_id = ?',[$request['quantity'],$request['service_id'],$request['depot_id']]);
                if($request['price']){
                    $request['total']=$request['quantity']*$request['price'];
                }

                $request['uuid']=$this->getUuId('C','SH');
                return $this->show(StockHistoryController::create($request->all()));
            }
           
        }else{
            return $response;
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StockHistoryController  $stockHistoryController
     * @return \Illuminate\Http\Response
     */
    public function show(StockHistoryController $stockHistoryController)
    {
       $stock=StockHistoryController::leftjoin('deposit_controllers as D','stock_history_controllers.depot_id','=','D.id')
        ->leftjoin('services_controllers as S','stock_history_controllers.service_id','=','S.id')
        ->leftjoin('unit_of_measure_controllers as UOM','S.uom_id','=','UOM.id')
        ->leftjoin('users as U','stock_history_controllers.user_id','=','U.id')
        ->leftjoin('provider_controllers as P','stock_history_controllers.provider_id','=','P.id')
        ->where('stock_history_controllers.id','=',$stockHistoryController['id'])->get(['stock_history_controllers.*','P.providerName','S.name as service_name','UOM.symbol as uom_symbol','D.name as deposit_name','U.user_name as done_by_name'])[0];
        $stock['total']=$stock['price']*$stock['quantity'];
        $pricecategory=PricesCategories::where('service_id',$stock['service_id'])->where('principal',1)->first();
        if($pricecategory){
            $stock['sellprice']=$pricecategory['price'];
            $stock['benefit']=($pricecategory['price']-$stock['price'])*$stock['quantity'];
        }else{
            $stock['sellprice']=0;
            $stock['benefit']=0;
        }
        return $stock; 
    }

    /**
     * get all story by service id
     */

     public function getbyservice($serviceid){

        return StockHistoryController::leftjoin('deposit_controllers as D','stock_history_controllers.depot_id','=','D.id')
        ->leftjoin('services_controllers as S','stock_history_controllers.service_id','=','S.id')
        ->leftjoin('users as U','stock_history_controllers.user_id','=','U.id')
        ->where('stock_history_controllers.service_id','=',$serviceid)
        ->orderby('stock_history_controllers.created_at','desc')
        ->get(['stock_history_controllers.*','S.name as service_name','D.name as deposit_name','U.user_name as done_by_name']);
     }

     /**
      * get all story by multiple services and periodic 
      * 
     */
     public function multipleservices(Request $request){
        $datatosend=[];
        //find the user
        if(isset($request->user_id) && !empty($request->user_id) && $request->user_id>0){
            $user = $this->getinfosuser($request->user_id);
            if($user){
                if($user['user_type']=='super_admin'){
                    foreach ($request['services'] as $key => $service) {

                        $list=collect(StockHistoryController::where('service_id','=',$service['service']['id'])
                        ->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                        ->orderby('created_at','desc')
                        ->get());
                        $list_data=$list->map(function ($item,$key){
                            return $this->show($item);
                        });
                        array_push($datatosend,$list_data);
                    }
                }
            }
        }
        return $datatosend;
     }

     public function getbyuser(Request $request){
        $grouped_data=$this->getbyusergrouped($request);
        $list_data=[];
        $user=$this->getinfosuser($request['user_id']);
        $enterprise=$this->getEse($user['id']);
        if(empty($request->from) && empty($request->to)){
            $request['from']= date('Y-m-d');
            $request['to']=date('Y-m-d');
        }

        if ($user['user_type']=='super_admin') {
            $deposits=DepositController::where('enterprise_id','=',$enterprise['id'])->get();
            foreach ($deposits as $deposit) {
                $list=collect(StockHistoryController::where('depot_id','=',$deposit['id'])
                ->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                ->orderby('created_at','asc')
                ->get());
                foreach ($list as $item) {
                    array_push($list_data,$this->show($item));
                }
            }
        } else {
            $deposits=DepositsUsers::where('user_id','=',$request->user_id)->get();
            foreach ($deposits as $deposit) {
                $list=collect(StockHistoryController::where('depot_id','=',$deposit->deposit_id)
                ->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                ->orderby('created_at','desc')
                ->get());
                foreach ($list as $item) {
                    array_push($list_data,$this->show($item));
                }
             }
        }
        
        return ['ungrouped'=>$list_data,'grouped'=>$grouped_data,'services_group'=>[],'from'=>$request['from'],'to'=>$request['to'],'tabular'=>$this->newReportStockHistory($request)];
     }
     
     /**
      * get stock history by user based on date operation
      */
     public function getbyuserbasedondateoperation(Request $request){
    
        $list_data=[];
        $user=$this->getinfosuser($request['user_id']);
        $enterprise=$this->getEse($user['id']);
        if(empty($request->from) && empty($request->to)){
            $request['from']= date('Y-m-d');
            $request['to']=date('Y-m-d');
        }

        if ($user['user_type']=='super_admin') {
            $deposits=DepositController::where('enterprise_id','=',$enterprise['id'])->get();
            foreach ($deposits as $deposit) {
                $list=collect(StockHistoryController::where('depot_id','=',$deposit['id'])
                ->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                ->orderby('done_at','asc')
                ->get());
                foreach ($list as $item) {
                    array_push($list_data,$this->show($item));
                }
            }
        } else {
            $deposits=DepositsUsers::where('user_id','=',$request->user_id)->get();
            foreach ($deposits as $deposit) {
                $list=collect(StockHistoryController::where('depot_id','=',$deposit->deposit_id)
                ->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                ->orderby('done_at','asc')
                ->get());
                foreach ($list as $item) {
                    array_push($list_data,$this->show($item));
                }
             }
        }
        
        return response()->json([
            "status"=>200,
            "message"=>"success",
            "data"=>$list_data,
            "from"=>$request['from'],
            'to'=>$request['to']
        ]);
     }
     
     /**
      * report stock grouped by dates
      */
      public function reportstockgroupedbydates(Request $request){
           
        $intervals=[];
        $datatoreturns=[];
        $fromdate=Carbon::parse($request['from']);
        $todate=Carbon::parse($request['to']);

        while($fromdate<=$todate){
            array_push($intervals,$fromdate->toDateString());
            $fromdate->addDay();
        }
        $cumul=$this->newReportStockHistorybasedondateoperation($request)->original;
        foreach ($intervals as $dateoperation) {
            $request['from']=$dateoperation;
            $request['to']=$dateoperation;
            $data=$this->getbyuserbasedondateoperation($request)->original;
            array_push($datatoreturns,$data);
        }
        return response()->json([
            "cumul"=>$cumul['data'],
            "details"=>$datatoreturns
        ]) ;
    
      }

     public function getbyusergrouped(Request $request){
        $list_data=[];
        $user=$this->getinfosuser($request['user_id']);
        $enterprise=$this->getEse($user['id']);
        if(empty($request->from) && empty($request->to)){
            $request['from']= date('Y-m-d');
            $request['to']=date('Y-m-d');
        }

        if ($user['user_type']=='super_admin') {
            $deposits=DepositController::where('enterprise_id','=',$enterprise['id'])->get();
            foreach ($deposits as $deposit) {
                $depositArray=['deposit'=>$deposit,'articles'=>[]];

                $articles= DB::table('stock_history_controllers')
                    ->leftjoin('services_controllers as S','stock_history_controllers.service_id','=','S.id')
                    ->leftjoin('unit_of_measure_controllers as UOM','S.uom_id','=','UOM.id')
                    ->where('stock_history_controllers.depot_id','=',$deposit['id'])
                    ->whereBetween('stock_history_controllers.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                    ->select('stock_history_controllers.service_id','S.name','UOM.symbol',DB::raw('sum(stock_history_controllers.quantity) as quantity_total'))
                    ->groupBy('stock_history_controllers.service_id','S.name','UOM.symbol')
                    ->get();
                    foreach ($articles as $key => $value) {
                        array_push($depositArray['articles'],$value);
                    }
                    array_push($list_data,$depositArray);
            }
        } else {
            $deposits=DepositsUsers::join('deposit_controllers as D','deposits_users.deposit_id','=','D.id')->where('deposits_users.user_id','=',$request->user_id)->get('D.*');
            foreach ($deposits as $deposit) {
                $depositArray=['deposit'=>$deposit,'articles'=>[]];

                $articles= DB::table('stock_history_controllers')
                    ->leftjoin('services_controllers as S','stock_history_controllers.service_id','=','S.id')
                    ->leftjoin('unit_of_measure_controllers as UOM','S.uom_id','=','UOM.id')
                    ->where('stock_history_controllers.depot_id','=',$deposit['id'])
                    ->whereBetween('stock_history_controllers.created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                    ->select('stock_history_controllers.service_id','S.name','UOM.symbol',DB::raw('sum(stock_history_controllers.quantity) as quantity_total'))
                    ->groupBy('stock_history_controllers.service_id','S.name','UOM.symbol')
                    ->get();
                    foreach ($articles as $key => $value) {
                        array_push($depositArray['articles'],$value);
                    }
                    array_push($list_data,$depositArray);
             }
        }
        
        return $list_data;
     } 
     
     /**
      * when filtered looking for report stock for multiples articles 
      */
     public function articlesgetbyusergrouped(Request $request){
        if(empty($request->from) && empty($request->to)){
            $request['from']= date('Y-m-d');
            $request['to']=date('Y-m-d');
        }
        $service_ctrl = new ServicesControllerController();
        $services=collect(ServicesController::whereIn('id',$request['services'])->get());
        $services->transform(function ($service) use ($service_ctrl,$request){
            $service=$service_ctrl->detailwithoutprices($service);
            $entries=StockHistoryController::where('type','=','entry')
            ->where('service_id','=',$service['id'])
            ->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->select(DB::raw('SUM(quantity) as total_entries'))
            ->get('total_entries')->first();
            $service['total_entries']=$entries['total_entries'];
            $withdraw=StockHistoryController::where('type','=','withdraw')
            ->where('service_id','=',$service['id'])
            ->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->select(DB::raw('SUM(quantity) as total_withdraw'))
            ->get('total_withdraw')->first();
            $service['total_withdraw']=$withdraw['total_withdraw'];
            $service['sold']=$service['total_entries']-$withdraw['total_withdraw'];
            return $service;
        });
      
       
        return response()->json([
            "stockmouvements"=>$services,
            "from"=>$request['from'],
            "to"=>$request['to'],
            "totalEntriesStock"=>$services->sum('total_entries'),
            "totalWithdrawStock"=>$services->sum('total_withdraw'),
            "totalSoldStock"=>$services->sum('sold')
        ]);
     }


    /**
      * when filtered looking for report stock for multiples articles based on date operations
      */
     public function articlesgetbyusergroupedbasedoperation(Request $request){
        if(empty($request->from) && empty($request->to)){
            $request['from']= date('Y-m-d');
            $request['to']=date('Y-m-d');
        }

        $service_ctrl = new ServicesControllerController();
        $services=collect(ServicesController::whereIn('id',$request['services'])->get());
        $services->transform(function ($service) use ($service_ctrl,$request){
            $service=$service_ctrl->detailwithoutprices($service);
            $entries=StockHistoryController::where('type','=','entry')
            ->where('service_id','=',$service['id'])
            ->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->select(DB::raw('SUM(quantity) as total_entries'))
            ->get('total_entries')->first();
            $service['total_entries']=$entries['total_entries'];
            $withdraw=StockHistoryController::where('type','=','withdraw')
            ->where('service_id','=',$service['id'])
            ->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->select(DB::raw('SUM(quantity) as total_withdraw'))
            ->get('total_withdraw')->first();
            $service['total_withdraw']=$withdraw['total_withdraw'];
            $service['sold']=$service['total_entries']-$withdraw['total_withdraw'];
            $service['details']=$this->stockhistorybyarticleforaspecifiperiod(
                new Request([
                    "from"=>$request['from'],
                    "to"=>$request['to'],
                    "service_id"=>$service['id']
                ])
            );
            return $service;
        });
      
       
        return response()->json([
            "stockmouvements"=>$services,
            "from"=>$request['from'],
            "to"=>$request['to'],
            "totalEntriesStock"=>$services->sum('total_entries'),
            "totalWithdrawStock"=>$services->sum('total_withdraw'),
            "totalSoldStock"=>$services->sum('sold')
        ]);
     }

     /**
      * details stock history by article for a specifi period
      */
      public function stockhistorybyarticleforaspecifiperiod(Request $request){
        $list_data=[];
        $mouvements=StockHistoryController::where('service_id','=',$request['service_id'])
        ->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
        ->get();
        foreach ($mouvements as $item) {
            array_push($list_data,$this->show($item));
        }
        
        return $list_data;
      }

     public function newReportStockHistory(Request $request){
        $list_data=[];
        $serviceCtrl= new ServicesControllerController();
        $user=$this->getinfosuser($request['user_id']);
        $enterprise=$this->getEse($user['id']);
        if(empty($request->from) && empty($request->to)){
            $request['from']= date('Y-m-d');
            $request['to']=date('Y-m-d');
        }

        if ($user['user_type']=='super_admin') {
            $services=StockHistoryController::where('enterprise_id','=',$enterprise['id'])
            ->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->select('service_id')
            ->groupBy('service_id')
            ->get();
            foreach ($services as $service) {
                $entries=StockHistoryController::select(DB::raw('sum(quantity) as totalEntries'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('service_id','=',$service['service_id'])->where('type','=','entry')->get('totalEntries')->first();
                $withdraw=StockHistoryController::select(DB::raw('sum(quantity) as totalWithdraw'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('service_id','=',$service['service_id'])->where('type','=','withdraw')->get('totalWithdraw')->first();
                $before=StockHistoryController::select(DB::raw('sum(quantity_before) as totalBefore'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('service_id','=',$service['service_id'])->get('totalBefore')->first();
                $soldinventory=DepositServices::join('deposit_controllers as DC','deposit_services.deposit_id','=','DC.id')->select(DB::raw('sum(available_qte) as soldinventory'))->where('service_id','=',$service['service_id'])->where('DC.enterprise_id','=',$enterprise['id'])->get('soldinventory')->first();
                
                $service['totalEntries']=$entries['totalEntries'];
                $service['totalWithdraw']=$withdraw['totalWithdraw'];
                $service['sold']=$entries['totalEntries']-$withdraw['totalWithdraw'];
                $service['totalBefore']=$before['totalBefore'];
                $service['soldinventory']=$soldinventory['soldinventory'];
               
               $service['service']=$serviceCtrl->show(ServicesController::find($service['service_id']))['service'];
                array_push($list_data,$service);
            }
          
        } else {
            $deposits=DepositsUsers::join('deposit_controllers as D','deposits_users.deposit_id','=','D.id')->where('deposits_users.user_id','=',$request->user_id)->get('D.*');
            foreach ($deposits as $deposit) {
                $services=StockHistoryController::where('depot_id','=',$deposit->id)
                ->where('user_id','=',$request->user_id)
                ->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                ->select('service_id')
                ->groupBy('service_id')
                ->get();

                foreach ($services as $service) {
                    $entries=StockHistoryController::select(DB::raw('sum(quantity) as totalEntries'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('depot_id','=',$deposit->id)->where('service_id','=',$service['service_id'])->where('type','=','entry')->where('user_id','=',$request->user_id)->get('totalEntries')->first();
                    $withdraw=StockHistoryController::select(DB::raw('sum(quantity) as totalWithdraw'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('depot_id','=',$deposit->id)->where('service_id','=',$service['service_id'])->where('type','=','withdraw')->where('user_id','=',$request->user_id)->get('totalWithdraw')->first();
                    $before=StockHistoryController::select(DB::raw('sum(quantity_before) as totalBefore'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('depot_id','=',$deposit->id)->where('service_id','=',$service['service_id'])->where('user_id','=',$request->user_id)->get('totalBefore')->last();
                    $soldinventory=DepositServices::join('deposits_users as DU','deposit_services.deposit_id','=','DU.deposit_id')->select(DB::raw('sum(available_qte) as soldinventory'))->where('service_id','=',$service['service_id'])->where('DU.user_id','=',$request->user_id)->get('soldinventory')->first();
                    $service['totalEntries']=$entries['totalEntries'];
                    $service['totalWithdraw']=$withdraw['totalWithdraw'];
                    $service['sold']=$entries['totalEntries']-$withdraw['totalWithdraw'];
                    $service['totalBefore']=$before['totalBefore'];
                    $service['soldinventory']=$soldinventory['soldinventory'];
                    $service['service']=$serviceCtrl->show(ServicesController::find($service['service_id']))['service'];
                    array_push($list_data,$service);
                }
                    
             }
        }
        
        return response()->json([
            'data'=>$list_data,
            'from'=>$request['from'],
            'to'=>$request['to']
        ]);
     }  
     
     public function newReportStockHistorybasedondateoperation(Request $request){
        $list_data=[];
        $serviceCtrl= new ServicesControllerController();
        $user=$this->getinfosuser($request['user_id']);
        $enterprise=$this->getEse($user['id']);
        if(empty($request->from) && empty($request->to)){
            $request['from']= date('Y-m-d');
            $request['to']=date('Y-m-d');
        }

        if ($user['user_type']=='super_admin') {
            $services=StockHistoryController::where('enterprise_id','=',$enterprise['id'])
            ->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->select('service_id')
            ->groupBy('service_id')
            ->get();
            foreach ($services as $service) {
                $entries=StockHistoryController::select(DB::raw('sum(quantity) as totalEntries'))->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('service_id','=',$service['service_id'])->where('type','=','entry')->get('totalEntries')->first();
                $withdraw=StockHistoryController::select(DB::raw('sum(quantity) as totalWithdraw'))->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('service_id','=',$service['service_id'])->where('type','=','withdraw')->get('totalWithdraw')->first();
                $soldinventory=DepositServices::join('deposit_controllers as DC','deposit_services.deposit_id','=','DC.id')->select(DB::raw('sum(available_qte) as soldinventory'))->where('service_id','=',$service['service_id'])->where('DC.enterprise_id','=',$enterprise['id'])->get('soldinventory')->first();
                $service['totalBefore']=0;
                $deposits=DepositController::where('enterprise_id','=',$enterprise['id'])->get();
                foreach ($deposits as $deposit) {
                    $stockbefore=StockHistoryController::where('service_id','=',$service['service_id'])
                    ->where('depot_id','=',$deposit->id)
                    ->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                    ->first();
                    if ($stockbefore) {
                        $service['totalBefore']=$service['totalBefore']+ $stockbefore->quantity_before;
                    }
                    
                 }

                $service['totalEntries']=$entries['totalEntries'];
                $service['totalWithdraw']=$withdraw['totalWithdraw'];
                $service['sold']=$entries['totalEntries']-$withdraw['totalWithdraw'];
                
                $service['soldinventory']= ($service['totalBefore']+$service['totalEntries'])-$service['totalWithdraw'];
               
               $service['service']=$serviceCtrl->show(ServicesController::find($service['service_id']))['service'];
                array_push($list_data,$service);
            }
          
        } else {
            $deposits=DepositsUsers::join('deposit_controllers as D','deposits_users.deposit_id','=','D.id')->where('deposits_users.user_id','=',$request->user_id)->get('D.*');
            foreach ($deposits as $deposit) {
                $services=StockHistoryController::where('depot_id','=',$deposit->id)
                ->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                ->select('service_id')
                ->groupBy('service_id')
                ->get();

                foreach ($services as $service) {
                    $entries=StockHistoryController::select(DB::raw('sum(quantity) as totalEntries'))->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('depot_id','=',$deposit->id)->where('service_id','=',$service['service_id'])->where('type','=','entry')->get('totalEntries')->first();
                    $withdraw=StockHistoryController::select(DB::raw('sum(quantity) as totalWithdraw'))->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('depot_id','=',$deposit->id)->where('service_id','=',$service['service_id'])->where('type','=','withdraw')->get('totalWithdraw')->first();
                    $before=StockHistoryController::select(DB::raw('quantity_before as totalBefore'))->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('depot_id','=',$deposit->id)->where('service_id','=',$service['service_id'])->get('totalBefore')->first();
                    $soldinventory=DepositServices::join('deposits_users as DU','deposit_services.deposit_id','=','DU.deposit_id')->select(DB::raw('sum(available_qte) as soldinventory'))->where('service_id','=',$service['service_id'])->where('deposit_services.deposit_id','=',$deposit->id)->get('soldinventory')->first();
                    $service['totalEntries']=$entries['totalEntries'];
                    $service['totalWithdraw']=$withdraw['totalWithdraw'];
                    $service['sold']=$entries['totalEntries']-$withdraw['totalWithdraw'];
                    $service['totalBefore']=$before['totalBefore'];
                    $service['soldinventory']=$soldinventory['soldinventory'];
                    $service['service']=$serviceCtrl->show(ServicesController::find($service['service_id']))['service'];
                    array_push($list_data,$service);
                }
                    
             }
        }
        
        return response()->json([
            'data'=>$list_data,
            'from'=>$request['from'],
            'to'=>$request['to']
        ]);
     } 

     public function fordeposit(Request $request){

        $list=collect(StockHistoryController::where('depot_id','=',$request->deposit_id)->orderby('created_at','desc')->get());
        $list_data=$list->map(function ($item,$key){
            return $this->show($item);
        });
        return $list_data;
     }

     /**
      * report by deposits and articles
      */
      public function reportbydeposits(Request $request){
        $deposits=[];
        $serviceCtrl= new ServicesControllerController();
        $user=$this->getinfosuser($request['user_id']);
        $enterprise=$this->getEse($user['id']);
        if(empty($request->from) && empty($request->to)){
            $request['from']= date('Y-m-d');
            $request['to']=date('Y-m-d');
        }

        if (isset($request['deposits']) && !empty($request['deposits'])) {
            $deposits=collect(DepositController::whereIn('id',$request['deposits'])->get());
            $deposits->transform(function ($deposit) use ($request){
                if (isset($request['services']) && !empty($request['services'])) {
                    //if there are services sent
                    $services=collect(ServicesController::leftjoin('categories_services_controllers as C', 'services_controllers.category_id','=','C.id')
                                                        ->leftjoin('unit_of_measure_controllers as U','services_controllers.uom_id','=','U.id')
                                                        ->whereIn('services_controllers.id',$request['services'])
                                                        ->get(['services_controllers.*','C.name as category_name','U.symbol as uom_symbol']));
                    $services->transform(function ($service) use ($request,$deposit){
                        $entries=StockHistoryController::select(DB::raw('sum(quantity) as total_entries'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('service_id','=',$service['id'])->where('type','=','entry')->where('depot_id','=',$deposit['id'])->get('total_entries')->first();
                        $withdraw=StockHistoryController::select(DB::raw('sum(quantity) as total_withdraw'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('service_id','=',$service['id'])->where('type','=','withdraw')->where('depot_id','=',$deposit['id'])->get('total_withdraw')->first();
                        $service['total_entries']=$entries['total_entries'];
                        $service['total_withdraw']=$withdraw['total_withdraw'];
                        $service['sold']=$entries['total_entries']-$withdraw['total_withdraw'];
                        return $service;
                    });
                    $deposit['services']=$services;
                    $deposit['total_entries']=$services->sum('total_entries');
                    $deposit['total_withdraw']=$services->sum('total_withdraw');
                    $deposit['total_sold']=$services->sum('sold');
                }else{
                    //if no services sent
                        $services=collect(StockHistoryController::where('depot_id','=',$deposit['id'])
                        ->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                        ->select('service_id')
                        ->groupBy('service_id')
                        ->get());
                        $services->transform(function ($service) use ($request,$deposit){
                            $service=ServicesController::leftjoin('categories_services_controllers as C', 'services_controllers.category_id','=','C.id')
                            ->leftjoin('unit_of_measure_controllers as U','services_controllers.uom_id','=','U.id')
                            ->where('services_controllers.id',$service['service_id'])
                            ->get(['services_controllers.*','C.name as category_name','U.symbol as uom_symbol'])->first();
                            $entries=StockHistoryController::select(DB::raw('sum(quantity) as total_entries'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('service_id','=',$service['id'])->where('type','=','entry')->where('depot_id','=',$deposit['id'])->get('total_entries')->first();
                            $withdraw=StockHistoryController::select(DB::raw('sum(quantity) as total_withdraw'))->whereBetween('created_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('service_id','=',$service['id'])->where('type','=','withdraw')->where('depot_id','=',$deposit['id'])->get('total_withdraw')->first();
                            $service['total_entries']=$entries['total_entries'];
                            $service['total_withdraw']=$withdraw['total_withdraw'];
                            $service['sold']=$entries['total_entries']-$withdraw['total_withdraw'];
                            return $service;
                        });
                        $deposit['total_entries']=$services->sum('total_entries');
                        $deposit['total_withdraw']=$services->sum('total_withdraw');
                        $deposit['total_sold']=$services->sum('sold');
                        
                    $deposit['services']=$services;      
                }
                return $deposit;
            });
        }
       
        return response()->json([
            'data'=>$deposits,
            'from'=>$request['from'],
            'to'=>$request['to']
        ]);
      }
      
      /**
      * report by deposits and articles based on date operation
      */
      public function reportbydepositsbasedondateoperation(Request $request){
        $deposits=[];
        $serviceCtrl= new ServicesControllerController();
        $user=$this->getinfosuser($request['user_id']);
        $enterprise=$this->getEse($user['id']);
        if(empty($request->from) && empty($request->to)){
            $request['from']= date('Y-m-d');
            $request['to']=date('Y-m-d');
        }

        if (isset($request['deposits']) && !empty($request['deposits'])) {
            $deposits=collect(DepositController::whereIn('id',$request['deposits'])->get());
            $deposits->transform(function ($deposit) use ($request){
                if (isset($request['services']) && !empty($request['services'])) {
                    //if there are services sent
                    $services=collect(ServicesController::leftjoin('categories_services_controllers as C', 'services_controllers.category_id','=','C.id')
                                                        ->leftjoin('unit_of_measure_controllers as U','services_controllers.uom_id','=','U.id')
                                                        ->whereIn('services_controllers.id',$request['services'])
                                                        ->get(['services_controllers.*','C.name as category_name','U.symbol as uom_symbol']));
                    $services->transform(function ($service) use ($request,$deposit){
                        $entries=StockHistoryController::select(DB::raw('sum(quantity) as total_entries'))->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('service_id','=',$service['id'])->where('type','=','entry')->where('depot_id','=',$deposit['id'])->get('total_entries')->first();
                        $withdraw=StockHistoryController::select(DB::raw('sum(quantity) as total_withdraw'))->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('service_id','=',$service['id'])->where('type','=','withdraw')->where('depot_id','=',$deposit['id'])->get('total_withdraw')->first();
                        $service['total_entries']=$entries['total_entries'];
                        $service['total_withdraw']=$withdraw['total_withdraw'];
                        $service['sold']=$entries['total_entries']-$withdraw['total_withdraw'];
                       
                        return $service;
                    });
                  
                    $deposit['services']=$services;
                    $deposit['total_entries']=$services->sum('total_entries');
                    $deposit['total_withdraw']=$services->sum('total_withdraw');
                    $deposit['total_sold']=$services->sum('sold');
                    $deposit['details']=$this->detailsdepositwitharticlesincoming(new Request([
                        "from"=>$request['from'],
                        "to"=>$request['to'],
                        "deposit_id"=>$deposit['id'],
                        "services"=>$request['services']
                    ]));
                }else{
                    //if no services sent
                        $services=collect(StockHistoryController::where('depot_id','=',$deposit['id'])
                        ->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
                        ->select('service_id')
                        ->groupBy('service_id')
                        ->get());
                        $services->transform(function ($service) use ($request,$deposit){
                            $service=ServicesController::leftjoin('categories_services_controllers as C', 'services_controllers.category_id','=','C.id')
                            ->leftjoin('unit_of_measure_controllers as U','services_controllers.uom_id','=','U.id')
                            ->where('services_controllers.id',$service['service_id'])
                            ->get(['services_controllers.*','C.name as category_name','U.symbol as uom_symbol'])->first();
                            $entries=StockHistoryController::select(DB::raw('sum(quantity) as total_entries'))->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('service_id','=',$service['id'])->where('type','=','entry')->where('depot_id','=',$deposit['id'])->get('total_entries')->first();
                            $withdraw=StockHistoryController::select(DB::raw('sum(quantity) as total_withdraw'))->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])->where('service_id','=',$service['id'])->where('type','=','withdraw')->where('depot_id','=',$deposit['id'])->get('total_withdraw')->first();
                            $service['total_entries']=$entries['total_entries'];
                            $service['total_withdraw']=$withdraw['total_withdraw'];
                            $service['sold']=$entries['total_entries']-$withdraw['total_withdraw'];
                            return $service;
                        });
                        $deposit['total_entries']=$services->sum('total_entries');
                        $deposit['total_withdraw']=$services->sum('total_withdraw');
                        $deposit['total_sold']=$services->sum('sold');
                        $deposit['services']=$services;
                        $deposit['details']=$this->detailsdepositwitharticlesincoming(new Request([
                            "from"=>$request['from'],
                            "to"=>$request['to'],
                            "deposit_id"=>$deposit['id'],
                            "services"=>[]
                        ]));      
                }
                return $deposit;
            });
        }
       
        return response()->json([
            'data'=>$deposits,
            'from'=>$request['from'],
            'to'=>$request['to']
        ]);
      }

    /**
     * details by deposit with articles incoming
     */
    public function detailsdepositwitharticlesincoming(Request $request){
        $list_data=[];
       
        if (count($request['services'])>0) {
            $list=StockHistoryController::where('depot_id','=',$request['deposit_id'])
            ->whereIn('service_id',$request['services'])
            ->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->orderby('done_at','asc')
            ->get();
            foreach ($list as $item) {
                array_push($list_data,$this->show($item));
            }
        }else{
            $list=StockHistoryController::where('depot_id','=',$request['deposit_id'])
            ->whereBetween('done_at',[$request['from'].' 00:00:00',$request['to'].' 23:59:59'])
            ->orderby('done_at','asc')
            ->get();
            foreach ($list as $item) {
                array_push($list_data,$this->show($item));
            }
        }
       

        return $list_data;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\StockHistoryController  $stockHistoryController
     * @return \Illuminate\Http\Response
     */
    public function edit(StockHistoryController $stockHistoryController)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateStockHistoryControllerRequest  $request
     * @param  \App\Models\StockHistoryController  $stockHistoryController
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateStockHistoryControllerRequest $request, StockHistoryController $stockHistoryController)
    {
        try {
            if ($request['id']) {
                $stockfind=StockHistoryController::find($request['id']);
                if ($stockfind) {
                    $stockfind->price=$request['price'];
                    $stockfind->provider_id=$request['provider_id'];
                    $stockfind->type_approvement=$request['type_approvement'];
                    $stockfind->palette=$request['palette'];
                    $stockfind->motif=$request['motif'];
                    $stockfind->note=$request['note'];
                    $stockfind->done_at=$request['done_at'];
                    $stockfind->total=$request['price']*$stockfind->quantity;
                    $stockfind->save();
                    return response()->json([
                        "status"=>200,
                        "message"=>"success",
                        "error"=>null,
                        "data"=>$this->show($stockfind)
                    ]);
                }else{
                    return response()->json([
                        "status"=>400,
                        "message"=>"error",
                        "error"=>'not find',
                        "data"=>null
                    ]); 
                }
            }else{
                return response()->json([
                    "status"=>400,
                    "message"=>"error",
                    "error"=>'not sent',
                    "data"=>null
                ]);
            }
        } catch (Exception $th) {
            return response()->json([
                "status"=>500,
                "message"=>"error",
                "error"=>$th->getMessage(),
                "data"=>null
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StockHistoryController  $stockHistoryController
     * @return \Illuminate\Http\Response
     */
    public function destroy(StockHistoryController $stockHistoryController)
    {
        //
    }
}
