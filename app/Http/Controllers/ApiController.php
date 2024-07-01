<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BalancePayment;
use App\Models\Event;
use App\Models\EventPayment;
use App\Models\EventsAttendance;
use App\Models\Group;
use App\Models\Payment;
use App\Models\PlayersCatogory;
use App\Models\User;
use App\Models\UserImage;
use App\Models\UserType;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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

    public function getCategories()
    {
        return $this->getApiMessage(true, PlayersCatogory::all());
    }

    public function getUsertypes()
    {
        return $this->getApiMessage(true, UserType::all());
    }

    public function getUserByID($userID)
    {
        $user = User::with(['group', 'type', 'mainImage', 'player_category'])->find($userID);
        if ($user) {
            $user->full_image_url =  ($user->mainImage) ? asset('storage/' . $user->mainImage->USIM_URL) : '';
            return $this->getApiMessage(true, $user);
        } else
            return $this->getApiMessage(false);
    }

    public function getNextUserID()
    {
        $allCodes = User::select("USER_CODE")->get();
        $max = 0;
        foreach ($allCodes as $code) {
            if (is_numeric($code->USER_CODE) && $code->USER_CODE > $max)
                $max = $code->USER_CODE;
        }
        if ($max) {
            return $this->getApiMessage(true, $max + 1);
        } else
            return $this->getApiMessage(false);
    }

    public function getUsers()
    {
        $users = User::join("groups", "groups.id", '=', 'USER_GRUP_ID')->leftJoin('app_user_images', 'app_user_images.id', '=', 'USER_MAIN_IMGE')
            ->where('GRUP_ACTV', 1)->where('USER_ACTV', 1)->select(["app_users.id", "USER_NAME", "GRUP_NAME", "USIM_URL"])
            ->where('USER_CODE', '!=', 'A999')
            ->selectRaw('(Select COUNT(ATND_DATE) from attendance where ATND_USER_ID = app_users.id and DATE(ATND_DATE) = CURDATE() )  as isAttended,
            (Select new_balance from balance_payments where balance_payments.app_users_id = app_users.id ORDER BY id desc LIMIT 1 ) as userBalance')
            ->get(["app_users.id", "USER_NAME", "GRUP_NAME", "USIM_URL", "isAttended", "userBalance"]);
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
                ->where('USER_CODE', '!=', 'A999')
                ->select(["app_users.id", "USER_NAME", "GRUP_NAME", "USIM_URL"])
                ->selectRaw('(Select COUNT(ATND_DATE) from attendance where ATND_USER_ID = app_users.id and DATE(ATND_DATE) = CURDATE() )  as isAttended,
                (Select new_balance from balance_payments where balance_payments.app_users_id = app_users.id ORDER BY id desc LIMIT 1 ) as userBalance');
            foreach ($arguments as $value) {
                $users = $users->whereRaw(
                    " ( GRUP_NAME LIKE '{$value}%' OR USER_NAME LIKE '%{$value}%' OR YEAR(USER_BDAY) = ? ) ",
                    [$value]
                );
            }

            $users = $users->get(["app_users.id", "USER_NAME", "GRUP_NAME", "USIM_URL", "isAttended", "userBalance"]);
            if ($users) {
                $this->adjustImageUrl($users);
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
        $user = Auth::user();
        if ($user->USER_USTP_ID == 4) abort(403, "Unauthorized");

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
            "player_category" => "nullable|exists:players_categories,id",
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
            $user->players_category_id = $request->player_category;

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
            "player_category" => "required|exists:players_categories,id",
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
            $user->players_category_id = $request->player_category;

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
                return $this->getApiMessage(true, $user->load(['group', 'type', 'mainImage', 'player_category']));
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

    public function getUserOverview(Request $request)
    {
        $request->validate([
            "userID" => "required",
        ]);

        $user = User::with('player_category')->findOrFail($request->userID);
        $payments =  $user->getLatestPayments($request->months);
        $attendance = $user->getOverviewAttendance($request->months);

        $payments = $payments->mapWithKeys(function ($row) {
            return [$row->OVRV_YEAR * 100 + $row->OVRV_MNTH   => [
                "Month" => $this->getMonthName($row->OVRV_MNTH),
                "Year" => $row->OVRV_YEAR,
                "P" => $row->OVRV_PAID
            ]];
        });

        $attendance = $attendance->mapWithKeys(function ($row) use ($user) {
            return [$row->OVRV_YEAR * 100 + $row->OVRV_MNTH  => [
                "Month" => $this->getMonthName($row->OVRV_MNTH),
                "Year" => $row->OVRV_YEAR,
                "A" => $row->OVRV_ATND,
                "D" => $user->player_category->getDue($row->OVRV_ATND)
            ]];
        });

        $merged = $attendance->toArray();

        foreach ($payments as $key => $row) {
            if (key_exists($key, $merged))
                $merged[$key]["P"] = $row['P'];
            else
                $merged[$key] = $row;
        }
        krsort($merged, SORT_NUMERIC);
        // Log::info($merged);
        return $this->getApiMessage(true, $merged);
    }

    public function getAttendanceDetails($id, $month, $year)
    {
        $month = new Carbon($year . '-' . $month . '-01');
        $attendance = Attendance::getAttendance($month, $month->format('Y-m-t'), $id);
        return $this->getApiMessage(true, $attendance);
    }

    public function getUserPayments($id)
    {
        $user = Auth::user();
        if ($user->USER_USTP_ID == 4) abort(403, "Unauthorized");

        $user = User::find($id);
        if ($user)
            return $this->getApiMessage(true, $user->payments()->whereRaw('PYMT_DATE >= DATE_SUB(NOW(), INTERVAL 24 MONTH)')->orderByDesc('PYMT_DATE')->get());
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

    public function getBalanceEntries($id)
    {
        $user = Auth::user();
        if ($user->USER_USTP_ID == 4) abort(403, "Unauthorized");

        User::findOrFail($id);
        $balancePayments = BalancePayment::byUser($id)->with('collected_by_user', 'collected_by_user.type', 'collected_by_user.group', 'collected_by_user.player_category')->orderByDesc('id')->limit(25)->get();
        return $this->getApiMessage(true, $balancePayments);
    }

    public function getUserEventPayments($id)
    {
        $user = Auth::user();
        if ($user->USER_USTP_ID == 4) abort(403, "Unauthorized");

        $payments = EventPayment::getUserEventPayments($id);
        if ($payments)
            return $this->getApiMessage(true, $this->makeLikePayment3shanMickeyBeh($payments));
        else
            return $this->getApiMessage(false, ['error' => 'invalid user id']);
    }

    public function addPayment(Request $request)
    {
        $user = Auth::user();
        if ($user->USER_USTP_ID == 4) abort(403, "Unauthorized");

        $this->validateRequest($request, [
            "userID" => 'required|exists:app_users,id',
            "amount" => 'required',
            "eventID" => 'required_if:type,2',
            "isSettlment" => 'required_if:type,1|boolean',
            "type"  => "required",
        ]);

        /** @var User */
        $user = User::findOrFail($request->userID);
        $res = false;
        if ($request->type == 1) {
            $res = $user->addToBalance($request->amount, "New Payment", $request->note ?? "User Balance Payment In", $request->isSettlment);
        } elseif ($request->type == 2) {
            $request->validate([
                "eventID" => "exists:events,id"
            ]);
            $res = $user->payEvent($request->eventID, $request->amount, $request->eventState, $request->note);
        }

        if ($res) {
            return $this->getApiMessage(true);
        } else {
            return $this->getApiMessage(false, ['error' => 'Payment failed']);
        }
    }

    public function sendReminder(Request $request)
    {
        $user = Auth::user();
        if ($user->USER_USTP_ID == 4) abort(403, "Unauthorized");

        $this->validateRequest($request, [
            "userID" => 'required|exists:app_users,id'
        ]);

        /** @var User */
        $user = User::findOrFail($request->userID);
        $res = $user->sendBalanceReminder();

        if ($res) {
            return $this->getApiMessage(true);
        } else {
            return $this->getApiMessage(false, ['error' => 'Sending SMS failed']);
        }
    }

    public function sendLastUpdate(Request $request)
    {
        $user = Auth::user();
        if ($user->USER_USTP_ID == 4) abort(403, "Unauthorized");

        $this->validateRequest($request, [
            "userID" => 'required|exists:app_users,id'
        ]);

        /** @var User */
        $user = User::findOrFail($request->userID);
        $res = $user->sendLastUpdate();

        if ($res) {
            return $this->getApiMessage(true);
        } else {
            return $this->getApiMessage(false, ['error' => 'Sending SMS failed']);
        }
    }

    public function getLastUpdate(Request $request)
    {
        $user = Auth::user();
        if ($user->USER_USTP_ID == 4) abort(403, "Unauthorized");

        $this->validateRequest($request, [
            "userID" => 'required|exists:app_users,id'
        ]);

        /** @var User */
        $user = User::findOrFail($request->userID);
        $res = $user->getLastUpdate();

        if ($res) {
            return $this->getApiMessage(true, [
                'update_message' => $res,
                'number' => $user->USER_MOBN
            ]);
        } else {
            return $this->getApiMessage(false, ['error' => 'Sending SMS failed']);
        }
    }

    public function getBalanceUpdate(Request $request)
    {
        $user = Auth::user();
        if ($user->USER_USTP_ID == 4) abort(403, "Unauthorized");
        Log::info($request->userID);
        Log::info($request->balanceID);

        $this->validateRequest($request, [
            "userID" => 'required|exists:app_users,id',
            "balanceID" => 'required|exists:balance_payments,id',
        ]);

        /** @var BalancePayment */
        $user = User::findOrFail($request->userID);

        /** @var BalancePayment */
        $update = BalancePayment::findOrFail($request->id);
        $res = $update->getSms();
        Log::info($res);
        if ($res) {
            return $this->getApiMessage(true, [
                'update_message' => $res,
                'number' => $user->USER_MOBN
            ]);
        } else {
            return $this->getApiMessage(false, ['error' => 'Sending SMS failed']);
        }
    }

    public function sendSMS(Request $request)
    {
        $user = Auth::user();
        if ($user->USER_USTP_ID == 4) abort(403, "Unauthorized");

        $this->validateRequest($request, [
            "userID" => 'required|exists:app_users,id',
            "msg" => 'required'
        ]);

        /** @var User */
        $user = User::findOrFail($request->userID);
        $res = $user->sendSMS($request->msg);

        if ($res) {
            return $this->getApiMessage(true);
        } else {
            return $this->getApiMessage(false, ['error' => 'Sending SMS failed']);
        }
    }

    public function deleteUserPayment(Request $request)
    {
        $user = Auth::user();
        if ($user->USER_USTP_ID == 4) abort(403, "Unauthorized");

        $this->validate($request, [
            "paymentID" => "required|exists:payments,id"
        ]);

        $payment = Payment::findOrFail($request->paymentID);
        $res = $payment->refund();
        if ($res) {
            return $this->getApiMessage(true);
        } else {
            return $this->getApiMessage(false, ['error' => 'Refund failed']);
        }
    }

    public function deleteEventPayment(Request $request)
    {
        $user = Auth::user();
        if ($user->USER_USTP_ID == 4) abort(403, "Unauthorized");

        $this->validate($request, [
            "paymentID" => "required|exists:event_payments,id"
        ]);
        /** @var EventPayment */
        $payment = EventPayment::findOrFail($request->paymentID);
        $res = $payment->refund();
        if ($res) {
            return $this->getApiMessage(true);
        } else {
            return $this->getApiMessage(false, ['error' => 'Refund failed']);
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
        $user = $request->user()->load('group', 'type', 'mainImage', 'player_category');
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

    private function makeLikePayment3shanMickeyBeh($payments)
    {
        $ret = new Collection();
        foreach ($payments as $payment) {
            $ret->add([
                "id" => $payment->id,
                "PYMT_AMNT" => $payment->EVPY_AMNT,
                "PYMT_DATE" => $payment->created_at->format('Y-m-d'),
                "PYMT_USER_ID" => $payment->EVPY_USER_ID,
                "EVNT_NAME" => $payment->EVNT_NAME . " (" . EventsAttendance::getStatusName($payment->EVAT_STTS) . ")",
            ]);
        }
        return $ret;
    }

    private function getMonthName(int $month)
    {
        switch ($month) {

            case 1:
                return "January";
            case 2:
                return "February";
            case 3:
                return "March";
            case 4:
                return "April";
            case 5:
                return "May";
            case 6:
                return "June";
            case 7:
                return "July";
            case 8:
                return "August";
            case 9:
                return "September";
            case 10:
                return "October";
            case 11:
                return "November";
            case 12:
                return "December";
            default:
                return null;
        }
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
