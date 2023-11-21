<?php

namespace App\Http\Requests\Vehicle;

class VehicleList extends RecordList
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
        $rules = parent::rules();
        $rules["maintenanceStatus"]="nullable|bool";
        return $rules;
    }
}
