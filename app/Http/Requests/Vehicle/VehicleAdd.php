<?php

namespace App\Http\Requests\Vehicle;

use App\Models\Vehicle\DriverMaster;
use App\Models\Vehicle\VehicleTypeMaster;
use Carbon\Carbon;

class VehicleAdd extends Vehicle
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $mobile_regex = $this->_REX_MOBILE_NO;
        $name_regex   = $this->_REX_OWNER_NAME;
        $license_regex = $this->_REX_ALPHA_NUM_OPS_DOT_MIN_COM_AND_SPACE_SL;
        $current_date = $this->_CURRENT_DATE;
        $date_regex   = $this->_REX_DATE_YYYY_MM_DD;
        $num_dot_regex = $this->_REX_NUM_DOT;
        $current_Year = Carbon::parse($current_date)->format("Y");
        $vehicle_type = new VehicleTypeMaster();
        $driver     = new DriverMaster();
        $vehicalTypeIds = ($vehicle_type->select("id")->where("status",1)->GET())->implode("id",","); 
        $driverIds = ($driver->select("id")->where("status",1)->GET())->implode("id",",");      
        if(!$vehicalTypeIds)
        {
            $vehicalTypeIds = 0;
        }
        if(!$driverIds)
        {
            $driverIds = 0;
        }
        $rules=[
            "vehicleTypeId"      =>  "required|digits_between:1,9223372036854775807|in:$vehicalTypeIds",
            "make"  =>  "required|regex:$name_regex ",
            "model"      =>  "required|regex:$license_regex",
            "productionYear"      =>  "required|int|before_or_equal:$current_Year",
            "isLicensePlate"    => "required|bool",
            "registrationNo"  =>  ($this->isLicensePlate?"required":"nullable").($this->registrationNo?"|unique:vehicle_masters,registration_no":"")."|max:20|regex:$license_regex",
            "currentMilage"      =>  "nullable|regex:$num_dot_regex",
            "driverId"      =>  "nullable|digits_between:1,9223372036854775807|in:$driverIds",
            "image"         =>  "nullable|mimes:jpeg,png,jpg|max:2048",#2 mb
            "maintenanceStatus"    => "nullable|bool"
        ];  
        $metaRule = parent::rules();
        $rules = (array_merge($rules,$metaRule));
        return $rules;
    }
}
