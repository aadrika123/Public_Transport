<?php

namespace App\Models\Vehicle;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function insert(array $data)
    {
        $reqs = [
            'ulb_id'              => $data["ulbId"]??null,
            'location_name'       => $data["locationName"]??null,
        ];
        return Location::create($reqs)->id;  
    }

    public function edit(array $data)
    {
        $requestData = self::find($data["id"]);
        $reqs = [
            'ulb_id'              => $data["ulbId"]??null,
            'location_name'       => $data["locationName"]??null,
        ];
        if(isset($data['status']))
        {
            $reqs["status"]= $data['status']?1:0;
        }
        return $requestData->update($reqs);
    }
}
