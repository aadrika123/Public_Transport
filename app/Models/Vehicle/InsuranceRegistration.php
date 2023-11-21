<?php

namespace App\Models\Vehicle;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsuranceRegistration extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function insert(array $data)
    {
        $reqs = [
            'vehicle_id'             => $data["vehicleId"]??null,
            'insurance_company_id'   => $data["insuranceCompanyId"]??null,
            'policy_no'              => $data["policyNo"]??null,
            'policy_expiration_date' => $data["policyExpirationDate"],
            'renewal_status'         => $data["renewalStatus"]??null,
            'insurance_doc'          => $data["imagePath"]??null,
        ];
        return InsuranceRegistration::create($reqs)->id;   
    }

    public function edit(array $data)
    {
        $requestData = self::find($data["id"]);
        $reqs = [
            'vehicle_id'             => $data["vehicleId"]??null,
            'insurance_company_id'   => $data["insuranceCompanyId"]??null,
            'policy_no'              => $data["policyNo"]??null,
            'policy_expiration_date' => $data["policyExpirationDate"],
            'renewal_status'         => $data["renewalStatus"]??null,
        ];
        if(isset($data['status']))
        {
            $reqs["status"]= $data['status']?1:0;
        }
        if(isset($data['imagePath']))
        {
            $reqs["insurance_doc"]= $data['imagePath'];
        }
        return $requestData->update($reqs);
    }

    public function vehicleMaster()
    {
        return $this->belongsTo(VehicleMaster::class,"vehicle_id","id");
    }

    public function insuranceCompany()
    {
        return $this->belongsTo(InsuranceCompany::class,"insurance_company_id","id");
    }
}
