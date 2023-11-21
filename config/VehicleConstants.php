<?php

/**
 * created By- Sandeep Bara 
 * Date - 17/06/2023
 */

 return [
    "BACKEND_URL"   => env("BACKEND_URL","APP_URL"),
    "API_KEY"       => env("API_KEY",""),
    "RAZORPAY_URL"=>env("RAZORPAY_HTTP"),
    "RAZORPAY_ODERID_URL"=>env("RAZORPAY_HTTP").env("RAZORPAY_ORDER_ID"),
    "CITIZEN"       => "Citizen",
    "MODULE_ID"     => "12",
    "PARAM_ID"       =>1,
    "VEHICLE_RELATIVE_PATH" => "Uploads/Vehicle/Vehicle_doc",
    "VEHICLE_TYPE_RELATIVE_PATH" => "Uploads/Vehicle/Vehicle_type_doc",
    "DRIVER_RELATIVE_PATH" => "Uploads/Vehicle/Driver_doc",
    "INSURANCE_RELATIVE_PATH" => "Uploads/Vehicle/Insurance_doc",
    "AGENT_RELATIVE_PATH" => "Uploads/Vehicle/Agent_doc",
 ];
