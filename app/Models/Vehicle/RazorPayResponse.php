<?php

namespace App\Models\Vehicle;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RazorPayResponse extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function insert(array $data)
    {
        $reqs = [
            'request_id'           => $data["requestId"],
            'order_id'   => $data["orderId"]??null,
            'merchant_id'       => $data["merchantId"]??null,
            'payment_id'       => $data["paymentId"]??null,
            'user_id'       => $data["userId"]??null,
            "user_type"     => $data["userType"]??null,
            'ulb_id'        => $data["ulbId"]??null,
            'amount'   => $data["amount"]??null,
            "tracking_id" => $data["trackingId"]??null,
            "bank_ref_no" =>$data["bankRefNo"]??null,
            'error_code'       => $data["errorCode"]??null,
            'error_desc'       => $data["errorDesc"]??null,
            'error_source'       => $data["errorSource"]??null,
            'error_step'       => $data["errorStep"]??null,
            'error_reason'       => $data["errorReason"]??null,
            'ip_address'       => $data["ipAddress"]??null,
            'respons_data'       => $data["responsData"]??null
        ];
        return RazorPayResponse::create($reqs)->id;   
    }

    public function getRequest()
    {
        return $this->belongsTo(RazorPayRequest::class,"id","request_id");
    }
}
