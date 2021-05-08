<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\EventPayment;
use App\Models\Payment;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentsController extends Controller
{

    public function month()
    {
        $now = new DateTime();
        $startDate =  $now->format('Y-m-01');
        $endDate =  $now->format('Y-m-t');
        $this->initPaymentsArray($startDate, $endDate);

        return view('payments.show', $this->data);
    }

    public function due()
    {
        $data['items'] = Attendance::getDuePayments();
        $data['title'] = "Payments Due";
        $data['subTitle'] = "Check payments to be collected";
        $data['cols'] = ['User', 'Month'];
        $data['atts'] = [['attUrl' => ['url' => 'users/profile', 'urlAtt' => 'ATND_USER_ID', 'shownAtt' => 'USER_NAME']], 'paymentDue'];

        return view('payments.show', $data);
    }

    public function queryPage()
    {
        $data['users'] = User::orderByRaw("ABS(USER_CODE), USER_CODE")->get();
        $data['formTitle'] = "Payments Report";
        $data['formURL'] = "payments/query";
        return view('payments.query', $data);
    }

    public function queryRes(Request $request)
    {
        $request->validate([
            "userID" => 'required',
            "fromDate" => 'required',
            "toDate" => 'required'
        ]);

        $isDate = $request->isDate ? 1 : 0;

        $this->initPaymentsArray($request->fromDate, $request->toDate, $request->userID, $isDate);
        return view('payments.show', $this->data);
    }

    public function addPayment()
    {
        $data['users'] = User::orderByRaw("ABS(USER_CODE), USER_CODE")->get();
        $data['events'] = Event::all();
        $data['formTitle'] = "Add New Payment";
        $data['formURL'] = url("payments/insert");
        return view('payments.add', $data);
    }

    public function delete($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->refund();
        return back();
    }

    public function insert(Request $request)
    {
        $request->validate([
            "userID" => 'required|exists:app_users,id',
            "amount" => 'required',
            "date" => 'required_if:type,1',
            "eventID" => 'required_if:type,2',
            "type"  => "required"
        ]);


        if ($request->type == 1) {
            //normal monthly payment
            $res = Payment::addPayment($request->userID, $request->amount, $request->date, $request->note);
            return redirect('payments/show');
        } elseif ($request->type == 2) {
            //event payment
            echo EventPayment::addPayment($request->userID, $request->eventID, $request->amount);
            if ($request->return)
                return back();
        }
    }

    public function getUnpaidDays($userID)
    {
        $days = Attendance::getUnpaidDays($userID);
        echo json_encode($days, JSON_UNESCAPED_UNICODE);
    }


    //////data array
    protected $data;

    private function initPaymentsArray($startDate, $endDate, $userID = 0, $isDate = 0)
    {
        $startDate = new DateTime($startDate);
        $endDate = new DateTime($endDate);
        if ($isDate == 0) {
            $paymentQuery = Payment::with('user')->whereBetween("PYMT_DATE", [$startDate, $endDate]);
        } else {
            $paymentQuery = Payment::with('user')->whereBetween("created_at", [$startDate, $endDate]);
        }
        if ($userID != 0)
            $paymentQuery = $paymentQuery->where('PYMT_USER_ID', $userID);

        if ($userID == 0) {
            $userName = "All Users";
        } else {
            $user = User::findOrFail($userID);
            $userName = $user->USER_NAME;
        }

        if ($isDate == 0) {
            $userTitle = "Showing Payments for " . $userName . " Filtered by Due Date ";
        } else {
            $userTitle = "Showing Payments for " . $userName . " Filtered by Creation Date ";
        }
        $this->data['items'] = $paymentQuery->get();
        $this->data['title'] =  "Payments Report -- Total: " . $this->data['items']->sum('PYMT_AMNT');
        $this->data['subTitle'] = $userTitle . " From "  . $startDate->format('Y-F-d') . " to " . $endDate->format('Y-F-d');
        $this->data['cols'] = ['User', 'Due', 'Amount', 'Note', 'Date', 'Delete'];
        $this->data['atts'] = [
            ['foreignUrl' => ['users/profile', 'PYMT_USER_ID', 'user', 'USER_NAME']],
            'PYMT_DATE',
            'PYMT_AMNT',
            ['comment' => ['att' => 'PYMT_NOTE']],
            'created_at',
            ['del' => ['url' => 'payments/delete/', 'att' => 'id']]
        ];
    }
}
