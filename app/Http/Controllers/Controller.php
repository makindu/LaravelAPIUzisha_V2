<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\moneys;
use App\Models\Enterprises;
use Illuminate\Support\Str;
use App\Models\PricesCategories;
use App\Models\ServicesController;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
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
        return Enterprises::leftjoin('usersenterprises as UE', 'enterprises.id','=','UE.enterprise_id')->where('UE.user_id','=',$user_id)->get(['enterprises.*'])[0];
    }

    public function isactivatedEse($EseId){
       $activation=Enterprises::where('id','=',$EseId)->get('status')[0];
       if($activation['status']=='enabled'){
        return true;
       }else{
        return false;
       }
    }
    public function getStringUUID(){
        // return (string) Str::uuid();
        return (string) Str::orderedUuid();
    }

    public function getUuId($criteria1,$criteria2){
       
        return $criteria1.date('Ymd').'.'.date('his').'.'.$criteria2.time();
    }

    public function getdefaultmoney($EseId){
        return moneys::where('enterprise_id','=',$EseId)->where('principal','=',1)->get()[0];
    }
}
