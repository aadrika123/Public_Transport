<?php

namespace App\Http\Controllers;

use App\Http\Requests\Vehicle\DtlById;
use App\Http\Requests\Vehicle\InsuranceCompanyAdd;
use App\Http\Requests\Vehicle\InsuranceCompanyEdit;
use App\Http\Requests\Vehicle\RecordList;
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

use Illuminate\Support\Facades\DB;

class InsuranceCompanyController extends Controller
{
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


    public function store(InsuranceCompanyAdd $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vic1.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData;

        try{
            DB::beginTransaction();
            $insertId = $this->_MODEL_INSURANCE_COMPANY->insert($request->all());
            if(!$insertId)
            {
                throw new Exception("Somthing Went Wronge On Add Records");
            }
            DB::commit();
            return responseMsgs(true,"New Records Added Successfully","",$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }

    public function edit(InsuranceCompanyEdit $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vic2.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData;

        try{
            $test = $this->_MODEL_INSURANCE_COMPANY->find($request->id);
            if(!$test)
            {
                throw new Exception("Data Not Found");
            }
            DB::beginTransaction();
            $update = $this->_MODEL_INSURANCE_COMPANY->edit($request->all());
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

    public function list(RecordList $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vic3.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData; 

        try{

            $fromDate = $uptoDate = $key = $status = $maintenance_status = $ulbId = null;
            if($request->fromDate || $request->uptoDate)
            {
                $fromDate = $request->fromDate ? $request->fromDate : Carbon::now()->format('Y-m-d');
                $uptoDate = $request->uptoDate ? $request->uptoDate : Carbon::now()->format('Y-m-d');
            }
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

            $list = $this->_MODEL_INSURANCE_COMPANY->select("*");

            if(!is_null($key))
            {
                $list = $list->where(DB::raw("upper(insurance_companies.make)"),'LIKE', '%' . $key . '%');
            }
            if(!is_null($status))
            {
                $list = $list->where("insurance_companies.status",$status);
            }
            
            if(!is_null($fromDate) && !is_null($uptoDate))
            {
                $list = $list->whereBetween(DB::raw("CAST(insurance_companies.created_at AS DATE)"),[$fromDate,$uptoDate]);
            }
            // if(!is_null($ulbId))
            // {
            //     $list = $list->where("vehicle_masters.ulb_id",$ulbId);
            // }

            $list = $list->orderBy("insurance_companies.id","DESC");
            
            if($request->all==true)
            {
                $list = $list->get();
            }
            else{

                $perPage = $request->perPage ? $request->perPage :  10;
                $paginator = $list->paginate($perPage);                       
                $list = [
                    "current_page" => $paginator->currentPage(),
                    "last_page" => $paginator->lastPage(),
                    "data" => collect($paginator->items())->map(function($val) use($maintenance_status){ 
                        $vehical = $val->vehicleMaster();                       
                        if(!is_null($maintenance_status))
                        {
                            if($maintenance_status==1)
                            {
                                $vehical = $vehical->where("vehicle_masters.maintenance_status",$maintenance_status);
                            }
                            else
                            {
                                $vehical = $vehical->where(function($where) use($maintenance_status){
                                    $where->orWhere("vehicle_masters.maintenance_status",$maintenance_status)
                                        ->orWhereNull("vehicle_masters.maintenance_status");
                                });
                            }
                        }
                        $val->total_vehicle = ($vehical->count())??0;
                        return $val;
                    }),
                    "total" => $paginator->total(),
                ];
            }
            return responseMsgs(true,"Data Fetched",remove_null($list),$apiId, $version, $queryRunTime,$action,$deviceId,($request->all?true:false));
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
            $compny = $this->_MODEL_INSURANCE_COMPANY->find($request->id);
            if(!$compny)
            {
                throw new Exception("Data Not Found");
            }

            $vehicles = $compny->vehicleMaster()->get();
            $data["company"] =$compny;
            $data["vehicles"] = $vehicles;
            return responseMsgs(true,"Maintenance Schedule Detail",$data,$apiId, $version, $queryRunTime,$action,$deviceId,true); 
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }
}
