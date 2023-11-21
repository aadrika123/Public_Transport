<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;

class TicketAdd extends Vehicle
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
        $nume = $this->_REX_NUM_DOT;
        $rules = [
            "amount"          => "required|min:0|max:500|regex:$nume",
        ];
        $metaRule = parent::rules();
        $rules = (array_merge($rules,$metaRule)); 
        if($this->userType =="EMP")
        {
            $rules["citizenId"] = "required|digits_between:1,9223372036854775807";          
        }
        else
        {
            $rules["ulbId"] = "required|digits_between:1,9223372036854775807";
            $this->merge(["citizenId" => $this->logInId]);
        }      
        return $rules;
    }
}
