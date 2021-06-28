<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\EventPayment;
use App\Models\User;
use DateTime;
use Exception;
use Illuminate\Http\Request;

class EventsController extends Controller
{
    public function all()
    {
        $data['items'] = Event::with("users", "payments")->orderByDesc('events.id')->get();
        $data['title'] = "Events";
        $data['subTitle'] = "Check all Created Events";
        $data['cols'] = ['Date', 'Event', 'Price', 'Subscribers', 'Paid', 'Comment'];
        $data['atts'] = [
            'EVNT_DATE',
            ['dynamicUrl'   =>      ['att' => 'EVNT_NAME', '0' => 'events/', 'val' => 'id']],
            'EVNT_PRCE',
            ['countForeign' =>      ['rel' => 'users']],
            ['sumForeign'   =>      ['rel' => 'payments', 'att' => 'EVPY_AMNT']],
            ['comment' => ['att' => 'EVNT_CMNT']],
        ];

        return view('events.show', $data);
    }

    public function details($id)
    {
        $event = Event::with("users", "payments")->findOrFail($id);
        $data['attendance']     = $event->getEventAttendance();

        //load events tab
        $data['events'] = Event::all();

        //reservation tab
        $data['users']          = User::all();
        $data['registeredIDs'] = $event->users->pluck('id');
        $data['setStatusURL'] = url('events/attach');
        $data['deleteAttendanceURL'] = url('events/detach');
        $data['removePaymentURL'] = url('events/payments/delete');


        //payment tab
        $data['addPaymentURL'] = url('payments/insert');

        //settings tab
        $data['formTitle']  =   "Update Event Data";
        $data['formURL']    =   url('events/update');

        $data['event'] = $event;

        return view('events.details', $data);
    }

    public function attachUser(Request $request)
    {
        $request->validate([
            "eventID" => "required",
            "userID" => "required",
            "status" => "required",
        ]);

        if ($request->status == 0) {
            $this->detachUser($request);
            return;
        }

        echo Event::attachUser($request->eventID, $request->userID, $request->status);
    }

    public function detachUser(Request $request)
    {
        $request->validate([
            "eventID"   => "required",
            "userID" => "required",
        ]);
        $event = Event::findOrFail($request->eventID);
        echo $event->users()->detach($request->userID);
    }

    public function insert(Request $request)
    {

        $request->validate([
            "name" =>   "required",
            "date" =>   "required",
            "price" =>  "required",
        ]);

        $event = new Event();

        $event->EVNT_NAME = $request->name;
        $event->EVNT_PRCE = $request->price;
        $event->EVNT_DATE = $request->date;
        $event->EVNT_CMNT = $request->comment;

        $event->save();
        return redirect('events/' . $event->id);
    }

    public function update(Request $request)
    {

        $request->validate([
            "id"    => "required",
            "name" =>   "required",
            "date" =>   "required",
            "price" =>  "required",
        ]);

        $event = Event::findOrFail($request->id);

        $event->EVNT_NAME = $request->name;
        $event->EVNT_PRCE = $request->price;
        $event->EVNT_DATE = $request->date;
        $event->EVNT_CMNT = $request->comment;

        $event->save();
        return redirect('events/' . $event->id);
    }

    public function add()
    {
        $data['formURL'] = "events/insert";
        $data['formTitle'] = "Create New Event";
        return view('events.add', $data);
    }

    public function deletePayments(Request $request)
    {
        $request->validate([
            "eventID" => "required",
            "userID" => "required",
        ]);

        echo EventPayment::deletePayments($request->userID, $request->eventID);
    }

    public function delete($id)
    {
        $event = Event::findOrFail($id);
        $event->deleteAll();
        return redirect('events/all');
    }

    public function queryPage()
    {
        $data['users'] = User::orderByRaw("ABS(USER_CODE), USER_CODE")->get();
        $data['formTitle'] = "Event Payments Report";
        $data['formURL'] = "events/payments/report";
        return view('events.query', $data);
    }

    public function queryRes(Request $request)
    {
        $request->validate([
            "userID" => 'required',
            "fromDate" => 'required',
            "toDate" => 'required'
        ]);

        $from   = new DateTime($request->fromDate);
        $to     = new DateTime($request->toDate);

        $userID = $request->userID;

        if ($userID == 0) {
            $userName = "All Users";
        } else {
            $user = User::findOrFail($userID);
            $userName = $user->USER_NAME;
        }

        $data['items'] =  EventPayment::report($from, $to, $request->userID);

        $data['title'] =  "Payments Report -- Total: " . $data['items']->sum('EVPY_AMNT');
        $data['subTitle'] = "Showing Payments for " . $userName . " From "  . $from->format('Y-F-d') . " to " . $to->format('Y-F-d');
        $data['cols'] = ['User', 'Event', 'Amount', 'Date'];

        $data['atts'] = [
            ['foreignUrl' => ['events', 'EVPY_EVNT_ID', 'event', 'EVNT_NAME']],
            ['foreignUrl' => ['users/profile', 'EVPY_USER_ID', 'user', 'USER_NAME']],
            ['date' => [ 'att' => 'PYMT_DATE', 'format' => 'M-Y']],
            'EVPY_AMNT',
            'created_at',
        ];

        return view('events.show', $data);
    }
}
