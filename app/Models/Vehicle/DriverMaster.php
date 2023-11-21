<?php

namespace App\Models\Vehicle;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DriverMaster extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function insert(array $data)
    {
        $reqs = [
            'driver_name'           => Str::upper($data["name"]),
            'mobile_no'   => $data["mobileNo"]??null,
            'email'       => $data["email"]??null,
            'address'       => $data["address"]??null,
            'driving_license_no'    => $data["drivingLicenseNo"]??null,
            'license_expiry_date'   => $data["licenseExpiryDate"]??null,
            'joining_date'       => $data["joiningDate"]??null,
            'driver_image'          => $data["imagePath"]??null,            
            'working_status'       => $data["workingStatus"]??0,
        ];
        return DriverMaster::create($reqs)->id;   
    }

    public function edit(array $data)
    {
        $requestData = self::find($data["id"]);
        $reqs = [
            'driver_name'           => Str::upper($data["name"]),
            'mobile_no'   => $data["mobileNo"]??null,
            'email'       => $data["email"]??null,
            'address'       => $data["address"]??null,
            'driving_license_no'    => $data["drivingLicenseNo"]??null,
            'license_expiry_date'   => $data["licenseExpiryDate"]??null,
            'joining_date'       => $data["joiningDate"]??null,
            'working_status'       => $data["workingStatus"]??null,
        ];
        if(isset($data['status']))
        {
            $reqs["status"]= $data['status']?1:0;
        }
        if(isset($data["imagePath"]) && trim($data["imagePath"]))
        {
            $reqs['driver_image']          = $data["imagePath"]??null;
        }
        return $requestData->update($reqs);
    }

    public function transitBusDrivers()
    {
        return $this->hasMany(TransitBusDriver::class,"driver_id","id")
        ->where("transit_bus_drivers.status",1);
    }

    public function transitBusMaster()
    {
        return $this->hasManyThrough(TransitBusMaster::class,TransitBusDriver::class,"driver_id","id","id","transit_bus_id")
        ->where("transit_bus_drivers.status",1)
        ->where("transit_bus_masters.status",1);
    }
   

   }
