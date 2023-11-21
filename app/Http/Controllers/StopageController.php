<?php

namespace App\Http\Controllers;

use App\Http\Requests\Vehicle\DtlById;
use App\Http\Requests\Vehicle\RecordList;
use App\Http\Requests\Vehicle\StopageAdd;
use App\Http\Requests\Vehicle\StopageEdit;
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
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class StopageController extends Controller
{
    private $_DRIVER_RELATIVE_PATH;
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
        $this->_DRIVER_RELATIVE_PATH = Config::get("VehicleConstants.DRIVER_RELATIVE_PATH");

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

    public function store(StopageAdd $request)
    {           
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vs1.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData; 
        
        try{    
            DB::beginTransaction();        
            $insertId = $this->_MODEL_STOPAGE->insert($request->all());
            if(!$insertId)
            {
                throw new Exception("Somthing Went Wronge On Add Records");
            }
            // dd($insertId ,DB::getQueryLog());
            DB::commit();
            return responseMsgs(true,"New Stopage Added Successfully","",$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            DB::rollBack();
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }

    public function edit(StopageEdit $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vs2.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData; 
        try{
            $vahical = $this->_MODEL_STOPAGE->find($request->id);
            if(!$vahical)
            {
                throw new Exception("Data Not Found");
            }            
            DB::beginTransaction();
            $update = $this->_MODEL_STOPAGE->edit($request->all());
            if(!$update)
            {
                throw new Exception("Somthing Went Wrong");
            }
            DB::commit();
            return responseMsgs(true,"Stopage Update SuccessFully","",$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            DB::rollBack();
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }

    public function metaList()
    {
        return $this->_MODEL_STOPAGE->SELECT("*");
    }

    public function list(RecordList $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vs3.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData; 

        try{

            $key = $status =  $ulbId = null;
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
                        $where->ORWHERE(DB::raw("upper(stopage_name)"),'LIKE', '%' . $key . '%');
                });
            }
            if(!is_null($status))
            {
                $list = $list->where("status",$status);
            }
            
            // if(!is_null($ulbId))
            // {
            //     $list = $list->where("ulb_id",$ulbId);
            // }

            $list = $list->orderBy("stopage_name");
            
            $list=paginater($list,$request) ;          
            
            return responseMsgs(true,"Data Fetched",remove_null($list),$apiId, $version, $queryRunTime,$action,$deviceId);
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
            $request->merge(["metaData"=>["vs4.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData; 
        try{
            $stopage = $this->_MODEL_STOPAGE->find($request->id);
            if(!$stopage)
            {
                throw new Exception("Data Not Found");
            }
            $routes = $stopage->routes()->get();
            $data["stopage"] = $stopage;
            $data["routes"] = $routes;
            return responseMsgs(true,"Data Fetched",remove_null($data),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }
}
