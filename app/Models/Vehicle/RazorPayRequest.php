<?php

namespace App\Models\Vehicle;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RazorPayRequest extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function insert(array $data)
    {
        $reqs = [
            'order_id'           => $data["orderId"],
            'merchant_id'   => $data["merchantId"]??null,
            'user_id'       => $data["userId"]??null,
            'user_type'       => $data["userType"]??null,
            'ulb_id'        => $data["ulbId"]??null,
            'amount'   => $data["amount"]??null,
            'ip_address'       => $data["ipAddress"]??null
        ];
        return RazorPayRequest::create($reqs)->id;   
    }

    public function edit(array $data)
    {
        $requestData = self::find($data["id"]);        
        $reqs = [
            'status'       => $data["status"]
        ];        
        return $requestData->update($reqs);
    }

    public static function getRequestByOderId(string $orderId)
    {
        return self::where("order_id",$orderId);
    }

    public static function getPendingRequest()
    {
        return self::where("status",2);
    }

    public function getResponse()
    {
        return $this->hasOne(RazorPayResponse::class,"request_id","id")
                ->where("razor_pay_requests.status","<>",0);
    }


}
