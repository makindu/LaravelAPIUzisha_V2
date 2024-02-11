<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoresnisbczRequest;
use App\Http\Requests\UpdatesnisbczRequest;
use App\Models\snisbcz1150_1694;
use App\Models\snisbcz894_1149;
use App\Models\sniscs;
use Illuminate\Http\Request;

class SnisbczController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \App\Http\Requests\StoresnisbczRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){
        
        $snis1=snisbcz894_1149::create($request->all());
        $snis1=snisbcz1150_1694::create($request->all());

        return $request;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\snisbcz  $snisbcz
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\snisbcz  $snisbcz
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatesnisbczRequest  $request
     * @param  \App\Models\snisbcz  $snisbcz
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatesnisbczRequest $request)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\snisbcz  $snisbcz
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        //
    }
}
