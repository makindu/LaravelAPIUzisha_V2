<?php

namespace App\Http\Controllers;

use App\Models\colors;
use App\Http\Requests\StorecolorsRequest;
use App\Http\Requests\UpdatecolorsRequest;
use Illuminate\Http\Request;

class ColorsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterpriseId)
    {
        return colors::where('enterprise_id','=',$enterpriseId)->get();
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
     * @param  \App\Http\Requests\StorecolorsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorecolorsRequest $request)
    {
        return colors::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\colors  $colors
     * @return \Illuminate\Http\Response
     */
    public function show(colors $colors)
    {
        return colors::find($colors->id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\colors  $colors
     * @return \Illuminate\Http\Response
     */
    public function edit(colors $colors)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatecolorsRequest  $request
     * @param  \App\Models\colors  $colors
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatecolorsRequest $request, colors $colors)
    {
        return $this->show(colors::find($colors)->update($request->all()));
    }

    public function update2(Request $request,$id)
    {
        $color=colors::find($id);
        $color->update($request->all());

        return $this->show(colors::find($id));
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\colors  $colors
     * @return \Illuminate\Http\Response
     */
    public function destroy(colors $colors)
    {
        return colors::destroy($colors);
    }

    public function destroy2($id)
    {
        $message="failed";
        $get=colors::find($id);
        if ($get->delete()) {
            $message="deleted";
        }

        return ['message'=>$message];
    }
}
