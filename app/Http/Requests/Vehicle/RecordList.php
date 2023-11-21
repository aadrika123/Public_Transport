<?php

namespace App\Http\Requests\Vehicle;

class RecordList extends Vehicle
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

        $rules = [
            "fromDate"  => "nullable|date|date_format:Y-m-d",
            "uptoDate"  => "nullable|date|date_format:Y-m-d",
            "key"       => "nullable|regex:/^[^<>{};:.,~!?@#$%^=&*\"]*$/i",
            "wardId"    => "nullable|digits_between:1,9223372036854775807",
            "userId"    => "nullable|digits_between:1,9223372036854775807",
            "page"      => "nullable|digits_between:1,9223372036854775807",
            "perPage"   => "nullable|digits_between:1,9223372036854775807",
        ];
        return $rules;
    }
}
