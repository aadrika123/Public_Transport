<?php

namespace App\Http\Controllers;

use App\Http\Requests\Vehicle\DtlById;
use App\Http\Requests\Vehicle\RecordList;
use App\Http\Requests\Vehicle\RouteAdd;
use App\Http\Requests\Vehicle\RouteEdit;
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

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class RouteController extends Controller
{
    private $INSURANCE_RELATIVE_PATH;
    private $_MODEL_DRIVER_MASTER;
    private $_MODEL_FUEL_CONSUMPTION;
    private $_MODEL_INCIDENT_LOG;
    private $_MODEL_INSURANCE_COMPANY;
    private $_MODEL_INSURANCE_REGISTRATION;
    private $_MODEL_LOCATION;
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
        $this->_MODEL_DRIVER_MASTER = new DriverMaster();
        $this->_MODEL_FUEL_CONSUMPTION = new FuelConsumption();
        $this->_MODEL_INCIDENT_LOG = new IncidentLog();
        $this->_MODEL_INSURANCE_COMPANY = new InsuranceCompany();
        $this->_MODEL_INSURANCE_REGISTRATION = new InsuranceRegistration();
        $this->_MODEL_LOCATION = new Location();
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

    public function getApplyData(Request $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vr0.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData;
        $rules=[
            "ulbId"=>"required|digits_between:1,9223372036854775807",
        ];
        $validator = Validator::make($request->all(), $rules,);
        if ($validator->fails()) {
            return responseMsgs(false, $validator->errors(), $request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }       
        try{
            $stopage = $this->_MODEL_STOPAGE::select("stopages.id","stopages.stopage_name")
                        ->join("locations","locations.id","stopages.location_id")
                        ->where("stopages.status",1);
            if($request->ulbId)
            {
                $stopage = $stopage->where("locations.ulb_id",$request->ulbId);
            }
            $stopage = $stopage->get();
            $data["stopage"] = $stopage;
            return responseMsgs(true,"",remove_null($data),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }

    public function store(RouteAdd $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vr1.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData; 
        try{
            DB::beginTransaction();
            $insertId = $this->_MODEL_ROUTE->insert($request->all());
            if(!$insertId)
            {
                throw new Exception("Somthing Went Wrong");
            }
            $request->merge(["routId"=>$insertId,"stopageId"=>$request->startStopageId]);  
            // dd($request->all());          
            $RoutstopageId = $this->_MODEL_ROUTE_STOPPAGE->insert($request->all());            
            if(!$RoutstopageId)
            {
                throw new Exception("Somthing Went Wrong");
            }
            if($request->startStopageId != $request->endStopageId)
            {
                $request->merge(["routId"=>$insertId,"stopageId"=>$request->endStopageId]);
                $RoutstopageId = $this->_MODEL_ROUTE_STOPPAGE->insert($request->all());
            }
            if(!$RoutstopageId)
            {
                throw new Exception("Somthing Went Wrong");
            }
            DB::commit();
            return responseMsgs(true,"New Route Add Successfully","",$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            DB::rollBack();
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }

    public function getEditData(DtlById $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vr2.2",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData; 

        try{
            $route = $this->_MODEL_ROUTE->find($request->id);
            if(!$route)
            {
                throw new Exception("Data Not Found");
            }
            $stopageId = $route->start_stopage_id ? $route->start_stopage_id :$route->end_stopage_id;
            $stopage = $this->_MODEL_STOPAGE->find($stopageId??0);
            $locationId = $this->_MODEL_LOCATION->find($stopage->location_id??0);
            $ulbId = $locationId->ulb_id??0;
            if(!$stopageId || $locationId || $ulbId)
            {
                throw new Exception("Invalid Data Not Found");
            }
            
            $request->merge(["ulbId"=>$ulbId]);
            $response = $this->getApplyData($request);
            if(!$response->original["status"])
            {
                throw new Exception($response->original["message"]);
            }
            $data = $response->original["data"];
            $data["route"] = $route;
            return responseMsgs(true,"Data Fetched",remove_null($data),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }

    public function edit(RouteEdit $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vr2.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData; 

        try{
            $test = $this->_MODEL_ROUTE->find($request->id);
            if(!$test)
            {
                throw new Exception("No Data Found");
            }
            $newStartingStopage = $this->_MODEL_ROUTE_STOPPAGE
                                ->where("route_id",$request->id)
                                ->where("stopage_id",$request->startStopageId)
                                ->first();

            $newEndingStopage = $this->_MODEL_ROUTE_STOPPAGE
                                ->where("route_id",$request->id)
                                ->where("stopage_id",$request->endStopageId)
                                ->first();

            $oldStartingStopage = $this->_MODEL_ROUTE_STOPPAGE
                                ->where("route_id",$request->id)
                                ->where("stopage_id",$test->start_stopage_id)
                                ->first();

            $oldEndingStopage = $this->_MODEL_ROUTE_STOPPAGE
                                ->where("route_id",$request->id)
                                ->where("stopage_id",$test->end_stopage_id)
                                ->first();            
            if($newStartingStopage && $newStartingStopage->status!=1)
            {
                $newStartingStopage->status=1;
            }
            if($newEndingStopage && $newEndingStopage->status!=1)
            {
                $newEndingStopage->status=1;
            }

            if($oldStartingStopage && $request->startStopageId != $test->start_stopage_id)
            {                
                $oldStartingStopage->status=0;
            }

            if($oldEndingStopage && $request->endStopageId != $test->end_stopage_id)
            {                
                $oldEndingStopage->status=0;
            }

            DB::beginTransaction();
            $update = $this->_MODEL_ROUTE->edit($request->all());
            if(!$update)
            {
                throw new Exception("Somthing Went Wrong");
            } 
            $request->merge(["routId"=>$request->id,"stopageId"=>$request->startStopageId]);
            $newStartingStopage ? $newStartingStopage->update() : $this->_MODEL_ROUTE_STOPPAGE->insert($request->all());

            $request->merge(["routId"=>$request->id,"stopageId"=>$request->endStopageId]); 
            $newEndingStopage ? $newEndingStopage->update() : $this->_MODEL_ROUTE_STOPPAGE->insert($request->all()); 

            $oldStartingStopage ? $oldStartingStopage->update() : "";            
            $oldEndingStopage ? $oldEndingStopage->update() : "" ; 
            
            DB::commit();
            return responseMsgs(true,"New Route Add Successfully","",$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            DB::rollBack();
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }

    public function metaList()
    {
        return $this->_MODEL_ROUTE
                ->join("stopages as starting","starting.id","routes.start_stopage_id")
                ->join("locations","locations.id","starting.location_id")
                ->leftjoin("stopages as ending","ending.id","routes.end_stopage_id")                
                ->select("routes.*","locations.ulb_id","starting.location_id",
                            DB::raw("starting.stopage_name as strating_stopage, 
                                ending.stopage_name as ending_stopage"
                            )                           
                        );
    }

    public function list(RecordList $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vir2.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData;        
        try{
            $list = $this->metaList();
            if($request->all==true)
            {
                $list = $list->get();
            }
            else
            {

                $ulbList = (new LocationController())->getApplyData(new Request());
                $ulbList = $ulbList->original["data"]["ulbList"]??[];
                $list=paginater($list,$request) ;
                // dd($list);
                $itemp = collect($list["data"])->map(function($val) use($ulbList){
                    $ulbDtl = ((collect($ulbList)->where("id",$val->ulb_id)->values())->all())[0]??[];
                    $val->ulb_name = $ulbDtl["ulb_name"]??"";                    
                    return $val; 
                }) ;
                $list["data"]=$itemp;
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
            $request->merge(["metaData"=>["vr4.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData;

        try{
            
            $ulbList = (new LocationController())->getApplyData(new Request());            
            $ulbList = $ulbList->original["data"]["ulbList"]??[];
            $route = $this->_MODEL_ROUTE->find($request->id);
            if(!$route)
            {
                throw new Exception("Data Not Found");
            }
            $stopage = $route->stopages()->get();
            $stopage = $stopage->map(function($val)use($route,$ulbList){                
                if($route->start_stopage_id==$val->id)
                {
                    $route->starting_stopage = $val->stopage_name;
                }
                if($route->end_stopage_id==$val->id)
                {
                    $route->ending_stopage = $val->stopage_name;
                }
                $l = $val->location()->first();
                $ulbDtl = ((collect($ulbList)->where("id",$l->ulb_id??null)->values())->all())[0]??[];
                
                $val->ulb_name = $ulbDtl["ulb_name"]??""; 
                $val->location_name = $l->location_name??""; 
                return $val;

            });
            $statingStopage = $this->_MODEL_STOPAGE->find($route->start_stopage_id);            
            $location = $statingStopage->location()->first();
            $ulbDtl = ((collect($ulbList)->where("id",$location->ulb_id??null)->values())->all())[0]??[];
            $location->ulb_name = $route->ulb_name = $ulbDtl["ulb_name"]??""; 
            $data["route"] =$route;
            $data["stopage"] =$stopage;
            $data["startingLocation"] =$location;
            return responseMsgs(true,"Data Fetched",remove_null($data),$apiId, $version, $queryRunTime,$action,$deviceId,true);
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }

}
