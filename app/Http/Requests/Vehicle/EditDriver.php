<?php

namespace App\Http\Requests\Vehicle;

class EditDriver extends AddDriver
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
        $rules = parent::rules();
        $rules["id"] =  "required|digits_between:1,9223372036854775807";        
        $rules["drivingLicenseNo"] =  "required|unique:driver_masters,driving_license_no".($this->id?(",".$this->id):"")."|regex:$license_regex";    
        $rules["image"]      =  "nullable|mimes:jpeg,png,jpg|max:2048";#2 mb        
        return $rules;
    }
}
