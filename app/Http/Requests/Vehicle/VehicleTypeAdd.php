<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;

class VehicleTypeAdd extends  Vehicle
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

        $rules = [
            "type" => "required|unique:vehicle_type_masters,vehicle_type|regex:$license_regex",
            "image"  =>  "nullable|mimes:jpeg,png,jpg|max:2048",#2 mb
        ];
        $metaRule = parent::rules();
        $rules = (array_merge($rules,$metaRule));
        return $rules;
    }
}
