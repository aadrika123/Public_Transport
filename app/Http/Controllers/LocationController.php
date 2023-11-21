<?php

namespace App\Http\Controllers;

use App\Http\Requests\Vehicle\DtlById;
use App\Http\Requests\Vehicle\LocationAdd;
use App\Http\Requests\Vehicle\LocationEdit;
use App\Http\Requests\Vehicle\RecordList;
use App\Models\UlbMaster;
use App\Models\Vehicle\DriverMaster;
use App\Models\Vehicle\FuelConsumption;
use App\Models\Vehicle\IncidentLog;
use App\Models\Vehicle\InsuranceCompany;
use App\Models\Vehicle\InsuranceRegistration;
use App\Models\Vehicle\Location;
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
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;

class LocationController extends Controller
{
    use TraitDocumentUpload;
    
    private $_BACKEND_URL;
    private $_MODEL_DRIVER_MASTER;
    private $_MODEL_FUEL_CONSUMPTION;
    private $_MODEL_INCIDENT_LOG;
    private $_MODEL_INSURANCE_COMPANY;
    private $_MODEL_INSURANCE_REGISTRATION;
    private $_MODEL_MAINTENANCE_HISTORY;
    private $_MODEL_MAINTENANCE_SCHEDULE;
    private $_MODEL_LOCATION;
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
       
        $this->_BACKEND_URL = Config::get("VehicleConstants.BACKEND_URL");

        $this->_MODEL_DRIVER_MASTER = new DriverMaster();
        $this->_MODEL_FUEL_CONSUMPTION = new FuelConsumption();
        $this->_MODEL_INCIDENT_LOG = new IncidentLog();
        $this->_MODEL_INSURANCE_COMPANY = new InsuranceCompany();
        $this->_MODEL_INSURANCE_REGISTRATION = new InsuranceRegistration();
        $this->_MODEL_MAINTENANCE_HISTORY = new MaintenanceHistory();
        $this->_MODEL_MAINTENANCE_SCHEDULE = new MaintenanceSchedule();
        $this->_MODEL_LOCATION  = new Location();
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

    public function getApplyData(Request $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vl0.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData;
        try{
            $ulbList = json_decode(Redis::get("PUBLIC-TRANSPORT-ULB-LIST"),true); 
            if(!$ulbList)
            {
                $ulbList = UlbMaster::select("*")->orderBy("id")->get();                           
                Redis::set("PUBLIC-TRANSPORT-ULB-LIST",json_encode($ulbList));
            } 
            $data["ulbList"] = $ulbList;
            return responseMsgs(true,"",remove_null($data),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }

    public function store(LocationAdd $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vl1.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData;
        try{
            DB::beginTransaction();
            $insertId = $this->_MODEL_LOCATION->insert($request->all());
            if(!$insertId)
            {
                throw new Exception("Somthing Went Wronge On Add Records");
            }
            DB::commit();
            return responseMsgs(true,"New Location Added Successfully","",$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }

    public function edit(LocationEdit $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vl2.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData;
        try{
            $location = $this->_MODEL_LOCATION->find($request->id);
            if(!$location)
            {
                throw new Exception("Data Find");
            }
            DB::beginTransaction();
            $update = $this->_MODEL_LOCATION->edit($request->all());
            if(!$update)
            {
                throw new Exception("Somthing Went Wronge On Add Records");
            }
            DB::commit();
            return responseMsgs(true,"Update Location Successfully","",$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }

    public function metaList()
    {
        return $ulbList = $this->getApplyData(new Request);
        return $list = $this->_MODEL_LOCATION->select("locations.*","ulb.ulb_name","ulb.short_name","ulb.ulb_type")
                ->join(DB::RAW("( select *
                                    from json_to_recordset('".$ulbList->original["data"]["ulbList"]."')
                                    as x(id bigint, ulb_name text, ulb_type text, city_id bigint,remarks text, deleted_at text , incorporation_date text,
                                        created_at text, updated_at text,  department_id bigint, has_zone text , district_code text, category text,
                                        code text, logo text, short_name text, tolle_free_no text, hindi_name text, current_website text, parent_website text,
                                        email text, mobile_no text, state_id text, district_id text
                                        ) 
                                    )ulb"),function ($join){
                    $join->on("locations.ulb_id", "ulb.id");
                });
    }

    public function list(RecordList $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vl3.1",1.1,null,$request->getMethod(),null,]]);
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
            if(isset($request->maintenanceStatus))
            {
                $status = $request->maintenance_status?1:0;
            }
            if($request->ulbId)
            {
                $ulbId = $request->ulbId;
            }
            return $list =  $this->metaList(); 
            if(!is_null($key))
            {
                $list = $list->where(function($where)use($key){
                        $where->ORWHERE(DB::raw("upper(locations.location_name)"),'LIKE', '%' . $key . '%');
                });
            }
            if(!is_null($status))
            {
                $list = $list->where("locations.status",$status);
            }
            
            if(!is_null($ulbId))
            {
                $list = $list->where("locations.ulb_id",$ulbId);
            }

            $list = $list->orderBy("locations.ulb_id");

            $list=paginater($list,$request) ;
            return responseMsgs(true,"Data Fetched",remove_null($list),$apiId, $version, $queryRunTime,$action,$deviceId,true);
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
            $request->merge(["metaData"=>["vl4.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData; 
        try{
            $location = $this->_MODEL_LOCATION->find($request->id);
            if(!$location)
            {
                throw new Exception("Data Not Found");
            }
            $ulbList = $this->getApplyData(new Request());
            $ulbList = $ulbList->original["data"]["ulbList"]??[];
            $ulbDtl = ((collect($ulbList)->where("id",$location->ulb_id)->values())->all())[0]??[];
            $location->ulb_name = $ulbDtl["ulb_name"]??""; 
            $data["location"] = $location;
            return responseMsgs(true,"Location Detail",remove_null($data),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }
}
