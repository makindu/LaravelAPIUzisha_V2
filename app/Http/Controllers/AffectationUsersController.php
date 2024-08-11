<?php

namespace App\Http\Controllers;

use App\Models\affectation_users;
use App\Models\User;
use App\Http\Requests\Storeaffectation_usersRequest;
use App\Http\Requests\Updateaffectation_usersRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AffectationUsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return affectation_users::all();
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
        if($request->level=='chief'){
            DB::update('update affectation_users set level = ? where department_id = ? ',['simple',$request->department_id]);
        }
        affectation_users::create($request->all());
        
        return User::leftjoin('affectation_users as A', 'users.id','=','A.user_id')
        ->leftjoin('departments as D', 'A.department_id','=','D.id')
        ->where('users.id', '=',$request->user_id)
        ->get(['D.department_name as department_name', 'D.id as department_id', 'users.*', 'A.level'])[0];
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\affectation_users  $affectation_users
     * @return \Illuminate\Http\Response
     */
    public function show(affectation_users $affectation_users)
    {
        return affectation_users::find($affectation_users);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\affectation_users  $affectation_users
     * @return \Illuminate\Http\Response
     */
    public function edit(affectation_users $affectation_users)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Updateaffectation_usersRequest  $request
     * @param  \App\Models\affectation_users  $affectation_users
     * @return \Illuminate\Http\Response
     */
    public function update(Updateaffectation_usersRequest $request, affectation_users $affectation_users)
    {
        $element = affectation_users::find($affectation_users);
        return $element->update($request->all());
    }

    public function update2(Request $request){

        if($request->level=='chief'){
          DB::update('update affectation_users set level = ? where department_id = ? ',['simple',$request->department_id]);
        }

        $affectation=affectation_users::where('user_id','=',$request->user_id)->get();
        $affectation[0]->update($request->all());

        return User::leftjoin('affectation_users as A', 'users.id','=','A.user_id')
        ->leftjoin('departments as D', 'A.department_id','=','D.id')
        ->where('users.id', '=',$request->user_id)
        ->get(['D.department_name as department_name', 'D.id as department_id', 'users.*', 'A.level'])[0];
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\affectation_users  $affectation_users
     * @return \Illuminate\Http\Response
     */
    public function destroy(affectation_users $affectation_users)
    {
        return affectation_users::destroy($affectation_users);
    }

    public function destroy2($id)
    {
        $affectation=affectation_users::find($id);
        return $affectation->delete();
    }

    public function reference(){
        $uploaddir = "uploads/";
        $uploadfile = $uploaddir.basename($_FILES['filekey']['name']);
        $uploaded = move_uploaded_file($_FILES['filekey']['tmp_name'],$uploadfile);

        if ($uploaded) {
            echo "uploaded successfully";
        }else{
            echo "error on uploading";
        }
    }

}
