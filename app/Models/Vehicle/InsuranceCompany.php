<?php

namespace App\Models\Vehicle;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InsuranceCompany extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function insert(array $data)
    {
        $reqs = [
            'insurance_company'   => isset($data["insuranceCompany"])?Str::upper($data["insuranceCompany"]):null,
            
        ];
        return InsuranceCompany::create($reqs)->id;
    }

    public function edit(array $data)
    {
        $requestData = self::find($data["id"]);
        $reqs = [
            'insurance_company'   => isset($data["insuranceCompany"])?Str::upper($data["insuranceCompany"]):null,
            
        ];
        if(isset($data['status']))
        {
            $reqs["status"]= $data['status']?1:0;
        }
        return $requestData->update($reqs);
    }

    public function insuranceRegistrations()
    {
        return $this->hasMany(InsuranceRegistration::class,"insurance_company_id","id");
    }
    
    public function vehicleMaster()
    {
        return $this->hasManyThrough(VehicleMaster::class,InsuranceRegistration::class,"insurance_company_id","id","id","vehicle_id");
    }
}
