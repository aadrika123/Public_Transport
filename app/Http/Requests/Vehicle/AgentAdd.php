<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AgentAdd extends  Vehicle
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
        $license_regex = $this->_REX_ALPHA_SPACE;
        $mobile_regex = $this->_REX_MOBILE_NO;
        $rules = [
            "agentName"   => "required|regex:$license_regex",            
            "mobileNo"    => "required|digits:10|regex:$mobile_regex|unique:agents,agent_mobile_no",
            "image"       => "required|mimes:jpeg,png,jpg|max:2048",#2 mb            
            "status"      => "nullable|bool",
            "ulbId"       =>"required|digits_between:1,9223372036854775807", 
            "Password"    => [
                    'nullable',
                    'min:6',
                    'max:255',
                    'regex:/[a-z]/',      // must contain at least one lowercase letter
                    'regex:/[A-Z]/',      // must contain at least one uppercase letter
                    'regex:/[0-9]/',      // must contain at least one digit
                    'regex:/[@$!%*#?&]/'  // must contain a special character
                ]

        ];
        $metaRule = parent::rules();
        $rules = (array_merge($rules,$metaRule));       
        return  $rules;
    }
}
