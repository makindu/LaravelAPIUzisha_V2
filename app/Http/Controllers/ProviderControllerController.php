<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProviderController;
use App\Http\Requests\StoreProviderControllerRequest;
use App\Http\Requests\UpdateProviderControllerRequest;
use App\Models\StockHistoryController;

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

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProviderController  $providerController
     * @return \Illuminate\Http\Response
     */
    public function show(ProviderController $providerController)
    {
        return ProviderController::leftjoin('enterprises as E', 'provider_controllers.enterprise_id','=','E.id')
        ->leftjoin('users as U', 'provider_controllers.created_by_id','=','U.id')
        ->where('provider_controllers.id', '=', $providerController->id)
        ->get(['provider_controllers.*','E.name as enterprise_name','U.user_name'])[0];
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

       return StockHistoryController::leftjoin('deposit_controllers as D','stock_history_controllers.depot_id','=','D.id')
       ->leftjoin('services_controllers as S','stock_history_controllers.service_id','=','S.id')
       ->leftjoin('users as U','stock_history_controllers.user_id','=','U.id')
       ->where('provider_id','=',$providerid)->get(['stock_history_controllers.*','S.name as service_name','D.name as deposit_name','U.user_name as done_by_name']);
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
