<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\EventPayment;
use App\Models\Group;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserImage;
use App\Models\UserType;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ApiController extends Controller
{


    public function getAttendance($userID)
    {
    }

    public function getGroups()
    {
        return $this->getApiMessage(true, Group::withCount("users as usersCount")->get());
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

    public function getUsers()
    {
        $users = User::join("groups", "groups.id", '=', 'USER_GRUP_ID')->leftJoin('app_user_images', 'app_user_images.id', '=', 'USER_MAIN_IMGE')
            ->where('GRUP_ACTV', 1)->where('USER_ACTV', 1)->select(["app_users.id", "USER_NAME", "GRUP_NAME", "USIM_URL"])
            ->selectRaw('(Select COUNT(ATND_DATE) from attendance where ATND_USER_ID = app_users.id and DATE(ATND_DATE) = CURDATE() )  as isAttended,
                     (Select COUNT(payments.id) from payments where PYMT_USER_ID = app_users.id and MONTH(PYMT_DATE) = MONTH(CURDATE())) as monthlyPayments')
            ->get(["app_users.id", "USER_NAME", "GRUP_NAME", "USIM_URL", "isAttended", "paymentsDue"]);
        if ($users) {
            $this->adjustImageUrl($users);
            return $this->getApiMessage(true, $users);
        } else
            return $this->getApiMessage(false);
    }

    public function getUsersByGroupID($groupID)
    {
        $users = User::with(['group', 'type', 'mainImage'])->where("USER_GRUP_ID", $groupID)->get();
        if ($users) {
            $this->adjustImageUrl($users);
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
            $users = User::join("groups", "groups.id", '=', 'USER_GRUP_ID')->leftJoin('app_user_images', 'app_user_images.id', '=', 'USER_MAIN_IMGE')
                ->where('GRUP_ACTV', 1)->where('USER_ACTV', 1)
                ->select(["app_users.id", "USER_NAME", "GRUP_NAME", "USIM_URL"])
                ->selectRaw('(Select COUNT(ATND_DATE) from attendance where ATND_USER_ID = app_users.id and DATE(ATND_DATE) = CURDATE() )  as isAttended,
                (Select COUNT(payments.id) from payments where PYMT_USER_ID = app_users.id and MONTH(PYMT_DATE) = MONTH(CURDATE())) as monthlyPayments');
            foreach ($arguments as $value) {
                $users = $users->whereRaw(
                    " GRUP_NAME LIKE '{$value}%' OR USER_NAME LIKE '%{$value}' OR USER_NAME LIKE '{$value}%' OR YEAR(USER_BDAY) = ? " , [$value]
                );
            }
  
            // $users = $users->get(["app_users.id", "USER_NAME", "GRUP_NAME", "USIM_URL", "isAttended", "paymentsDue"]);
            $users = $users->toSql();
            if ($users) {
                // $this->adjustImageUrl($users);
                return $this->getApiMessage(true, $users);
            } else
                return $this->getApiMessage(false);
        }
    }

    public function takeAttendance(Request $request)
    {
        $validation = $this->validateRequest($request, [
            "userID" => 'required|exists:app_users,id',
            "date" => 'required'
        ]);
        if ($validation === true) {
            $res = Attendance::takeAttendace($request->userID, $request->date);
            if ($res) {
                return $this->getApiMessage(true, $res);
            } else
                return $this->getApiMessage(false);
        }
    }

    public function takeBulkAttendance(Request $request)
    {
        $validation = $this->validateRequest($request, [
            "date" => 'required'
        ]);
        if ($validation === true) {
            $failedIDs = [];
            $userIDs = json_decode($request->userIDs);
            if (is_array($userIDs)) {
                foreach ($userIDs as $id) {
                    try {
                        $res = Attendance::takeAttendace($id, $request->date);
                    } catch (Exception $e) {
                        $res = false;
                    }
                    if (!$res) {
                        array_push($failedIDs, $id);
                    }
                }
                if (count($failedIDs) == 0) {
                    return $this->getApiMessage(1);
                } elseif (count($failedIDs) < count($request->userIDs)) {
                    return $this->getApiMessage(2, ['failedIDs' => $failedIDs]);
                } elseif (count($failedIDs) == count($request->userIDs)) {
                    return $this->getApiMessage(false, ['Message' => "Failed to take attendance"]);
                } else {
                    return $this->getApiMessage(false, ['Message' => "Oops Something went wrong!"]);
                }
            } else {
                return $this->getApiMessage(false, ['Message' => "Invalid Array!"]);
            }
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

    public function toggleGroup(Request $request)
    {
        $validation = $this->validateRequest($request, [
            "id"      => "required|exists:groups,id",
        ]);
        if ($validation === true) {
            try {
                $group = Group::find($request->id);
                $res = $group->toggle();
                if ($res)
                    return $this->getApiMessage(true);
                else
                    return $this->getApiMessage(false, ['error' => 'Group Toggle Failed']);
            } catch (Exception $e) {
                return $this->getApiMessage(false, ['error' => 'Group Toggle Failed']);
            }
        } else
            return $this->getApiMessage(false, ['error' => 'Group Not Found']);
    }

    public function delGroup(Request $request)
    {
        $validation = $this->validateRequest($request, [
            "id"      => "required|exists:groups,id",
        ]);
        if ($validation === true) {
            try {
                $res = Group::destroy($request->id);
                if ($res)
                    return $this->getApiMessage(true);
                else
                    return $this->getApiMessage(false, ['error' => 'Group Deletion Failed']);
            } catch (Exception $e) {
                return $this->getApiMessage(false, ['error' => 'Group Deletion Failed']);
            }
        } else
            return $this->getApiMessage(false, ['error' => 'Group Not Found']);
    }

    public function addUser(Request $request)
    {
        $validation = $this->validateRequest($request, [
            "name" => "required|unique:app_users,USER_NAME",
            "code" => "required|unique:app_users,USER_CODE",
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
            $user->USER_NOTE = $request->note;
            $user->USER_CODE = $request->code;
            $user->USER_MOBN = $request->mobn;

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

    public function editUser(Request $request)
    {

        $validation = $this->validateRequest($request, [
            'id' => 'required'
        ]);

        $user = User::findOrFail($request->id);

        $validation = $this->validateRequest($request, [
            "id"   => "required|exists:app_users,id",
            "name" => ["required", Rule::unique('app_users', "USER_NAME")->ignore($user->USER_NAME, "USER_NAME")],
            "code" => ["required", Rule::unique('app_users', "USER_CODE")->ignore($user->USER_CODE, "USER_CODE")],
            "group" => "required|exists:groups,id",
            "birthDate" => "nullable|date",
        ]);
        if ($validation === true) {

            $user->USER_NAME = $request->name;
            $user->USER_BDAY = $request->birthDate;
            $user->USER_FACE_ID = bcrypt($user->USER_NAME);
            $user->USER_NOTE = $request->note;
            $user->USER_CODE = $request->code;
            $user->USER_MOBN = $request->mobn;
            $user->USER_GRUP_ID = $request->group;

            $user->save();
            if ($request->hasFile('photo')) {
                try {
                    $newImage = new UserImage();
                    $newImage->USIM_URL = $request->photo->store('images/users/' . $user->USER_NAME, 'public');
                    $newImage->USIM_USER_ID = $user->id;
                    $newImage->save();
                    $newImage->compress();
                    $user->USER_MAIN_IMGE = $newImage->id;
                    $user->save();
                } catch (Exception $e) {
                }
            }

            if ($user)
                return $this->getApiMessage(true, $user->load(['group', 'type', 'mainImage']));
            else
                return $this->getApiMessage(false, ['error' => 'User Modification Failed']);
        }
    }

    public function changePassword(Request $request)
    {

        $validation = $this->validateRequest($request, [
            'id' => 'required'
        ]);

        $user = User::findOrFail($request->id);

        $validation = $this->validateRequest($request, [
            "oldPassword" => "required",
            "newPassword" => "required",
        ]);
        if ($validation === true) {

            if (Hash::check($request->oldPassword, $user->USER_PASS)) {
                $user->USER_PASS = bcrypt($request->newPassword);


                $res = $user->save();

                if ($res)
                    return $this->getApiMessage(true);
                else
                    return $this->getApiMessage(false, ['error' => 'Password Modification Failed']);
            } else {
                return $this->getApiMessage(false, ['error' => 'Invalid Password']);
            }
        }
    }

    public function changeEmail(Request $request)
    {
        $validation = $this->validateRequest($request, [
            'id' => 'required'
        ]);

        $user = User::findOrFail($request->id);

        $validation = $this->validateRequest($request, [
            "password"  => "required",
            "email"     => "required",
        ]);
        if ($validation === true) {

            if (Hash::check($request->password, $user->USER_PASS)) {
                $user->USER_MAIL = $request->email;


                $res = $user->save();

                if ($res)
                    return $this->getApiMessage(true);
                else
                    return $this->getApiMessage(false, ['error' => 'Email Modification Failed']);
            } else {
                return $this->getApiMessage(false, ['error' => 'Invalid Password']);
            }
        }
    }

    public function getUserOverview(Request $request){
        $request->validate([
            "userID" => "required",
        ]);

        $user = User::findOrFail($request->userID);
        $payments =  $user->getLatestPayments($request->months);
        $attendance = $user->getOverviewAttendance($request->months);

        $payments = $payments->mapWithKeys(function ($row){
            return [$row->OVRV_MNTH ."-". $row->OVRV_YEAR => $row->OVRV_PAID];
        });

        $attendance = $attendance->mapWithKeys(function ($row){
            return [$row->OVRV_MNTH ."-". $row->OVRV_YEAR => $row->OVRV_ATND];
        });

 
        return $this->getApiMessage(true, $payments);
    }

    public function getUserPayments($id)
    {
        $user = User::find($id);
        if ($user)
            return $this->getApiMessage(true, $user->payments()->whereRaw('PYMT_DATE >= DATE_SUB(NOW(), INTERVAL 24 MONTH)')->orderByDesc('payments.id')->get());
        else
            return $this->getApiMessage(false, ['error' => 'invalid user id']);
    }

    public function getEvents()
    {
        $events = Event::orderByDesc('id')->get();
        if ($events)
            return $this->getApiMessage(true, $events);
        else
            return $this->getApiMessage(false, ['error' => 'Cant load events']);
    }

    public function getUserEventPayments($id)
    {
        $payments = EventPayment::with('user:id,USER_NAME', 'event:id,EVNT_NAME')->where('EVPY_USER_ID', $id)->get();
        if ($payments)
            return $this->getApiMessage(true, $this->makeLikePayment3shanMickeyBeh($payments));
        else
            return $this->getApiMessage(false, ['error' => 'invalid user id']);
    }

    public function addPayment(Request $request)
    {
        $this->validateRequest($request, [
            "userID" => 'required|exists:app_users,id',
            "amount" => 'required',
            "date" => 'required_if:type,1',
            "eventID" => 'required_if:type,2',
            "type"  => "required"
        ]);

        if ($request->type == 1) {
            //normal monthly payment
            $res = Payment::addPayment($request->userID, $request->amount, $request->date, $request->note);
        } elseif ($request->type == 2) {
            $request->validate([
                "eventID" => "exists:events,id"
            ]);
            //event payment
            $res = EventPayment::addPayment($request->userID, $request->eventID, $request->amount);
        }

        if ($res) {
            return $this->getApiMessage(true);
        } else {
            return $this->getApiMessage(false, ['error' => 'Payment failed']);
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

    private function adjustImageUrl($users)
    {
        foreach ($users as $key => $user) {
            $user->USIM_URL = $user->USIM_URL ? url(Storage::url($user->USIM_URL)) : NULL;
        }
    }

    private function makeLikePayment3shanMickeyBeh($payments){
        $ret = new Collection();
        foreach($payments as $payment){
            $ret->add([
                "id" => $payment->id,
                "PYMT_AMNT" => $payment->EVPY_AMNT,
                "PYMT_DATE" => $payment->created_at->format('Y-m-d'),
                "PYMT_USER_ID" => $payment->EVPY_USER_ID,
                "EVNT_NAME" => $payment->event->EVNT_NAME,
            ]);
        }
        return $ret;
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
