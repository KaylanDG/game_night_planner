<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case Going = 'going';
    case NotGoing = 'not_going';

    public function label(): string
    {
        return match ($this) {
            self::Going => 'Going',
            self::NotGoing => "Can't make it",
        };
    }
}
