<?php

namespace App\Helpers;

class AvatarHelper
{
    public static function emoji($gender)
    {
        return $gender === 'F' ? '👩' : ($gender === 'M' ? '👨' : '👤');
    }
}
