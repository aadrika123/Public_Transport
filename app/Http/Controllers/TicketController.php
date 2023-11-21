<?php

namespace App\Http\Controllers;

use App\Http\Requests\Vehicle\TicketAdd;
use App\Http\Requests\Vehicle\TickeVerify;
use App\Http\Requests\Vehicle\Vehicle;
use App\Models\Vehicle\Agent;
use App\Models\Vehicle\DriverMaster;
use App\Models\Vehicle\FuelConsumption;
use App\Models\Vehicle\IncidentLog;
use App\Models\Vehicle\InsuranceCompany;
use App\Models\Vehicle\InsuranceRegistration;
use App\Models\Vehicle\MaintenanceHistory;
use App\Models\Vehicle\MaintenanceSchedule;
use App\Models\Vehicle\Route;
use App\Models\Vehicle\RouteStoppage;
use App\Models\Vehicle\Stopage;
use App\Models\Vehicle\TicketMaster;
use App\Models\Vehicle\TransitBusDriver;
use App\Models\Vehicle\TransitBusMaster;
use App\Models\Vehicle\TransitBusRun;
use App\Models\Vehicle\TransitBusRunDetail;
use App\Models\Vehicle\TransitBusSchedule;
use App\Models\Vehicle\VehicleMaster;
use App\Models\Vehicle\VehicleTypeMaster;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use App\MicroServices\Notification;
use App\Models\ActiveCitizen;
use App\Models\Vehicle\RazorPayRequest;
use App\Models\Vehicle\RazorPayResponse;
use App\Traits\GuzzulCinteRequest;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    use GuzzulCinteRequest;
    private $AGENT_RELATIVE_PATH;
    private $_MODEL_AGENT;
    private $_MODEL_DRIVER_MASTER;
    private $_MODEL_FUEL_CONSUMPTION;
    private $_MODEL_INCIDENT_LOG;
    private $_MODEL_INSURANCE_COMPANY;
    private $_MODEL_INSURANCE_REGISTRATION;
    private $_MODEL_MAINTENANCE_HISTORY;
    private $_MODEL_MAINTENANCE_SCHEDULE;
    private $_MODEL_ROUTE;
    private $_MODEL_ROUTE_STOPPAGE;
    private $_MODEL_STOPAGE;
    private $_MODEL_TICKET_MASTER;
    private $_MODEL_TRANSIT_BUS_DRIVER;
    private $_MODEL_TRANSIT_BUS_MASTER;
    private $_MODEL_TRANSIT_BUS_RUN;
    private $_MODEL_TRANSIT_BUS_RUN_DETAIL;
    private $_MODEL_TRANSIT_BUS_SCHEDULE;
    private $_MODEL_VEHICLE_MASTER;
    private $_MODEL_VEHICLE_TYPE_MASTER;
    private $_MODEL_RAZOR_PAY_REQUEST;
    private $_MODEL_RAZOR_PAY_RESPONSE;
    private $NotificationService;

    public function __construct()
    {
        DB::enableQueryLog();

        $this->_MODEL_AGENT = new Agent();
        $this->_MODEL_DRIVER_MASTER = new DriverMaster();
        $this->_MODEL_FUEL_CONSUMPTION = new FuelConsumption();
        $this->_MODEL_INCIDENT_LOG = new IncidentLog();
        $this->_MODEL_INSURANCE_COMPANY = new InsuranceCompany();
        $this->_MODEL_INSURANCE_REGISTRATION = new InsuranceRegistration();
        $this->_MODEL_MAINTENANCE_HISTORY = new MaintenanceHistory();
        $this->_MODEL_MAINTENANCE_SCHEDULE = new MaintenanceSchedule();
        $this->_MODEL_ROUTE = new Route();
        $this->_MODEL_ROUTE_STOPPAGE = new RouteStoppage();
        $this->_MODEL_STOPAGE = new Stopage();
        $this->_MODEL_TICKET_MASTER = new TicketMaster();
        $this->_MODEL_TRANSIT_BUS_DRIVER = new TransitBusDriver();
        $this->_MODEL_TRANSIT_BUS_MASTER = new TransitBusMaster();
        $this->_MODEL_TRANSIT_BUS_RUN = new TransitBusRun();
        $this->_MODEL_TRANSIT_BUS_RUN_DETAIL = new TransitBusRunDetail();
        $this->_MODEL_TRANSIT_BUS_SCHEDULE = new TransitBusSchedule();
        $this->_MODEL_VEHICLE_MASTER = new VehicleMaster();
        $this->_MODEL_VEHICLE_TYPE_MASTER = new VehicleTypeMaster();
        $this->_MODEL_RAZOR_PAY_REQUEST = new RazorPayRequest();
        $this->_MODEL_RAZOR_PAY_RESPONSE = new RazorPayResponse();
        $this->NotificationService = new Notification();
    }

    public function handeRazorPay(TicketAdd $request)
    { 
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vtt1.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData;
        try {
            $ulbList = (new LocationController())->getApplyData(new Request());                                
            if(!$ulbList->original["status"])
            {
                throw new Exception($ulbList->original["message"]);
            }
            $ulbList = $ulbList->original["data"]["ulbList"]??[];
            $marge =[
                "departmentId"=>Config::get("VehicleConstants.MODULE_ID"),
                "workflowId"=>"0",
                "id"=>"0"
            ];
            $request->merge($marge);
            $orderResponse = $this->requestMake($request,Config::get("VehicleConstants.RAZORPAY_ODERID_URL"));
            $orderResponse = $this->send($orderResponse); 
            if(!$orderResponse["state"])
            {
                throw new Exception($orderResponse["error"]);
            }
            if(!$orderResponse['value']["status"])
            {
                throw new Exception($orderResponse['value']["message"]);
            }   
            $request->merge($orderResponse['value']["data"]);
           
            DB::beginTransaction();
            $insertId = $this->_MODEL_RAZOR_PAY_REQUEST->insert($request->all());
            if(!$insertId)
            {
                throw new Exception("Somthing Went Wrong");
            }
            $temp["requestId"]  = $insertId;
            $temp['name']       = $request->auth['user_name'];
            $temp['mobile']     = $request->auth['mobile'];
            $temp['email']      = $request->auth['email'];
            $temp['userId']     = $request->auth['id'];
            $temp['ulbId']      = $request->ulbId;
            $temp['ulbName']    = ($ulbList->where("id",$request->ulbId)->pluck("ulb_name"))[0]??"";
            $temp['amount']     = $request->amount;
            
            DB::commit();

            return responseMsgs(true,
                $orderResponse['value']["message"],
                $temp,$apiId, $version, $queryRunTime,$action,$deviceId
            );
        } 
        catch(Exception $e)
        {
            DB::rollBack();
            return responseMsgs(false,$e->getMessage(),"",$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }
    public function razorPayResponse(Request $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vtt1.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData;
        try {
            $refUser        = Auth()->user();
            $refUserId      = $refUser->id ?? $request->userId;
            $refUlbId       = $refUser->ulb_id ?? $request->ulbId;
            $mNowDate       = Carbon::now()->format('Y-m-d');
            $mTimstamp      = Carbon::now()->format('Y-m-d H:i:s');
            

            #-----------valication-------------------   
            $RazorPayRequest = $this->_MODEL_RAZOR_PAY_REQUEST->getRequestByOderId($request->orderId)
                ->where("status", 2)
                ->first();
            if (!$RazorPayRequest) {
                throw new Exception("Data Not Found");
            }
            if($RazorPayRequest->amount!=$request->amount)
            {
                throw new Exception("Amount Missmatch !!!");
            }
            #-------- Transection -------------------
            $update["id"]= $RazorPayRequest->id;
            $update["status"] = 1;

            DB::beginTransaction();
            $ticketId = $this->_MODEL_TICKET_MASTER->insert($request->all());
            $request->merge(["requestId"=>$RazorPayRequest->id,"trackingId"=>$ticketId]);
            $insertId = $this->_MODEL_RAZOR_PAY_RESPONSE->insert($request->all());
            $updated = $this->_MODEL_RAZOR_PAY_REQUEST->edit($update);
            DB::commit();
            #----------End transaction------------------------
            #----------Response------------------------------
            $res['ticketId'] = $ticketId; #config('app.url') .
            // $res['paymentReceipt'] =  "/api/trade/application/payment-receipt/" . $licenceId . "/" . $transaction_id;
            return responseMsgs(true, "", $res,$apiId, $version, $queryRunTime,$action,$deviceId);
        } catch (Exception $e) {
            DB::rollBack();
            return responseMsgs(false,$e->getMessage(),"",$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }
    public function conformRazorPayTran(Request $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vtt1.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData;

        try {            
            $refUser     = Auth()->user();
            $rules = [
                'orderId'    => 'required|string',
                'paymentId'  => 'required|string',
            ];
            $validator = Validator::make($request->all(), $rules,);
            if ($validator->fails()) {
                return responseMsg(false, $validator->errors(), "");
            }
            $RazorPayResponse = $this->_MODEL_RAZOR_PAY_REQUEST->getResponse()
            ->where("trade_razor_pay_responses.order_id", $request->orderId)
            ->where("trade_razor_pay_responses.payment_id", $request->paymentId)
            ->where("trade_razor_pay_requests.status", 1)
            ->first();
            if (!$RazorPayResponse) {
                throw new Exception("Not Transection Found...");
            }
            $ticket = $this->_MODEL_TICKET_MASTER->userTikets()
                    ->where("id",$RazorPayResponse->tracking_id)
                    ->first();
            if(!$ticket)
            {
                throw new Exception("Not Ticket Found...");
            }
            $data["amount"]            = $RazorPayResponse->amount;
            $data["tickeId"]           = $ticket->id;
            $data["ticketNo"]          = $ticket->ticket_no;
            $data["expiryDate"]        = $ticket->expiry_date;
            return responseMsg(true,"",$data,$apiId, $version, $queryRunTime,$action,$deviceId);
        } catch (Exception $e) {
            return responseMsg(false,$e->getMessage(),"",$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }
    
    public function userTickets(Vehicle $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vtt1.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData;
        try{
            $userId = $request->logInId;
            $userType = $request->userType;
            $ulbList = (new LocationController())->getApplyData(new Request());
            $ulbList = $ulbList->original["data"]["ulbList"]??[];
            $userTikets = $this->_MODEL_TICKET_MASTER->userTikets()
                        ->where("citizen_id",$userId)
                        ->where("user_type",$userType)
                        ->orderBy("issue_date")
                        ->get();
            $userTikets = collect($userTikets)->map(function($val) use($ulbList){
                    $ulbDtl = ((collect($ulbList)->where("id",$val->ulb_id)->values())->all())[0]??[];
                    $val->ulb_name = $ulbDtl["ulb_name"]??"";                    
                    return $val; 
                }) ;
            return responseMsgs(true,"",$userTikets,$apiId, $version, $queryRunTime,$action,$deviceId,true);            
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),"",$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }

    public function ticketVerify(Request $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vtt1.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData;
        try{
            $user = Auth()->user();
            $userId = $user->id;
            $ulbId = $user->ulb_id;            
            $userTicket = $this->_MODEL_TICKET_MASTER->userTikets()
                        ->where("id",$request->id)
                        ->first();
            
            if(!$userTicket)
            {
                throw new Exception("User tiket Not found");
            }
            if($userTicket->ulb_id != $ulbId)
            {
                throw new Exception("This Ticket is Not Valide for This ULB");
            }
            if($userTicket->consumed_at)
            {
                throw new Exception("Ticket Was Used On ". Carbon::parse($userTicket->consumed_at)->format("d-m-Y"));
            }
            if($userTicket->expiry_date < Carbon::now()->format("Y-m-d"))
            {
                throw new Exception("Ticket Was Expired On ". Carbon::parse($userTicket->expiry_date)->format("d-m-Y"));
            }
            $citizen = ActiveCitizen::find($userTicket->citizen_id);
            $mobileNo = ($citizen->mobile??"");
            $otp = $this->NotificationService->sendOtpOnMobile($mobileNo,5,"ticketVerification");
            return responseMsgs(true,"Otp Sends On ".(str_pad(substr((string) $mobileNo,strlen($mobileNo)-4, 4),10,"X",STR_PAD_LEFT)),"",$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),"",$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }

    public function ticketOtpVerify(Request $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vtt1.2",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData;
        try{
            $user = Auth()->user();
            $userId = $user->id;
            $ulbId = $user->ulb_id;            
            $userTicket = $this->_MODEL_TICKET_MASTER->userTikets()
                        ->where("id",$request->id)
                        ->first();
            
            if(!$userTicket)
            {
                throw new Exception("User tiket Not found");
            }
            if($userTicket->ulb_id != $ulbId)
            {
                throw new Exception("This Ticket is Not Valide for This ULB");
            }
            if($userTicket->consumed_at)
            {
                throw new Exception("Ticket Was Used On ". Carbon::parse($userTicket->consumed_at)->format("d-m-Y"));
            }
            if($userTicket->expiry_date < Carbon::now()->format("Y-m-d"))
            {
                throw new Exception("Ticket Was Expired On ". Carbon::parse($userTicket->expiry_date)->format("d-m-Y"));
            }
            $citizen = ActiveCitizen::find($userTicket->citizen_id);
            $mobileNo = ($citizen->mobile??"");
            $otp = $this->NotificationService->verifyMobileOtp($mobileNo,5,$request->otp,"ticketVerification");
            if(!$otp)
            {
                throw new Exception($this->NotificationService->getMessage());
            }
            return responseMsgs(true,"Otp Sends On ".(str_pad(substr((string) $mobileNo,strlen($mobileNo)-4, 4),10,"X",STR_PAD_LEFT)),"",$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),"",$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }

    public function useTicket(Request $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vtt1.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData;
        try{
            $userTicket = $this->_MODEL_TICKET_MASTER->userTikets()
                        ->where("id",$request->id)
                        ->first();
            if($userTicket->consumed_at)
            {
                throw new Exception("Ticket Was Used On ". Carbon::parse($userTicket->consumed_at)->format("d-m-Y"));
            }
            if($userTicket->expiry_date < Carbon::now()->format("Y-m-d"))
            {
                throw new Exception("Ticket Was Expired On ". Carbon::parse($userTicket->expiry_date)->format("d-m-Y"));
            }
            dd($userTicket);
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),"",$apiId, $version, $queryRunTime,$action,$deviceId);
        }

    }
}
