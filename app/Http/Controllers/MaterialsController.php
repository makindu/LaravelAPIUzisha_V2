<?php

namespace App\Http\Controllers;

use App\Models\materials;
use App\Http\Requests\StorematerialsRequest;
use App\Http\Requests\UpdatematerialsRequest;
use Illuminate\Http\Request;

class MaterialsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterpriseId)
    {
        return materials::where('enterprise_id','=',$enterpriseId)->get();
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
     * @param  \App\Http\Requests\StorematerialsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorematerialsRequest $request)
    {
        return materials::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\materials  $materials
     * @return \Illuminate\Http\Response
     */
    public function show(materials $materials)
    {
        return materials::find($materials->id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\materials  $materials
     * @return \Illuminate\Http\Response
     */
    public function edit(materials $materials)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatematerialsRequest  $request
     * @param  \App\Models\materials  $materials
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatematerialsRequest $request, materials $materials)
    {
        return $this->show(materials::find($materials)->update($request->all()));
    }

    /**
     * update 2
     */
    public function update2(Request $request,$id)
    {
        $color=materials::find($id);
        $color->update($request->all());

        return $this->show(materials::find($id));
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\materials  $materials
     * @return \Illuminate\Http\Response
     */
    public function destroy(materials $materials)
    {
        return $materials->delete();
    }

     /**
     * Delete
     */
    public function destroy2($id)
    {
        $message="failed";
        $get=materials::find($id);
        if ($get->delete()) {
            $message="deleted";
        }

        return ['message'=>$message];
    }
}
