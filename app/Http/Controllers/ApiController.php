<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{


    public function getAttendance($userID)
    {
    }

    public function getGroups()
    {
        return $this->getApiMessage(true, Group::all());
    }

    public function getUserByID($userID)
    {
        $user = User::with(['group', 'type', 'mainImage'])->find($userID);
        $user->main_image = url($user->mainImage->USIM_URL);
        if ($user)
            return $this->getApiMessage(true, $user);
        else
            return $this->getApiMessage(false);
    }

    public function getUserByFaceID(Request $request)
    {
        $validation = $this->validateRequest($request, [
            'faceID' => 'required'
        ]);
        if ($validation === true) {
            $user = User::with(['group', 'type', 'mainImage'])->where("USER_FACE_ID", $request->faceID)->first();
            $user->full_image_url = url($user->mainImage->USIM_URL);
            if ($user)
                return $this->getApiMessage(true, $user);
            else
                return $this->getApiMessage(false, ['error' => 'FaceID not found']);
        } 
    }

    public function getCurrentUser(Request $request)
    {
        $user = $request->user()->load('group', 'type', 'mainImage');
        $user->full_image_url = url($user->mainImage->USIM_URL);
        
        return $this->getApiMessage(true, $user);
    }

    public function login(Request $request)
    {
        if ($this->validateRequest($request, [
            "email" => "required|exists:app_users,USER_MAIL",
            "password" => "required",
            'deviceName' => 'required'
        ])) {
            $user = User::where('USER_MAIL', $request->email)->first();

            if (Hash::check($request->password, $user->USER_PASS)) {
                return $this->getApiMessage(true, ['token' => $user->createToken($request->deviceName)->plainTextToken]);
            } else {
                return $this->getApiMessage(false, ['errors' => ['password' => 'Incorrect password']]);
            }
        }
    }

    /**
     * validate request via passed rules
     * 
     * @param array $rules
     * @param Request $request
     */
    private function validateRequest($request, $rules)
    {
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $this->getApiMessage(false, ['errors' => $validator->errors()])->send();
            die;
        } else return true;
    }

    /**
     * 
     * echo generic json message
     * 
     * @param bool $status  failed or passed
     * @param mixed|null $returnObject object to return as json 
     */
    private function getApiMessage(bool $status, $returnObject = null)
    {
        return response(json_encode(new ApiMessage($status, $returnObject), JSON_UNESCAPED_UNICODE))->withHeaders(['Content-Type' => 'application/json']);
    }
}

class ApiMessage
{
    public $status;
    public $message;

    function __construct(bool $status, $message = null)
    {
        $this->status = $status;
        $this->message = $message;
    }
}
