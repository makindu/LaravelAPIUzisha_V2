<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UsersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'=>(string) $this->id,
            'names'=>(string)$this->names,
            'email'=>(string)$this->email,
            'phone'=>(string)$this->phone,
            'avatar'=>(string)$this->avatar,
            'sylogan'=>(string)$this->sylogan,
            'status'=>(string)$this->status,
            'password'=>(string)$this->password,
            'created_at'=>$this->created_at,
            'updated_at'=>$this->updated_at,
            'requests'=>$this->requests
        ];
    }
}
