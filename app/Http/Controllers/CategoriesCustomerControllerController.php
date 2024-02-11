<?php

namespace App\Http\Controllers;
use App\Models\CategoriesCustomerController;
use App\Http\Requests\StoreCategoriesCustomerControllerRequest;
use App\Http\Requests\UpdateCategoriesCustomerControllerRequest;
use Illuminate\Http\Request;

class CategoriesCustomerControllerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterprise_id)
    {
        return CategoriesCustomerController::where('enterprise_id','=',$enterprise_id)->get();
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
     * @param  \App\Http\Requests\StoreCategoriesCustomerControllerRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCategoriesCustomerControllerRequest $request)
    {
        return CategoriesCustomerController::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CategoriesCustomerController  $categoriesCustomerController
     * @return \Illuminate\Http\Response
     */
    public function show(CategoriesCustomerController $categoriesCustomerController)
    {
        return CategoriesCustomerController::find($categoriesCustomerController);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CategoriesCustomerController  $categoriesCustomerController
     * @return \Illuminate\Http\Response
     */
    public function edit(CategoriesCustomerController $categoriesCustomerController)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCategoriesCustomerControllerRequest  $request
     * @param  \App\Models\CategoriesCustomerController  $categoriesCustomerController
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCategoriesCustomerControllerRequest $request, CategoriesCustomerController $categoriesCustomerController)
    {
        return $categoriesCustomerController->update($request->all());
    }

    public function update2(Request $request,$id)
    {
        $categ=CategoriesCustomerController::find($id);
        $categ->update($request->all());

        return $categ;
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CategoriesCustomerController  $categoriesCustomerController
     * @return \Illuminate\Http\Response
     */
    public function destroy(CategoriesCustomerController $categoriesCustomerController)
    {
       return CategoriesCustomerController::destroy($categoriesCustomerController);
    } 
    
    public function destroy2($categoriesCustomerController)
    {
       return CategoriesCustomerController::find($categoriesCustomerController)->delete();
    }
}
