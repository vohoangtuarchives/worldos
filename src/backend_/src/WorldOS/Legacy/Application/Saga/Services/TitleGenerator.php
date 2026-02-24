<?php

namespace WorldOS\Legacy\Application\Saga\Services;

use Illuminate\Support\Collection;

class TitleGenerator
{
    private const TEMPLATES = [
        'WAR' => ['Chiến Hỏa Liên Miên', 'Binh Biến', 'Máu Nhuộm Sa Trường', 'Tiếng Trống Trận'],
        'PEACE' => ['Thái Bình Thịnh Trị', 'Ngày Tháng Êm Đềm', 'Hồi Phục', 'Ánh Sáng Mới'],
        'DISASTER' => ['Thiên Tai Giáng Lâm', 'Đại Địa Rung Chuyển', 'Kiếp Nạn', 'Bóng Tối Bao Trùm'],
        'MYTH' => ['Thần Thoại Tái Hiện', 'Dấu Ấn Thần Linh', 'Lời Tiên Tri', 'Bí Mật Cổ Đại'],
        'CRISIS' => ['Nguy Cơ Tiềm Tàng', 'Sụp Đổ', 'Hỗn Loạn', 'Bước Ngoặt'],
        'DEFAULT' => ['Sóng Gió Nổi Lên', 'Vận Mệnh Xoay Chuyền', 'Những Kẻ Lữ Hành', 'Dòng Chảy Thời Gian'],
    ];

    private const STORY_PREFIXES = [
        'xianxia' => ['Tiên Lộ', 'Đạo Kỷ', 'Vĩnh Hằng', 'Huyền Giới'],
        'survival' => ['Sinh Tồn Ký', 'Tận Thế', 'Hoang Tàn', 'Huyết Lộ'],
        'urban_fantasy' => ['Đô Thị Di Năng', 'Thành Phố Ngầm', 'Bóng Đêm'],
        'historical' => ['Đại Nam Sử Thi', 'Trường Ca', 'Binh Pháp'],
        'mundane' => ['Chuyện Ở', 'Niên Giám', 'Ký Sự'],
        'default' => ['Huyền Sử', 'Biên Niên Ký', 'Thế Giới', 'Sự Trỗi Dậy Của'],
    ];

    public function generateStoryTitle(string $worldName, string $genre = 'default'): string
    {
        $prefixes = self::STORY_PREFIXES[$genre] ?? self::STORY_PREFIXES['default'];
        $prefix = $prefixes[array_rand($prefixes)];
        
        return "$prefix: $worldName";
    }

    public function generateChapterTitle(Collection $chronicleChunk, int $chapterOrder): string
    {
        // 1. Analyze Event Content
        // (Assuming chronicle content contains keywords or we check related events)
        $content = $chronicleChunk->pluck('content')->implode(' ');
        
        $type = $this->detectDominantType($content);
        
        // 2. Select Template
        $options = self::TEMPLATES[$type] ?? self::TEMPLATES['DEFAULT'];
        $title = $options[array_rand($options)];
        
        // 3. Add variation based on order
        if ($chapterOrder === 1) {
            return "Khởi Đầu: $title";
        }

        return $title;
    }

    private function detectDominantType(string $content): string
    {
        // Simple keyword matching for now
        if (str_contains($content, 'chiến tranh') || str_contains($content, 'tấn công')) return 'WAR';
        if (str_contains($content, 'hòa bình') || str_contains($content, 'phát triển')) return 'PEACE';
        if (str_contains($content, 'thảm họa') || str_contains($content, 'động đất')) return 'DISASTER';
        if (str_contains($content, 'thần') || str_contains($content, 'thiêng liêng')) return 'MYTH';
        
        return 'DEFAULT';
    }
}
