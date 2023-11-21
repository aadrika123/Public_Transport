<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;

class TransitBusMasterAdd extends Vehicle
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
        
        $rules["vehicleId"] = "required|digits_between:1,9223372036854775807";
        $rules["routeId"] = "required|digits_between:1,9223372036854775807";
        $rules["startTime"] = "required|date_format:H:i";
        $rules["endTime"] = "required|after_or_equal:start_date|date_format:H:i";
        $metaRule = parent::rules();
        $rules = (array_merge($rules,$metaRule));
        return $rules;
    }
}
