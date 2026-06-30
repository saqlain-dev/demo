<?php

namespace App\Enum;

use App\Traits\EnumToArray;

enum FormCategory: int {

    use EnumToArray;
    case MnePlan = 1;
    case IndicatorProgress = 2;

}


