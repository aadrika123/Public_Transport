<?php

namespace App\MicroServices\Vehicle;

use App\Models\Vehicle\IdGenerationParam;

class PrefixIdGenerator
{
    protected $prefix;
    protected $paramId;
    protected $incrementStatus;

    public function __construct(int $paramId)
    {
        $this->paramId = $paramId;
        $this->incrementStatus = true;
    }

    /**
     * | Id Generation Business Logic 
     */
    public function generate(): string
    {
        $paramId = $this->paramId;
        $mIdGenerationParams = new IdGenerationParam();

        $params = $mIdGenerationParams->getParams($paramId);
        $prefixString = $params->string_val;
        $intVal = $params->int_val;
        $placeHolder = rand(500,900);
        // Case for the Increamental
        if ($this->incrementStatus == true) {
            $id =  str_pad(("/".$intVal), 8, $placeHolder, STR_PAD_LEFT);
            $intVal += 1;
            $params->int_val = $intVal;
            $params->save();
        }

        // Case for not Increamental        
        if ($this->incrementStatus == false) {
            $id =  str_pad($intVal, 8, $placeHolder, STR_PAD_LEFT);
        }

        return trim($prefixString . '/' . $id,"/") ;
    }
}
