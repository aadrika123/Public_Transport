<?php

namespace App\Models\Vehicle;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Stopage extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function insert(array $data)
    {
        $reqs = [
            'stopage_name'              => isset($data["stopageName"])?Str::ucfirst($data["stopageName"]):null,
            'location_id'              => $data["locationId"]??null,
        ];
        return Stopage::create($reqs)->id;   
    }

    public function edit(array $data)
    {
        $requestData = self::find($data["id"]);
        $reqs = [
            'stopage_name'              => isset($data["stopageName"])?Str::ucfirst($data["stopageName"]):null,
            'location_id'              => $data["locationId"]??null,
        ];
        if(isset($data['status']))
        {
            $reqs["status"]= $data['status']?1:0;
        }
        return $requestData->update($reqs);
    }

    public function routeStopage()
    {
        return $this->hasMany(RouteStoppage::class,"stopage_id","id")->where("status",1);
    }

    public function routes()
    {
        return $this->hasManyThrough(Route::class,RouteStoppage::class,"stopage_id","id","id","route_id")
            ->where("route_stoppages.status",1)
            ->where("routes.status",1);
    }
    public function location()
    {
        return $this->belongsTo(Location::class,"location_id","id","id");
    }
}
