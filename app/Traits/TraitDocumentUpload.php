<?php

namespace App\Traits;

trait TraitDocumentUpload
{
    public function upload($refImageName, $image, $relativePath)
    {
        $extention = $image->getClientOriginalExtension();
        $imageName = trim(time() . '-' . $refImageName,"-") . '.' . $extention;        
        $image->move($relativePath, $imageName);
        return trim(($relativePath."/".$imageName),"/");
    }

    public function deleteFile($relativePath)
    {
        $delete_path =  $relativePath;
        return @unlink($delete_path); 
    }
}