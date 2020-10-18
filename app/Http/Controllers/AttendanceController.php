<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use DateInterval;
use DateTime;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function newAttendance()
    {
        $data['users'] = User::all();
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

    public function queryPage()
    {
        $data['users'] = User::all();
        $data['formTitle'] = "Attendance Report";
        $data['formURL'] = "attendance/query";
        return view('attendance.query', $data);
    }

    public function queryRes(Request $request)
    {
        $request->validate([
            "userID" => 'required',
            "fromDate" => 'required',
            "toDate" => 'required'
        ]);
        
        $this->initAttendanceArray($request->fromDate, $request->toDate, $request->userID);
        return view('attendance.show', $this->data);
    }

    public function insert(Request $request)
    {
        $request->validate([
            "userID" => 'required|exists:app_users,id',
            "date" => 'required'
        ]);

        Attendance::takeAttendace($request->userID, $request->date);
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
        Attendance::where('id', $id)->delete();
        return back();
    }
    //////attendance array
    protected $data;

    public function initAttendanceArray($from, $to, $userID=0){
        $this->data['items'] = Attendance::getAttendance($from, $to, $userID);
        $this->data['title'] = "Users Attendance";
        $this->data['subTitle'] = "Check users attendance";
        $this->data['cols'] = ['User', 'Class', 'Attendance Dates', 'Delete'];
        $this->data['atts'] =
            [
                'USER_NAME', 'GRUP_NAME', "ATND_DATE",
                ['del'  =>  ['att' => 'id', 'url' => 'attendance/delete/']]
            ];
    }
}
