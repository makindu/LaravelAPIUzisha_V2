<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DepositsUsers;
use App\Models\DepositController;
use App\Models\DepositsCategories;
use App\Models\CategoriesServicesController;
use App\Http\Requests\StoreCategoriesServicesControllerRequest;
use App\Http\Requests\UpdateCategoriesServicesControllerRequest;

class CategoriesServicesControllerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterprise_id)
    {
        $list=collect(CategoriesServicesController::where('enterprise_id','=',$enterprise_id)->get());
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
     * @param  \App\Http\Requests\StoreCategoriesServicesControllerRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCategoriesServicesControllerRequest $request)
    {
        if(isset($request->enterprise_id) && isset($request->user_id)){
            $new=CategoriesServicesController::create($request->all());
            //check the user who does the action is affected to a deposit
            $isheaffected=DepositsUsers::where('user_id','=',$request->user_id)->get();
            foreach ($isheaffected as $affectation) {
                //insert the category in all the deposit where he's affected
                DepositsCategories::create([
                   'deposit_id'=>$affectation['deposit_id'],
                   'category_id'=>$new->id,
               ]);
           }
            //affect the category to all deposits group
            $deposits=DepositController::where('enterprise_id','=',$request->enterprise_id)->where('type','=','group')->get();
            foreach ($deposits as $deposit) {
                if(count($ifnotexists=DepositsCategories::where('deposit_id','=',$deposit->id)->where('category_id','=',$new->id)->get())<1){
                    //affect categories to the default deposit
                    DepositsCategories::create([
                        'category_id'=>$new->id,
                        'deposit_id'=>$deposit->id
                    ]);
                }
            }
        }
        
        return $this->show($new);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CategoriesServicesController  $categoriesServicesController
     * @return \Illuminate\Http\Response
     */
    public function show(CategoriesServicesController $categoriesServicesController)
    {
        $categ=CategoriesServicesController::find($categoriesServicesController->id);
        $subcateg=CategoriesServicesController::where('parent_id','=',$categoriesServicesController->id)->get();
        return ['category'=>$categ,'subcategories'=>$subcateg];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CategoriesServicesController  $categoriesServicesController
     * @return \Illuminate\Http\Response
     */
    public function edit(CategoriesServicesController $categoriesServicesController)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCategoriesServicesControllerRequest  $request
     * @param  \App\Models\CategoriesServicesController  $categoriesServicesController
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCategoriesServicesControllerRequest $request, CategoriesServicesController $categoriesServicesController)
    {
        return $this->show(CategoriesServicesController::find($categoriesServicesController->update($request->all())));
    } 
    
    public function update2(Request $request,$id)
    {
        $categ=CategoriesServicesController::find($id);
        $categ->update($request->all());
        return $this->show($categ);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CategoriesServicesController  $categoriesServicesController
     * @return \Illuminate\Http\Response
     */
    public function destroy(CategoriesServicesController $categoriesServicesController)
    {
        return CategoriesServicesController::destroy($categoriesServicesController);
    }
}
