<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PricesCategories;
use App\Http\Requests\StorePricesCategoriesRequest;
use App\Http\Requests\UpdatePricesCategoriesRequest;
use App\Models\moneys;
use Illuminate\Http\Request as HttpRequest;

class PricesCategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $list=collect(PricesCategories::all());
        $listdata=$list->map(function ($item,$key){
            return $this->show($item);
        });
        return $listdata;
    }

    public function foraservice($service_id){

        $list=collect(PricesCategories::where('service_id','=',$service_id)->get());
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
     * @param  \App\Http\Requests\StorePricesCategoriesRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePricesCategoriesRequest $request)
    {
        //looking money has been sent
        if(empty($request->money_id) || $request->money_id<1 ){
            if(isset($request->enterprise_id) && $request->enterprise_id>0 ){
                $principal_money=moneys::where('enterprise_id','=',$request->enterprise_id)->where('principal','=',1)->get()[0];
                $request['money_id']=$principal_money->id;
            }
        }

        //if no price existed already, make it principal
       if(count($prices=PricesCategories::where('service_id','=',$request->service_id)->get())===0){
            $request['principal']=1;
       }
        $new = PricesCategories::create($request->all());
        return $this->show($new);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PricesCategories  $pricesCategories
     * @return \Illuminate\Http\Response
     */
    public function show(PricesCategories $pricesCategories)
    {
        return PricesCategories::leftjoin('services_controllers as S', 'prices_categories.service_id','=','S.id')
        ->leftjoin('moneys as M','prices_categories.money_id','=','M.id')
        ->where('prices_categories.id','=',$pricesCategories['id'])
        ->get(['S.name as service_name','M.money_name','M.abreviation','prices_categories.*'])[0];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PricesCategories  $pricesCategories
     * @return \Illuminate\Http\Response
     */
    public function edit(PricesCategories $pricesCategories)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePricesCategoriesRequest  $request
     * @param  \App\Models\PricesCategories  $pricesCategories
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePricesCategoriesRequest $request, PricesCategories $pricesCategories)
    {
        $pricesCategories->update($request->all());
        return $this->show($pricesCategories);
    }

    public function update2(Request $request,$id)
    {
        $categ=PricesCategories::find($id);
        $categ->update($request->all());
        return $this->show($categ);
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PricesCategories  $pricesCategories
     * @return \Illuminate\Http\Response
     */
    public function destroy(PricesCategories $pricesCategories)
    {
        PricesCategories::destroy($pricesCategories);
    }

    public function deletepricing($pricingid){

        $pricing=PricesCategories::find($pricingid);
        return $pricing->delete();
    }
}
