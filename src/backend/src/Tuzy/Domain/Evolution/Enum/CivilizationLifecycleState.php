<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Enum;

enum CivilizationLifecycleState: string
{
    case EMERGENCE = 'emergence';   // Giai đoạn hình thành
    case GROWTH = 'growth';         // Phát triển
    case STRESS = 'stress';         // Bắt đầu bất ổn
    case CRISIS = 'crisis';         // Khủng hoảng
    case COLLAPSE = 'collapse';     // Đang sụp đổ/Nỗ lực tái sinh
    case EXTINCT = 'extinct';       // Diệt vong hoàn toàn
    case DORMANT = 'dormant';       // Trạng thái tiềm sinh, chờ khởi đầu mới
}
