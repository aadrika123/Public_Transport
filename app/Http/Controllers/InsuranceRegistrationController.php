<?php

namespace App\Http\Controllers;

use App\Http\Requests\Vehicle\DtlById;
use App\Http\Requests\Vehicle\InsuranceRegistrationAdd;
use App\Http\Requests\Vehicle\InsuranceRegistrationEdit;
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
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class InsuranceRegistrationController extends Controller
{

    use TraitDocumentUpload;

    private $INSURANCE_RELATIVE_PATH;
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

        $this->INSURANCE_RELATIVE_PATH = Config::get("VehicleConstants.INSURANCE_RELATIVE_PATH");
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
            $request->merge(["metaData"=>["vir.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData;
        try{
            $vehicleList = $this->_MODEL_VEHICLE_MASTER->SELECT("*")
                            ->WHERE("status",1)
                            ->orderBy("id")
                            ->get();
            $insurenceCompanyList = $this->_MODEL_INSURANCE_COMPANY->SELECT("*")
                            ->WHERE("status",1)
                            ->orderBy("id")
                            ->get();
            $data["vehicleList"] = $vehicleList;
            $data["insurenceCompanyList"] = $insurenceCompanyList;
            return responseMsgs(true,"",remove_null($data),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    } 

    public function store(InsuranceRegistrationAdd $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vir1.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData; 
        try{        
            DB::beginTransaction();

            $insertId = $this->_MODEL_INSURANCE_REGISTRATION->insert($request->all());
            if(!$insertId)
            {
                throw new Exception("Somthing Went Wrong");
            }
            if($request->image)
            {
                $update = $this->_MODEL_INSURANCE_REGISTRATION->find($insertId);

                $refImageName = $update->policy_no;
                $refImageName = $insertId . '-' . str_replace(' ', '_', $refImageName);
                $document = $request->image;
                $imageName = $this->upload($refImageName, $document, $this->INSURANCE_RELATIVE_PATH);                
                $update->insurance_doc = $imageName;
                $update->update();
            }
            DB::commit();
            return responseMsgs(true,"New Record Add Successfully","",$apiId, $version, $queryRunTime,$action,$deviceId);
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
            $request->merge(["metaData"=>["vir2.2",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData; 

        try{
            $insuranceRegtDtl = $this->_MODEL_INSURANCE_REGISTRATION->find($request->id);
            if(!$insuranceRegtDtl)
            {
                throw new Exception("Data Not Found");
            } 
            $response = $this->getApplyData($request);
            if(!$response->original["status"])
            {
                throw new Exception($response->original["message"]);
            }
            $data = $response->original["data"];
            $data["insuranceRegtDtl"] = $insuranceRegtDtl;
            return responseMsgs(true,"",remove_null($data),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }

    public function edit(InsuranceRegistrationEdit $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vir2.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData; 

        try{
            $test = $this->_MODEL_INSURANCE_REGISTRATION->find($request->id);
            if(!$test)
            {
                throw new Exception("Data Not Found");
            }
            if($request->image)
            {
                $refImageName = $test->policy_no;
                $refImageName = $test->id . '-' . str_replace(' ', '_', $refImageName);
                $document = $request->image;
                $imageName = $this->upload($refImageName, $document, $this->INSURANCE_RELATIVE_PATH);                
                $request->merge(["imagePath"=>$imageName]);
            }

            DB::beginTransaction();
            $update = $this->_MODEL_INSURANCE_REGISTRATION->edit($request->all());
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
        return $this->_MODEL_INSURANCE_REGISTRATION
                ->join("vehicle_masters","vehicle_masters.id","insurance_registrations.vehicle_id")
                ->join("insurance_companies","insurance_companies.id","insurance_registrations.insurance_company_id")
                ->leftjoin("vehicle_type_masters","vehicle_type_masters.id","vehicle_masters.vehicle_type_id")
                ->select("insurance_registrations.*",
                            DB::raw("vehicle_masters.make, vehicle_masters.model, vehicle_masters.production_year,
                            vehicle_masters.registration_no, vehicle_masters.vehicle_image,
                            vehicle_type_masters.vehicle_type, vehicle_type_masters.type_image,
                            insurance_companies.insurance_company,
                            CASE WHEN insurance_registrations.policy_expiration_date > CAST(NOW() AS DATE) THEN 'Active'
                                 WHEN insurance_registrations.policy_expiration_date = CAST(NOW() AS DATE) THEN 'Today Expired'
                                 WHEN insurance_registrations.policy_expiration_date < CAST(NOW() AS DATE) THEN 'Expired'
                                 ELSE NULL END AS expary_status
                            ")                            
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
                        $where->ORWHERE(DB::raw("upper(insurance_registrations.policy_no)"),'LIKE', '%' . $key . '%')
                        ->ORWHERE(DB::raw("upper(vehicle_masters.model)"),'LIKE', '%' . $key . '%')
                        ->ORWHERE(DB::raw("upper(vehicle_masters.registration_no)"),'LIKE', '%' . $key . '%')
                        ->ORWHERE(DB::raw("upper(insurance_companies.insurance_company)"),'LIKE', '%' . $key . '%')
                        ->ORWHERE(DB::raw("upper(vehicle_type_masters.vehicle_type)"),'LIKE', '%' . $key . '%')
                        ;
                });
            }
            if(!is_null($status))
            {
                $list = $list->where("insurance_registrations.status",$status);
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
            $request->merge(["metaData"=>["vir4.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData;

        try{

            $insuranceRegtDtl = $this->_MODEL_INSURANCE_REGISTRATION->find($request->id);
            if(!$insuranceRegtDtl)
            {
                throw new Exception("Data Not Found");
            } 

            $vehicleDtl = $insuranceRegtDtl->vehicleMaster()->first();
            $companyDtl = $insuranceRegtDtl->insuranceCompany()->first();

            $data["insuranceRegtDtl"] = $insuranceRegtDtl;
            $data["vehicleDtl"] = $vehicleDtl;
            $data["companyDtl"] = $companyDtl;
            
            $queryRunTime = collect(DB::getQueryLog())->sum("time");
            // dd($queryRunTime,DB::getQueryLog());
            return responseMsgs(true,"Data Fetched",remove_null($data),$apiId, $version, $queryRunTime,$action,$deviceId,true);
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }
}
