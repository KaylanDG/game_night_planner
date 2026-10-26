<?php

namespace App\Enums;

enum SessionStatus: string
{
    case Planned = 'planned';
    case Completed = 'completed';
}
