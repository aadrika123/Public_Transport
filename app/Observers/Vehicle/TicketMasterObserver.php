<?php

namespace App\Observers\Vehicle;

use App\MicroServices\Vehicle\PrefixIdGenerator;
use App\Models\Vehicle\TicketMaster;
use Illuminate\Support\Facades\Config;

class TicketMasterObserver
{
    public function created(TicketMaster $obj)
    {
        $paramId = Config::get('VehicleConstants.PARAM_ID');
        if (is_null($obj->ticket_no)) {
            $idGeneration = new PrefixIdGenerator($paramId);
            $ticket_no = $idGeneration->generate();
            
            $obj->ticket_no = $ticket_no;
            $obj->save();
        }
    }
}
