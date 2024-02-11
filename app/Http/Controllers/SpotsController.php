<?php

namespace App\Http\Controllers;

use App\Models\spots;
use App\Http\Requests\StorespotsRequest;
use App\Http\Requests\UpdatespotsRequest;
use Illuminate\Http\Request;

class SpotsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterpriseId)
    {
        return spots::where('enterprise_id','=',$enterpriseId)->get();
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
     * @param  \App\Http\Requests\StorespotsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorespotsRequest $request)
    {
        return spots::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\spots  $spots
     * @return \Illuminate\Http\Response
     */
    public function show(spots $spots)
    {
        return spots::find($spots->id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\spots  $spots
     * @return \Illuminate\Http\Response
     */
    public function edit(spots $spots)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatespotsRequest  $request
     * @param  \App\Models\spots  $spots
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatespotsRequest $request, spots $spots)
    {
        return $this->show(spots::find($spots)->update($request->all()));
    }

    /**
     * update 2
     */
    public function update2(Request $request,$id)
    {
        $color=spots::find($id);
        $color->update($request->all());

        return $this->show(spots::find($id));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\spots  $spots
     * @return \Illuminate\Http\Response
     */
    public function destroy(spots $spots)
    {
        return $spots->delete();
    }

    /**
     * delete
     */
    public function destroy2($id)
    {
        $message="failed";
        $get=spots::find($id);
        if ($get->delete()) {
            $message="deleted";
        }

        return ['message'=>$message];
    }
}
