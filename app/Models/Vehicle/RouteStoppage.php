<?php

namespace App\Models\Vehicle;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RouteStoppage extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function insert(array $data)
    {        
        $reqs = [
                'route_id'       => $data["routId"],
                'stopage_id'    => $data["stopageId"]??null,
        ];
        return RouteStoppage::create($reqs)->id;
    }

    public function edit(array $data)
    {
        $requestData = self::find($data["id"]);
        $reqs = [
                'route_id'       => $data["routId"],
                'stopage_id'    => $data["StopageId"]??null,
        ];
        if(isset($data['status']))
        {
            $reqs["status"]= $data['status']?1:0;
        }
        return $requestData->update($reqs);
    }
}
