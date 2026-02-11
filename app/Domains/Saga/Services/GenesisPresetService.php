<?php

namespace App\Domains\Saga\Services;

use App\Domains\World\Enums\PowerSystemType;
use App\Domains\World\Enums\StartingEnvironment;
use App\Domains\World\Enums\SocialStructure;
use App\Domains\World\Enums\StartingCrisis;
use App\Domains\World\Enums\PowerRanking;
use App\Domains\World\Enums\TechLevel;
use App\Domains\World\Enums\PowerCeiling;

class GenesisPresetService
{
    /**
     * Get all presets organized by category.
     */
    public function allByCategory(): array
    {
        return [
            'huyen_ao' => [
                'label' => '🗡️ Huyền Ảo',
                'presets' => [
                    $this->cuuTrongThien(),
                    $this->giangHoPhongVan(),
                    $this->huyenMonHocVien(),
                    $this->maDaoToSu(),
                    $this->hacAmTeDan(),
                ],
            ],
            'linh_di' => [
                'label' => '👻 Linh Dị',
                'presets' => [
                    $this->maoSonPhuLuc(),
                    $this->huyenMonBiPhap(),
                ],
            ],
            'doi_thuong' => [
                'label' => '🏙️ Đời Thường',
                'presets' => [
                    $this->myThucKy(),
                    $this->showbizPhongVan(),
                    $this->thanYTaiThe(),
                ],
            ],
            'quyen_muu' => [
                'label' => '🏛️ Quyền Mưu',
                'presets' => [
                    $this->cungDauTruyenKy(),
                    $this->daiTuongQuan(),
                ],
            ],
            'lich_su' => [
                'label' => '📜 Lịch Sử',
                'presets' => [
                    $this->daiVietPhongVan(),
                    $this->haiTacVienDuong(),
                ],
            ],
            'khoa_huyen' => [
                'label' => '🚀 Khoa Huyễn',
                'presets' => [
                    $this->neonDystopia(),
                    $this->tinhHaChienKy(),
                    $this->kiemThanOnline(),
                    $this->tienHoaMatThe(),
                ],
            ],
            'anime' => [
                'label' => '🔥 Anime / Niệm',
                'presets' => [
                    $this->nhanGiaDaiChien(),
                    $this->niemSuHoi(),
                    $this->heThongThucTinh(),
                ],
            ],
            'lai_tao' => [
                'label' => '🔀 Lai Tạo',
                'presets' => [
                    $this->tuChanKhoaHoc(),
                    $this->doThiDiNang(),
                    $this->daVuTruHanh(),
                    $this->vongLapVanMenh(),
                ],
            ],
        ];
    }

    /**
     * Get a flat list of all presets.
     */
    public function all(): array
    {
        $presets = [];
        foreach ($this->allByCategory() as $category) {
            foreach ($category['presets'] as $preset) {
                $presets[$preset['key']] = $preset;
            }
        }
        return $presets;
    }

    /**
     * Get a single preset by key.
     */
    public function find(string $key): ?array
    {
        return $this->all()[$key] ?? null;
    }

    // ==========================================
    // 🗡️ HUYỀN ẢO
    // ==========================================

    private function cuuTrongThien(): array
    {
        return [
            'key' => 'cuu_trong_thien',
            'name' => 'Cửu Trọng Thiên',
            'icon' => '🐉',
            'description' => 'Tu chân thế giới mới, bắt đầu từ phàm nhân, mục tiêu phi thăng',
            'genre' => 'xianxia',
            'power_system' => PowerSystemType::SPIRITUAL_QI->value,
            'power_ceiling' => PowerCeiling::IMMORTAL->value,
            'tech_level' => TechLevel::DYNASTIC->value,
            'environment' => StartingEnvironment::SKY_REALM->value,
            'social_structure' => SocialStructure::SECTS->value,
            'starting_crisis' => StartingCrisis::NONE->value,
            'power_ranking' => PowerRanking::CULTIVATION->value,
            'author_persona' => 'WuxiaMaster',
        ];
    }

    private function giangHoPhongVan(): array
    {
        return [
            'key' => 'giang_ho_phong_van',
            'name' => 'Giang Hồ Phong Vân',
            'icon' => '⚔️',
            'description' => 'Kiếm hiệp cổ trang, bang phái tranh hùng, nghĩa khí giang hồ',
            'genre' => 'wuxia',
            'power_system' => PowerSystemType::INTERNAL_QI->value,
            'power_ceiling' => PowerCeiling::HUMAN_PLUS->value,
            'tech_level' => TechLevel::MEDIEVAL->value,
            'environment' => StartingEnvironment::CONTINENTAL->value,
            'social_structure' => SocialStructure::SECTS->value,
            'starting_crisis' => StartingCrisis::WAR->value,
            'power_ranking' => PowerRanking::NATURAL->value,
        ];
    }

    private function maDaoToSu(): array
    {
        return [
            'key' => 'ma_dao_to_su',
            'name' => 'Ma Đạo Tổ Sư',
            'icon' => '👹',
            'description' => 'Quỷ tu ma đạo, yểm bùa ám sát, ranh giới thiện ác mờ nhạt',
            'genre' => 'demon_realm',
            'power_system' => PowerSystemType::DEMONIC_QI->value,
            'power_ceiling' => PowerCeiling::IMMORTAL->value,
            'tech_level' => TechLevel::DYNASTIC->value,
            'environment' => StartingEnvironment::UNDERGROUND->value,
            'social_structure' => SocialStructure::SECTS->value,
            'starting_crisis' => StartingCrisis::INVASION->value,
            'power_ranking' => PowerRanking::CULTIVATION->value,
            'author_persona' => 'DarkHistorian',
        ];
    }

    private function hacAmTeDan(): array
    {
        return [
            'key' => 'hac_am_te_dan',
            'name' => 'Hắc Ám Tế Đàn',
            'icon' => '🌑',
            'description' => 'Cấm thuật, hiến tế, nguyền rủa — cái giá của sức mạnh',
            'genre' => 'dark_fantasy',
            'power_system' => PowerSystemType::DARK_MANA->value,
            'power_ceiling' => PowerCeiling::TRANSCENDENT->value,
            'tech_level' => TechLevel::MEDIEVAL->value,
            'environment' => StartingEnvironment::UNDERGROUND->value,
            'social_structure' => SocialStructure::TRIBES->value,
            'starting_crisis' => StartingCrisis::PLAGUE->value,
            'power_ranking' => PowerRanking::NATURAL->value,
        ];
    }

    private function huyenMonHocVien(): array
    {
        return [
            'key' => 'huyen_mon_hoc_vien',
            'name' => 'Huyền Môn Học Viện',
            'icon' => '🪄',
            'description' => 'Thế giới hiện đại nơi phép thuật ẩn mình. Các học viện đào tạo phù thủy trẻ chiến đấu chống lại thế lực Hắc Ám.',
            'genre' => 'modern_fantasy',
            'power_system' => PowerSystemType::MANA->value,
            'power_ceiling' => PowerCeiling::TRANSCENDENT->value,
            'tech_level' => TechLevel::MODERN->value,
            'environment' => StartingEnvironment::CONTINENTAL->value,
            'social_structure' => SocialStructure::SECTS->value, // Represents Schools and Houses
            'starting_crisis' => StartingCrisis::WAR->value, // The return of the Dark Lord
            'power_ranking' => PowerRanking::NATURAL->value,
        ];
    }

    // ==========================================
    // 👻 LINH DỊ
    // ==========================================

    private function maoSonPhuLuc(): array
    {
        return [
            'key' => 'mao_son_phu_luc',
            'name' => 'Mao Sơn Phù Lục',
            'icon' => '🕯️',
            'description' => 'Trừ tà, phong thủy, bùa chú — thế giới linh dị đầy bí ẩn',
            'genre' => 'ling_di',
            'power_system' => PowerSystemType::TALISMAN->value,
            'power_ceiling' => PowerCeiling::TRANSCENDENT->value,
            'tech_level' => TechLevel::DYNASTIC->value,
            'environment' => StartingEnvironment::CONTINENTAL->value,
            'social_structure' => SocialStructure::TRIBES->value,
            'starting_crisis' => StartingCrisis::PLAGUE->value,
            'power_ranking' => PowerRanking::NATURAL->value,
        ];
    }

    private function huyenMonBiPhap(): array
    {
        return [
            'key' => 'huyen_mon_bi_phap',
            'name' => 'Huyền Môn Bí Pháp',
            'icon' => '📿',
            'description' => 'Nghi thức tế đàn, triệu hồi thực thể, ký kết khế ước',
            'genre' => 'occult',
            'power_system' => PowerSystemType::OCCULT_RITUAL->value,
            'power_ceiling' => PowerCeiling::TRANSCENDENT->value,
            'tech_level' => TechLevel::MEDIEVAL->value,
            'environment' => StartingEnvironment::CONTINENTAL->value,
            'social_structure' => SocialStructure::SECTS->value,
            'starting_crisis' => StartingCrisis::AWAKENING->value,
            'power_ranking' => PowerRanking::NATURAL->value,
        ];
    }

    // ==========================================
    // 🏙️ ĐỜI THƯỜNG
    // ==========================================

    private function myThucKy(): array
    {
        return [
            'key' => 'my_thuc_ky',
            'name' => 'Mỹ Thực Ký',
            'icon' => '🍜',
            'description' => 'Ẩm thực, gia đình, tình cảm đời thường, hương vị cuộc sống',
            'genre' => 'slice_of_life',
            'power_system' => PowerSystemType::NONE->value,
            'power_ceiling' => PowerCeiling::HUMAN->value,
            'tech_level' => TechLevel::MODERN->value,
            'environment' => StartingEnvironment::CONTINENTAL->value,
            'social_structure' => SocialStructure::CITY_STATES->value,
            'starting_crisis' => StartingCrisis::NONE->value,
            'power_ranking' => PowerRanking::NATURAL->value,
        ];
    }

    private function showbizPhongVan(): array
    {
        return [
            'key' => 'showbiz_phong_van',
            'name' => 'Showbiz Phong Vân',
            'icon' => '🎬',
            'description' => 'Giới giải trí, idol, diễn viên, tranh đấu showbiz',
            'genre' => 'entertainment',
            'power_system' => PowerSystemType::NONE->value,
            'power_ceiling' => PowerCeiling::HUMAN->value,
            'tech_level' => TechLevel::MODERN->value,
            'environment' => StartingEnvironment::CONTINENTAL->value,
            'social_structure' => SocialStructure::CITY_STATES->value,
            'starting_crisis' => StartingCrisis::NONE->value,
            'power_ranking' => PowerRanking::NATURAL->value,
        ];
    }

    private function thanYTaiThe(): array
    {
        return [
            'key' => 'than_y_tai_the',
            'name' => 'Thần Y Tái Thế',
            'icon' => '🏥',
            'description' => 'Y thuật cao siêu, cứu người, nghiên cứu y học đột phá',
            'genre' => 'medical',
            'power_system' => PowerSystemType::NONE->value,
            'power_ceiling' => PowerCeiling::HUMAN->value,
            'tech_level' => TechLevel::MODERN->value,
            'environment' => StartingEnvironment::CONTINENTAL->value,
            'social_structure' => SocialStructure::EMPIRE->value,
            'starting_crisis' => StartingCrisis::PLAGUE->value,
            'power_ranking' => PowerRanking::NATURAL->value,
        ];
    }

    // ==========================================
    // 🏛️ QUYỀN MƯU
    // ==========================================

    private function cungDauTruyenKy(): array
    {
        return [
            'key' => 'cung_dau_truyen_ky',
            'name' => 'Cung Đấu Truyền Kỳ',
            'icon' => '👑',
            'description' => 'Triều đình mưu kế, thăng quan tiến chức, cung đấu hậu cung',
            'genre' => 'court_intrigue',
            'power_system' => PowerSystemType::NONE->value,
            'power_ceiling' => PowerCeiling::HUMAN->value,
            'tech_level' => TechLevel::DYNASTIC->value,
            'environment' => StartingEnvironment::CONTINENTAL->value,
            'social_structure' => SocialStructure::EMPIRE->value,
            'starting_crisis' => StartingCrisis::NONE->value,
            'power_ranking' => PowerRanking::NATURAL->value,
        ];
    }

    private function daiTuongQuan(): array
    {
        return [
            'key' => 'dai_tuong_quan',
            'name' => 'Đại Tướng Quân',
            'icon' => '🎖️',
            'description' => 'Chiến tranh, chiến lược quân sự, thống lĩnh vạn quân',
            'genre' => 'military',
            'power_system' => PowerSystemType::NONE->value,
            'power_ceiling' => PowerCeiling::HUMAN->value,
            'tech_level' => TechLevel::DYNASTIC->value,
            'environment' => StartingEnvironment::CONTINENTAL->value,
            'social_structure' => SocialStructure::EMPIRE->value,
            'starting_crisis' => StartingCrisis::WAR->value,
            'power_ranking' => PowerRanking::NATURAL->value,
        ];
    }

    // ==========================================
    // 📜 LỊCH SỬ
    // ==========================================

    private function daiVietPhongVan(): array
    {
        return [
            'key' => 'dai_viet_phong_van',
            'name' => 'Đại Việt Phong Vân',
            'icon' => '🏯',
            'description' => 'Vương triều Đại Việt, chính trị, chiến tranh, xây dựng quốc gia',
            'genre' => 'dynasty',
            'power_system' => PowerSystemType::NONE->value,
            'power_ceiling' => PowerCeiling::HUMAN->value,
            'tech_level' => TechLevel::DYNASTIC->value,
            'environment' => StartingEnvironment::CONTINENTAL->value,
            'social_structure' => SocialStructure::EMPIRE->value,
            'starting_crisis' => StartingCrisis::WAR->value,
            'power_ranking' => PowerRanking::NATURAL->value,
        ];
    }

    private function haiTacVienDuong(): array
    {
        return [
            'key' => 'hai_tac_vien_duong',
            'name' => 'Hải Tặc Viễn Dương',
            'icon' => '🏴‍☠️',
            'description' => 'Hàng hải, cướp biển, khám phá vùng đất mới',
            'genre' => 'pirate',
            'power_system' => PowerSystemType::NONE->value,
            'power_ceiling' => PowerCeiling::HUMAN_PLUS->value,
            'tech_level' => TechLevel::EARLY_INDUSTRIAL->value,
            'environment' => StartingEnvironment::ARCHIPELAGO->value,
            'social_structure' => SocialStructure::CITY_STATES->value,
            'starting_crisis' => StartingCrisis::FAMINE->value,
            'power_ranking' => PowerRanking::NATURAL->value,
        ];
    }

    // ==========================================
    // 🚀 KHOA HUYỄN
    // ==========================================

    private function neonDystopia(): array
    {
        return [
            'key' => 'neon_dystopia',
            'name' => 'Neon Dystopia',
            'icon' => '🌃',
            'description' => 'Cyberpunk dystopia, megacorp, hack thần kinh, tầng dưới xã hội',
            'genre' => 'cyberpunk',
            'power_system' => PowerSystemType::TECH_IMPLANT->value,
            'power_ceiling' => PowerCeiling::TRANSCENDENT->value,
            'tech_level' => TechLevel::FUTURISTIC->value,
            'environment' => StartingEnvironment::CONTINENTAL->value,
            'social_structure' => SocialStructure::CITY_STATES->value,
            'starting_crisis' => StartingCrisis::NONE->value,
            'power_ranking' => PowerRanking::ALPHABET->value,
        ];
    }

    private function tinhHaChienKy(): array
    {
        return [
            'key' => 'tinh_ha_chien_ky',
            'name' => 'Tinh Hà Chiến Ký',
            'icon' => '🌌',
            'description' => 'Chiến tranh giữa các vì sao, quy tắc vũ trụ, đế chế thiên hà',
            'genre' => 'space_opera',
            'power_system' => PowerSystemType::COSMIC_RULE->value,
            'power_ceiling' => PowerCeiling::IMMORTAL->value,
            'tech_level' => TechLevel::FUTURISTIC->value,
            'environment' => StartingEnvironment::SKY_REALM->value,
            'social_structure' => SocialStructure::EMPIRE->value,
            'starting_crisis' => StartingCrisis::WAR->value,
            'power_ranking' => PowerRanking::ALPHABET->value,
        ];
    }

    private function kiemThanOnline(): array
    {
        return [
            'key' => 'kiem_than_online',
            'name' => 'Kiếm Thần Online',
            'icon' => '🎮',
            'description' => 'Full-dive VR, NPC sentient, game trở thành thực tại',
            'genre' => 'vr_world',
            'power_system' => PowerSystemType::VR_SIMULATION->value,
            'power_ceiling' => PowerCeiling::IMMORTAL->value,
            'tech_level' => TechLevel::FUTURISTIC->value,
            'environment' => StartingEnvironment::SKY_REALM->value,
            'social_structure' => SocialStructure::SECTS->value,
            'starting_crisis' => StartingCrisis::AWAKENING->value,
            'power_ranking' => PowerRanking::ALPHABET->value,
        ];
    }

    private function tienHoaMatThe(): array
    {
        return [
            'key' => 'tien_hoa_mat_the',
            'name' => 'Tiến Hoá Mạt Thế',
            'icon' => '🧬',
            'description' => 'Hậu tận thế, tiến hoá đột biến, kẻ mạnh sinh tồn',
            'genre' => 'post_apocalyptic',
            'power_system' => PowerSystemType::EVOLUTION->value,
            'power_ceiling' => PowerCeiling::TRANSCENDENT->value,
            'tech_level' => TechLevel::MODERN->value,
            'environment' => StartingEnvironment::WASTELAND->value,
            'social_structure' => SocialStructure::ANARCHY->value,
            'starting_crisis' => StartingCrisis::INVASION->value,
            'power_ranking' => PowerRanking::ALPHABET->value,
        ];
    }

    // ==========================================
    // 🔥 ANIME / NIỆM
    // ==========================================

    private function nhanGiaDaiChien(): array
    {
        return [
            'key' => 'nhan_gia_dai_chien',
            'name' => 'Nhẫn Giả Đại Chiến',
            'icon' => '⚡',
            'description' => 'Ấn thuật, nhẫn giả, chiến tranh làng ninja',
            'genre' => 'anime_power',
            'power_system' => PowerSystemType::CHAKRA->value,
            'power_ceiling' => PowerCeiling::IMMORTAL->value,
            'tech_level' => TechLevel::MEDIEVAL->value,
            'environment' => StartingEnvironment::CONTINENTAL->value,
            'social_structure' => SocialStructure::SECTS->value,
            'starting_crisis' => StartingCrisis::WAR->value,
            'power_ranking' => PowerRanking::ALPHABET->value,
        ];
    }

    private function niemSuHoi(): array
    {
        return [
            'key' => 'niem_su_hoi',
            'name' => 'Niệm Sư Hội',
            'icon' => '🎯',
            'description' => 'Hệ Niệm (Nen), Hatsu, En, Zetsu — khám phá tiềm năng bản thân',
            'genre' => 'anime_power',
            'power_system' => PowerSystemType::NEN->value,
            'power_ceiling' => PowerCeiling::TRANSCENDENT->value,
            'tech_level' => TechLevel::MODERN->value,
            'environment' => StartingEnvironment::CONTINENTAL->value,
            'social_structure' => SocialStructure::CITY_STATES->value,
            'starting_crisis' => StartingCrisis::NONE->value,
            'power_ranking' => PowerRanking::ALPHABET->value,
        ];
    }

    private function heThongThucTinh(): array
    {
        return [
            'key' => 'he_thong_thuc_tinh',
            'name' => 'Hệ Thống Thức Tỉnh',
            'icon' => '📊',
            'description' => 'Hệ thống thức tỉnh, bảng trạng thái, nhiệm vụ, leveling',
            'genre' => 'system',
            'power_system' => PowerSystemType::SYSTEM_STATS->value,
            'power_ceiling' => PowerCeiling::IMMORTAL->value,
            'tech_level' => TechLevel::MODERN->value,
            'environment' => StartingEnvironment::CONTINENTAL->value,
            'social_structure' => SocialStructure::CITY_STATES->value,
            'starting_crisis' => StartingCrisis::AWAKENING->value,
            'power_ranking' => PowerRanking::ALPHABET->value,
        ];
    }

    // ==========================================
    // 🔀 LAI TẠO
    // ==========================================

    private function tuChanKhoaHoc(): array
    {
        return [
            'key' => 'tu_chan_khoa_hoc',
            'name' => 'Tu Chân Khoa Học',
            'icon' => '⚙️',
            'description' => 'Tiên hiệp + công nghệ, tu luyện bằng khoa học',
            'genre' => 'cultivation_tech',
            'power_system' => PowerSystemType::SPIRITUAL_QI->value,
            'power_ceiling' => PowerCeiling::IMMORTAL->value,
            'tech_level' => TechLevel::FUTURISTIC->value,
            'environment' => StartingEnvironment::CONTINENTAL->value,
            'social_structure' => SocialStructure::EMPIRE->value,
            'starting_crisis' => StartingCrisis::AWAKENING->value,
            'power_ranking' => PowerRanking::CULTIVATION->value,
        ];
    }

    private function doThiDiNang(): array
    {
        return [
            'key' => 'do_thi_di_nang',
            'name' => 'Đô Thị Dị Năng',
            'icon' => '🌃',
            'description' => 'Thành phố hiện đại + siêu năng lực thức tỉnh',
            'genre' => 'urban_fantasy',
            'power_system' => PowerSystemType::ANOMALY->value,
            'power_ceiling' => PowerCeiling::TRANSCENDENT->value,
            'tech_level' => TechLevel::MODERN->value,
            'environment' => StartingEnvironment::CONTINENTAL->value,
            'social_structure' => SocialStructure::CITY_STATES->value,
            'starting_crisis' => StartingCrisis::AWAKENING->value,
            'power_ranking' => PowerRanking::ALPHABET->value,
        ];
    }

    private function daVuTruHanh(): array
    {
        return [
            'key' => 'da_vu_tru_hanh',
            'name' => 'Đa Vũ Trụ Hành',
            'icon' => '🌀',
            'description' => 'Xuyên không gian, luật giữa các vũ trụ, đa chiều',
            'genre' => 'multiverse_saga',
            'power_system' => PowerSystemType::MULTIVERSE->value,
            'power_ceiling' => PowerCeiling::IMMORTAL->value,
            'tech_level' => TechLevel::FUTURISTIC->value,
            'environment' => StartingEnvironment::SKY_REALM->value,
            'social_structure' => SocialStructure::ANARCHY->value,
            'starting_crisis' => StartingCrisis::INVASION->value,
            'power_ranking' => PowerRanking::ALPHABET->value,
        ];
    }

    private function vongLapVanMenh(): array
    {
        return [
            'key' => 'vong_lap_van_menh',
            'name' => 'Vòng Lặp Vận Mệnh',
            'icon' => '🔄',
            'description' => 'Lặp lại thời gian, thay đổi vận mệnh, nghịch thiên cải số',
            'genre' => 'time_loop',
            'power_system' => PowerSystemType::NONE->value,
            'power_ceiling' => PowerCeiling::HUMAN->value,
            'tech_level' => TechLevel::MODERN->value,
            'environment' => StartingEnvironment::CONTINENTAL->value,
            'social_structure' => SocialStructure::EMPIRE->value,
            'starting_crisis' => StartingCrisis::WAR->value,
            'power_ranking' => PowerRanking::NATURAL->value,
        ];
    }
}
