<?php

namespace App\Models\Vehicle;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TicketMaster extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function insert(array $data)
    {
        $reqs = [
            'citizen_id'      => $data["citizenId"]??null,
            'user_type'       => $data["userType"]??null,
            'user_id'         => $data["logInId"]??null,
            'ticket_value'    => $data["amount"]??null,
            'ulb_id'          => $data["ulbId"]??null,
            'expiry_date'     => Carbon::now()->addRealDays(30)->format("Y-m-d"),            
        ];
        return TicketMaster::create($reqs)->id;   
    }

    public function edit(array $data)
    {
        $requestData = self::find($data["id"]);
        $bookingDate = $data["bookingDate"]?$data["bookingDate"]:$requestData->issue_date;
        $reqs = [
            'ticket_value'    => $data["amount"]??null,
            "issue_date"      => Carbon::parse($bookingDate)->format("Y-m-d"),
            'expiry_date'     => Carbon::parse($bookingDate)->addRealDays(30)->format("Y-m-d"),
            "transit_run_id"  => $data["transitRunId"]??null,
        ];
        if(isset($data['status']))
        {
            $reqs["status"]= $data['status']?1:0;
        }
        if(isset($data['TravelingDate']))
        {
            $reqs["transit_run_id"]  = $data["transitRunId"]??null;
            $reqs["consumed_at"]= Carbon::parse($data['TravelingDate'])->addRealDays(30)->format("Y-m-d H:i:s");
        }
        return $requestData->update($reqs);
    }

    public function validTickets()
    {
        return self::select("*")
                ->where("status",1)
                ->whereNull("consumed_at")
                ->where("expiry_date",">=",Carbon::now()->format("Y-m-d"));
    }

    public function toDayConsumTickets()
    {
        return self::select("*")
                ->where("status",1)
                ->where(DB::raw("cast(consumed_at as date)"),"=",Carbon::now()->format("Y-m-d"));
    }

    public function toDayBookTickets()
    {
        return self::select("*")
                ->where("status",1)
                ->where(DB::raw("cast(issue_date as date)"),"=",Carbon::now()->format("Y-m-d"));
    }

    public function toDayExpiredTickets()
    {
        return self::select("*")
                ->where("status",1)
                ->whereNull("consumed_at")
                ->where(DB::raw("cast(expiry_date as date)"),"=",Carbon::now()->format("Y-m-d"));
    }

    public function notUsedExpiredTickets()
    {
        return self::select("*")
                ->where("status",1)
                ->whereNull("consumed_at")
                ->where(DB::raw("cast(expiry_date as date)"),"<=",Carbon::now()->format("Y-m-d"));
    }

    public function userTikets()
    {
        return self::select("*")
                ->where("status",1);
    }
}
