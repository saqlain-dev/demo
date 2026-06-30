<?php

namespace App\Enum;

use App\Traits\EnumToArray;

enum RmMethodology: int {

    use EnumToArray;
    case Quantitative = 1;
    case Qualitative = 2;
    case MixedMethodsStudy = 3;
    case InDepthInterviews = 4;
    case DeskReview = 5;
    case CaseFileAnalysis = 6;
    case SecondaryLiteratureAnalysis = 7;
    case Others = 8;

}


