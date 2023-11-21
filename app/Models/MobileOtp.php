<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileOtp extends Model
{
    use HasFactory;
    protected $guarded = [];


    public function insert(array $data)
    {
        $reqs = [
            'otp'      => $data["otp"]??null,
            'mobile_no'  => $data["mobile_no"]??null,
            'check_points'  => $data["check_points"]??null,
            "created_at"    => $data["created_at"]??null,
            "expired_at"   =>  $data["expired_at"]??null,      
        ];
        if($update = $this->getMobileOtp($reqs["mobile_no"],$reqs["check_points"]))
        {
            $update->otp =$reqs["otp"];
            $update->created_at =$reqs["created_at"];
            $update->expired_at =$reqs["expired_at"];
            return $update->update();
        }
        return MobileOtp::create($reqs)->id;   
    }

    public function getMobileOtp($mobileNo,$checkPoint,$otp=null)
    {
        if(is_array($checkPoint))
        {
            $checkPoint = json_encode($checkPoint);
        }
        $builder =  self::where("mobile_no",$mobileNo)->where("check_points",$checkPoint);
        if($otp)
        {
            $builder =$builder->where("otp",$otp); 
        }
        return $builder->first();
    }
}
