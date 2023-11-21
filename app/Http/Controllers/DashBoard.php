<?php

namespace App\Http\Controllers;

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
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class DashBoard extends Controller
{
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

    private $_ROUTE_CONTROLLER;
    private $_TRANSIT_BUS_CONTROLLER;
    private $_VEHICLE_CONTROLLER;
    
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

        $this->_ROUTE_CONTROLLER =  new RouteController();
        $this->_TRANSIT_BUS_CONTROLLER = new TransitBusControllers();
        $this->_VEHICLE_CONTROLLER = new VehicleController();
    }


    public function vehicelDashBoard(Request $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vd0.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData;

        try{
            $user = Auth()->user();
            $ulbId = $user->ulb_id;

            $vehicles = $this->_VEHICLE_CONTROLLER->metaList()
                        ->where("vehicle_masters.ulb_id",$ulbId)
                        ->where("vehicle_masters.status",1)
                        ->get();
            $total_vehicle = $vehicles->count();
            $total_maintaind_vehicles = $vehicles
                        ->where("maintenance_status",1)
                        ->count("id");
            $roots = $this->_ROUTE_CONTROLLER->metaList()
                     ->where("ulb_id",$ulbId)
                     ->get()
                    ->count("id");
            $fleets = $this->_TRANSIT_BUS_CONTROLLER->metaList()
                        ->where("ulb_id",$ulbId)
                        ->get();
            $total_fleets = collect($fleets)->groupBy("route_id")->count("vehicle_id");
            $data=[
                "total_vehicle" =>$total_vehicle,
                "total_maintaind_vehicles" =>$total_maintaind_vehicles,
                "roots" =>$roots,
                "total_fleets" =>$total_fleets,
            ];
            return responseMsgs(true,"",$data,$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }

    public function fleetsDashBoard(Request $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vd1.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData;

        try{
            $user = Auth()->user();
            $ulbId = $user->ulb_id;
            
            $fleets = $this->_TRANSIT_BUS_CONTROLLER->metaList()
                        ->where("ulb_id",$ulbId)
                        ->get();
            $fleet_id = $fleets->pluck("route_id")->unique();
            $fleets_dtl = array();
            foreach($fleet_id as $id)
            {
                $fleets_dtl[]=[
                    "fleets_name" => ($fleets->where("route_id",$id)->pluck("route_name")->unique())[0]??"",
                    "fleets_list"=>$fleets->where("route_id",$id),
                ];
            }
            return responseMsgs(true,"",$fleets_dtl,$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }

    public function runningVehicleDashBoard(Request $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vd1.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData;

        try{
            $user = Auth()->user();
            $ulbId = $user->ulb_id;
            $vehicles = $this->_VEHICLE_CONTROLLER->metaList()
                        ->leftjoin("transit_bus_masters","transit_bus_masters.vehicle_id","vehicle_masters.id")
                        ->leftjoin("routes","routes.id","transit_bus_masters.route_id")
                        ->leftjoin("stopages as starting","starting.id","routes.start_stopage_id")
                        ->join("locations","locations.id","starting.location_id")
                        ->leftjoin("stopages as ending","ending.id","routes.end_stopage_id")
                        ->where("vehicle_masters.ulb_id",$ulbId)
                        ->where("vehicle_masters.status",1)
                        ->select(
                            "vehicle_masters.*",                            
                            "routes.route_name","routes.start_stopage_id","routes.end_stopage_id","routes.is_circular",
                            "routes.distance","routes.travel_time",
                            "routes.sunday","routes.monday","routes.tuesday","routes.wednesday","routes.thursday","routes.friday","routes.saturday",
                            "starting.location_id",
                            DB::raw("vehicle_type_masters.vehicle_type AS vehicle_type,starting.stopage_name as strating_stopage, 
                            ending.stopage_name as ending_stopage"),                         
                        )
                        ->orderBy("vehicle_masters.id")
                        ->get()
                        ;
            
            return responseMsgs(true,"",$vehicles,$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }

    public function vehicleInMentanance(Request $request)
    { 
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vd1.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData;

        try{
            $user = Auth()->user();
            $ulbId = $user->ulb_id;
            $vehicles = $this->_VEHICLE_CONTROLLER->metaList()                        
                        ->where("vehicle_masters.ulb_id",$ulbId)
                        ->where("vehicle_masters.status",1)
                        ->where("maintenance_status",1)                        
                        ->orderBy("vehicle_masters.id")
                        ->get()
                        ;
            $vehicleRoute = $vehicles->map(function($val){
                $val->fleets = ($val->routes()->get())->map(function($val1){
                    $val1->strating_stopage = ($val1->statrtingStopage()->first())->stopage_name;
                    $val1->ending_stopage = ($val1->endingStopage()->first())->stopage_name;
                    return $val1;
                });
                
                return $val;
            });
            return responseMsgs(true,"",$vehicleRoute,$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }
}
