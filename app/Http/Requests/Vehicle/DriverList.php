<?php

namespace App\Http\Requests\Vehicle;

class DriverList extends RecordList
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
        return $rules;
    }
}
