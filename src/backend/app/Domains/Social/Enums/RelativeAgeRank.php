<?php

namespace App\Domains\Social\Enums;

enum RelativeAgeRank: string
{
    case YOUTH = 'youth';         // Thiếu niên (under 20)
    case JUNIOR = 'junior';       // Tiểu bối (20-40)
    case MATURE = 'mature';       // Trung niên (40-60)
    case SENIOR = 'senior';       // Tiền bối (60-100)
    case ANCIENT = 'ancient';     // Lão quái (>100)
}
