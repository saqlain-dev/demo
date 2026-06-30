<?php

namespace App\Enum;

use App\Traits\EnumToArray;

enum PerformanceFactorType: int {

    use EnumToArray;
    case EffectivelyManagingProgramActivities = 1;
    case TeamManagement = 2;
    case ResourceUtilization = 3;
    case RiskManagement = 4;
    case MonitoringEvaluation = 5;
    case StrategicThinking = 6;
    case BusinessDevelopment = 7;

}


