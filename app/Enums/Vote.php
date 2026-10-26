<?php

namespace App\Enums;

enum Vote: string
{
    case Like = 'like';
    case Dislike = 'dislike';
}
