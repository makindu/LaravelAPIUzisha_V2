<?php

namespace App\Http\Controllers;

use App\Models\DepositController;
use App\Models\User;
use App\Models\moneys;
use App\Models\Enterprises;
use App\Models\enterprisesettings;
use App\Models\funds;
use App\Models\Invoices;
use App\Models\libraries;
use Illuminate\Support\Str;
use App\Models\PricesCategories;
use App\Models\ServicesController;
use App\Models\usersenterprise;
use App\Models\wekamemberaccounts;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="CERO UZISHA REST API DOCUMENTATION",
 *      description="L5 Swagger OpenApi for Cero Point of sale",
 *      @OA\Contact(
 *          email="kilimbanyifabrice@gmail.com"
 *      ),
 *     @OA\License(
 *         name="Apache 2.0",
 *         url="https://www.apache.org/licenses/LICENSE-2.0.html"
 *     )
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
     /**
     * general method grouped by moneys
     */

    public function generalmethodgroupedbymoneys(Request $request){
        $moneys=collect(moneys::where('enterprise_id',$request['enterprise_id'])->get());
        if ($request['filter']=='entries_requesthistory') {

            $moneys->transform(function ($money) use($request){ 
                $money['total']=0;
                $money['total']=$money['total']+($request['data']->where('money_id','=', $money['id'])->sum($request['columnsumb'])); 
                return $money;
            });
            return $moneys;
        }  
        
        if ($request['filter']=='withdraw_requesthistory') {

            $moneys->transform(function ($money) use($request){ 
                $money['total']=0;
                $money['total']= $money['total']+($request['data']->where('money_id','=', $money['id'])->sum($request['columnsumb']));
                return $money;
            });
            return $moneys;
        } 
        
        if ($request['filter']=='funds') {
        
            $moneys->transform(function ($money) use($request){ 
                $money['total']=0;
                $money['total']= $money['total']+($request['data']->where('money_id','=', $money['id'])->sum($request['columnsumb']));
                return $money;
            });
            return $moneys;
        }

        if ($request['filter']=="solds_net_from_request_histories") {
            $moneys->transform(function ($money) use($request){ 
                $money['totalgeneral']=0;
                // $money['totalgeneral']=$money['subtotalsold']+($request['data']->where('money_id','=', $money['id'])->sum($request['columnsumb']));
                return $money;
            });
            return $moneys;
        }
           
    }

    public function listfunds($enterpriseId, string $criteria){
        if ($criteria==="all") {
            return funds::where('enterprise_id',$enterpriseId)->get();
        }
        
        if ($criteria==="bank") {
            return funds::where('enterprise_id',$enterpriseId)->where('type','bank')->get();
        }

    }
    
    public function userenterpriseaffectation($user_id,$enterpriseId){
        return usersenterprise::where('enterprise_id',$enterpriseId)->where('user_id',$user_id)->first();
    }
    
    public function enterpriseSettings($enterpriseid){
        $storage=enterprisesettings::where('enterprise_id',$enterpriseid)->first();
        $images = Libraries::where('enterprise_id',$enterpriseid)->whereIn('extension',['png','jpg','jpeg'])->get();
        $docs = Libraries::where('enterprise_id',$enterpriseid)->whereNotIn('extension',['png','jpg','jpeg'])->get();
        $sizeimages=$images->sum('size');
        $sizedocs=$docs->sum('size');

        $totalstorage=($storage->storage)/1024000;
        $totalmedias=($sizeimages)/1024000;
        $totaldocs=($sizedocs)/1024000;
        $totalused=($totalmedias/1024000)+($totaldocs/1024000);
        $totalremain=$totalstorage-$totalused;

        return json_encode([
            "storage_allocated"=>$totalstorage,
            "medias_used"=>$totalmedias,
            "docs_used"=>$totaldocs,
            "total_used"=>$totalused,
            "remaining"=>$totalremain,
        ]);
    }

    public function reamingstorage($enterpriseId){
        $storage=json_decode($this->enterpriseSettings($enterpriseId));
        return $storage->remaining;
    }

    public function updaterequeststatus(int $requestid,string $status){

        $update=DB::update('update requests set status = ? where id = ?',[$status,$requestid]);
    }

    public function defaultmoney($enterpriseid){
        return moneys::where('enterprise_id','=',$enterpriseid)->where('principal','=',1)->first();
    }

    public function showService(ServicesController $servicesController)
    {
        $prices=PricesCategories::leftjoin('moneys as M','prices_categories.money_id','=','M.id')
        ->where('prices_categories.service_id','=',$servicesController->id)
        ->get(['M.money_name','M.abreviation','prices_categories.*']);

        $service=ServicesController::leftjoin('categories_services_controllers as C', 'services_controllers.category_id','=','C.id')
        ->leftjoin('unit_of_measure_controllers as U','services_controllers.uom_id','=','U.id')
        ->leftjoin('deposit_services','services_controllers.id','=','deposit_services.service_id')
        ->where('services_controllers.id', '=', $servicesController->id)
        ->get(['deposit_services.available_qte','C.name as category_name','U.name as uom_name','U.symbol as uom_symbol','services_controllers.*'])[0];
        
        return ['service'=>$service,'prices'=>$prices];
    }

    public function getinfosuser($user_id){
        return User::find($user_id);
    }

    public function getEse($user_id){
        $enterprise=Enterprises::leftjoin('usersenterprises as UE', 'enterprises.id','=','UE.enterprise_id')->where('UE.user_id','=',$user_id)->get(['enterprises.*'])->first();
        if ($enterprise) {
            $enterprise['settings']=enterprisesettings::where('enterprise_id',$enterprise->id)->get()->first();
            return $enterprise;
        }else{
            return response()->json((object)[]);
        }  
    }

    public function isactivatedEse($EseId){
       $activation=Enterprises::where('id','=',$EseId)->get('status')[0];
       if($activation['status']=='enabled'){
        return true;
       }else{
        return false;
       }
    } 
    
    public function EseNumberUsers($EseId){
        
       $users=user::leftjoin('usersenterprises as UE','users.id','=','UE.user_id')
                    ->where('UE.enterprise_id','=',$EseId) 
                    ->get();
        return $users->count();
    
    }  
    
    public function EseNumberAccounts($EseId){
        
       $accounts=wekamemberaccounts::where('enterprise_id','=',$EseId)->get();
        return $accounts->count();
    }

    public function getStringUUID(){
        // return (string) Str::uuid();
        return (string) Str::orderedUuid();
    }

    public function getUuId($criteria1,$criteria2){
       
        return $criteria1.date('Y').'.'.date('his').'.'.$criteria2.date('sh');
    }
    
    public function getinvoiceUuid($EseId){
        $lastinvoice= DB::table('invoices')->latest('created_at')->first();
       if($lastinvoice){
        return ;
        $newinvoicenumber='F'.Carbon::now()->format('YmdHis').'C'.$lastinvoice['id']+1+$EseId;
       }
       else{
        $newinvoicenumber='F'.Carbon::now()->format('YmdHis').'C'.$EseId;
       }
       
        return $newinvoicenumber;
    }

    public function getdefaultmoney($EseId){
        return moneys::where('enterprise_id','=',$EseId)->where('principal','=',1)->get()[0];
    }

    public function defaultdeposit($EseId){
        return DepositController::where('enterprise_id','=',$EseId)->first();
    }
}
