<?php

namespace App\Http\Controllers;

use App\Http\Requests\Vehicle\AgentAdd;
use App\Http\Requests\Vehicle\AgentEdit;
use App\Http\Requests\Vehicle\DtlById;
use App\Http\Requests\Vehicle\RecordList;
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
use App\Traits\TraitDocumentUpload;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AgentController extends Controller
{
    use TraitDocumentUpload;

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

    public function __construct()
    {
        DB::enableQueryLog();

        $this->AGENT_RELATIVE_PATH = Config::get("VehicleConstants.AGENT_RELATIVE_PATH");
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
    }

    public function loginAuth(Request $req)
    {
        $validated = Validator::make(
            $req->all(),
            [
                'mobileNo' => 'required|regex:/[0-9]{10}/',
                'password' => 'required',
                'type' => "nullable|in:mobile"
            ]
        );
        if ($validated->fails())
            return validationError($validated);
        try {
            $user = $this->_MODEL_AGENT->getUserByMobile($req->mobileNo);
        
            if (!$user)
                throw new Exception("Oops! Given Mobile No. does not exist");
            if ($user->status == 0)
                throw new Exception("You are not authorized to log in!");
            if (Hash::check($req->password, $user->password)) {
                $token = $user->createToken('my-app-token')->plainTextToken;
                $user->remember_token = $token;  
                $user->update();              
                $data['token'] = $token;
                $data['userDetail'] = $user;
                return responseMsgs(true, "You have Logged In Successfully", $data, 010101, "1.0", responseTime(), "POST", $req->deviceId);
            }
            throw new Exception("Password Not Matched");
        } 
        catch (Exception $e) 
        {
            return responseMsg(false, $e->getMessage(), "");
        }
    }

    /**
     * | logout
     */
    public function logout(Request $req)
    {      return ($req->header());  
        try {
            $req->user()->currentAccessToken()->delete();                               // Delete the Current Accessable Token
            return responseMsgs(true, "You have Logged Out", [], "", "1.0", responseTime(), "POST", $req->deviceId);
        } 
        catch (Exception $e) 
        {
            return response()->json($e, 400);
        }
    }
    #AgentAdd
    public function store( Request $request)
    {return ("lksajfskjf");
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["va1.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData;
        try{
            DB::beginTransaction();
            $insertId = $this->_MODEL_AGENT->insert($request->all());
            if(!$insertId)
            {
                throw new Exception("Somthing Went Wrong");
            }
            if($request->image)
            {
                $update = $this->_MODEL_AGENT->find($insertId);

                $refImageName = "";
                // $refImageName = trim($insertId . '-' . str_replace(' ', '_', $refImageName),"-");
                $document = $request->image;
                $imageName = $this->upload($refImageName, $document, $this->AGENT_RELATIVE_PATH);                
                $update->agent_image = $imageName;
                $update->update();
            }
            DB::commit();
            return responseMsgs(true,"New Agent Add Successfully","",$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            DB::rollBack();
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }

    public function edit(AgentEdit $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["va2.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData;

        try{
            $test = $this->_MODEL_AGENT->find($request->id);
            if(!$test)
            {
                throw new Exception("Data Not Found");
            }
            if($request->image)
            {  
                $this->deleteFile($test->agent_image); 
                $refImageName = "";
                $document = $request->image;
                $imageName = $this->upload($refImageName, $document, $this->AGENT_RELATIVE_PATH);
                $request->merge(["imagePath"=>$imageName]);
            }

            DB::beginTransaction();
            $update = $this->_MODEL_AGENT->edit($request->all());
            if(!$update)
            {
                throw new Exception("Somthing Went Wrong");
            }
            DB::commit();
            return responseMsgs(true,"Agent Edit SuccessFully","",$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            DB::rollBack();
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }

    public function metaList()
    {
        return $this->_MODEL_AGENT
                ->leftjoin("transit_bus_runs","transit_bus_runs.agent_id","agents.id")
                ->leftjoin("transit_bus_masters","transit_bus_masters.id","transit_bus_runs.transit_bus_id")
                ->leftjoin("vehicle_masters","vehicle_masters.id","transit_bus_masters.vehicle_id")
                ->leftjoin("vehicle_type_masters","vehicle_type_masters.id","vehicle_masters.vehicle_type_id")
                ->select("agents.*",
                            DB::raw("vehicle_masters.id as vehicle_id,
                                vehicle_masters.registration_no,
                                vehicle_masters.vehicle_image,
                                vehicle_type_masters.vehicle_type,
                                vehicle_type_masters.type_image
                            ")                            
                        );
    }

    public function list(RecordList $request)
    {  return (["request"=>$request->all(),"headers"=>$request->header()]); 
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["va3.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData;

        try{
            $key = $status = $maintenance_status = $ulbId = null;
            if($request->key)
            {
                $key = strtoupper(trim($request->key));
            }
            if(isset($request->status))
            {
                $status = $request->status?1:0;
            }
            if($request->ulbId)
            {
                $ulbId = $request->ulbId;
            }

            $list = $this->metaList();

            if(!is_null($key))
            {
                $list = $list->where(function($where)use($key){
                        $where->ORWHERE(DB::raw("upper(agents.agent_name)"),'LIKE', '%' . $key . '%')
                        ->ORWHERE(DB::raw("upper(agents.agent_mobile_no)"),'LIKE', '%' . $key . '%')
                        ->ORWHERE(DB::raw("upper(vehicle_masters.registration_no)"),'LIKE', '%' . $key . '%')
                        ->ORWHERE(DB::raw("upper(vehicle_type_masters.vehicle_type)"),'LIKE', '%' . $key . '%')
                        ;
                });
            }
            if(!is_null($status))
            {
                $list = $list->where("agents.status",$status);
            }

            if($request->all==true)
            {
                $list = $list->get();
            }
            else
            {

                $list=paginater($list,$request) ;
            }
            return responseMsgs(true,"Data Fetched",remove_null($list),$apiId, $version, $queryRunTime,$action,$deviceId,($request->all?false:true));
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }

    public function dtlById(DtlById $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["va4.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData;
        try{
            $agent = $this->_MODEL_AGENT->find($request->id);
            if(!$agent)
            {
                throw new Exception("Data Not Found");
            } 
            $data["agent"] = $agent;
            $queryRunTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgs(true,"Data Fetched",remove_null($data),$apiId, $version, $queryRunTime,$action,$deviceId,true);
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }
}
