<?php

namespace App\Models;

use App\Providers\WhatsappServiceProvider;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\HasApiTokens;
use Twilio\TwiML\Voice\Pay;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = "app_users";
    public $timestamps = false;

    //functions
    public function addToBalance($value, $title, $desc = null, $isSettlment = false)
    {
        $oldBalance = 0;
        $user = Auth::user();
        $lastPayment = $this->balance_payments()->latest()->first();

        if ($lastPayment != null) $oldBalance = $lastPayment->new_balance;
        try {
            DB::transaction(function () use ($value, $title, $desc, $oldBalance, $user, $isSettlment) {
                $this->balance = $oldBalance + $value;
                /** @var BalancePayment */
                $balanceRec = $this->balance_payments()->create([
                    "collected_by"  =>  $user ? $user->id : null,
                    "value"         =>  $value,
                    "new_balance"   =>  $oldBalance + $value,
                    "title"         =>  $title,
                    "is_settlment"  =>  $isSettlment,
                    "desc"          =>  $desc,
                ]);
                $this->save();
                $balanceRec->sendSms();
            });
            return true;
        } catch (Exception $e) {
            report($e);
            return false;
        }
    }

    public function sendBalanceReminder()
    {
        try {
            $latestPayment = $this->balance_payments()->orderByDesc('id')->first();
            $now = new Carbon($this->created_at);
            $balance = $latestPayment ? $latestPayment->new_balance : 0;
            $firstName = explode(' ', $this->USER_NAME)[0];
            $msg = "Reminder
            Dear $firstName's Parent,
            We kindly remind you that your current balance is $balance EGP 
            Thank you";

            Payment::sendSMS($this->USER_MOBN, $msg);

            return true;
        } catch (Exception $e) {
            report($e);
            return false;
        }
    }

    public function sendLastUpdate()
    {
        try {
            $latestPayment = $this->balance_payments()->orderByDesc('id')->first();
            return $latestPayment?->sendSms();
        } catch (Exception $e) {
            report($e);
            return false;
        }
    }

    public function getReminder()
    {
        try {
            $latestPayment = $this->balance_payments()->orderByDesc('id')->first();
            $now = new Carbon($this->created_at);
            $balance = $latestPayment ? $latestPayment->new_balance : 0;
            $firstName = explode(' ', $this->USER_NAME)[0];
            $msg = "               REMINDER    
............................................

Dear $firstName 's  Parent,
we kindly remind you
that your current
balance is  $balance EGP";

            return $msg;
        } catch (Exception $e) {
            report($e);
            return false;
        }
    }

    public function getLastUpdate()
    {
        try {
            $latestPayment = $this->balance_payments()->orderByDesc('id')->first();
            return $latestPayment?->getSms();
        } catch (Exception $e) {
            report($e);
            return false;
        }
    }

    public function sendSMS($msg)
    {
        Payment::sendSMS($this->USER_MOBN, $msg);
        return true;
    }

    public function payEvent($event_id, $amount, $eventState, $note)
    {
        try {
            DB::transaction(function () use ($event_id, $amount, $eventState, $note) {
                /** @var Event */
                $event = Event::findOrFail($event_id);
                EventPayment::addPayment($this->id, $event->id, $amount);

                $this->addToBalance(-1 * $amount, "($event->EVNT_NAME) Subsc.", $note, false);

                if (isset($eventState) && $eventState > 0) {
                    Event::attachUser($event->id, $this->id, $eventState);
                }
            });
            return true;
        } catch (Exception $e) {
            report($e);
            return false;
        }
    }
    //scopes
    public function scopePlayers($query)
    {
        return $query->where('USER_USTP_ID', 2);
    }
    public function scopeCoachesAndAdmins($query)
    {
        return $query->whereIn('USER_USTP_ID', [1, 3]);
    }

    public function scopeDue($query, $group_id = null)
    {
        $query->when($group_id, function ($q, $g){
            $q->where('USER_GRUP_ID', "=" ,$g);
        });
        return $query->where('balance', '!=', 0);
    }


    //relations
    public function images()
    {
        return $this->hasMany('App\Models\UserImage', "USIM_USER_ID");
    }
    public function mainImage()
    {
        return $this->hasOne('App\Models\UserImage', 'id', "USER_MAIN_IMGE");
    }

    public function type()
    {
        return $this->belongsTo('App\Models\UserType', 'USER_USTP_ID');
    }

    public function group()
    {
        return $this->belongsTo('App\Models\Group', 'USER_GRUP_ID');
    }

    public function attendance()
    {
        return $this->hasMany('App\Models\Attendance', "ATND_USER_ID");
    }

    public function balance_payments(): HasMany
    {
        return $this->hasMany(BalancePayment::class, "app_users_id");
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, "PYMT_USER_ID");
    }

    public function player_category()
    {
        return $this->belongsTo(PlayersCatogory::class, 'players_category_id');
    }

    public function eventPayments()
    {
        return $this->hasMany('App\Models\EventPayment', "EVPY_USER_ID");
    }

    public function getLatestAttendance()
    {
        return Attendance::where('ATND_USER_ID', $this->id)->orderByDesc('ATND_DATE')->limit(200)->get();
    }

    public function getAttendedYears()
    {
        return Attendance::where('ATND_USER_ID', $this->id)->selectRaw("DISTINCT YEAR(ATND_DATE) as year")->get();
    }

    public function monthlyAttendance()
    {
        return $this->attendance()->whereRaw("MONTH(ATND_DATE) = MONTH(CURDATE()) AND YEAR(ATND_DATE) = YEAR(ATND_DATE) ")->count();
    }

    public function monthlyPayment()
    {
        return $this->payments()->whereRaw("MONTH(PYMT_DATE) = MONTH(CURDATE()) AND YEAR(PYMT_DATE) = YEAR(PYMT_DATE) ")->sum('PYMT_AMNT');
    }

    public function getLatestPayments($months = 1)
    {
        return $this->payments()->whereRaw(" PYMT_DATE >= DATE_SUB(NOW(),  Interval {$months} Month) ")
            ->groupByRaw(" OVRV_MNTH ")->groupByRaw(" OVRV_YEAR ")->selectRaw(" MONTH(PYMT_DATE) as OVRV_MNTH , YEAR(PYMT_DATE) as OVRV_YEAR, SUM(PYMT_AMNT) as OVRV_PAID ")->get();
    }

    public function getOverviewAttendance($months = 1)
    {
        return $this->attendance()->whereRaw(" ATND_DATE >= DATE_SUB(NOW(),  Interval {$months} Month) ")
            ->groupByRaw(" OVRV_MNTH ")->groupByRaw(" OVRV_YEAR ")->selectRaw(" MONTH(ATND_DATE) as OVRV_MNTH , YEAR(ATND_DATE) as OVRV_YEAR, COUNT(attendance.id) as OVRV_ATND ")->get();
    }

    public static function overviewQuery($from, $to)
    {
        return DB::table("app_users", "t1")->join("groups", "groups.id", '=', 'USER_GRUP_ID')->select("t1.*", 'groups.GRUP_NAME')
            ->selectRaw(" (SELECT SUM(PYMT_AMNT) from payments where PYMT_USER_ID = t1.id and PYMT_DATE >= '{$from}' AND PYMT_DATE <= '{$to}') as TotalPaid ")
            ->selectRaw(" (SELECT COUNT(id)    from attendance where ATND_USER_ID = t1.id and ATND_DATE >= '{$from}' AND ATND_DATE <= '{$to}') as A ")->get();
    }

    public function setReminderDate(DateTime $date = null)
    {
        $this->USER_LTST_RMDR = $date ? $date->format("Y-m-d H:i:s") : date("Y-m-d H:i:s");
        $this->save();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'USER_NAME', 'USER_USTP_ID', 'USER_PASS', 'USER_MAIL', 'USER_MAIN_IMGE', 'USER_FACE_ID', 'USER_CLASS_NAME', 'USER_BDAY'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'USER_PASS'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'USER_LTST_RMDR' => 'datetime:Y-m-d',
        'USER_BDAY' => 'datetime:Y-m-d',
    ];

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'USER_MAIL';
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->USER_PASS;
    }

    // this is a recommended way to declare event handlers
    public static function boot()
    {
        parent::boot();

        static::deleting(function ($user) { // before delete() method call this
            $user->USER_MAIN_IMGE = NULL;
            $user->save();
            $user->attendance()->delete();
            $user->balance_payments()->delete();
            $user->payments()->delete();
            $user->eventPayments()->delete();
            $user->images()->delete();
            // do the rest of the cleanup...
        });
    }
}
