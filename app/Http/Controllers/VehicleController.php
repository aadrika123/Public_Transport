<?php

namespace App\Http\Controllers;

use App\Http\Requests\Vehicle\AddDriver;
use App\Http\Requests\Vehicle\DtlById;
use App\Http\Requests\Vehicle\EditDriver;
use App\Http\Requests\Vehicle\RecordList;
use App\Http\Requests\Vehicle\VehicleAdd;
use App\Http\Requests\Vehicle\VehicleEdit;
use App\Http\Requests\Vehicle\VehicleList;
use App\Models\User;
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
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use function PHPUnit\Framework\isNull;

class VehicleController extends Controller
{

    use TraitDocumentUpload;

    private $_VEHICLE_RELATIVE_PATH;
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
        $this->_VEHICLE_RELATIVE_PATH = Config::get("VehicleConstants.VEHICLE_RELATIVE_PATH");
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

    public function getApplyData(Request $request)
    {
        
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vh0.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData;
        try{
            // new User($request->user);
            // Auth()->setUser(new AuthManager($request->user));
            $vehicalTypeList = $this->_MODEL_VEHICLE_TYPE_MASTER->SELECT("*")
                            ->WHERE("status",1)
                            ->orderBy("id")
                            ->get();
            $drivareList = $this->_MODEL_DRIVER_MASTER->SELECT("*")
                            ->WHERE("status",1)
                            ->WHERENOT("working_status",2)
                            ->orderBy("id")
                            ->get();
            $data["vehicalTypeList"] = $vehicalTypeList;
            $data["driverList"] = $drivareList;
            return responseMsgs(true,"",remove_null($data),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }
    #VehicleAdd
    public function store( VehicleAdd $request)
    {   
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vh1.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData; 

        try{    
            DB::beginTransaction();        
            $insertId = $this->_MODEL_VEHICLE_MASTER->insert($request->all());
            if(!$insertId)
            {
                throw new Exception("Somthing Went Wronge On Add Records");
            }
            if($request->image)
            {
                $refImageName = "";
                $refImageName = $insertId . '-' . str_replace(' ', '_', $refImageName);
                $document = $request->image;
                $imageName = $this->upload($refImageName, $document, $this->_VEHICLE_RELATIVE_PATH);
                $update = $this->_MODEL_VEHICLE_MASTER->find($insertId);
                $update->vehicle_image = $imageName;
                $update->update();
            }
            DB::commit();
            // dd($insertId ,DB::getQueryLog());
            return responseMsgs(true,"New Vehicle Added Successfully","",$apiId, $version, $queryRunTime,$action,$deviceId);
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
            $request->merge(["metaData"=>["vh2.2",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData; 

        try{
            $vehicle = $this->_MODEL_VEHICLE_MASTER->find($request->id);
            if(!$vehicle)
            {
                throw new Exception("Data Not Found");
            } 
            $response = $this->getApplyData($request);
            if(!$response->original["status"])
            {
                throw new Exception($response->original["message"]);
            }
            $data = $response->original["data"];
            $data["vehicleDtl"] = $vehicle;
            return responseMsgs(true,"",remove_null($data),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }
    public function edit(VehicleEdit $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vh2.2",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData; 
        try{
            $vahical = $this->_MODEL_VEHICLE_MASTER->find($request->id);
            if(!$vahical)
            {
                throw new Exception("Data Not Found");
            }   
            if($request->image)
            {
                $refImageName = "";
                $refImageName = $vahical->id . '-' . str_replace(' ', '_', $refImageName);
                $document = $request->image;
                $imageName = $this->upload($refImageName, $document, $this->_VEHICLE_RELATIVE_PATH);
                $request->merge(["imagePath"=>$imageName]);
            }

            DB::beginTransaction();
            $update = $this->_MODEL_VEHICLE_MASTER->edit($request->all());
            if(!$update)
            {
                throw new Exception("Somthing Went Wrong");
            }
            DB::commit();
            return responseMsgs(true,"Data Edit SuccessFully","",$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            DB::rollBack();
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }

    public function metaList()
    {
        return $this->_MODEL_VEHICLE_MASTER
                    ->select("vehicle_masters.*",                            
                            DB::raw("vehicle_type_masters.vehicle_type AS vehicle_type")
                            )
                    ->join("vehicle_type_masters","vehicle_type_masters.id","vehicle_masters.vehicle_type_id");
    }

    public function list(VehicleList $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vh3.1",1.1,null,$request->getMethod(),null,]]);
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
                $maintenance_status = $request->maintenanceStatus?1:0;
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
                        ->ORWHERE(DB::raw("upper(vehicle_masters.registration_no)"),'LIKE', '%' . $key . '%');
                });
            }
            if(!is_null($status))
            {
                $list = $list->where("vehicle_masters.status",$status);
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

            $list = $list->orderBy("vehicle_masters.vehicle_type_id")
                    ->orderBy("vehicle_masters.id");
            
            if($request->all==true)
            {
                $list = $list->get();
            }
            else{

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
            $request->merge(["metaData"=>["vh4.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData; 
        try{
            $vehicle = $this->_MODEL_VEHICLE_MASTER->find($request->id);
            if(!$vehicle)
            {
                throw new Exception("Data Not Found");
            }                      
            $lastmentenanceSchedules = $vehicle->mentenanceSchedules()->orderby("service_date","DESC")->first();
            $mentenanceHistories = $vehicle->mentenanceHistories()->orderby("service_date","DESC")->get();
            $fuelConsumptions = $vehicle->fuelConsumptions()->orderby("tran_date","DESC")->get();
            $incidentLogs = ($vehicle->incidentLogs()->orderby("incident_date","DESC")->get())->map(function($val){
                $driverDtl = $this->_MODEL_DRIVER_MASTER->find($val->driver_id);
                $val->driver_name = $driverDtl->driver_name??"";
                return $val;
            });
            $insuranceRegistrations = $vehicle->insuranceRegistrations()->orderby("id","DESC")->get();
            $insuranceCompanies = $vehicle->insuranceCompanies()->get();
            $insuranceRegistrations = $insuranceRegistrations->map(function($val)use($insuranceCompanies){
                $val->compny_name = ($insuranceCompanies->where("id",2)->pluck("insurance_company")??"");
                return $val;
            });
            $transitMasters = $vehicle->transitMasters()->get();
            $lastTransitBusDrivers = $vehicle->transitBusDrivers()->orderby("assignment_date","DESC")->first();
            if(!is_Null($lastTransitBusDrivers))
            {
                $lastTransitBusDrivers->driver_name = ($this->_MODEL_DRIVER_MASTER->find($lastTransitBusDrivers->driver_id)->driver_name??"");
            }
            
            $transitBusSchedules = $vehicle->transitBusSchedules()->orderby("id")->first();
            $routes = $vehicle->routes()->get();
            
            $data["vehicleDtl"] = $vehicle;
            $data["lastmentenanceSchedules"]   = $lastmentenanceSchedules;
            $data["mentenanceHistories"] = $mentenanceHistories;

            $data["fuelConsumptions"] = $fuelConsumptions;
            $data["incidentLogs"]   = $incidentLogs;
            $data["insuranceRegistrations"] = $insuranceRegistrations;

            $data["insuranceCompanies"] = $insuranceCompanies;
            $data["transitMasters"]   = $transitMasters;
            $data["lastTransitBusDrivers"] = $lastTransitBusDrivers;

            $data["transitBusSchedules"] = $transitBusSchedules;
            $data["routes"]   = $routes->map(function($val){
                $val->starting_stopage = ($this->_MODEL_STOPAGE->find($val->start_stopage_id)->stopage_name??"");
                $val->ending_stopage= ($this->_MODEL_STOPAGE->find($val->end_stopage_id)->stopage_name??"");
                $val->start_time = ($val->start_time?Carbon::parse($val->start_time)->format("h:i:s A"):"");
                $val->end_time= ($val->end_time?Carbon::parse($val->end_time)->format("h:i:s A"):"");
                return $val;
            });

            return responseMsgs(true,"Data Fetched",remove_null($data),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }

}
