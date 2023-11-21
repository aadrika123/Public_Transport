<?php

namespace App\Http\Requests\Vehicle;

class DtlById extends Vehicle
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
        return [
            "id"=>"required|digits_between:1,9223372036854775807",
        ];
    }
}
