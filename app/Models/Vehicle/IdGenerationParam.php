<?php

namespace App\Models\Vehicle;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdGenerationParam extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function getParams($id)
    {
        return IdGenerationParam::find($id);
    }
}
