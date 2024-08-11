<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DepositsUsers;
use App\Models\DepositServices;
use App\Models\PricesCategories;
use App\Models\DepositController;
use App\Models\ServicesController;
use App\Models\StockHistoryController;

class PressingServicesController extends Controller
{
    /**
     * services list for a specific user
     */
    public function services_list(Request $request){ 
        $serviceCtrl = new ServicesControllerController();
        return $serviceCtrl->services_list($request);
    }

    /**
     * Store
     */
    public function store(Request $request){
        $serviceCtrl = new ServicesControllerController();
        return $serviceCtrl->store($request);
    }

     /**
     * getting detail for a service in deposit
     */
    public function servicedetail(DepositServices $servicesController)
    {
        $serviceCtrl = new ServicesControllerController();
        return $serviceCtrl->servicedetail($servicesController);
    }

    public function destroy2($id)
    {
        $serviceCtrl = new ServicesControllerController();
        return $serviceCtrl->destroy2($id);
    }
}
