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

    public function sendSms()
    {
        if ($this->is_settlment) return;

        $now = new Carbon($this->created_at);
        $this->loadMissing('app_user');
        $oldBalance = $this->new_balance - $this->value;
        $is_monthly_balance_update = str_contains($this->title, "Atnd");
        $is_new_payment = str_contains($this->title, "New Payment");
        $messageTitle = $is_monthly_balance_update ? str_replace("Atnd", "Attendance", $this->title) : (($is_new_payment) ? str_replace("New Payment", "Payment Receipt", $this->title) :
            $this->title);
        $msg = "$messageTitle 
             {$this->app_user->USER_NAME}
        Old Balance       : {$oldBalance}EGP
        {$this->title}    : {$this->value}EGP
                  ------------------------
        New balance       : {$this->new_balance}EGP ";
        if ($is_monthly_balance_update) {
            $msg .= "Till {$now->format('d-M-Y')}";
        }
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
