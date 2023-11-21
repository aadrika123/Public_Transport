<?php

namespace App\Models\Vehicle;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class TransitBusMaster extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function insert(array $data)
    {
        $reqs = [
            'vehicle_id'              => $data["vehicleId"]??null,
            'route_id'              => $data["routeId"]??null,
            'start_time'              => $data["startTime"]??null,
            'end_time'              => $data["endTime"]??null,
        ];
        return TransitBusMaster::create($reqs)->id;   
    }

    public function edit(array $data)
    {
        $requestData = self::find($data["id"]);
        $reqs = [
            'vehicle_id'              => $data["vehicleId"]??null,
            'route_id'              => $data["routeId"]??null,
            'start_time'              => $data["startTime"]??null,
            'end_time'              => $data["endTime"]??null,
        ];
        if(isset($data['status']))
        {
            $reqs["status"]= $data['status']?1:0;
        }
        return $requestData->update($reqs);
    }

    public function transitBusDrivers()
    {
        return $this->hasMany(TransitBusDriver::class,"transit_bus_id","id")
                ->where("status",1);
    }

    public function drivers()
    {
        return $this->hasManyThrough(DriverMaster::class,TransitBusDriver::class,"transit_bus_id","id","id","driver_id")
            ->where("transit_bus_drivers.status",1)
            ->where("driver_masters.status",1);
    }

    public function transitBusRunDetails()
    {
        return $this->hasManyThrough(TransitBusRunDetail::class,TransitBusRun::class,"transit_bus_id","transit_run_id","id","id")
            ->where("transit_bus_runs.status",1)
            ->where("transit_bus_run_details.status",1);;

    }

    public function ticketMastes()
    {
        return $this->hasManyThrough(TicketMaster::class,TransitBusRun::class,"transit_bus_id","transit_run_id","id","id")
            ->where("transit_bus_runs.status",1)
            ->where("ticket_masters.status",1);
    }

    public function routes()
    {
        return $this->hasMany(Route::class,"id","route_id")
            ->where("status",1);
    }
}
