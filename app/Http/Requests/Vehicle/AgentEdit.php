<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class AgentEdit extends  AgentAdd
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
        $mobile_regex = $this->_REX_MOBILE_NO;
        $rules["id"] = "required|digits_between:1,9223372036854775807";
        $rules["mobileNo"]    = "required|digits:10|regex:$mobile_regex|unique:agents,agent_mobile_no".($this->id?(",".$this->id):"");
        $rules["image"]  =    "nullable|mimes:jpeg,png,jpg|max:2048";#2 mb 
        return $rules;
    }
}
