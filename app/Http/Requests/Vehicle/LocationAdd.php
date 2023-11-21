<?php

namespace App\Http\Requests\Vehicle;

use App\Http\Controllers\LocationController;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redis;

class LocationAdd extends Vehicle
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

        $ulbList = null;json_decode(Redis::get("PUBLIC-TRANSPORT-ULB-LIST"),true);
        if(!$ulbList)
        {
            $controller = App::makeWith(LocationController::class, []);        
            $data = $controller->getApplyData($this); 
            $ulbList = collect($data->original["data"]["ulbList"]??[])->toArray();
        }
        $ulbIds = implode(",",array_map(function($val)
                        {
                            return $val["id"];
                        },$ulbList));
        $rules =[
            "ulbId" =>"required|digits_between:1,9223372036854775807|in:$ulbIds",
            "locationName" =>"required|regex:$license_regex"
        ];
        $metaRule = parent::rules();
        $rules = (array_merge($rules,$metaRule));
        return $rules;
    }
}
