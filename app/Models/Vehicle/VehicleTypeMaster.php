<?php

namespace App\Models\Vehicle;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VehicleTypeMaster extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function insert(array $data)
    {
        $reqs = [
            'vehicle_type'              => isset($data["type"])?Str::title($data["type"]):null,
            'type_image'        => $data["imagePath"]??null,
        ];
        return VehicleTypeMaster::create($reqs)->id;   
    }

    public function edit(array $data)
    {
        $requestData = self::find($data["id"]);
        $reqs = [
            'vehicle_type'              => isset($data["type"])?Str::title($data["type"]):null,
        ];
        if(isset($data['status']))
        {
            $reqs["status"]= $data['status']?1:0;
        }
        if(isset($data["imagePath"]) && trim($data["imagePath"]))
        {
            $reqs['type_image']          = $data["imagePath"]??null;
        }
        return $requestData->update($reqs);
    }

    public function vehicalMasters()
    {
        return $this->hasMany(VehicleMaster::class,"vehicle_type_id","id")->where("status",1);
    }
}
