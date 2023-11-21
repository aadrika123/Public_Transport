<?php

namespace App\Http\Requests\Vehicle;

class VehicleEdit extends VehicleAdd
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
        $license_regex = $this->_REX_ALPHA_NUM_OPS_DOT_MIN_COM_AND_SPACE_SL;
        $rules = parent::rules();
        $rules["registrationNo"]  =  ($this->isLicensePlate?"required":"nullable").($this->registrationNo?"|unique:vehicle_masters,registration_no".($this->id?(",".$this->id):""):"")."|max:20|regex:$license_regex";         
        $rules["id"]="required|digits_between:1,9223372036854775807";
        $rules["status"]="nullable|bool";
        return $rules;
    }
}
