<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;

class InsuranceRegistrationEdit extends InsuranceRegistrationAdd
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
        $current_date = $this->_CURRENT_DATE; 
        $rules = parent::rules();
        $rules["id"] = "required|digits_between:1,9223372036854775807";
        $rules["policyNo"] = "required|regex:$license_regex|unique:insurance_registrations,policy_no,".($this->id?$this->id:"NULL").",id".($this->insuranceCompanyId?(",insurance_company_id," . $this->insuranceCompanyId):"");
        $rules["policyExpirationDate"] = "required|date|date_format:Y-m-d";
        $rules["image"] = "nullable|mimes:pdf,jpeg,png,jpg|max:2048";#2 mb
        return $rules;
    }
}
