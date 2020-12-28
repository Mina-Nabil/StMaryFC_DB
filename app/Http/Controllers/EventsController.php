<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;

class EventsController extends Controller
{
    public function all()
    {
        $data['items'] = Event::with("users", "payments")->get();
        $data['title'] = "Groups";
        $data['subTitle'] = "Check all Created Events";
        $data['cols'] = ['Date', 'Event', 'Price', 'Subscribers', 'Paid', 'Delete', 'Comment'];
        $data['atts'] = [
            ['date'         =>      ['att' => 'EVNT_DATE']],
            ['dynamicUrl'   =>      ['att' => 'EVNT_NAME', '0' => 'events/', 'val' => 'id']],
            'EVNT_PRCE',
            ['sumForeign'   =>      ['rel' => 'payments', 'att' => 'EVPY_AMNT']],
            ['countForeign' =>      ['rel' => 'users']],
            ['del' => ['url' => 'events/delete/', 'att' => 'id']],
        ];

        return view('events.show', $data);
    }

    public function details($id)
    {
        $event = Event::with("users", "payments")->findOrFail($id);
        $data['attendance']     = $event->getEventAttendance();
        $data['users']          = User::all();
        return view('events.details', $data);
    }

    public function attachUsers(Request $request){
        $request->validate([
            "eventID"
        ]);

        $event = Event::findOrFail($request->eventID);
        $event->users()->attach($request->users);
        return back();
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

    public function add()
    {
        $data['insertURL'] = "events/insert";
        return view('events.add', $data);
    }

    public function delete($id){
        $event = Event::findOrFail($id);
        $event->deleteAll();
        return redirect('events/all');
    }

}
