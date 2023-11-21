<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;

class MaintainanceScheduleAdd extends Vehicle
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
        $current_date = $this->_CURRENT_DATE;
        $rules = [
            "vehicleId"         => "required|digits_between:1,9223372036854775807",
            "serviceType"       => "required|regex:$license_regex",
            "serviceProvider"   => "required|regex:$license_regex",
            "nextDueDate"       => "required|date|date_format:Y-m-d|after_or_equal:$current_date",
            "cost"              => "required|numeric|min:0",
            "notes"             => "required|regex:$license_regex",
        ];
        $metaRule = parent::rules();
        $rules = (array_merge($rules,$metaRule));
        return $rules;
    }
}
