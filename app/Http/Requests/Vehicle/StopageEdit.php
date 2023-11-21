<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;

class StopageEdit extends StopageAdd
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
        $name_regex   = $this->_REX_OWNER_NAME;
        $license_regex = $this->_REX_ALPHA_NUM_OPS_DOT_MIN_COM_AND_SPACE_SL;

        $rules = parent::rules();
        $rules["id"] = "required|digits_between:1,9223372036854775807"; 
        return $rules;
    }
}
