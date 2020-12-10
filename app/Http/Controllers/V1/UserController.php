<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\ConfirmSmsCode;
use App\Http\Requests\V1\RegisterRequest;
use App\Repository\UserRepository\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class UserController extends Controller

{
    protected $userRepository ;



    /**
     * UserController constructor.
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository ;
    }



    /**
     * @param RegisterRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request)
    {

         /*
         |--------------------------------------------------------------------------
         | register Function
         |--------------------------------------------------------------------------
         |
         | 0 - Validate Request
         | 1 - check phone number from userRepository
         | 2 - gnerate sms code
         | 3 - setSmsCode in database from userRepository
         |
         */


         $phoneNumber = $request->phonenumber;
         $checkPhoneNumber = $this->userRepository->checkPhoneNumber($phoneNumber);


         if ($checkPhoneNumber != true)
            $this->userRepository->createUser($phoneNumber);


        $smsCode = $this->genarateSmsCode();
        $setSmsCode = $this->userRepository->setSmsCode($smsCode , $phoneNumber);


        if($setSmsCode == true)
        {
            return response()->json([
                'data' => [
                    'smscode' => $smsCode
                ] ,
                'message' => 'success'
            ],200);
        }
        else{
            return response()->json([
                'message' => ' request fail '
            ],408);
        }
    }



    /**
     * @param ConfirmSmsCode $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirmSmsCode(ConfirmSmsCode $request)
    {
        /*
         |--------------------------------------------------------------------------
         | register Function
         |--------------------------------------------------------------------------
         |
         | 0 - Validate Request
         | 1 - check sms code
         | 2 - genarate token code
         | 3 - setTokenCode
         |
         */

        // todo : validate $request ;

        $phoneNumber = $request -> phonenumber;
        $smsCode = $request -> smscode;

        $checkSmsCode = $this->userRepository->checkSmsCode($smsCode , $phoneNumber);

        if($checkSmsCode)
        {
            $token = $this->genarateToken();
            $this->userRepository->setTokenCode($phoneNumber , $token);

            return response()->json([
                'data' => [
                    'token' => $token
                ] ,
                'message' => 'success'
            ],200);
        }else
            return response()->json([
                'message' => 'your information is invalid'
            ],422);

    }



    /**
     * @return int
     */
    public function genarateSmsCode()
    {
        // echo 'genarateSmsCode done ';
        return rand ( 1001 , 9999 );
    }



    /**
     * @return string
     */
    public function genarateToken()
    {
        return Str::random(500);
    }



    public function getUserInfo(Request $request)
    {
        $user_id =  $request->get('id');
        return response()->json([
            'data' => [
                'id' => $user_id
            ]
        ],200);
    }
}
