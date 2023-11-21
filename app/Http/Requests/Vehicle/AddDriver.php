<?php

namespace App\Http\Requests\Vehicle;

class AddDriver extends Vehicle
{
    
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $mobile_regex = $this->_REX_MOBILE_NO;
        $name_regex   = $this->_REX_OWNER_NAME;
        $license_regex = $this->_REX_ALPHA_NUM_OPS_DOT_MIN_COM_AND_SPACE_SL;
        $current_date = $this->_CURRENT_DATE;
        $date_regex   = $this->_REX_DATE_YYYY_MM_DD;

        $rules =[
            "name"      =>  "required|regex:$name_regex",
            "mobileNo"  =>  "required|digits:10|regex:$mobile_regex",
            "email"     =>  "nullable|email",
            "address"     =>  "required|regex:$license_regex",
            "drivingLicenseNo"      =>  "required|unique:driver_masters,driving_license_no|regex:$license_regex",
            "licenseExpiryDate"      =>  "required|date|date_format:Y-m-d|after_or_equal:$current_date",
            "joiningDate"      =>  "nullable|date|date_format:Y-m-d|before_or_equal:$current_date",
            "image"      =>  "required|mimes:jpeg,png,jpg|max:2048",#2 mb
            "workingStatus" => "nullable|int|in:0,1,2,3",
            "status"    => "nullable|bool",
            "ulbId"       =>"required|digits_between:1,9223372036854775807",
        ];
        
        $metaRule = parent::rules();
        $rules = (array_merge($rules,$metaRule));
        return $rules;
    }
}
