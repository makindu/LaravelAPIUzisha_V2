<?php

namespace App\Http\Controllers;

use App\Models\reasons;
use App\Http\Requests\StorereasonsRequest;
use App\Http\Requests\UpdatereasonsRequest;
use App\Models\spots;
use Illuminate\Http\Request;

class ReasonsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterpriseId)
    {
        return reasons::where('enterprise_id','=',$enterpriseId)->get();
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
     * @param  \App\Http\Requests\StorereasonsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorereasonsRequest $request)
    {
        return reasons::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\reasons  $reasons
     * @return \Illuminate\Http\Response
     */
    public function show(reasons $reasons)
    {
        return reasons::find($reasons->id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\reasons  $reasons
     * @return \Illuminate\Http\Response
     */
    public function edit(reasons $reasons)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatereasonsRequest  $request
     * @param  \App\Models\reasons  $reasons
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatereasonsRequest $request, reasons $reasons)
    {
        return $this->show(reasons::find($reasons)->update($request->all()));
    }

    /**
     * update 2
     */
    public function update2(Request $request,$id)
    {
        $color=reasons::find($id);
        $color->update($request->all());

        return $this->show(reasons::find($id));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\reasons  $reasons
     * @return \Illuminate\Http\Response
     */
    public function destroy(reasons $reasons)
    {
        return $reasons->delete();
    }

     /**
     * Delete
     */
    public function destroy2($id)
    {
        $message="failed";
        $get=reasons::find($id);
        if ($get->delete()) {
            $message="deleted";
        }

        return ['message'=>$message];
    }
}
