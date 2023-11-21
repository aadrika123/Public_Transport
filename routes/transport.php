
<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\DashBoard;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\InsuranceCompanyController;
use App\Http\Controllers\InsuranceRegistrationController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MaintenanceScheduleController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\StopageController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TransitBusControllers;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehicleTypeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * | Created On-19-06-2023 
 * | Created For-The Routes defined for the public-transport Module
 * | Created By-SandeepBara  
 */

 Route::group(['middleware' => ["auth_maker",'apikey']],function () {
    Route::controller(AgentController::class)->group(function(){
        Route::post('agent/login', 'loginAuth');
        Route::post('agent/logout', 'logout')->middleware("auth:sanctum");
    });
});
Route::group(['middleware' => ["auth_maker",'apikey']],function () {
    Route::controller(DashBoard::class)->group(function(){
        Route::post("dashboard","vehicelDashBoard");
        Route::post("dashboard/fleets","fleetsDashBoard");
        Route::post("dashboard/vehicle-running","runningVehicleDashBoard");
        Route::post("dashboard/maintaind-vehicle","vehicleInMentanance");
    });

    Route::controller(VehicleController::class)->group(function(){
        Route::post("vehicle/mdm-data","getApplyData");
        Route::post("vehicle/add","store");
        Route::post("vehicle/edit-data","getEditData");
        Route::post("vehicle/edit","edit");
        Route::post("vehicle/list","list");
        Route::post("vehicle/dtl","dtlById");
    });

    Route::controller(VehicleTypeController::class)->group(function(){
        Route::post("vehicle-type/add","store");
        Route::post("vehicle-type/edit","edit");
        Route::post("vehicle-type/list","list");
        Route::post("vehicle-type/dtl","dtlById");
    });

    Route::controller(DriverController::class)->group(function(){
        Route::post("driver/add","store");
        Route::post("driver/edit","edit");
        Route::post("driver/list","list");
        Route::post("driver/dtl","dtlById");
        Route::post("driver/validate-driving-license","validateLiceseNo");
    });

    Route::controller(StopageController::class)->group(function(){
        Route::post("stopage/add","store");
        Route::post("stopage/edit","edit");
        Route::post("stopage/list","list");
        Route::post("stopage/dtl","dtlById");
    });

    Route::controller(TransitBusControllers::class)->group(function(){
        Route::post("transit-bus/mdm-data","getApplyData");
        Route::post("transit-bus/add","store");
        Route::post("transit-bus/edit-data","getEditData");
        Route::post("transit-bus/edit","edit");
        Route::post("transit-bus/list","list");
        Route::post("transit-bus/dtl","dtlById");
    });

    Route::controller(LocationController::class)->group(function(){
        Route::post("location/mdm-data","getApplyData");
        Route::post("location/add","store");
        Route::post("location/edit","edit");
        Route::post("location/list","list");
        Route::post("location/dtl","dtlById");
    });

    Route::controller(MaintenanceScheduleController::class)->group(function(){
        Route::post("maintenance-schedule/mdm-data","getApplyData");
        Route::post("maintenance-schedule/add","store");
        Route::post("maintenance-schedule/edit-data","getEditData");
        Route::post("maintenance-schedule/edit","edit");
        Route::post("maintenance-schedule/list","list");
        Route::post("maintenance-schedule/dtl","dtlById");

        Route::post("maintenance-schedule/Write","upload")->withoutMiddleware(["apikey"]);;
        Route::post("maintenance-schedule/delete","deleteFile")->withoutMiddleware(["apikey"]);;
        Route::post("maintenance-schedule/rename","gioTaging")->withoutMiddleware(["apikey"]);;
        Route::post("maintenance-schedule/docServer","checkDoc")->withoutMiddleware(["apikey"]);;
        Route::post("maintenance-schedule/docServer-multy-doc","severalDoc")->withoutMiddleware(["apikey"]);;
        Route::post("maintenance-schedule/read","fileRead")->withoutMiddleware(["apikey"]);
        Route::post("maintenance-schedule/mim-type","mimType")->withoutMiddleware(["apikey"]);
    });

    Route::controller(InsuranceCompanyController::class)->group(function(){
        Route::post("insurance-company/add","store");
        Route::post("insurance-company/edit","edit");
        Route::post("insurance-company/list","list");
        Route::post("insurance-company/dtl","dtlById");
    });

    Route::controller(InsuranceRegistrationController::class)->group(function(){
        Route::post("insurance-registration/mdm-data","getApplyData");
        Route::post("insurance-registration/add","store");
        Route::post("insurance-registration/edit-data","getEditData");
        Route::post("insurance-registration/edit","edit");
        Route::post("insurance-registration/list","list");
        Route::post("insurance-registration/dtl","dtlById");
    });

    Route::controller(RouteController::class)->group(function(){
        Route::post("route/mdm-data","getApplyData");
        Route::post("route/add","store");
        Route::post("route/edit-data","getEditData");
        Route::post("route/edit","edit");
        Route::post("route/list","list");
        Route::post("route/dtl","dtlById");
    });

    Route::controller(AgentController::class)->group(function(){        
        Route::post("agent/add","store");
        Route::post("agent/edit","edit");
        Route::post("agent/list","list");
        Route::post("agent/dtl","dtlById");
    });

    Route::controller(TicketController::class)->group(function(){
        Route::post('ticket/pay-razorpay-charge', 'handeRazorPay');
        Route::post("ticket/pay-razorpay-response","razorPayResponse");
        Route::post("ticket/conform-razorpay-tran","conformRazorPayTran");
        Route::post("ticket/edit","edit");
        Route::post("ticket/list","list");
        Route::post("ticket/user-tickets","userTickets");
        Route::post("ticket/dtl","dtlById");
        Route::get("ticket/verify/{id}","ticketVerify")->middleware('auth:sanctum');;
    });

});