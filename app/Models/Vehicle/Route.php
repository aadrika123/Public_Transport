<?php

namespace App\Models\Vehicle;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Route extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function insert(array $data)
    {
        $reqs = [
                'route_name'       => Str::title($data["routeName"]),
                'start_stopage_id' => $data["startStopageId"]??null,
                'end_stopage_id'   => $data["endStopageId"]??null,
                'is_circular'      => $data["isCircular"]??false,
                'distance'         => $data["distance"]??null,
                'travel_time'      => $data["travelTime"]??null,
                'sunday'           => $data["sunday"]??false,
                'monday'           => $data["monday"]??false,            
                'tuesday'          => $data["tuesday"]??false,
                'wednesday'        => $data["wednesday"]??false,
                'thursday'         => $data["thursday"]??false,            
                'friday'           => $data["friday"]??false,
                'saturday'         => $data["saturday"]??false,
        ];
        return Route::create($reqs)->id;   
    }

    public function edit(array $data)
    {
        $requestData = self::find($data["id"]);
        $reqs = [
                'route_name'       => Str::title($data["routeName"]),
                'start_stopage_id' => $data["startStopageId"]??null,
                'end_stopage_id'   => $data["endStopageId"]??null,
                'is_circular'      => $data["isCircular"]??false,
                'distance'         => $data["distance"]??null,
                'travel_time'      => $data["travelTime"]??null,
                'sunday'           => $data["sunday"]??false,
                'monday'           => $data["monday"]??false,            
                'tuesday'          => $data["tuesday"]??false,
                'wednesday'        => $data["wednesday"]??false,
                'thursday'         => $data["thursday"]??false,            
                'friday'           => $data["friday"]??false,
                'saturday'         => $data["saturday"]??false,
        ];
        if(isset($data['status']))
        {
            $reqs["status"]= $data['status']?1:0;
        }
        return $requestData->update($reqs);
    }

    public function dtlById($id)
    {
        $data = self::select("routes.*",
                        DB::raw("starting.stopage_name as strating_stopage, 
                        ending.stopage_name as ending_stopage")
                )
                ->join("stopages as starting","starting.id","routes.start_stopage_id")
                ->leftjoin("stopages as ending","ending.id","routes.end_stopage_id")
                ->first();
        return $data;
    }

    public function stopages()
    {
        return $this->hasManyThrough(Stopage::class,RouteStoppage::class,"route_id","id","id","stopage_id")
        ->where("route_stoppages.status",1)
        ->where("stopages.status",1);
    }

    public function statrtingStopage()
    {
        return $this->hasOne(Stopage::class,"id","start_stopage_id");
    }

    public function endingStopage()
    {
        return $this->hasOne(Stopage::class,"id","end_stopage_id");
    }


}
