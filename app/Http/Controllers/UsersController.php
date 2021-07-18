<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Group;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserImage;
use App\Models\UserType;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UsersController extends Controller
{

    public function home($type = 0)
    {
        $this->initDataArray($type);

        return view('users.home', $this->data);
    }

    public function addPage()
    {

        $this->initAddArray();
        return view('users.add', $this->data);
    }

    public function insert(Request $request)
    {
        $request->validate([
            "name" => "required|unique:app_users,USER_NAME",
            "code" => "required|unique:app_users,USER_CODE",
            "type" => "required|exists:app_user_types,id",
            "group" => "required|exists:groups,id",
            "birthDate" => "nullable|date",
            "mail" => "required_if:type,1,3|nullable",
            "password" => "required_if:type,1,3|nullable",
        ]);

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

        return redirect('users/profile/' . $user->id);
    }

    public function profile($id, Request $request)
    {
        $this->initProfileArray($id, $request->year ?? null);
        return view('users.profile', $this->data);
    }

    public function toggle($id)
    {
        $user = User::findOrFail($id);
        if ($user->USER_USTP_ID == 1)
            $user->USER_USTP_ID = 2;
        else
            $user->USER_USTP_ID = 1;
        $user->save();
        return back();
    }


    public function update(Request $request)
    {

        $request->validate([
            'id' => 'required'
        ]);

        $user = User::findOrFail($request->id);

        $request->validate([
            "name" => ["required", Rule::unique('app_users', "USER_NAME")->ignore($user->USER_NAME, "USER_NAME")],
            "code" => ["required", Rule::unique('app_users', "USER_CODE")->ignore($user->USER_CODE, "USER_CODE")],
            "type" => "required|exists:app_user_types,id",
            "group" => "required|exists:groups,id",
            "birthDate" => "nullable|date",
            "mail" => "required_if:type,1,3"
        ]);

        $user->USER_NAME = $request->name;
        $user->USER_USTP_ID = $request->type;
        $user->USER_BDAY = $request->birthDate;
        $user->USER_MAIL = $request->mail;
        if(isset($request->password) && $request->password != '')
        $user->USER_PASS = bcrypt($request->password);
        $user->USER_GRUP_ID = $request->group;
        $user->USER_FACE_ID = bcrypt($user->USER_NAME);
        $user->USER_NOTE = $request->note;
        $user->USER_CODE = $request->code;
        $user->USER_MOBN = $request->mobn;
        $user->save();

        return redirect('users/profile/' . $user->id);
    }

    ////////images function 
    public function attachImage(Request $request)
    {
        $request->validate([
            "userID" => "required|exists:app_users,id",
            "photo" => "file"
        ]);
        $user = User::findOrFail($request->userID);
        $newImage = new UserImage();
        if ($request->hasFile('photo')) {
            $newImage->USIM_URL = $request->photo->store('images/users/' . $user->USER_NAME, 'public');
        }
        $newImage->USIM_USER_ID = $request->userID;

        $newImage->save();
        return back();
    }

    public function setMainImage($user, $imageID)
    {
        $user = User::findOrFail($user);
        $user->USER_MAIN_IMGE = $imageID;
        $user->save();
        return back();
    }


    public function sendReminder($id)
    {
        $user = User::find($id);
        Payment::sendPaymentReminderSMS($user->USER_NAME, $user->USER_MOBN);
        echo 1;
    }

    public function delete($id)
    {
        $user = User::find($id);
        $user->delete();
        return redirect('users/show');
    }

    public function deleteImage($id)
    {
        $image = UserImage::findOrFail($id);
        $image->deleteImage();
        return back();
    }
    ////////data functions
    protected $data;

    private function initDataArray($type)
    {
        if ($type == 0) {
            $this->data['items'] = User::orderByRaw("ABS(USER_CODE), USER_CODE")->get();
        } else {
            $this->data['items'] = User::where('USER_USTP_ID', $type)->orderByRaw("ABS(USER_CODE), USER_CODE")->get();
        }

        $this->data['title'] = "Dashboard Users";
        $this->data['subTitle'] = "Manage All Dashboard Users";
        $this->data['cols'] = ['#', 'Username', 'Class', "A", "Paid", "Born", 'Mob#', 'Comment'];
        $this->data['atts'] =
            [
                ['dynamicUrl' => ['att' => 'USER_CODE', '0' => 'users/profile/', 'val' => 'id']],
                ['dynamicUrl' => ['att' => 'USER_NAME', '0' => 'users/profile/', 'val' => 'id']],
                ['foreign' => ['group', 'GRUP_NAME']],
                ['modelFunc' => ['funcName' => 'monthlyAttendance'] ] ,
                ['modelFunc' => ['funcName' => 'monthlyPayment'] ] ,
                ['date' => ['att' => "USER_BDAY", 'format' => 'd-M-Y']],
                'USER_MOBN',
                ['comment' => ['att' => 'USER_NOTE']]
            ];
    }

    private function initAddArray()
    {
        $this->data['types'] = UserType::all();
        $this->data['images'] = UserImage::all();
        $this->data['groups'] = Group::all();
        $this->data['formTitle'] = "Add New User";
        $this->data['formURL'] = "users/insert";
        $this->data['homeURL'] = "users/show";
        $this->data['isCancel'] = true;
        $this->data['isPassNeeded'] = false;
    }

    private function initProfileArray($id, $overviewYear = null)
    {
        $this->data['user'] = User::findOrFail($id);
        $this->data['types'] = UserType::all();
        $this->data['images'] = UserImage::all();
        $this->data['groups'] = Group::all();
        $this->data['years'] = $this->data['user']->getAttendedYears();
        $this->data['formTitle'] = "Add New User";
        $this->data['formURL'] = "users/update";
        $this->data['homeURL'] = "users/show";
        $this->data['isCancel'] = true;
        $this->data['isPassNeeded'] = false;

        //Attendance array
        $this->data['items'] = $this->data['user']->getLatestAttendance();
        $this->data['title'] = "User Attendance";
        $this->data['subTitle'] = "Check user attendance";
        $this->data['cols'] = ['Attendance Dates', 'Delete'];
        $this->data['atts'] =
            [
                ['date' => ['att' => "ATND_DATE", 'format' => 'd-M-Y']],
                ['del'  =>  ['att' => 'id', 'url' => 'attendance/delete/']]
            ];

        $this->data['attendanceFormURL'] = url('attendance/take');
        $this->data['sendReminderURL'] = url('users/send/reminder') . '/' . $id;

        //Images array
        $this->data['imageFormURL'] = url('users/add/image');

        //Overview array
        $now = new DateTime();
        $year = $overviewYear ?? $now->format('Y');
        $this->data['loadedYear'] = $year;
        $this->data['overItems'] = array();
        for($month=1 ; $month <13 ; $month++){
            $date = new DateTime($year . '-' . $month . '-01');
            $tmp = [ 
                $date->format('F Y') ,
                Attendance::getAttendanceLite($date->format('Y-m-01'), $date->format('Y-m-t'), $this->data['user']->id)->count(),
                Payment::getPaymentsLite($date->format('Y-m-01'),$date->format('Y-m-t'), $this->data['user']->id)->sum('PYMT_AMNT')
            ];
            array_push($this->data['overItems'], $tmp);
        }
    }
}
