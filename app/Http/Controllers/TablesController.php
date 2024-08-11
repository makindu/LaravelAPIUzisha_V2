<?php

namespace App\Http\Controllers;

use App\Models\tables;
use App\Models\Invoices;
use Illuminate\Http\Request;
use App\Http\Requests\StoretablesRequest;
use App\Http\Requests\UpdatetablesRequest;

class TablesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterprise_id)
    {
        return tables::where('enterprise_id','=',$enterprise_id)->get();
    }

    public function sales($table_id){
        return Invoices::where('table_id','=',$table_id)->get();
    }
    
    public function servants($table_id){
        return Invoices::lefjoin('servants as S','invoices.servant_id','=','S.id')
        ->where('table_id','=',$table_id)
        ->get(['invoices.*','S.name','S.description','S.photo']);
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
     * @param  \App\Http\Requests\StoretablesRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoretablesRequest $request)
    {
        return tables::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\tables  $tables
     * @return \Illuminate\Http\Response
     */
    public function show(tables $tables)
    {
        return tables::find($tables);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\tables  $tables
     * @return \Illuminate\Http\Response
     */
    public function edit(tables $tables)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatetablesRequest  $request
     * @param  \App\Models\tables  $tables
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatetablesRequest $request, tables $tables)
    {
        return tables::find($tables->id)::update($request->all());
    }

    public function update2(Request $request,$id)
    {
        $table=tables::find($id);
        $table->update($request->all());

        return tables::find($id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\tables  $tables
     * @return \Illuminate\Http\Response
     */
    public function destroy(tables $tables)
    {
        $tables->delete();
    }

    public function delete2($id)
    {
        $get=tables::find($id);
        return $get->delete();
    }
}
