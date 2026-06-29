<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    protected $table = 'message_templates';

    protected $fillable = [
        'name', 'key', 'body', 'is_system', 'is_active'
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * The placeholder keys supported in template bodies, with a human description
     * and whether the key is resolvable from the per-user app context (SendSMS picker).
     * Single source of truth for the admin legend.
     */
    public static function keys(): array
    {
        return [
            ['key' => '{{user_name}}',    'desc' => "Player's full name",                            'app' => true],
            ['key' => '{{first_name}}',   'desc' => "Player's first name (first word of the name)",   'app' => true],
            ['key' => '{{balance}}',      'desc' => 'Current / remaining balance in EGP',             'app' => true],
            ['key' => '{{new_balance}}',  'desc' => 'Balance after the transaction',                  'app' => true],
            ['key' => '{{old_balance}}',  'desc' => 'Balance before the transaction',                 'app' => true],
            ['key' => '{{value}}',        'desc' => 'Transaction amount (payment or charge)',         'app' => true],
            ['key' => '{{title}}',        'desc' => 'Transaction title / description',                'app' => true],
            ['key' => '{{report_month}}', 'desc' => 'Month name for monthly reports (e.g. October)',  'app' => false],
            ['key' => '{{month}}',        'desc' => 'Month-year of a payment (e.g. Oct-2026)',         'app' => false],
        ];
    }

    /**
     * Canonical default body for every system scenario, keyed by slug. This mirrors the original
     * hardcoded strings (with values swapped for {{keys}}) and is the single source used by both the
     * seeder and the send-path fallback, so the two can never drift.
     */
    public static function systemDefaults(): array
    {
        $dots = str_repeat('.', 50);
        $receipt = function ($messageTitle, $suffix = '') use ($dots) {
            return ".           *{$messageTitle}*\n\n          *{{user_name}}*\n{$dots}\n\nOld Balance             {{old_balance}} EGP\n\nFees          {{value}} EGP\n{$dots}\n*New* *Balance*          *{{new_balance}}* *EGP*" . $suffix;
        };
        $thankYou = "            \n            \n            \n{$dots}\n                   THANK YOU";

        return [
            'balance_reminder_whatsapp' => [
                'name' => 'Balance Reminder (WhatsApp)',
                'body' => "\nDear {{first_name}} 's  Parent,\nkindly settle the outstanding \nbalance of *{{balance}} EGP*",
            ],
            'balance_reminder_sms' => [
                'name' => 'Balance Reminder (SMS)',
                'body' => "Reminder\n            Dear {{first_name}}'s Parent,\n            We kindly remind you that your current balance is {{balance}} EGP \n            Thank you",
            ],
            'receipt_new_payment' => [
                'name' => 'Receipt - New Payment',
                'body' => $receipt('Payment Receipt', $thankYou),
            ],
            'receipt_attendance' => [
                'name' => 'Receipt - Attendance / Monthly',
                'body' => $receipt('{{report_month}} Report'),
            ],
            'receipt_settlement' => [
                'name' => 'Receipt - Settlement',
                'body' => $receipt('Settlment'),
            ],
            'receipt_generic' => [
                'name' => 'Receipt - Other',
                'body' => $receipt('{{title}}'),
            ],
            'payment_received' => [
                'name' => 'Payment Received (SMS)',
                'body' => "St. Mary Rehab Football Academy \n{{user_name}} \nPayment Received {{value}} LE \n{{month}}\n        \n        Thank you",
            ],
            'payment_refund' => [
                'name' => 'Payment Refund (SMS)',
                'body' => "[REFUND] \n{{user_name}} \nRefund {{value}} LE \n{{month}}\n        \n        Thank you",
            ],
        ];
    }

    public static function defaultBody(string $key): string
    {
        return static::systemDefaults()[$key]['body'] ?? '';
    }

    /**
     * Replace placeholders in the body. $data maps '{{key}}' => value (already formatted by caller).
     */
    public function render(array $data): string
    {
        return strtr($this->body, $data);
    }

    /**
     * Build the per-user placeholder data from the user + their latest balance record.
     * Used by the app render endpoint and any user-context custom template.
     */
    public static function dataForUser(User $user): array
    {
        $latest = $user->balance_payments()->orderByDesc('id')->first();
        $firstName = explode(' ', $user->USER_NAME)[0];
        $balance = $latest ? $latest->new_balance : 0;
        $value = $latest ? $latest->value : 0;
        $oldBalance = $latest ? $latest->new_balance - $latest->value : 0;
        $title = $latest ? $latest->title : '';

        return [
            '{{user_name}}'   => $user->USER_NAME,
            '{{first_name}}'  => $firstName,
            '{{balance}}'     => $balance,
            '{{new_balance}}' => $balance,
            '{{old_balance}}' => $oldBalance,
            '{{value}}'       => $value,
            '{{title}}'       => $title,
        ];
    }

    /**
     * Active body for a system scenario key, or the supplied fallback (original hardcoded text)
     * when the row is missing/inactive. Safety net so an un-seeded row never breaks a send.
     */
    public static function bodyByKey(string $key, string $fallback): string
    {
        $template = static::where('key', $key)->where('is_active', true)->first();
        return $template ? $template->body : $fallback;
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->is_system ? 'System' : 'Custom';
    }
}
