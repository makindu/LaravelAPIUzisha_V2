<?php

namespace App\Http\Controllers;

use App\Models\User;
use Faker\Core\Number;
use App\Models\department;
use Illuminate\Http\Request;
use App\Http\Resources\DepartmentResource;
use App\Http\Requests\StoredepartementRequest;
use App\Http\Requests\UpdatedepartementRequest;

class DepartementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $list=collect(department::all());
        $listdata=$list->map(function ($item){
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $newdepart=department::create($request->all());
        if($request->subdeparts){
            foreach ($request->subdeparts as $depart) {
                $depart['header_depart']=$newdepart['id'];
                $departupdated=department::find($depart['id']);
                $departupdated->update($depart);
            }
        }

        return $this->show($newdepart);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\departement  $departement
     * @return \Illuminate\Http\Response
     */
    public function show(department $departement)
    {
        return new DepartmentResource($departement);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\departement  $departement
     * @return \Illuminate\Http\Response
     */
    public function edit(department $departement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatedepartementRequest  $request
     * @param  \App\Models\departement  $departement
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatedepartementRequest $request, department $departement)
    {
        $element = department::find($departement);
        return $this->show($element->update($request->all()));
    }

    public function update2(Request $request, $id){
        $depart=department::find($id);
        $depart->update($request->all());
        return $this->show($depart);
    }

    //find one by id 
    public function findbyid($id){
        $element= department::find($id);
        
        return $this->show($element);
    }
    
    //find users affected
    public function findusers($id)
    {
        return User::leftjoin('affectation_users as A', 'users.id','=','A.user_id')
        ->where('A.department_id','=',$id)
        ->get(['users.*', 'A.level','A.id as affectation_id']);
    }

    public function findsubdeparts($id){
        return department::where('header_depart','=',$id)
        ->get();
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\departement  $departement
     * @return \Illuminate\Http\Response
     */
    public function destroy(department $departement)
    {
        return department::destroy($departement);
    }
    
    public function destroy2($id)
    {
        $depart=department::find($id);
        return $depart->delete();
    }

}
