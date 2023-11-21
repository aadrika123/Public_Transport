<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;

class RouteAdd extends  Vehicle
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
        $reule = [
            "routeName"         => "required|regex:$license_regex",            
            "startStopageId"    => "required|digits_between:1,9223372036854775807",
            "endStopageId"      => "required|digits_between:1,9223372036854775807",
            "isCircular"        => "required|bool",
            "distance"          => "required|numeric|min:0|max:999999",
            "travelTime"        => "required|numeric|min:0|max:9999999",
            "sunday"            => "required|bool",
            "monday"            => "required|bool",
            "tuesday"           => "required|bool",
            "wednesday"         => "required|bool",
            "thursday"          => "required|bool",
            "friday"            => "required|bool",
            "saturday"          => "required|bool",
            "status"            => "nullable|bool",
        ];
        if($this->isCircular)
        {
            $reule["endStopageId"] = "required|same:startStopageId|digits_between:1,9223372036854775807";
        }
        else{
            $reule["endStopageId"] = "required|digits_between:1,9223372036854775807".($this->startStopageId?"|notIn:".$this->startStopageId:"");
        }
        $metaRule = parent::rules();
        $reule = (array_merge($reule,$metaRule));
        return $reule;
    }
}
