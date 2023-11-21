<?php

namespace App\Http\Controllers;

use App\Http\Requests\Vehicle\DtlById;
use App\Http\Requests\Vehicle\VehicleTypeAdd;
use App\Http\Requests\Vehicle\VehicleTypeEdit;
use App\Http\Requests\Vehicle\VehicleTypeList;
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

class VehicleTypeController extends Controller
{
    use TraitDocumentUpload;

    /**
     * created By Sandeep Bara
     * Date 19-06-2023
     * 
     */

     private $VEHICLE_TYPE_RELATIVE_PATH;
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
        $this->VEHICLE_TYPE_RELATIVE_PATH = Config::get("VehicleConstants.DRIVER_RELATIVE_PATH");
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

    public function store(VehicleTypeAdd $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vt1.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData; 
        try{            
            DB::beginTransaction();
            $vahicalTypeId =$this->_MODEL_VEHICLE_TYPE_MASTER->insert($request->all());
            if(!$vahicalTypeId)
            {
                throw new Exception("Somthing Went Wrong");
            }
            if($request->image)
            {
                $update = $this->_MODEL_VEHICLE_TYPE_MASTER->find($vahicalTypeId);

                $refImageName = $update->type;
                $refImageName = $vahicalTypeId . '-' . str_replace(' ', '_', $refImageName);
                $document = $request->image;
                $imageName = $this->upload($refImageName, $document, $this->VEHICLE_TYPE_RELATIVE_PATH);                
                $update->type_image = $imageName;
                $update->update();
            }
            DB::commit();
            return responseMsgs(true,"New Vehicle Type Add Successfully","",$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            DB::rollBack();
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }

    public function edit(VehicleTypeEdit $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vt2.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData;

        try{  
            $type = $this->_MODEL_VEHICLE_TYPE_MASTER->find($request->id) ; 
            if(!$type)
            {
                throw new Exception("Data Not Found");
            }   
            if($request->image)
            {
                $refImageName = $type->type;
                $refImageName = $type->id . '-' . str_replace(' ', '_', $refImageName);
                $document     = $request->image;
                $imageName    = $this->upload($refImageName, $document, $this->VEHICLE_TYPE_RELATIVE_PATH);            
                $request->merge(["imagePath"=>$imageName]);
            }     
            DB::beginTransaction();
            $update =$this->_MODEL_VEHICLE_TYPE_MASTER->edit($request->all());
            if(!$update)
            {
                throw new Exception("Somthing Went Wrong");
            }
            DB::commit();
            return responseMsgs(true,"Vehicle Type Update Successfully","",$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            DB::rollBack();
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }

    }

    public function list(VehicleTypeList $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vt3.1",1.1,null,$request->getMethod(),null,]]);
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

            $list = $this->_MODEL_VEHICLE_TYPE_MASTER->select("*");
            if(!is_null($key))
            {
                $list = $list->where(DB::raw("upper(type)"),'LIKE', '%' . $key . '%');
            }
            if(!is_null($status))
            {
                $list = $list->where("status",$status);
            }

            $perPage = $request->perPage ? $request->perPage :  10;
            $paginator = $list->paginate($perPage);                       
            $list = [
                "current_page" => $paginator->currentPage(),
                "last_page" => $paginator->lastPage(),
                "data" => collect($paginator->items())->map(function($val){
                    $val->total_vehicle = ($val->vehicalMasters()->count())??0;
                    return $val;
                }),
                "total" => $paginator->total(),
            ];

            return responseMsgs(true,"Vehicle Type List",remove_null($list),$apiId, $version, $queryRunTime,$action,$deviceId);
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
            $request->merge(["metaData"=>["vt4.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData; 
        try{
            $type = $this->_MODEL_VEHICLE_TYPE_MASTER->find($request->id);
            if(!$type)
            {
                throw new Exception("Data Not Found");
            }            
            $data["vehicleType"] = $type;
            $data["vehical_masters"]   = $type->vehicalMasters;
            return responseMsgs(true,"Data Fetched",remove_null($data),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }
}
