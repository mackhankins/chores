<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class SmsService
{
    protected ?Client $client = null;

    public function send(string $to, string $message): void
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.auth_token');
        $messagingServiceSid = config('services.twilio.messaging_service_sid');

        if (! $sid || ! $token || ! $messagingServiceSid) {
            Log::warning('Twilio credentials not configured, skipping SMS.', [
                'to' => $to,
                'message' => $message,
            ]);

            return;
        }

        $this->client ??= new Client($sid, $token);

        $result = $this->client->messages->create($to, [
            'messagingServiceSid' => $messagingServiceSid,
            'body' => $message,
        ]);

        Log::info('Twilio SMS sent', [
            'to' => $to,
            'sid' => $result->sid,
            'status' => $result->status,
            'errorCode' => $result->errorCode,
            'errorMessage' => $result->errorMessage,
        ]);
    }
}
