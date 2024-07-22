<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BalancePayment extends Model
{
    use HasFactory;

    protected $table = 'balance_payments';
    protected $fillable = [
        'app_users_id',
        'collected_by',
        'is_settlment', 'value',
        'new_balance', 'title', 'desc'
    ];

    public function getSms()
    {
        return self::sendSms(true);
    }

    public function sendSms($return_text_only = false)
    {

        $now = new Carbon($this->created_at);
        $this->loadMissing('app_user');
        $oldBalance = $this->new_balance - $this->value;
        $is_monthly_balance_update = str_contains($this->title, "Atnd");
        $is_new_payment = str_contains($this->title, "New Payment");
        $messageTitle = $this->is_settlment ? "Settlment"  :(  $is_monthly_balance_update ? $now->subMonth()->shortEnglishMonth . " Report": (($is_new_payment) ? "Payment Receipt" :
            $this->title));
        if (str_contains($this->title, "Atnd")) {
            $this->title = str_replace('Atnd', 'Attendance', $this->title);
        }
        $valueText = str_replace("-", "- ", $this->value);
        $balanceText = str_replace("-", "- ", $this->new_balance);
        $oldBalanceText = str_replace("-", "- ", $oldBalance);
        $msg = ".     $messageTitle

          *{$this->app_user->USER_NAME}*
............................................

Old Balance          {$oldBalanceText} EGP

{$this->title}       {$valueText} EGP
............................................
*New* *Balance*          *{$balanceText}* *EGP*";

        if ($is_new_payment) {
            $msg .= "            
            
            
............................................
            THANK YOU";
        }
        Log::info($msg);
        if ($return_text_only) return $msg;
        return Payment::sendSMS($this->app_user->USER_MOBN, $msg);
    }

    //scopes
    public function scopeByUser($query, $id)
    {
        return $query->where('app_users_id', $id);
    }

    ////relations
    public function app_user(): BelongsTo
    {
        return $this->belongsTo(User::class, "app_users_id");
    }
    public function collected_by_user(): BelongsTo
    {
        return $this->belongsTo(User::class, "collected_by");
    }
}
