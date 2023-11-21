<?php

namespace App\Models\Vehicle;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class Agent extends  Model
{
    use HasFactory;
    use HasApiTokens, HasFactory, Notifiable;
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function changeToken($request)
    {
        $citizenInfo = self::where('mobile', $request->mobileNo)
            ->first();

        if (isset($citizenInfo)) {
            $token['token'] = $citizenInfo->createToken('my-app-token')->plainTextToken;
            $citizenInfo->remember_token = $token['token'];
            $citizenInfo->save();
            return $token;
        }
    }

    public function insert(array $data)
    {   
        $name = Str::upper((substr($data["agentName"], 0, 3)));   
        $reqs = [
            'agent_name'       => isset($data["agentName"])?Str::title($data["agentName"]):null,
            'agent_mobile_no'  => $data["mobileNo"]??null,            
            'agent_image'      => $data["imagePath"]??null,
            'ulb_id'           => $data["ulbId"]??null,
            "password"         => isset($data["Password"]) && !empty(trim($data["Password"]))? Hash::make($data["Password"]): (Hash::make($name . '@' . substr($data["mobileNo"], 7, 3))),
        ];
        return Agent::create($reqs)->id;   
    }

    public function edit(array $data)
    {
        $requestData = self::find($data["id"]);
        $name = Str::upper((substr($data["agentName"], 0, 3)));   
        $reqs = [
            'agent_name'       => isset($data["agentName"])?Str::title($data["agentName"]):null,
            'agent_mobile_no'  => $data["mobileNo"]??null, 
            'ulb_id'           => $data["ulbId"]??null,
            "password"         => isset($data["Password"]) && !empty(trim($data["Password"]))? Hash::make($data["Password"]): ($requestData->password ? $requestData->password : Hash::make($name . '@' . substr($data["mobileNo"], 7, 3))),
        ];
        if(isset($data['status']))
        {
            $reqs["status"]= $data['status']?1:0;
        }
        if(isset($data['imagePath']))
        {
            $reqs["agent_image"]= $data['imagePath'];
        }
        return $requestData->update($reqs);
    }


    public function getUserByMobile($mobileNo)
    {
        return self::where('agent_mobile_no', $mobileNo)
            ->first();
    }
}
