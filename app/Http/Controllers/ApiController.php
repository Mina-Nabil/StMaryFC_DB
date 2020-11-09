<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use App\Models\UserImage;
use App\Models\UserType;
use Exception;
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

    public function getUsertypes()
    {
        return $this->getApiMessage(true, UserType::all());
    }

    public function getUserByID($userID)
    {
        $user = User::with(['group', 'type', 'mainImage'])->find($userID);
        if ($user) {
            $user->full_image_url =  ($user->mainImage) ? asset('storage/' . $user->mainImage->USIM_URL) : '';
            return $this->getApiMessage(true, $user);
        } else
            return $this->getApiMessage(false);
    }

    public function getUsersByGroupID($groupID)
    {
        $users = User::with(['group', 'type', 'mainImage'])->where("USER_GRUP_ID", $groupID)->get();
        if ($users) {
            return $this->getApiMessage(true, $users);
        } else
            return $this->getApiMessage(false);
    }

    public function searchByName(Request $request)
    {
        $validation = $this->validateRequest($request, [
            "name"      => "required",
        ]);
        if ($validation === true) {
            $arguments = explode(" ", $request->name);
            $users = User::with(['group', 'type', 'mainImage'])->join("groups", "groups.id", '=', 'USER_GRUP_ID');
            foreach($arguments as $value){
                $users = $users->where([
                    ["GRUP_NAME",  "LIKE", "%" . $request->name . "%", 'and'],
                    ["USER_NAME", "LIKE", "%" . $request->name . "%", 'or']
                    ]);
            }
            $users = $users->get();
            if ($users) {
                return $this->getApiMessage(true, $users);
            } else
                return $this->getApiMessage(false);
        }
    }


    public function addGroup(Request $request)
    {
        $validation = $this->validateRequest($request, [
            "name"      => "required|unique:groups,GRUP_NAME",
        ]);
        if ($validation === true) {

            $group = new Group();
            $group->GRUP_NAME = $request->name;
            $group->save();
            if ($group)
                return $this->getApiMessage(true, $group);
            else
                return $this->getApiMessage(false, ['error' => 'Group Addition Failed']);
        }
    }

    public function addUser(Request $request)
    {
        $validation = $this->validateRequest($request, [
            "name" => "required|unique:app_users,USER_NAME",
            "type" => "required|exists:app_user_types,id",
            "group" => "required|exists:groups,id",
            "birthDate" => "nullable|date",
            "mail" => "required_if:type,1|nullable|email",
            "password" => "required_if:type,1|nullable",
        ]);
        if ($validation === true) {

            $user = new User();
            $user->USER_NAME = $request->name;
            $user->USER_USTP_ID = $request->type;
            $user->USER_BDAY = $request->birthDate;
            $user->USER_MAIL = $request->mail;
            $user->USER_PASS = bcrypt($request->password);
            $user->USER_GRUP_ID = $request->group;
            $user->USER_FACE_ID = bcrypt($user->USER_NAME);

            $user->save();
            if ($request->hasFile('photo')) {
                try {
                    $newImage = new UserImage();
                    $newImage->USIM_URL = $request->photo->store('images/users/' . $user->USER_NAME, 'public');
                    $newImage->USIM_USER_ID = $user->id;
                    $newImage->save();
                    $user->USER_MAIN_IMGE = $newImage->id;
                    $user->save();
                } catch (Exception $e) {
                }
            }

            if ($user)
                return $this->getApiMessage(true, $user->load(['group', 'type', 'mainImage']));
            else
                return $this->getApiMessage(false, ['error' => 'User Addition Failed']);
        }
    }

    public function getUserByFaceID(Request $request)
    {
        $validation = $this->validateRequest($request, [
            'faceID' => 'required|exists:app_users,USER_FACE_ID'
        ]);
        if ($validation === true) {
            $user = User::with(['group', 'type', 'mainImage'])->where("USER_FACE_ID", $request->faceID)->first();
            $user->full_image_url =  ($user->mainImage) ? asset('storage/' . $user->mainImage->USIM_URL) : '';
            if ($user)
                return $this->getApiMessage(true, $user);
            else
                return $this->getApiMessage(false, ['error' => 'FaceID not found']);
        }
    }

    public function getCurrentUser(Request $request)
    {
        $user = $request->user()->load('group', 'type', 'mainImage');
        $user->full_image_url =  ($user->mainImage) ? asset('storage/' . $user->mainImage->USIM_URL) : '';

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
