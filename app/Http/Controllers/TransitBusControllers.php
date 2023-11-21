<?php

namespace App\Http\Controllers;

use App\Http\Requests\Vehicle\DtlById;
use App\Http\Requests\Vehicle\RecordList;
use App\Http\Requests\Vehicle\TransitBusMasterAdd;
use App\Http\Requests\Vehicle\TransitBusMasterEdit;
use App\Http\Requests\Vehicle\VehicleList;
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
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TransitBusControllers extends Controller
{
    use TraitDocumentUpload;
    
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

    private $_VEHICLE_CONTROLLER ;
    
    public function __construct()
    {
        DB::enableQueryLog();
       
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

        $this->_VEHICLE_CONTROLLER = new VehicleController();
    }

    public function getApplyData(Request $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vtb0.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData;
        try{
            $request->merge(["all"=>true]);
            $newRequest = new VehicleList();
            $newRequest->merge($request->all()) ;
            $vehicleList = $this->_VEHICLE_CONTROLLER->list($newRequest)->original["data"]??[];
            $routList  = $this->_MODEL_ROUTE->SELECT("*")->WHERE("status",1)->get();
            $data["vehicleList"] = $vehicleList;
            $data["routList"]   = $routList;
            return responseMsgs(true,"",remove_null($data),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }

    public function store(TransitBusMasterAdd $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vtb1.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData;
        try{
            DB::beginTransaction();
            $insertId = $this->_MODEL_TRANSIT_BUS_MASTER->insert($request->all());
            if(!$insertId)
            {
                throw new Exception("Somthing Went Wronge On Add Records");
            }
            DB::commit();
            return responseMsgs(true,"New Record Added Successfully","",$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }

    public function getEditData(DtlById $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vtb2.2",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData; 

        try{
            $transitBus = $this->_MODEL_TRANSIT_BUS_MASTER->find($request->id);
            if(!$transitBus)
            {
                throw new Exception("Data Not Found");
            } 
            $response = $this->getApplyData($request);
            if(!$response->original["status"])
            {
                throw new Exception($response->original["message"]);
            }
            $transitBus->start_time = $transitBus->start_time? Carbon::parse($transitBus->start_time)->format("H:m"):"";
            $transitBus->end_time = $transitBus->end_time? Carbon::parse($transitBus->end_time)->format("H:m"):"";
            $data = $response->original["data"];
            $data["transitBusDtl"] = $transitBus;
            return responseMsgs(true,"",remove_null($data),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }

    public function edit(TransitBusMasterEdit $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vtb2.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData;
        try{
            $test = $this->_MODEL_TRANSIT_BUS_MASTER->find($request->id);
            if(!$test)
            {
                throw new Exception("Data Not Found");
            }
            DB::beginTransaction();
            $update = $this->_MODEL_TRANSIT_BUS_MASTER->edit($request->all());
            if(!$update)
            {
                throw new Exception("Somthing Went Wrong");
            }
            DB::commit();
            return responseMsgs(true,"Data Edit SuccessFully","",$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }

    }

    public function metaList()
    {
        return $this->_MODEL_TRANSIT_BUS_MASTER
             ->join("vehicle_masters","vehicle_masters.id","transit_bus_masters.vehicle_id")
             ->join("routes","routes.id","transit_bus_masters.route_id")
             ->join("vehicle_type_masters","vehicle_type_masters.id","vehicle_masters.vehicle_type_id")
             ->join("stopages","stopages.id","routes.start_stopage_id")
             ->join("stopages AS ending","ending.id","routes.end_stopage_id")
            ->select("transit_bus_masters.*",
                    "vehicle_masters.registration_no","vehicle_masters.vehicle_image","vehicle_masters.ulb_id",
                    "routes.route_name","routes.route_name","routes.travel_time",
                    "stopages.stopage_name AS starting_stopage","ending.stopage_name AS ending_stopage",
                    "vehicle_type_masters.vehicle_type"
                );
                
    }

    public function list(RecordList $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vtb3.1",1.1,null,$request->getMethod(),null,]]);
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

            $list = $this->metaList();

            if(!is_null($key))
            {
                $list = $list->where(function($where)use($key){
                        $where->ORWHERE(DB::raw("upper(vehicle_masters.make)"),'LIKE', '%' . $key . '%')
                        ->ORWHERE(DB::raw("upper(vehicle_masters.model)"),'LIKE', '%' . $key . '%')
                        ->ORWHERE(DB::raw("upper(vehicle_masters.registration_no)"),'LIKE', '%' . $key . '%')
                        ->ORWHERE(DB::raw("upper(stopages.stopage_name)"),'LIKE', '%' . $key . '%')
                        ->ORWHERE(DB::raw("upper(ending.stopage_name)"),'LIKE', '%' . $key . '%')
                        ->ORWHERE(DB::raw("upper(routes.route_name)"),'LIKE', '%' . $key . '%');
                });
            }
            if(!is_null($status))
            {
                $list = $list->where("transit_bus_masters.status",$status);
            }
            if(!is_null($maintenance_status))
            {
                if($maintenance_status==1)
                {
                    $list = $list->where("vehicle_masters.maintenance_status",$maintenance_status);
                }
                else
                {
                    $list = $list->where(function($where) use($maintenance_status){
                        $where->orWhere("vehicle_masters.maintenance_status",$maintenance_status)
                            ->orWhereNull("vehicle_masters.maintenance_status");
                    });
                }
            }
            // if(!is_null($ulbId))
            // {
            //     $list = $list->where("vehicle_masters.ulb_id",$ulbId);
            // }

            $list = $list->orderBy("transit_bus_masters.id");
            
            if($request->all==true)
            {
                $list = $list->get();
            }
            else{

                $list=paginater($list,$request) ;
            }

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
            $request->merge(["metaData"=>["vtb4.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData; 
        try{
            $transitBus = $this->_MODEL_TRANSIT_BUS_MASTER->find($request->id);
            if(!$transitBus)
            {
                throw new Exception("Data Not Found");
            }
            $drivers = $transitBus->drivers()->get();
            $transiBusRunDtl = $transitBus->transitBusRunDetails()->get();
            $tickets = $transitBus->ticketMastes()->get();
            $routes = $transitBus->routes()->get();
            
            $data["transitBus"] = $transitBus;
            $data["driverList"] = $drivers;
            $data["transiBusRunDetails"] = $transiBusRunDtl;
            $data["ticketList"] = $tickets;
            $data["routes"] = $routes;
            return responseMsgs(true,"Data Fetched",remove_null($data),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }
}
