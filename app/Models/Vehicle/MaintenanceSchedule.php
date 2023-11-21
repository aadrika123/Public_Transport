<?php

namespace App\Models\Vehicle;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MaintenanceSchedule extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function insert(array $data)
    {
        $reqs = [
            'vehicle_id'        => $data["vehicleId"],
            'service_type'      => Str::upper($data["serviceType"])??null,
            'service_provider'  => $data["serviceProvider"]??null,
            'next_due_date'     => $data["nextDueDate"]??null,
            'cost'              => $data["cost"]??null,
            'notes'             => $data["notes"]??null,
        ];
        return MaintenanceSchedule::create($reqs)->id;
    }

    public function edit(array $data)
    {
        $requestData = self::find($data["id"]);
        $reqs = [
            'vehicle_id'        => $data["vehicleId"],
            'service_type'      => Str::upper($data["serviceType"])??null,
            'service_provider'  => $data["serviceProvider"]??null,
            'next_due_date'     => $data["nextDueDate"]??null,
            'cost'              => $data["cost"]??null,
            'notes'             => $data["notes"]??null,
        ];
        if(isset($data['status']))
        {
            $reqs["status"] = $data['status']?1:0;
        }
        return $requestData->update($reqs);
    }

    public function vehicleMaster()
    {
        return $this->belongsTo(VehicleMaster::class,"vehicle_id","id");
    }
}
