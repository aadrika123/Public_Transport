<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;

class TickeVerify extends Vehicle
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
        $metaRule = parent::rules();
        $rules = [
            "TicketId"          => "required|digits_between:1,9223372036854775807",
        ];
        $rules = (array_merge($rules,$metaRule)); 
        return $rules;
    }
}
