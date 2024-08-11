<?php

namespace App\Http\Controllers;

use App\Models\DocumentTypes;
use App\Http\Requests\StoreDocumentTypesRequest;
use App\Http\Requests\UpdateDocumentTypesRequest;

class DocumentTypesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($enterprise_id)
    {
        return DocumentTypes::where('enterprise_id','=',$enterprise_id)->get();
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
     * @param  \App\Http\Requests\StoreDocumentTypesRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDocumentTypesRequest $request)
    {
        return DocumentTypes::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DocumentTypes  $documentTypes
     * @return \Illuminate\Http\Response
     */
    public function show(DocumentTypes $documentTypes)
    {
        return DocumentTypes::find($documentTypes->id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DocumentTypes  $documentTypes
     * @return \Illuminate\Http\Response
     */
    public function edit(DocumentTypes $documentTypes)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDocumentTypesRequest  $request
     * @param  \App\Models\DocumentTypes  $documentTypes
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDocumentTypesRequest $request, DocumentTypes $documentTypes)
    {
        $given=DocumentTypes::find($documentTypes->id);
        $given->update($request->all());
        return $this->show($given);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DocumentTypes  $documentTypes
     * @return \Illuminate\Http\Response
     */
    public function destroy(DocumentTypes $documentTypes)
    {
        return $documentTypes->delete();
    }
}
