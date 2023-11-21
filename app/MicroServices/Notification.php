<?php

namespace App\MicroServices;

use App\MicroServices\OtpGenrator;
use App\Models\MobileOtp;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class Notification 
{
    /**
     created by Sandeep Bara
     created At 07-08-2023
     status open
     */

    /**
     * The column value of the mobile_no.
     *
     * @var string
     */
    protected $mobileNo;

    /**
     * the column value of email
     * @var string 
     */
    protected $email;

    /**
     * the column value of otp
     * @var string
     */
    protected $otp;

    /**
     * the otp validity priade on minuts
     * @var int
     */
    protected $validity;

    /**
     * error message
     * @var string
     */
    protected $message;

    /**
     * otp genration charecter set 
     * @var string 
     */
    protected $charactersSet;

    /**
     * initialize the charectersSet
     * @param (string $charactersSet)
     */

    public function __construct(string $charactersSet="")
    {
        $this->charactersSet = $charactersSet;
    }

    public function getMessage()
    {
        return $this->message;
    }
    public function getMobileNo()
    {
        return $this->mobileNo;
    }
    public function getOtp()
    {
        return $this->otp;
    }

    protected function sendOtpThowMobile()
    {
        $this->mobileNo;$this->otp;
    }

    protected function sendOtpThowEmail()
    {

        #
    }

    protected function sendOtp():void
    {
        if($this->mobileNo)
        {
            $this->sendOtpThowMobile();
        }
        if($this->email)
        {
            $this->sendOtpThowEmail();
        }
    }

    /**
     * sends and stor the otp for mobile
     * @var mobileNo valid mobile no
     * @var validity in minuts
     * @var checkPoint
     */
    public function sendOtpOnMobile($mobilNo, int $validity ,string ...$checkPoint):string
    {
        
        try {
            if(strlen($mobilNo)< 10 || !preg_match('/^([1-9])([0-9]){9}$/i', $mobilNo))
                return"invalid Mobile No."; 
            $this->mobileNo =  $mobilNo;  
            $this->validity = $validity;        
            $checkers = [...$checkPoint];                  
            $this->otp = OtpGenrator::otp(4,$this->charactersSet);
            $input["otp"]       = $this->otp;
            $input["mobile_no"] = $mobilNo;
            $input["check_points"]=json_encode($checkers);  
            $input["created_at"]= Carbon::now()->format("Y-m-d H:i:s");
            $input["expired_at"] =  Carbon::parse($input["created_at"])->addMinutes($this->validity)->format("Y-m-d H:i:s");  
            
            DB::beginTransaction();
            (new MobileOtp())->insert($input);
            $this->sendOTP();
            DB::commit();
            return $this->otp ; 
        } 
        catch (Exception $e) 
        {
            DB::rollBack();
            dd($e->getMessage(),$e->getFile(),$e->getLine());
            return responseMsgs(false, $e->getMessage(), "", "0101", "01", ".ms", "POST", "");
        }
    }

    /**
     * | Verify OTP 
     * @var mobileNo valid mobile no
     * @var validity in minuts
     * @var otp string 
     * @var checkPoint
     * @return bool
     */
    public function verifyMobileOtp($mobilNo, int $validity,$otp,string ...$checkPoint):bool
    {  
        try {
            $this->mobileNo =  $mobilNo;  
            $this->validity = $validity;        
            $checkers = [...$checkPoint];
            $otps = (new MobileOtp())->getMobileOtp($this->mobileNo,$checkers,$otp);
            if(!$otps)
            {
                $this->message="OTP not match!";
                return false;
            }
            if($otps->status!=2)
            {
                $this->message="OTP wase already used";
                return false;
            }
            if($otps->expired_at < Carbon::now()->addMinute($this->validity))
            {
                $this->message="OTP wase Expired";
                return false;
            }
            DB::beginTransaction();
            // $otps->delete();
            DB::commit();
            return true;
        } 
        catch (Exception $e) 
        {
            DB::rollBack();
            return responseMsgs(false, $e->getMessage(), "", "", "01", ".ms", "POST", "");
        }
    }
}
