<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;

class VehicleTypeEdit extends  Vehicle
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
        $rules["id"] = "required|digits_between:1,9223372036854775807"; 
        $rules["type"] = "required|unique:vehicle_type_masters,vehicle_type".($this->id?(",".$this->id):"")."|regex:$license_regex";
        $rules["stauts"]    = "nullable|bool";
        return $rules;
    }
}
