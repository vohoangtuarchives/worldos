<?php

namespace App\Domains\Social\Enums;

enum SocialStatus: string
{
    case COMMONER = 'commoner';     // Phàm nhân / Đệ tử ngoại môn
    case ELITE = 'elite';           // Đệ tử nội môn / Chân truyền
    case AUTHORITY = 'authority';   // Trưởng lão / Hộ pháp
    case SOVEREIGN = 'sovereign';   // Chưởng môn / Tông chủ
}
