<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function home(){

    }


    public function takeAttendance(Request $request){
        $request->validate([
            "userID" => 'required|exists:app_users,id',
            "date" => 'required'
        ]);

        Attendance::takeAttendace($request->userID, $request->date);
        return back();
    }

    public function deleteAttendance($id){
        Attendance::where('id', $id)->delete();
        return back();
    }
}
