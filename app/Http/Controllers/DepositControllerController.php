<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DepositsUsers;
use App\Models\DepositServices;
use App\Models\DepositController;
use App\Models\DepositsCategories;
use App\Models\ServicesController;
use Illuminate\Support\Facades\DB;
use App\Models\StockHistoryController;
use App\Models\CategoriesServicesController;
use App\Http\Requests\StoreDepositControllerRequest;
use App\Http\Requests\UpdateDepositControllerRequest;

class DepositControllerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterpriseid)
    {
    
        $data=collect(DepositController::where('enterprise_id','=',$enterpriseid)->get());
        $data=$data->map(function ($item){
            $item['categories']=$this->getcategories($item['id']);
            return $item;
        });

        return $data;
    }

    /**
     * reset a specific deposit
     */
    public function reset(Request $request){
        $user=$this->getinfosuser($request['user_id']);
        $counter=0;
        $inventory=[];
        $message="";
        if ($user) {
            $inventory=DepositServices::where('deposit_id','=',$request['deposit_id'])->get();
            foreach ($inventory as $item) {
                if ($item['available_qte']>0) {
                    $update=DB::update('update deposit_services set available_qte =0 where id = ?',[$item['id']]);
                    if ($update) {
                        $counter ++;
                        //make a stock history
                        if (isset($request['motif'])==false) {
                            $request['motif']="Réinitialisation stock";
                        }
                        StockHistoryController::create([
                            'service_id'=>$item['service_id'],
                            'user_id'=>$user['id'],
                            'invoice_id'=>0,
                            'quantity'=>$item['available_qte'],
                            'price'=>0,
                            'type'=>'withdraw',
                            'type_approvement'=>"cash",
                            'enterprise_id'=>$this->getEse($user['id'])['id'],
                            'motif'=>$request['motif'],
                            'depot_id'=>$request['deposit_id'],
                            'quantity_before'=>$item->available_qte,
                        ]);
                    }
                }
               
            }
        }else{
            $message="unknown user";
        }
       
        return response()->json(
            [
                "all"=>$inventory->count(),
                "updated"=>$counter,
                "message"=>$message
            ]);
    }

    /**
     * For a specific users.. where he's affected
     */
    public function depositForUser(Request $request){
        $deposits=[];
        $user=$this->getinfosuser($request['user_id']);
        $enterprise=$this->getEse($user['id']);
        if ($user['user_type']=='super_admin') {
            $deposits=$this->index($enterprise['id']);
            // $deposits=DepositController::where('enterprise_id','=',$enterprise['id'])->get();
        } else {
            $deposits=DepositsUsers::join('deposit_controllers as D','deposits_users.deposit_id','=','D.id')->where('deposits_users.user_id','=',$request->user_id)->get('D.*');
        }
        
        return $deposits; 
    }
    /**
     * Get participants
     */
    public function participants(Request $request){
        return DepositsUsers::leftjoin('users as U','deposits_users.user_id','=','U.id')->where('deposit_id','=',$request->deposit_id)->get(['U.user_name','U.note','U.avatar','deposits_users.*']);
    }

    public function affectagents(Request $request){
        
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
     * @param  \App\Http\Requests\StoreDepositControllerRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDepositControllerRequest $request)
    {
        $new=DepositController::create($request->all());

        //if type is group
        if(isset($request->type) && $request->type==='group'){
            $categories=CategoriesServicesController::where('enterprise_id','=',$request->enterprise_id)->get();
            foreach ($categories as $categ) {
                //affect category to deposit
                DepositsCategories::create([
                    'category_id'=>$categ->id,
                    'deposit_id'=>$new->id
                ]);
                //getting its services and putting them in depotServices model
                $services=ServicesController::where('category_id','=',$categ->id)->get();
                foreach ($services as $service) {
                    DepositServices::create([
                        'deposit_id'=>$new->id,
                        'service_id'=>$service->id,
                        'available_qte'=>0
                    ]);
                }
            }
        }elseif(isset($request->type) && $request->type==='category'){
            foreach ($request->categories as $categ) {
                //affect category to deposit
                DepositsCategories::create([
                    'category_id'=>$categ['id'],
                    'deposit_id'=>$new['id']
                ]);
                //getting its services and putting them in depotServices model
                $services=ServicesController::where('category_id','=',$categ['id'])->get();
                foreach ($services as $service) {
                    DepositServices::create([
                        'deposit_id'=>$new->id,
                        'service_id'=>$service->id,
                        'available_qte'=>0
                    ]);
                }
            }
        }else{

        }

        return $this->show($new);
    }

    /**
     * Add services to a specific deposit
     */
    public function addservices(Request $request){
        // return $request;
        $services=[];

        foreach ($request->services as $service) {
            $new=DepositServices::create([
                'deposit_id'=>$request->depositId,
                'service_id'=>$service['service']['id'],
                'available_qte'=>0
            ]);
            if($new){
                $funded=$this->showService(servicesController::find($new->service_id));
                array_push($services,$funded);
            }
        }
        //getting services for each deposit
                    
        return $services;
    }   
    
    /**
     * Add services to a specific deposit
     */
    public function withdrawServices(Request $request){
        $counter=0;
        
        if (isset($request->services) && count($request->services)>0) {
            foreach ($request->services as $service) {
                $ifexist=DepositServices::where('service_id','=',$service['service_id'])->where('deposit_id','=',$service['deposit_id'])->first();
                if($ifexist){
                    $ifexist->delete();
                    $counter ++;
                    $service['deleted']=1;
                }else{
                    $service['deleted']=0;  
                }
            }
        }

        $request['number']=$counter;       
        return $request;
    }

    /**
     * categories for deposit
     */
    public function getcategories($deposit){
        $actual=DepositController::find($deposit);
        if($actual['type']==="group"){
            return CategoriesServicesController::where('enterprise_id','=',$actual['enterprise_id'])->get();
        }else{
            return CategoriesServicesController::join('deposits_categories as DC','categories_services_controllers.id','=','DC.category_id')->Where('DC.deposit_id','=',$actual['id'])->get('categories_services_controllers.*');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DepositController  $depositController
     * @return \Illuminate\Http\Response
     */
    public function show(DepositController $depositController)
    {
        return DepositController::find($depositController->id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DepositController  $depositController
     * @return \Illuminate\Http\Response
     */
    public function edit(DepositController $depositController)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDepositControllerRequest  $request
     * @param  \App\Models\DepositController  $depositController
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDepositControllerRequest $request, DepositController $depositController)
    {
      return $depositController->update($request->all());
    }

    public function update2(Request $request,$id)
    {
        $deposit=DepositController::find($id);
        $deposit->update($request->all());

        return $deposit;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DepositController  $depositController
     * @return \Illuminate\Http\Response
     */
    public function destroy(DepositController $depositController)
    {
        return DepositController::destroy($depositController);
    }

    public function delete2($id){
        $deposit=DepositController::find($id);
        //deleting users
        DepositsUsers::where('deposit_id','=',$id)->delete(); //deleting users
        DepositsCategories::where('deposit_id','=',$id)->delete(); //deleting categories
        DepositServices::where('deposit_id','=',$id)->delete(); //deleting services
        // StockHistoryController::where('deposit_id','=',$id)->delete(); //deleting stockhistory
        
       return DepositController::find($id)->delete();
    }
}
