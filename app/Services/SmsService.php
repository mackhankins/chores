<?php

namespace App\Services;

use App\Enums\Carrier;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SmsService
{
    /**
     * Send an SMS via the carrier's email-to-SMS gateway.
     *
     * @param  string  $phone  Phone number in +1XXXXXXXXXX format
     */
    public function send(string $phone, string $message, Carrier $carrier): void
    {
        $digits = preg_replace('/\D/', '', $phone);
        $digits = substr($digits, -10);

        $gateway = $digits.'@'.$carrier->smsGatewayDomain();

        Mail::raw($message, function ($mail) use ($gateway) {
            $mail->to($gateway);
            $mail->subject('');
        });

        Log::info('SMS sent via mail-to-SMS gateway', [
            'to' => $phone,
            'gateway' => $gateway,
            'carrier' => $carrier->value,
        ]);
    }
}
