<?php

namespace App\Models\Vehicle;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VehicleMaster extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function insert(array $data)
    {
        $reqs = [
            'vehicle_type_id'   => $data["vehicleTypeId"]??null,
            'make'              => isset($data["make"])?Str::upper($data["make"]):null,
            'model'             => $data["model"]??null,
            'production_year'   => $data["productionYear"]??null,
            'is_license_plate'  => $data["isLicensePlate"],
            'registration_no'   => $data["registrationNo"]??null,
            'current_milage'    => $data["currentMilage"]??null,
            'driver_id'         => $data["driverId"]??null,
            'vehicle_image'     => $data["imagePath"]??null,
            'ulb_id'             => $data["ulbId"]??null,
            'maintenance_status'   => (isset($data["maintenanceStatus"]) && $data["maintenanceStatus"] )?1:null,
        ];
        return VehicleMaster::create($reqs)->id;   
    }

    public function edit(array $data)
    {
        $requestData = self::find($data["id"]);
        $reqs = [
            'vehicle_type_id'   => $data["vehicleTypeId"]??null,
            'make'              => isset($data["make"])?Str::upper($data["make"]):null,
            'model'             => $data["model"]??null,
            'production_year'   => $data["productionYear"]??null,
            'is_license_plate'  => $data["isLicensePlate"],
            'registration_no'   => $data["registrationNo"]??null,
            'current_milage'    => $data["currentMilage"]??null,
            'driver_id'         => $data["driverId"]??null,
            'maintenance_status'   => $data["maintenanceStatus"]??null,
        ];
        if(isset($data['status']))
        {
            $reqs["status"]= $data['status']?1:0;
        }
        if(isset($data['imagePath']))
        {
            $reqs["vehicle_image"]= $data['imagePath'];
        }
        return $requestData->update($reqs);
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleTypeMaster::class,"id","vehicle_type_id");
    }

    public function mentenanceSchedules()
    {
        return $this->hasMany(MaintenanceSchedule::class,"vehicle_id","id")
            ->where("status",1);
    }

    public function mentenanceHistories()
    {
        return $this->hasMany(MaintenanceHistory::class,"vehicle_id","id")
            ->where("status",1);
    }

    public function fuelConsumptions()
    {
        return $this->hasMany(FuelConsumption::class,"vehicle_id","id")
            ->where("status",1);
    }

    public function incidentLogs()
    {
        return $this->hasMany(IncidentLog::class,"vehicle_id","id")
            ->where("status",1);
    }

    public function insuranceRegistrations()
    {
        return $this->hasMany(InsuranceRegistration::class,"vehicle_id","id")
            ->where("status",1);
    }

    public function insuranceCompanies()
    {
        return $this->hasManyThrough(InsuranceCompany::class,InsuranceRegistration::class,"vehicle_id","id","id","insurance_company_id")
        ->where("insurance_registrations.status",1)
        ->where("insurance_companies.status",1);
    }

    public function transitMasters()
    {
        return $this->hasMany(TransitBusMaster::class,"vehicle_id","id")
            ->where("status",1);
    }

    public function transitBusDrivers()
    {
        return $this->hasManyThrough(TransitBusDriver::class,TransitBusMaster::class,"vehicle_id","transit_bus_id","id","id")
            ->where("transit_bus_drivers.status",1)
            ->where("transit_bus_masters.status",1);
    }

    public function transitBusRuns()
    {
        return $this->hasManyThrough(TransitBusRun::class,TransitBusMaster::class,"vehicle_id","id","id","transit_bus_id")
            ->where("transit_bus_runs.status",1)
            ->where("transit_bus_masters.status",1);
    }

    public function transitBusSchedules()
    {
        return $this->hasManyThrough(TransitBusSchedule::class,TransitBusMaster::class,"vehicle_id","transit_bus_id","id","id")
            ->where("transit_bus_schedules.status",1)
            ->where("transit_bus_masters.status",1);
    }

    public function routes()
    {
        return $this->hasManyThrough(Route::class,TransitBusMaster::class,"vehicle_id","id","id","route_id")
            ->select("routes.*","transit_bus_masters.start_time","transit_bus_masters.end_time")
            ->where("transit_bus_masters.status",1)
            ->where("routes.status",1);
        
    }
}
