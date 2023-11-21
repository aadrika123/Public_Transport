<?php

namespace App\Http\Controllers;

use App\Http\Requests\Vehicle\AddDriver;
use App\Http\Requests\Vehicle\DriverList;
use App\Http\Requests\Vehicle\DtlById;
use App\Http\Requests\Vehicle\EditDriver;
use App\Http\Requests\Vehicle\ValidateDriverLicense;
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

class DriverController extends Controller
{
    use TraitDocumentUpload;

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

    public function store(AddDriver $request)
    {   
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vd1.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData; 

        try{ 
            
            DB::beginTransaction();        
            $insertId = $this->_MODEL_DRIVER_MASTER->insert($request->all());
            if(!$insertId)
            {
                throw new Exception("Some Error Occures On Record Add");
            }
            if($request->image)
            {
                $refImageName = $request->name;
                $refImageName = $insertId . '-' . str_replace(' ', '_', $refImageName);
                $document = $request->image;
                $imageName = $this->upload($refImageName, $document, $this->_DRIVER_RELATIVE_PATH);
                $update = $this->_MODEL_DRIVER_MASTER->find($insertId);
                $update->driver_image = $imageName;
                $update->update();
            }
            DB::commit();
            return responseMsgs(true,"New Driver Added Successfully",[],$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            DB::rollBack();
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }

    public function edit(EditDriver $request)
    { 
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vd2.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData; 
        try{
            $driver = $this->_MODEL_DRIVER_MASTER->find($request->id);
            if(!$driver)
            {
                throw new Exception("Data Not Found");
            }
            if($request->image)
            {
                $refImageName = $driver->name;
                $refImageName = $driver->id . '-' . str_replace(' ', '_', $refImageName);
                $document     = $request->image;
                $imageName    = $this->upload($refImageName, $document, $this->_DRIVER_RELATIVE_PATH);            
                $request->merge(["imagePath"=>$imageName]);
            }
            DB::beginTransaction();
            $update = $this->_MODEL_DRIVER_MASTER->edit($request->all());
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
        return $this->_MODEL_DRIVER_MASTER
                    ->select("driver_masters.*",
                            DB::raw("
                                TO_CHAR(CAST(license_expiry_date AS DATE), 'DD-MM-YYYY') as license_expiry_date,
                                TO_CHAR(CAST(joining_date AS DATE), 'DD-MM-YYYY') as joining_date
                            "));
    }

    public function list(DriverList $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vd3.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData; 

        try{

            $fromData = $uptoDate = $key = $status = $ulbId = null;
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

            if($request->fromDate && $request->uptoDate)
            {
                $fromData = $request->fromDate;
                $uptoDate = $request->uptoDate;
            }

            $list = $this->metaList();

            if(!is_null($key))
            {
                $list = $list->where(function($where)use($key){
                        $where->ORWHERE(DB::raw("upper(driver_name)"),'LIKE', '%' . $key . '%')
                        ->ORWHERE(DB::raw("upper(driving_license_no)"),'LIKE', '%' . $key . '%')
                        ->ORWHERE(DB::raw("contact_information"),'LIKE', '%' . $key . '%');
                });
            }
            if(!is_null($status))
            {
                $list = $list->where("status",$status);
            }
            if($fromData && $uptoDate)
            {
                $list = $list->whereBetween(DB::raw("caset(created_at as date)"),[$fromData,$uptoDate]);
            }
            // if(!is_null($ulbId))
            // {
            //     $list = $list->where("vahical_dtls.ulb_id",$ulbId);
            // }

            $list = $list->orderBy("id");

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
            $request->merge(["metaData"=>["vh4.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData; 
        try{
            $driver = $this->_MODEL_DRIVER_MASTER->find($request->id);
            if(!$driver)
            {
                throw new Exception("Data Not Found");
            }
            $transitBusMaster = $driver->transitBusMaster()->orderby("id")->get();
            $data["driver"] = $driver;
            $data["transit"]   = $transitBusMaster;
            return responseMsgs(true,"Data Fetched",remove_null($data),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }

    public function validateLiceseNo(ValidateDriverLicense $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vh4.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData; 
        try{
            $licenseNo = $request->drivingLicenseNo;
            if($this->_MODEL_DRIVER_MASTER->where("driving_license_no",$licenseNo)->count("id")>0)
            {
                throw new Exception("Driving License No Already Exist. Try Another");
            }
            return responseMsgs(true,"Ok","",$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        
    }
}
