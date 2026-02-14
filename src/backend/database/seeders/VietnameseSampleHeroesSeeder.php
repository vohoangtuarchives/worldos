<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Vietnamese\Models\VietnameseHero;
use App\Domains\Vietnamese\Models\HeroEvent;

class VietnameseSampleHeroesSeeder extends Seeder
{
    /**
     * Seed sample heroes with events (event-driven approach)
     */
    public function run(): void
    {
        // 1. Mythological: Thánh Gióng
        $thanhGiong = VietnameseHero::create([
            'name' => 'Thánh Gióng',
            'period' => 'MYTHICAL',
            'era' => 10,
            'archetype' => 'EMERGENCY_SAVIOR',
            'cosmic_role' => 'DIVINE_INTERVENTION',
            'biography' => '3 tuổi không biết nói, đột nhiên trưởng thành khi giặc Ân tràn đến. Cưỡi ngựa sắt, mặc giáp sắt, đánh đuổi giặc rồi thăng thiên.',
            'quote' => 'Kỳ tích từ đất Phong Châu',
        ]);

        HeroEvent::create([
            'hero_id' => $thanhGiong->id,
            'event_type' => 'battle',
            'name' => 'Đánh đuổi giặc Ân',
            'scale' => 5,
            'duration_years' => 1,
            'success' => 1.0,
        ]);

        HeroEvent::create([
            'hero_id' => $thanhGiong->id,
            'event_type' => 'myth_event',
            'name' => 'Thần thánh hóa - thăng thiên',
            'scale' => 5,
            'duration_years' => 1,
            'success' => 1.0,
        ]);

        // 2. Resistance: Hai Bà Trưng
        $haiBaTrung = VietnameseHero::create([
            'name' => 'Hai Bà Trưng',
            'birth_year' => 12,
            'death_year' => 43,
            'period' => 'BAC_THUOC',
            'era' => 31,
            'archetype' => 'RESISTANCE_QUEENS',
            'cosmic_role' => 'FAILED_BIFURCATION_ACCUMULATOR',
            'biography' => 'Trưng Trắc và Trưng Nhị khởi nghĩa chống Hán (40 AD), giải phóng 65 thành, lập nước nhưng bị Mã Viện đánh bại sau 3 năm.',
            'quote' => 'Tôi chỉ muốn cưỡi cơn gió mạnh, đạp luồng sóng dữ...',
        ]);

        HeroEvent::create([
            'hero_id' => $haiBaTrung->id,
            'event_type' => 'rebellion',
            'name' => 'Khởi nghĩa chống Hán',
            'year' => 40,
            'scale' => 5,
            'duration_years' => 3,
            'success' => 0.6, // Failed but legendary
        ]);

        HeroEvent::create([
            'hero_id' => $haiBaTrung->id,
            'event_type' => 'founding_state',
            'name' => 'Lập nước Lĩnh Nam',
            'year' => 40,
            'scale' => 4,
            'duration_years' => 3,
            'success' => 0.5, // Short-lived
        ]);

        // 3. Independence: Ngô Quyền
        $ngoQuyen = VietnameseHero::create([
            'name' => 'Ngô Quyền',
            'birth_year' => 897,
            'death_year' => 944,
            'period' => 'INDEPENDENCE',
            'era' => 51,
            'archetype' => 'INDEPENDENCE_HERO',
            'cosmic_role' => 'BIFURCATION_TRIGGER',
            'biography' => 'Đại thắng quân Nam Hán tại Bạch Đằng (938), chấm dứt 1000 năm Bắc thuộc, mở ra kỷ nguyên độc lập.',
            'quote' => 'Trận Bạch Đằng - đánh dấu độc lập ngàn năm',
        ]);

        HeroEvent::create([
            'hero_id' => $ngoQuyen->id,
            'event_type' => 'battle',
            'name' => 'Trận Bạch Đằng',
            'year' => 938,
            'scale' => 5,
            'duration_years' => 1,
            'success' => 1.0,
        ]);

        HeroEvent::create([
            'hero_id' => $ngoQuyen->id,
            'event_type' => 'founding_state',
            'name' => 'Lập nhà Ngô',
            'year' => 939,
            'scale' => 4,
            'duration_years' => 5,
            'success' => 0.8, // Stable but short
        ]);

        // 4. Military Genius: Trần Hưng Đạo
        $tranHungDao = VietnameseHero::create([
            'name' => 'Trần Hưng Đạo',
            'birth_year' => 1228,
            'death_year' => 1300,
            'period' => 'TRAN',
            'era' => 64,
            'archetype' => 'LEGENDARY_GENERAL',
            'cosmic_role' => 'MEGA_RESONANCE',
            'biography' => 'Đại tướng nhà Trần, đánh thắng quân Mông Cổ 3 lần (1258, 1285, 1288), bảo vệ độc lập dân tộc trước đế chế hùng mạnh nhất thế giới.',
            'quote' => 'Giặc đến nhà Burns, vì dân phải đánh',
        ]);

        HeroEvent::create([
            'hero_id' => $tranHungDao->id,
            'event_type' => 'battle',
            'name' => 'Trận Bạch Đằng lần 2 (vs Mông Cổ)',
            'year' => 1288,
            'scale' => 5,
            'duration_years' => 1,
            'success' => 1.0,
        ]);

        HeroEvent::create([
            'hero_id' => $tranHungDao->id,
            'event_type' => 'battle',
            'name' => 'Hai trận đánh Mông Cổ trước đó',
            'year' => 1258,
            'scale' => 5,
            'duration_years' => 30,
            'success' => 1.0,
        ]);

        // 5. Scholar-Strategist: Nguyễn Trãi
        $nguyenTrai = VietnameseHero::create([
            'name' => 'Nguyễn Trãi',
            'birth_year' => 1380,
            'death_year' => 1442,
            'period' => 'HAU_LE',
            'era' => 70,
            'archetype' => 'TRI_THUC_KIEN_QUOC',
            'cosmic_role' => 'IDEOLOGICAL_STABILIZER',
            'biography' => 'Nhà chiến lược, tư tưởng gia nhà Lê. Soạn Bình Ngô đại cáo (tuyên ngôn độc lập), giúp Lê Lợi đánh thắng nhà Minh.',
            'quote' => 'Việc nhân nghĩa, thời thế tạo anh hùng',
        ]);

        HeroEvent::create([
            'hero_id' => $nguyenTrai->id,
            'event_type' => 'writing_book',
            'name' => 'Bình Ngô đại cáo',
            'year' => 1428,
            'scale' => 5,
            'duration_years' => 1,
            'success' => 1.0,
        ]);

        HeroEvent::create([
            'hero_id' => $nguyenTrai->id,
            'event_type' => 'rebellion',
            'name' => 'Hỗ trợ Lê Lợi khởi nghĩa',
            'year' => 1418,
            'scale' => 5,
            'duration_years' => 10,
            'success' => 1.0,
        ]);

        HeroEvent::create([
            'hero_id' => $nguyenTrai->id,
            'event_type' => 'legal_reform',
            'name' => 'Soạn luật pháp nhà Lê',
            'year' => 1430,
            'scale' => 4,
            'duration_years' => 10,
            'success' => 0.9,
        ]);

        // 6. Cultural Icon: Nguyễn Du
        $nguyenDu = VietnameseHero::create([
            'name' => 'Nguyễn Du',
            'birth_year' => 1765,
            'death_year' => 1820,
            'period' => 'CULTURE',
            'era' => 90,
            'archetype' => 'CULTURAL_SOUL_ARCHITECT',
            'cosmic_role' => 'NATIONAL_SOUL',
            'biography' => 'Đại thi hào, tác giả Truyện Kiều - kiệt tác văn học dân tộc, được coi là linh hồn văn hóa Việt Nam.',
            'quote' => 'Trăm năm trong cõi người ta...',
        ]);

        HeroEvent::create([
            'hero_id' => $nguyenDu->id,
            'event_type' => 'writing_book',
            'name' => 'Truyện Kiều',
            'year' => 1820,
            'scale' => 5,
            'duration_years' => 10,
            'success' => 1.0,
        ]);

        HeroEvent::create([
            'hero_id' => $nguyenDu->id,
            'event_type' => 'writing_book',
            'name' => 'Các tác phẩm thơ văn khác',
            'year' => 1800,
            'scale' => 3,
            'duration_years' => 20,
            'success' => 0.9,
        ]);

        // 7. Revolutionary: Hồ Chí Minh
        $hoChiMinh = VietnameseHero::create([
            'name' => 'Hồ Chí Minh',
            'birth_year' => 1890,
            'death_year' => 1969,
            'period' => 'MODERN',
            'era' => 98,
            'archetype' => 'REVOLUTIONARY_LEADER',
            'cosmic_role' => 'INDEPENDENCE_BIFURCATION',
            'biography' => 'Lãnh tụ cách mạng, Chủ tịch nước. Lãnh đạo giành độc lập từ Pháp (1954) và thống nhất đất nước (1975).',
            'quote' => 'Không có gì quý hơn độc lập tự do',
        ]);

        HeroEvent::create([
            'hero_id' => $hoChiMinh->id,
            'event_type' => 'rebellion',
            'name' => 'Cách mạng Tháng Tám',
            'year' => 1945,
            'scale' => 5,
            'duration_years' => 1,
            'success' => 1.0,
        ]);

        HeroEvent::create([
            'hero_id' => $hoChiMinh->id,
            'event_type' => 'founding_state',
            'name' => 'Thành lập nước Việt Nam Dân chủ Cộng hòa',
            'year' => 1945,
            'scale' => 5,
            'duration_years' => 24,
            'success' => 1.0,
        ]);

        HeroEvent::create([
            'hero_id' => $hoChiMinh->id,
            'event_type' => 'diplomacy',
            'name' => 'Hoạt động ngoại giao quốc tế',
            'year' => 1920,
            'scale' => 5,
            'duration_years' => 49,
            'success' => 0.9,
        ]);

        // Trigger auto-recalculation for all
        $this->command->info('Recalculating scores for all heroes...');
        VietnameseHero::all()->each(fn($hero) => $hero->recalculateScores());
    }
}
