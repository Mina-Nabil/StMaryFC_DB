<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
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

        $this->initPaymentsArray($request->fromDate, $request->toDate, $request->userID);
        return view('payments.show', $this->data);
    }

    public function addPayment()
    {
        $data['users'] = User::orderByRaw("ABS(USER_CODE), USER_CODE")->get();
        $data['formTitle'] = "Add New Payment";
        $data['formURL'] = url("payments/insert");
        return view('payments.add', $data);
    }

    public function delete($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->delete();
        return back();
    }

    public function insert(Request $request)
    {
        $request->validate([
            "userID" => 'required|exists:app_users,id',
            "date" => 'required',
            "amount" => 'required'
        ]);
        DB::transaction(function () use ($request) {
            Payment::insertPayment($request->date, $request->userID, $request->amount, $request->note);
            $startDate =  (new DateTime($request->date))->format('Y-m-01');
            $endDate =  (new DateTime($request->date))->format('Y-m-t');
            Attendance::setPaid($request->userID, $startDate, $endDate);
        });
        return redirect('payments/show');
    }

    public function getUnpaidDays($userID)
    {
        $days = Attendance::getUnpaidDays($userID);
        echo json_encode($days, JSON_UNESCAPED_UNICODE);
    }


    //////data array
    protected $data;

    private function initPaymentsArray($startDate, $endDate, $userID = 0)
    {
        $startDate = new DateTime($startDate);
        $endDate = new DateTime($endDate);
        $paymentQuery = Payment::with('user')->whereBetween("PYMT_DATE", [$startDate, $endDate]);
        if ($userID != 0)
            $paymentQuery = $paymentQuery->where('PYMT_USER_ID', $userID);

        if ($userID == 0) {
            $userTitle = "All Users";
        } else {
            $user = User::findOrFail($userID);
            $userTitle = $user->USER_NAME . '\' Payments ';
        }
        $this->data['items'] = $paymentQuery->get();
        $this->data['title'] =  "Payments Report -- Total: " . $this->data['items']->sum('PYMT_AMNT');
        $this->data['subTitle'] = $userTitle . " From "  . $startDate->format('Y-F-d') . " to " . $endDate->format('Y-F-d');
        $this->data['cols'] = ['User', 'For', 'Amount', 'Note', 'Delete'];
        $this->data['atts'] = [
            ['foreignUrl' => ['users/profile', 'PYMT_USER_ID', 'user', 'USER_NAME']], 
            'PYMT_DATE', 
            'PYMT_AMNT', 
            ['comment' => ['att' => 'PYMT_NOTE']],
            ['del' => ['url' => 'payments/delete/', 'att' => 'id']]
        ];
    }
}
