<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Group;
use App\Models\User;
use DateInterval;
use DateTime;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function newAttendance()
    {
        $data['users'] = User::with('group')->get();
        $data['formTitle'] = "Take Manual Attendance";
        $data['formURL'] = "attendance/insert";
        return view('attendance.add', $data);
    }

    public function month()
    {
        $now = new DateTime();
        $start = $now->format('Y-m-01');
        $end = $now->format('Y-m-t');
        $this->initAttendanceArray($start, $end);
        return view('attendance.show', $this->data);
    }

    public function week()
    {
        $now = new DateTime();
        $start = $now->sub(new DateInterval("P7D"));
        $now = new DateTime();
        $this->initAttendanceArray($start->format('Y-m-d'), $now->format('Y-m-d'));
        return view('attendance.show', $this->data);
    }

    public function queryUser()
    {
        $data['items'] = User::all();
        $data['formTitle'] = "Attendance Report";
        $data['formURL'] = "attendance/query";
        $data['byGroup'] = false;
        return view('attendance.query', $data);
    }

    public function queryGroup()
    {
        $data['items'] = Group::all();
        $data['formTitle'] = "Attendance Report";
        $data['formURL'] = "attendance/query";
        $data['byGroup'] = true;
        return view('attendance.query', $data);
    }

    public function queryOverview()
    {
        $data['formTitle'] = "Overview Report";
        $data['formURL'] = "overview/query";
        $data['byGroup'] = false;
        return view('attendance.query', $data);
    }

    public function overviewLoad(Request $request)
    {
        $request->validate([
            "fromDate" => 'required',
            "toDate" => 'required'
        ]);
        $this->initOverviewArray($request->fromDate, $request->toDate);
        return view('attendance.show', $this->data);
    }

    public function queryRes(Request $request)
    {
        $request->validate([
            "fromDate" => 'required',
            "toDate" => 'required'
        ]);
        if (isset($request->userID) && $request->userID > 0) {
            $this->initAttendanceArray($request->fromDate, $request->toDate, $request->userID);
        } elseif (isset($request->groupID) && $request->groupID > 0) {
            $this->initAttendanceArray($request->fromDate, $request->toDate, 0, $request->groupID);
        } else {
            $this->initAttendanceArray($request->fromDate, $request->toDate, 0);
        }
        return view('attendance.show', $this->data);
    }

    public function insert(Request $request)
    {
        $request->validate([
            "date" => 'required'
        ]);
        foreach ($request->userID as $userID)
            Attendance::takeAttendace($userID, $request->date);
        return redirect('attendance/show');
    }

    public function takeAttendance(Request $request)
    {
        $request->validate([
            "userID" => 'required|exists:app_users,id',
            "date" => 'required'
        ]);

        Attendance::takeAttendace($request->userID, $request->date);
        return back();
    }

    public function deleteAttendance($id)
    {
        return Attendance::where('id', $id)->delete();
    }
    //////attendance array
    protected $data;

    public function initAttendanceArray($from, $to, $userID = 0, $groupID = 0)
    {
        if ($userID != 0)
            $this->data['items'] = Attendance::getAttendance($from, $to, $userID);
        elseif ($groupID != 0)
            $this->data['items'] = Attendance::getAttendance($from, $to, 0, $groupID);
        else
            $this->data['items'] = Attendance::getAttendance($from, $to);
        $this->data['title'] = "Users Attendance";
        $this->data['subTitle'] = "Check users attendance";
        $this->data['cols'] = ['User', 'Class', 'Attendance Dates', 'Delete'];
        $this->data['atts'] =
            [
                ['attUrl' => ['url' => 'users/profile', 'urlAtt' => 'ATND_USER_ID', 'shownAtt' => 'USER_NAME']],
                'GRUP_NAME',
                ['verified' => ['att' => 'ATND_DATE', 'isVerified' => 'ATND_PAID']],
                ['delJs'  =>  ['att' => 'id', 'func' => 'deleteAttendace']]
            ];

        $this->data['deleteAttendanceURL'] = url('attendance/delete');
    }

    public function initOverviewArray($from, $to)
    {
        $this->data['items'] = User::overviewQuery($from, $to);
        $this->data['title'] = "Overview Report";
        $this->data['subTitle'] = "Check users overview";
        $this->data['cols'] = ['#', 'Username', 'Class', "A", "Paid", "Born", 'Mob#', 'Comment'];
        $this->data['atts'] =
            [
                ['dynamicUrl' => ['att' => 'USER_CODE', '0' => 'users/profile/', 'val' => 'id']],
                ['dynamicUrl' => ['att' => 'USER_NAME', '0' => 'users/profile/', 'val' => 'id']],
                'GRUP_NAME',
                "A",
                "P",
                 "USER_BDAY",
                'USER_MOBN',
                ['comment' => ['att' => 'USER_NOTE']]
            ];

        $this->data['deleteAttendanceURL'] = url('attendance/delete');
    }
}
