<?php

namespace App\Http\Controllers;

use App\Http\Requests\Vehicle\DtlById;
use App\Http\Requests\Vehicle\MaintainanceScheduleAdd;
use App\Http\Requests\Vehicle\MaintainanceScheduleEdit;
use App\Http\Requests\Vehicle\RecordList;
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
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class MaintenanceScheduleController extends Controller
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

    private $_VEHICLE_Controller;
    
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

        $this->_VEHICLE_Controller = new VehicleController();
    }

    public function getApplyData(Request $request)
    { 
        return($request->all());
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vms0.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData;
        try{
            $request->merge(["all"=>true]);
            $newRequest = new VehicleList();
            $newRequest->merge($request->all());
            $vehicleList = $this->_VEHICLE_Controller->list($newRequest);
            if(!$vehicleList->original["status"])
            {
                throw new Exception($vehicleList->original["message"]);
            }
            $data["vehicleList"] = $vehicleList->original["data"];
            return responseMsgs(true,"",remove_null($data),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }

    public function store(MaintainanceScheduleAdd $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vsm1.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData; 
        try{
            DB::beginTransaction();
            $insertId = $this->_MODEL_MAINTENANCE_SCHEDULE->insert($request->all());
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

    public function getEditData(DtlById $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vsm2.2",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData;

        try{
            $maintenanceSchedule = $this->_MODEL_MAINTENANCE_SCHEDULE->find($request->id);
            if(!$maintenanceSchedule)
            {
                throw new Exception("Data Not Found");
            } 
            $response = $this->getApplyData($request);
            if(!$response->original["status"])
            {
                throw new Exception($response->original["message"]);
            }
            $data = $response->original["data"];
            $data["maintenanceSchedule"] = $maintenanceSchedule;
            return responseMsgs(true,"",remove_null($data),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }


    public function edit(MaintainanceScheduleEdit $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vms2.2",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData; 

        try{
            $maintenanceSchedule = $this->_MODEL_MAINTENANCE_SCHEDULE->find($request->id);
            if(!$maintenanceSchedule)
            {
                throw new Exception("Data Not Found");
            }
            DB::beginTransaction();            
            $update = $this->_MODEL_MAINTENANCE_SCHEDULE->edit($request->all());;
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
        return $this->_MODEL_MAINTENANCE_SCHEDULE   
                    ->join("vehicle_masters","vehicle_masters.id","maintenance_schedules.vehicle_id")                 
                    ->join("vehicle_type_masters","vehicle_type_masters.id","vehicle_masters.vehicle_type_id")
                    ->select("maintenance_schedules.*",
                            "vehicle_masters.vehicle_type_id","vehicle_masters.make","vehicle_masters.model" ,
                             "vehicle_masters.production_year", "vehicle_masters.registration_no"  ,
                             "vehicle_masters.current_milage" ,"vehicle_masters.vehicle_image",         
                            DB::raw("vehicle_masters.status as vehicle_status, 
                            vehicle_type_masters.vehicle_type AS vehicle_type                            
                            ")
                    );
    }

    public function list(RecordList $request)
    {
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vms3.1",1.1,null,$request->getMethod(),null,]]);
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
                        ->ORWHERE(DB::raw("upper(vehicle_type_masters.vehicle_type)"),'LIKE', '%' . $key . '%');
                });
            }
            if(!is_null($status))
            {
                $list = $list->where("maintenance_schedules.status",$status);
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
            if(!is_null($fromDate) && !is_null($uptoDate))
            {
                $list = $list->whereBetween("maintenance_schedules.service_date",[$fromDate,$uptoDate]);
            }
            // if(!is_null($ulbId))
            // {
            //     $list = $list->where("vehicle_masters.ulb_id",$ulbId);
            // }

            $list = $list->orderBy("maintenance_schedules.service_date","DESC")
                    ->orderBy("vehicle_masters.id");
            
            if($request->all==true)
            {
                $list = $list->get();
            }
            else{

                $list=paginater($list,$request) ;
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
            $maintenanceSchedule = $this->_MODEL_MAINTENANCE_SCHEDULE->find($request->id);
            if(!$maintenanceSchedule)
            {
                throw new Exception("Data Not Found");
            }

            $vehicleDtl = $maintenanceSchedule->vehicleMaster()->get();
            $data["maintenanceSchedule"] =$maintenanceSchedule;
            $data["vehicle"] = $vehicleDtl;
            return responseMsgs(true,"Maintenance Schedule Detail",$data,$apiId, $version, $queryRunTime,$action,$deviceId,true); 
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),$request->all(),$apiId, $version, $queryRunTime,$action,$deviceId);
        }

    }


    #test document upload server
    public function upload(Request $request)
    {
        try{
            // return($request->all());
            $extention = $request->file->getClientOriginalExtension();
            $imageName = $request->newName;
            $request->file;
            $path =   $request->targetPath? $request->targetPath:"Uploads/Test/";
            $drive = $request->drive?$request->drive:"D";
            
            $currentDirectory = getcwd();

            // Extract the drive name from the current directory path
            $driveName = substr($currentDirectory, 0, 1);            

            $full_path = $path;
            // if($drive!=$driveName)
            {
                $path = $drive.":/RMCDMCDOC/".$path;
            }
            $request->file->move($path, $imageName);
            return ["status"=>true,"file_name"=>$imageName,"path"=>$full_path,"full_path"=>$path.$imageName,"drive"=>$drive];
        }
        catch(Exception $e)
        {
            return ["status"=>false,"file_name"=>null,"path"=>null,"full_path"=>null,"error"=>$e->getMessage()];
        }
    }

    public function gioTaging(Request $request)
    {
        try{
            $drive = $request->drive?$request->drive:"C";
            $hoarding_image_temp_path = $request->hoarding_image_temp_path;
            $destinationFilePath = $request->destinationFilePath;
            $form = $drive.":/RMCDMCDOC/".$hoarding_image_temp_path;
            $to = $drive.":/RMCDMCDOC/".$destinationFilePath;
            if(!rename($form, $to))
            {
                throw new Exception("");
            }
            return ["status"=>true];
        }
        catch(Exception $e)
        {
            return ["status"=>false,"error"=>$e->getMessage()];
        }
    }

    public function deleteFile(Request $request)
    {
        try{
            $path =   $request->targetPath? $request->targetPath:"Uploads/Test/";
            $drive = $request->drive;
            $currentDirectory = getcwd();

            // Extract the drive name from the current directory path
            $driveName = substr($currentDirectory, 0, 1);            

            $full_path = $path;
            // if($drive!=$driveName)
            {
                $full_path = $drive.":/RMCDMCDOC/".$path;
            }
            @unlink($full_path);
            return true;

        }
        catch(Exception $e)
        {
            return false;
        }
    }

    public function fileRead(Request $request)
    {
        try{
            // return($request->all());            
            $path =   $request->targetPath? $request->targetPath:"Uploads/RMCDMC/";
            $drive = $request->drive;
            $currentDirectory = getcwd();

            // Extract the drive name from the current directory path
            $driveName = substr($currentDirectory, 0, 1);            

            $full_path = $_SERVER['DOCUMENT_ROOT']."/".$path;
            // if($drive!=$driveName)
            {
                $full_path = $drive.":/RMCDMCDOC/".$path;
            }

        
            if(!file_exists($full_path))
            {
                return;
            }
            return (file_get_contents($full_path));
        }
        catch(Exception $e)
        {
            return ["status"=>false,"file_name"=>null,"path"=>null,"full_path"=>null,"error"=>$e->getMessage()];
        }
    }
    public function mimType(Request $request)
    {
        try{
            // return($request->all());            
            $path =   $request->targetPath? $request->targetPath:"Uploads/Test/";
            $drive = $request->drive;
            $currentDirectory = getcwd();

            // Extract the drive name from the current directory path
            $driveName = substr($currentDirectory, 0, 1);            

            $full_path = $_SERVER['DOCUMENT_ROOT']."/".$path;
            // if($drive!=$driveName)
            {
                $full_path = $drive.":/RMCDMCDOC/".$path;
            }

        
            if(!file_exists($full_path))
            {
                return;
            }
            $getInfo = getimagesize($full_path);
            if(file_exists($full_path) && !isset($getInfo['mime']))
            {
                $getInfo['mime']='application/pdf';
            }
            $getInfo["size"] = filesize($full_path);
            return $getInfo;
            
        }
        catch(Exception $e)
        {
            return ["status"=>false,"file_name"=>null,"path"=>null,"full_path"=>null,"error"=>$e->getMessage()];
        }
    }

    public function test()
    {
        return view("welcome");
    }

    #egove-backend doc server
    public function checkDoc(Request $request)
    {
        try {
            // $contentType = (collect(($request->headers->all())['content-type'] ?? "")->first());
            $file = $request->document;
            $filePath = $file->getPathname();
            $hashedFile = hash_file('sha256', $filePath);
            $filename = ($request->document)->getClientOriginalName();
            $api = "http://192.168.0.122:8001/backend/document/upload";
            $transfer = [
                "file" => $request->document,
                "tags" => "good",
                // "reference" => 425
            ];
            $returnData = Http::withHeaders([
                "x-digest"      => "$hashedFile",
                "token"         => "8Ufn6Jio6Obv9V7VXeP7gbzHSyRJcKluQOGorAD58qA1IQKYE0",
                "folderPathId"  => 1
            ])->attach([
                [
                    'file',
                    file_get_contents($request->file('document')->getRealPath()),
                    $filename
                ]
            ])->post("$api", $transfer);
            if ($returnData->successful()) 
            {
                return(json_decode($returnData->body(),true));
            } 
            throw new Exception((json_decode($returnData->body(),true))["message"]??"");
        } catch (Exception $e) {
            return responseMsg(false,$e->getMessage(),"");
        }
    }


    public function severalDoc(Request $request)
    { 
        if(!$request->metaData)
        {
            $request->merge(["metaData"=>["vsm1.1",1.1,null,$request->getMethod(),null,]]);
        }
        $metaData= collect($request->metaData)->all();
        list($apiId, $version, $queryRunTime,$action,$deviceId)=$metaData; 
        try{
            $response = ($this->MultipartHandel($request));
            return responseMsgs(true,"",$response,$apiId, $version, $queryRunTime,$action,$deviceId);
        }
        catch(Exception $e)
        {
            return responseMsgs(false,$e->getMessage(),"",$apiId, $version, $queryRunTime,$action,$deviceId);
        }
    }
    public function MultipartHandel(Request $request)
    {
        $data = [];
        $header = apache_request_headers();
        $header = collect($header)->merge(
                                        ["token"         => "8Ufn6Jio6Obv9V7VXeP7gbzHSyRJcKluQOGorAD58qA1IQKYE0",
                                        "folderPathId"  => 1
                                    ]);
        $dotIndexes = $this->generateDotIndexes($_FILES);
        $url = "http://192.168.0.122:8001/backend/document/upload";
        foreach ($dotIndexes as $val) 
        {
            
            $patern = "/\.name/i";
            if (!preg_match($patern, $val)) {
                continue;
            }
            $file = $this->getArrayValueByDotNotation($request->file(), preg_replace($patern, "", $val));   
            
            $filePath = $file->getPathname();
            $hashedFile = hash_file('sha256', $filePath);
            $filename = $file->getClientOriginalName();
            $header = collect($header)->merge(
                ["x-digest"      => "$hashedFile"]);
            $postData = [
                "file" => $file,
                "tags" => "good",
                // "reference" => 425
            ];
            $response = Http::withHeaders(
                // $header->toArray()
                [
                    "x-digest"      => "$hashedFile",
                    "token"         => "8Ufn6Jio6Obv9V7VXeP7gbzHSyRJcKluQOGorAD58qA1IQKYE0",
                    "folderPathId"  => 1
                ]
                
            );
            $response->attach([
                [
                    'file',
                    file_get_contents($filePath),
                    $filename
                ]]

            );
            $response = $response->post("$url",$postData);
            if ($response->successful()) 
            {
                $response = (json_decode($response->body(),true));
            }
            else{
                $response = [false,json_decode($response->body(),true),""];
            }
            $keys = explode('.', $val);
            $currentLevel = &$data;
            foreach ($keys as $index=>$key) { 
                $patern = "/name/i";
                if (preg_match($patern, $key)) {
                    continue;
                }
                if (!isset($currentLevel[$key])) {
                    $currentLevel[$key] = [];
                }
                $currentLevel = &$currentLevel[$key]; 
            }
            $currentLevel = $response ;
        }
        return $data;
          
    }

    public function getArrayValueByDotNotation(array $array, string $key)
    {
        $keys = explode('.', $key);

        foreach ($keys as $key) {
            if (isset($array[$key])) {
                $array = $array[$key];
            } else {
                return null; // Key doesn't exist in the array
            }
        }

        return $array;
    }

    public function generateDotIndexes(array $array, $prefix = '', $result = [])
    {

        foreach ($array as $key => $value) {
            $newKey = $prefix . $key;
            if (is_array($value)) {
                $result = $this->generateDotIndexes($value, $newKey . '.', $result);
            } else {
                $result[] = $newKey;
            }
        }
        return $result;
    }

}
