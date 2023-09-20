<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BalancePayment;
use App\Models\Event;
use App\Models\EventPayment;
use App\Models\Group;
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
        $data['items'] = User::due()->get();
        $data['title'] = "Payments Due -- Total: " . $data['items']->sum('balance');
        $data['subTitle'] = "Check total due amounts";
        $data['cols'] = ['User', 'due'];
        $data['atts'] = [
            ['attUrl' => ['url' => 'users/profile', 'urlAtt' => 'ATND_USER_ID', 'shownAtt' => 'USER_NAME']],
            'balance'
        ];

        return view('payments.show', $data);
    }

    public function queryPage()
    {
        $data['users'] = User::orderByRaw("ABS(USER_CODE), USER_CODE")->get();
        $data['formTitle'] = "Payments Report";
        $data['formURL'] = "payments/query";
        return view('payments.query', $data);
    }

    public function groupQueryPage()
    {
        $data['groups'] = Group::all();
        $data['formTitle'] = "Payments Report by group";
        $data['formURL'] = "payments/query";
        return view('payments.query_group', $data);
    }

    public function queryRes(Request $request)
    {
        $request->validate([
            "userID" => 'required_if:groupID,null',
            "groupID" => 'required_if:userID,null',
            "fromDate" => 'required',
            "toDate" => 'required'
        ]);

        $this->initPaymentsArray($request->fromDate, $request->toDate, $request->userID, $request->groupID, $request->onlySettlment ?? false);
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
            "eventID" => 'required_if:type,2',
            "type"  => "required"
        ]);

        /** @var User */
        $user = User::findOrFail($request->userID);

        if ($request->type == 1) {
            //normal monthly payment
            $res = $user->addToBalance($request->amount, "User In", "User Balance Payment In", $request->note);
            return redirect('payments/show');
        } elseif ($request->type == 2) {
            //event payment
            $res = $user->payEvent($request->eventID, $request->amount, $request->eventState, $request->note);
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

    private function initPaymentsArray($startDate, $endDate, $userID = 0, $groupID = null, $only_settlment = false)
    {
        $startDate = new DateTime($startDate);
        $endDate = new DateTime($endDate);


        $endDate = $endDate->setTime(23, 59, 59);
        $paymentQuery = BalancePayment::with('app_user')->whereBetween("created_at", [$startDate, $endDate]);

        if ($userID != 0)
            $paymentQuery = $paymentQuery->where('app_users_id', $userID);

        if ($groupID != null) {
            $group = Group::findOrFail($groupID);
            $paymentQuery = $paymentQuery->join('app_users', 'app_users.id', '=', 'app_users_id')
                ->join('groups', 'groups.id', '=', 'USER_GRUP_ID')
                ->where('USER_GRUP_ID', $groupID);
        }

        if ($groupID != null) {
            $userName = $group->GRUP_NAME;
        } else if ($userID == 0) {
            $userName = "All Users";
        } else {
            $user = User::findOrFail($userID);
            $userName = $user->USER_NAME;
        }

        if ($only_settlment) {
            $paymentQuery->where('is_settlment', true);
        } else {
            $paymentQuery->where('is_settlment', false);
        }

        $userTitle = "Showing Balance Payments for " . $userName;

        $this->data['items'] = $paymentQuery->get();
        $this->data['title'] =  "Payments Report -- Total: " . $this->data['items']->sum('value');
        $this->data['subTitle'] = $userTitle . " From "  . $startDate->format('Y-F-d') . " to " . $endDate->format('Y-F-d');
        $this->data['cols'] = ['User', 'Date', 'Amount', 'Note', 'Collector'];
        $this->data['atts'] = [
            ['foreignUrl' => ['users/profile', 'app_users_id', 'app_user', 'USER_NAME']],
            ['date' => ['att' => 'created_at', 'format' => 'Y-m-d']],
            'value',
            ['comment' => ['att' => 'note']],
            ['foreignUrl' => ['users/profile', 'collected_by', 'collected_by_user', 'USER_NAME']],
        ];
    }
}
