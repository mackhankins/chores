<?php

namespace App\Enums;

enum Carrier: string
{
    case Verizon = 'verizon';
    case ATT = 'att';
    case TMobile = 'tmobile';
    case Sprint = 'sprint';

    public function smsGatewayDomain(): string
    {
        return match ($this) {
            self::Verizon => 'vtext.com',
            self::ATT => 'txt.att.net',
            self::TMobile => 'tmomail.net',
            self::Sprint => 'messaging.sprintpcs.com',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Verizon => 'Verizon',
            self::ATT => 'AT&T',
            self::TMobile => 'T-Mobile',
            self::Sprint => 'Sprint',
        };
    }
}
