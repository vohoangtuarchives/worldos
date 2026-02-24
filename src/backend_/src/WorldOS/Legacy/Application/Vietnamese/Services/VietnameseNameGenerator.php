<?php

namespace WorldOS\Legacy\Application\Vietnamese\Services;

class VietnameseNameGenerator
{
    private array $surnames = [
        'Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Phan', 'Vũ', 'Đặng', 'Bùi', 'Đỗ',
        'Hồ', 'Ngô', 'Dương', 'Lý', 'Vương', 'Trịnh', 'Đinh', 'Lâm', 'Mai', 'Cao',
        'Đoàn', 'Hà', 'Luân', 'Khương', 'Quách', 'Tiêu', 'Châu', 'Mã'
    ];

    private array $middleNamesMale = [
        'Văn', 'Hữu', 'Đức', 'Công', 'Quang', 'Minh', 'Quốc', 'Gia', 'Bảo', 'Thế',
        'Tấn', 'Mạnh', 'Hoàng', 'Hùng', 'Trọng', 'Tuấn', 'Thành', 'Vĩnh', 'Xuân', 'Thái',
        'Việt', 'Đình', 'Chí', 'Thiện', 'Duy', 'Ngọc'
    ];

    private array $middleNamesFemale = [
        'Thị', 'Ngọc', 'Kim', 'Thu', 'Phương', 'Thảo', 'Thanh', 'Tuyết', 'Hồng', 'Mai',
        'Hương', 'Lan', 'Vân', 'Mỹ', 'Ánh', 'Diệu', 'Linh', 'Quỳnh', 'Trúc', 'Yến'
    ];

    private array $givenNames = [
        // Powerful Hán Việt names
        'Long' => 'Rồng', 'Lân' => 'Kỳ Lân', 'Quy' => 'Rùa', 'Phụng' => 'Phượng Hoàng',
        'Hùng' => 'Anh Hùng', 'Dũng' => 'Dũng Cảm', 'Cường' => 'Mạnh Mẽ', 'Tráng' => 'Tráng Lệ',
        'Sơn' => 'Núi', 'Hải' => 'Biển', 'Phong' => 'Gió', 'Vũ' => 'Mưa', 'Lôi' => 'Sấm',
        'Điện' => 'Chớp', 'Thiên' => 'Trời', 'Địa' => 'Đất', 'Nhân' => 'Người', 'Tâm' => 'Lòng',
        'Trí' => 'Trí Tuệ', 'Tín' => 'Tin Cậy', 'Nghĩa' => 'Nghĩa Khí', 'Lễ' => 'Lễ Nghi',
        'Bình' => 'Bình An', 'An' => 'Yên Ổn', 'Khang' => 'Khỏe Mạnh', 'Ninh' => 'Yên Vui',
        'Phúc' => 'Hạnh Phúc', 'Lộc' => 'Tài Lộc', 'Thọ' => 'Sống Lâu', 'Toàn' => 'Trọn Vẹn',
        'Kiệt' => 'Tuấn Kiệt', 'Vỹ' => 'Vĩ Đại', 'Hào' => 'Hào Kiệt', 'Quân' => 'Vua/Quân Tử',
        'Dương' => 'Mặt Trời', 'Nhật' => 'Mặt Trời', 'Nguyệt' => 'Mặt Trăng', 'Tinh' => 'Sao',
        'Bách' => 'Trăm', 'Vạn' => 'Mười Ngàn', 'Thành' => 'Thành Công', 'Đạt' => 'Đạt Được',
        'Chiến' => 'Chiến Đấu', 'Thắng' => 'Chiến Thắng', 'Công' => 'Công Lao', 'Danh' => 'Danh Tiếng',
        'Quyền' => 'Quyền Lực', 'Lực' => 'Sức Mạnh', 'Uy' => 'Uy Nghi', 'Đạo' => 'Đạo Lý',
        'Kình' => 'Cá Kình', 'Bằng' => 'Chim Bằng', 'Kha' => 'Mạnh Mẽ', 'Triết' => 'Triết Lý'
    ];

    public function generateName(string $gender = 'male'): array
    {
        $ho = $this->surnames[array_rand($this->surnames)];
        
        $middles = $gender === 'female' ? $this->middleNamesFemale : $this->middleNamesMale;
        $dem = $middles[array_rand($middles)];

        $ten = array_rand($this->givenNames);
        $meaning = $this->givenNames[$ten];

        // Ensure "Hán Việt" feel: Avoid "Nguyễn Văn A". Prefer "Nguyễn Quốc A" or "Trần Gia A".
        // 20% chance for double middle name (4-word name)
        if (rand(1, 100) <= 20) {
            $dem2 = $middles[array_rand($middles)];
            if ($dem !== $dem2) {
                $dem .= ' ' . $dem2;
            }
        }

        return [
            'full_name' => "{$ho} {$dem} {$ten}",
            'meaning' => "{$ho} {$dem} {$ten} ({$meaning})",
            'parts' => compact('ho', 'dem', 'ten')
        ];
    }

    public function generateTitle(string $archetype): string
    {
        $titles = [
            'LEGENDARY_GENERAL' => ['Đại Tướng Quân', 'Thượng Tướng', 'Đại Nguyên Soái', 'Đô Đốc'],
            'FOUNDING_KING' => ['Thái Tổ', 'Tiên Đế', 'Khai Quốc Công Thần', 'Vua'],
            'CULTURAL_HERO' => ['Đại Học Sĩ', 'Trạng Nguyên', 'Tiên Sinh', 'Quốc Sư'],
            'REBEL_LEADER' => ['Thủ Lĩnh', 'Nghĩa Quân', 'Đại Vương', 'Chủ Tướng'],
            'PHILOSOPHER_KING' => ['Minh Quân', 'Thánh Đế', 'Hiền Triết'],
            'WISE_QUEEN' => ['Hoàng Hậu', 'Thái Hậu', 'Nữ Vương']
        ];

        if (isset($titles[$archetype])) {
            return $titles[$archetype][array_rand($titles[$archetype])];
        }

        return 'Vị Anh Hùng';
    }
}
