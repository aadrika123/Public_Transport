<?php

namespace App\MicroServices;

class OtpGenrator
{
    /**
     created by Sandeep Bara
     created on 08-07-2023
     status open
     */

    /**
     * @var charactersSet string set for genrate otp
     */
    
    // public static $charactersSet = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    public static $charactersSet = '0123456789';

    /**
     * otp genration
     * @var size lenght of otp
     * @var cahractersSet costome charecterset
     * @return string 
     */

    public static function Otp(int $size =6, string $charactersSet=""):string
    {
        self::$charactersSet = $charactersSet? $charactersSet : self::$charactersSet;
        $otp = "";
        for ($i = 0; $i < $size ; $i++) 
        {
            $index = rand(0, strlen(self::$charactersSet) - 1);
            $otp .= self::$charactersSet[$index];
        }
        return $otp;
    }

}
