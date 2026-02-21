<?php

namespace Tuzy\Application\Saga\Services;

use Tuzy\Domain\World\Enums\PowerSystemType;
use Tuzy\Domain\World\Enums\StartingEnvironment;
use Tuzy\Domain\World\Enums\SocialStructure;
use Tuzy\Domain\World\Enums\StartingCrisis;
use Tuzy\Domain\World\Enums\PowerRanking;
use Tuzy\Domain\World\Enums\TechLevel;
use Tuzy\Domain\World\Enums\PowerCeiling;

class GenesisPresetService
{
    private const PRESET_ARCHETYPE_MAP = [
        'cuu_trong_thien' => 'ascension_mysticism',
        'ma_dao_to_su' => 'ascension_mysticism',
        'he_thong_thuc_tinh' => 'ascension_mysticism',
        'tu_chan_khoa_hoc' => 'ascension_mysticism',

        'giang_ho_phong_van' => 'martial_honor_hierarchy',
        'nhan_gia_dai_chien' => 'martial_honor_hierarchy',

        'neon_dystopia' => 'tech_stratified_world',
        'kiem_than_online' => 'tech_stratified_world',
        'showbiz_phong_van' => 'tech_stratified_world',

        'cung_dau_truyen_ky' => 'political_intrigue_mortality',
        'dai_tuong_quan' => 'political_intrigue_mortality',
        'dai_viet_phong_van' => 'political_intrigue_mortality',
        'hai_tac_vien_duong' => 'political_intrigue_mortality',

        'tien_hoa_mat_the' => 'entropic_collapse',
        'hac_am_te_dan' => 'entropic_collapse',

        'da_vu_tru_hanh' => 'hybrid_evolution',
        'vong_lap_van_menh' => 'hybrid_evolution',
        'do_thi_di_nang' => 'hybrid_evolution',
        'tinh_ha_chien_ky' => 'hybrid_evolution',

        'my_thuc_ky' => 'cultural_slice_of_life',
        'than_y_tai_the' => 'cultural_slice_of_life',

        'mao_son_phu_luc' => 'spiritual_occult_instability',
        'huyen_mon_bi_phap' => 'spiritual_occult_instability',
        'huyen_mon_hoc_vien' => 'spiritual_occult_instability',
        'niem_su_hoi' => 'spiritual_occult_instability',
    ];

    private const ARCHETYPE_PROFILES = [
        'ascension_mysticism' => [
            'ontology' => ['energy_density' => [0.78, 0.92], 'mortality_weight' => [0.15, 0.35], 'causality_strength' => [0.62, 0.82], 'consciousness_imprint' => [0.72, 0.94], 'entropy_pressure' => [0.25, 0.45], 'reality_rigidity' => [0.55, 0.75]],
            'epistemic' => ['epistemic_stability' => [0.52, 0.72], 'belief_fragmentation' => [0.40, 0.62], 'rationality_bias' => [0.25, 0.45], 'mysticism_openness' => [0.74, 0.92], 'historical_certainty' => [0.40, 0.65]],
            'civilization' => ['innovation_rate' => [0.42, 0.60], 'innovation_resistance' => [0.56, 0.75], 'hierarchy_tendency' => [0.68, 0.92], 'conflict_drive' => [0.45, 0.68], 'cooperation_bias' => [0.34, 0.55], 'resource_distribution_skew' => [0.52, 0.76], 'population_growth_pressure' => [0.33, 0.58]],
            'energy' => ['manifestation_type' => 'internal', 'accessibility_index' => [0.08, 0.24], 'scaling_curve' => 'exponential', 'saturation_threshold' => [0.78, 0.94], 'mutation_potential' => [0.36, 0.55]],
            'drift' => ['baseline_rate' => 0.004, 'volatility' => 0.12, 'stability_bias' => 0.76],
        ],
        'martial_honor_hierarchy' => [
            'ontology' => ['energy_density' => [0.50, 0.68], 'mortality_weight' => [0.58, 0.82], 'causality_strength' => [0.62, 0.85], 'consciousness_imprint' => [0.35, 0.55], 'entropy_pressure' => [0.40, 0.62], 'reality_rigidity' => [0.65, 0.88]],
            'epistemic' => ['epistemic_stability' => [0.64, 0.84], 'belief_fragmentation' => [0.22, 0.45], 'rationality_bias' => [0.42, 0.62], 'mysticism_openness' => [0.35, 0.56], 'historical_certainty' => [0.62, 0.88]],
            'civilization' => ['innovation_rate' => [0.34, 0.52], 'innovation_resistance' => [0.55, 0.76], 'hierarchy_tendency' => [0.70, 0.90], 'conflict_drive' => [0.62, 0.88], 'cooperation_bias' => [0.25, 0.44], 'resource_distribution_skew' => [0.44, 0.64], 'population_growth_pressure' => [0.46, 0.65]],
            'energy' => ['manifestation_type' => 'internal_or_artifact', 'accessibility_index' => [0.24, 0.45], 'scaling_curve' => 'linear', 'saturation_threshold' => [0.55, 0.74], 'mutation_potential' => [0.28, 0.46]],
            'drift' => ['baseline_rate' => 0.003, 'volatility' => 0.10, 'stability_bias' => 0.81],
        ],
        'tech_stratified_world' => [
            'ontology' => ['energy_density' => [0.25, 0.45], 'mortality_weight' => [0.46, 0.66], 'causality_strength' => [0.72, 0.92], 'consciousness_imprint' => [0.30, 0.50], 'entropy_pressure' => [0.44, 0.66], 'reality_rigidity' => [0.70, 0.90]],
            'epistemic' => ['epistemic_stability' => [0.74, 0.90], 'belief_fragmentation' => [0.32, 0.55], 'rationality_bias' => [0.72, 0.92], 'mysticism_openness' => [0.14, 0.35], 'historical_certainty' => [0.60, 0.82]],
            'civilization' => ['innovation_rate' => [0.75, 0.94], 'innovation_resistance' => [0.22, 0.44], 'hierarchy_tendency' => [0.52, 0.78], 'conflict_drive' => [0.45, 0.67], 'cooperation_bias' => [0.28, 0.52], 'resource_distribution_skew' => [0.70, 0.92], 'population_growth_pressure' => [0.50, 0.74]],
            'energy' => ['manifestation_type' => 'artifact', 'accessibility_index' => [0.36, 0.70], 'scaling_curve' => 'sigmoid', 'saturation_threshold' => [0.50, 0.72], 'mutation_potential' => [0.52, 0.76]],
            'drift' => ['baseline_rate' => 0.006, 'volatility' => 0.18, 'stability_bias' => 0.65],
        ],
        'political_intrigue_mortality' => [
            'ontology' => ['energy_density' => [0.12, 0.32], 'mortality_weight' => [0.74, 0.94], 'causality_strength' => [0.76, 0.94], 'consciousness_imprint' => [0.20, 0.42], 'entropy_pressure' => [0.38, 0.58], 'reality_rigidity' => [0.72, 0.92]],
            'epistemic' => ['epistemic_stability' => [0.72, 0.90], 'belief_fragmentation' => [0.24, 0.46], 'rationality_bias' => [0.62, 0.84], 'mysticism_openness' => [0.10, 0.32], 'historical_certainty' => [0.74, 0.92]],
            'civilization' => ['innovation_rate' => [0.24, 0.42], 'innovation_resistance' => [0.62, 0.84], 'hierarchy_tendency' => [0.80, 0.95], 'conflict_drive' => [0.56, 0.78], 'cooperation_bias' => [0.22, 0.40], 'resource_distribution_skew' => [0.62, 0.84], 'population_growth_pressure' => [0.40, 0.64]],
            'energy' => ['manifestation_type' => 'human_dominant', 'accessibility_index' => [0.04, 0.18], 'scaling_curve' => 'plateau', 'saturation_threshold' => [0.30, 0.52], 'mutation_potential' => [0.16, 0.34]],
            'drift' => ['baseline_rate' => 0.0025, 'volatility' => 0.08, 'stability_bias' => 0.87],
        ],
        'entropic_collapse' => [
            'ontology' => ['energy_density' => [0.38, 0.66], 'mortality_weight' => [0.62, 0.88], 'causality_strength' => [0.24, 0.50], 'consciousness_imprint' => [0.45, 0.72], 'entropy_pressure' => [0.78, 0.96], 'reality_rigidity' => [0.18, 0.42]],
            'epistemic' => ['epistemic_stability' => [0.18, 0.44], 'belief_fragmentation' => [0.62, 0.88], 'rationality_bias' => [0.24, 0.48], 'mysticism_openness' => [0.48, 0.74], 'historical_certainty' => [0.12, 0.35]],
            'civilization' => ['innovation_rate' => [0.30, 0.58], 'innovation_resistance' => [0.46, 0.70], 'hierarchy_tendency' => [0.24, 0.55], 'conflict_drive' => [0.68, 0.92], 'cooperation_bias' => [0.12, 0.34], 'resource_distribution_skew' => [0.68, 0.92], 'population_growth_pressure' => [0.08, 0.24]],
            'energy' => ['manifestation_type' => 'unstable_hybrid', 'accessibility_index' => [0.20, 0.52], 'scaling_curve' => 'chaotic', 'saturation_threshold' => [0.42, 0.74], 'mutation_potential' => [0.74, 0.95]],
            'drift' => ['baseline_rate' => 0.009, 'volatility' => 0.30, 'stability_bias' => 0.48],
        ],
        'hybrid_evolution' => [
            'ontology' => ['energy_density' => [0.52, 0.82], 'mortality_weight' => [0.34, 0.62], 'causality_strength' => [0.45, 0.75], 'consciousness_imprint' => [0.52, 0.80], 'entropy_pressure' => [0.36, 0.62], 'reality_rigidity' => [0.34, 0.62]],
            'epistemic' => ['epistemic_stability' => [0.44, 0.70], 'belief_fragmentation' => [0.48, 0.76], 'rationality_bias' => [0.48, 0.76], 'mysticism_openness' => [0.46, 0.80], 'historical_certainty' => [0.36, 0.62]],
            'civilization' => ['innovation_rate' => [0.62, 0.88], 'innovation_resistance' => [0.30, 0.55], 'hierarchy_tendency' => [0.36, 0.66], 'conflict_drive' => [0.45, 0.72], 'cooperation_bias' => [0.40, 0.68], 'resource_distribution_skew' => [0.40, 0.70], 'population_growth_pressure' => [0.44, 0.70]],
            'energy' => ['manifestation_type' => 'hybrid', 'accessibility_index' => [0.24, 0.56], 'scaling_curve' => 'nonlinear', 'saturation_threshold' => [0.58, 0.82], 'mutation_potential' => [0.62, 0.86]],
            'drift' => ['baseline_rate' => 0.007, 'volatility' => 0.22, 'stability_bias' => 0.60],
        ],
        'cultural_slice_of_life' => [
            'ontology' => ['energy_density' => [0.05, 0.22], 'mortality_weight' => [0.78, 0.96], 'causality_strength' => [0.76, 0.96], 'consciousness_imprint' => [0.12, 0.32], 'entropy_pressure' => [0.24, 0.42], 'reality_rigidity' => [0.80, 0.96]],
            'epistemic' => ['epistemic_stability' => [0.80, 0.94], 'belief_fragmentation' => [0.15, 0.34], 'rationality_bias' => [0.66, 0.90], 'mysticism_openness' => [0.05, 0.24], 'historical_certainty' => [0.78, 0.96]],
            'civilization' => ['innovation_rate' => [0.46, 0.68], 'innovation_resistance' => [0.30, 0.52], 'hierarchy_tendency' => [0.35, 0.56], 'conflict_drive' => [0.12, 0.30], 'cooperation_bias' => [0.70, 0.90], 'resource_distribution_skew' => [0.28, 0.52], 'population_growth_pressure' => [0.42, 0.66]],
            'energy' => ['manifestation_type' => 'ambient', 'accessibility_index' => [0.52, 0.82], 'scaling_curve' => 'flat', 'saturation_threshold' => [0.20, 0.40], 'mutation_potential' => [0.08, 0.24]],
            'drift' => ['baseline_rate' => 0.0015, 'volatility' => 0.05, 'stability_bias' => 0.91],
        ],
        'spiritual_occult_instability' => [
            'ontology' => ['energy_density' => [0.44, 0.74], 'mortality_weight' => [0.38, 0.68], 'causality_strength' => [0.30, 0.58], 'consciousness_imprint' => [0.66, 0.92], 'entropy_pressure' => [0.52, 0.80], 'reality_rigidity' => [0.24, 0.52]],
            'epistemic' => ['epistemic_stability' => [0.24, 0.54], 'belief_fragmentation' => [0.58, 0.84], 'rationality_bias' => [0.18, 0.44], 'mysticism_openness' => [0.74, 0.96], 'historical_certainty' => [0.20, 0.48]],
            'civilization' => ['innovation_rate' => [0.30, 0.58], 'innovation_resistance' => [0.42, 0.70], 'hierarchy_tendency' => [0.34, 0.66], 'conflict_drive' => [0.50, 0.78], 'cooperation_bias' => [0.24, 0.50], 'resource_distribution_skew' => [0.44, 0.74], 'population_growth_pressure' => [0.28, 0.54]],
            'energy' => ['manifestation_type' => 'external', 'accessibility_index' => [0.10, 0.34], 'scaling_curve' => 'ritual_spike', 'saturation_threshold' => [0.50, 0.80], 'mutation_potential' => [0.56, 0.82]],
            'drift' => ['baseline_rate' => 0.008, 'volatility' => 0.26, 'stability_bias' => 0.54],
        ],
    ];
    /**
     * Get all presets organized by category.
     */
    public function allByCategory(): array
    {
        $categories = $this->rawCategories();
        $this->assertArchetypeCoverage($categories);

        foreach ($categories as &$category) {
            $category['presets'] = array_map(fn (array $preset) => $this->enrichPreset($preset), $category['presets']);
        }
        unset($category);

        return $categories;
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

    private function rawCategories(): array
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

    private function assertArchetypeCoverage(array $categories): void
    {
        foreach ($categories as $category) {
            foreach ($category['presets'] ?? [] as $preset) {
                $key = $preset['key'] ?? null;
                if (!is_string($key) || $key === '') {
                    throw new \LogicException('Genesis preset must have a non-empty key.');
                }

                if (!array_key_exists($key, self::PRESET_ARCHETYPE_MAP)) {
                    throw new \LogicException("Missing archetype mapping for preset [{$key}].");
                }

                $archetype = self::PRESET_ARCHETYPE_MAP[$key];
                if (!array_key_exists($archetype, self::ARCHETYPE_PROFILES)) {
                    throw new \LogicException("Missing archetype profile for [{$archetype}] mapped by preset [{$key}].");
                }
            }
        }
    }

    private function enrichPreset(array $preset): array
    {
        $archetype = self::PRESET_ARCHETYPE_MAP[$preset['key']];
        $profile = self::ARCHETYPE_PROFILES[$archetype];

        $preset['archetype'] = $archetype;
        $preset['seed_vector'] = [
            'ontology' => $profile['ontology'],
            'epistemic' => $profile['epistemic'],
            'civilization' => $profile['civilization'],
            'energy' => $profile['energy'],
            'sampling_mode' => 'bounded_stochastic',
        ];
        $preset['drift_profile'] = $profile['drift'];

        return $preset;
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
