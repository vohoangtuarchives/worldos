<?php

return [
    'cuu_trong_thien' => [
        'label' => 'Cửu Trọng Thiên',
        'preset_key' => 'cuu_trong_thien',
        'power_system' => 'SPIRITUAL_QI',
        'paths' => [
            'body' => ['refinement', 'bone_tampering'],
            'energy' => ['qi_cycling', 'meridian_opening'],
            'spirit' => ['dao_comprehension', 'soul_tempering'],
        ],
        'resources' => [
            'material_tags' => ['linh_thach', 'danh_duong', '50_year_herb'],
            'currencies' => ['contribution_points', 'spirit_stones'],
        ],
        'narrative' => [
            'tone' => 'xianxia_high',
            'keywords' => ['độ kiếp', 'linh khí', 'đại tông'],
        ],
    ],
    'giang_ho_phong_van' => [
        'label' => 'Giang Hồ Phong Vân',
        'preset_key' => 'giang_ho_phong_van',
        'power_system' => 'INTERNAL_QI',
        'paths' => [
            'body' => ['nội_công', 'khinh_công'],
            'energy' => ['chân_khí', 'âm_dương_cân_bằng'],
            'spirit' => ['kiếm_ý', 'võ_đạo'],
        ],
        'resources' => [
            'material_tags' => ['bí_tịch', 'linh_dược', 'thần_binh'],
            'currencies' => ['bạc_thỏi', 'danh_vọng'],
        ],
        'narrative' => [
            'tone' => 'wuxia',
            'keywords' => ['giang hồ', 'huynh đệ', 'bang phái'],
        ],
    ],
    'ma_dao_to_su' => [
        'label' => 'Ma Đạo Tổ Sư',
        'preset_key' => 'ma_dao_to_su',
        'power_system' => 'DEMONIC_QI',
        'paths' => [
            'body' => ['ma_pháp_thể', 'huyết_luyện'],
            'energy' => ['ma_sát', 'âm_tà_khí'],
            'spirit' => ['huyết_hồn', 'ác niệm'],
        ],
        'resources' => [
            'material_tags' => ['ma_hạch', 'tế_phẩm', 'linh_hồn'],
            'currencies' => ['ma_châu', 'linh_hồn_điểm'],
        ],
        'narrative' => [
            'tone' => 'dark_fantasy',
            'keywords' => ['ma đạo', 'tế đàn', 'hắc ám'],
        ],
    ],
    'hac_am_te_dan' => [
        'label' => 'Hắc Ám Tế Đàn',
        'preset_key' => 'hac_am_te_dan',
        'power_system' => 'DARK_MANA',
        'paths' => [
            'body' => ['huyết_khế', 'xác_thịt_kinh_tế'],
            'energy' => ['lời_nguyền', 'ma_trận'],
            'spirit' => ['giao_kèo_hắc_ám', 'dị_giới'],
        ],
        'resources' => [
            'material_tags' => ['huyết_ngọc', 'ma_văn', 'tai_ương'],
            'currencies' => ['tín_ngưỡng_đen', 'huyết_tế'],
        ],
        'narrative' => [
            'tone' => 'grimdark',
            'keywords' => ['hiến tế', 'cấm thuật', 'tà linh'],
        ],
    ],
    'huyen_mon_hoc_vien' => [
        'label' => 'Huyền Môn Học Viện',
        'preset_key' => 'huyen_mon_hoc_vien',
        'power_system' => 'MANA',
        'paths' => [
            'body' => ['pháp_phù', 'kết_ấn'],
            'energy' => ['pháp_trận', 'cầu_thần'],
            'spirit' => ['linh_ám', 'kết_giao_tri_khách'],
        ],
        'resources' => [
            'material_tags' => ['ma_trượng', 'pháp_thư', 'quặng_ma_lực'],
            'currencies' => ['học_điểm', 'ma_thạch'],
        ],
        'narrative' => [
            'tone' => 'modern_fantasy',
            'keywords' => ['học viện', 'phép thuật', 'đồng đội'],
        ],
    ],
    'mao_son_phu_luc' => [
        'label' => 'Mao Sơn Phù Lục',
        'preset_key' => 'mao_son_phu_luc',
        'power_system' => 'TALISMAN',
        'paths' => [
            'body' => ['đạo_thể', 'bùa_phép'],
            'energy' => ['âm_dương', 'ngũ_hành'],
            'spirit' => ['trấn_yểm', 'đàm_phán_quỷ_thần'],
        ],
        'resources' => [
            'material_tags' => ['linh_mộc', 'pháp_chú', 'bùa_chú'],
            'currencies' => ['công_đức', 'truyền_thừa'],
        ],
        'narrative' => [
            'tone' => 'ling_di',
            'keywords' => ['trừ tà', 'phù lục', 'âm phủ'],
        ],
    ],
    'huyen_mon_bi_phap' => [
        'label' => 'Huyền Môn Bí Pháp',
        'preset_key' => 'huyen_mon_bi_phap',
        'power_system' => 'OCCULT_RITUAL',
        'paths' => [
            'body' => ['tế_lễ', 'khế_ước'],
            'energy' => ['gọi_thực_thể', 'niêm_ấn'],
            'spirit' => ['thần_thoại', 'hợp_đồng'],
        ],
        'resources' => [
            'material_tags' => ['hiến_vật', 'huyền_tạng', 'chú_văn'],
            'currencies' => ['linh_hồn_hợp_đồng', 'tín_ngưỡng'],
        ],
        'narrative' => [
            'tone' => 'occult',
            'keywords' => ['triệu hồi', 'khế ước', 'bí mật'],
        ],
    ],
    'neon_dystopia' => [
        'label' => 'Neon Dystopia',
        'preset_key' => 'neon_dystopia',
        'power_system' => 'TECH_IMPLANT',
        'paths' => [
            'body' => ['cybernetic_upgrade', 'gene_splicing'],
            'energy' => ['neural_grid', 'battery_core'],
            'spirit' => ['ai_synced', 'virtual_identity'],
        ],
        'resources' => [
            'material_tags' => ['microchip', 'rare_alloy', 'neuro_gel'],
            'currencies' => ['credit', 'data_token'],
        ],
        'narrative' => [
            'tone' => 'cyberpunk',
            'keywords' => ['megacorp', 'hacker', 'dưới_tầng'],
        ],
    ],
    'niem_su_hoi' => [
        'label' => 'Niệm Sư Hội',
        'preset_key' => 'niem_su_hoi',
        'power_system' => 'NEN',
        'paths' => [
            'body' => ['nen_booster', 'nen_defense'],
            'energy' => ['nen_specialization', 'nen_category'],
            'spirit' => ['nen_oath', 'nen_guild'],
        ],
        'resources' => [
            'material_tags' => ['nen_core', 'aura_lens', 'combat_manual'],
            'currencies' => ['guild_token', 'license_points'],
        ],
        'narrative' => [
            'tone' => 'shonen',
            'keywords' => ['nhiệm vụ', 'đồng đội', 'đấu trường'],
        ],
    ],
    'tu_chan_khoa_hoc' => [
        'label' => 'Tu Chân Khoa Học',
        'preset_key' => 'tu_chan_khoa_hoc',
        'power_system' => 'MIXED',
        'paths' => [
            'body' => ['gene_refine', 'mechanical_aug'],
            'energy' => ['spirit_core', 'fusion_reactor'],
            'spirit' => ['dao_algorithm', 'data_soul'],
        ],
        'resources' => [
            'material_tags' => ['spirit_nanite', 'quantum_jade'],
            'currencies' => ['source_credit', 'spirit_bits'],
        ],
        'narrative' => [
            'tone' => 'hybrid',
            'keywords' => ['khoa huyễn', 'tu chân', 'hợp nhất'],
        ],
    ],
    'my_thuc_ky' => [
        'label' => 'Mỹ Thực Ký',
        'preset_key' => 'my_thuc_ky',
        'power_system' => 'NONE',
        'paths' => [
            'body' => ['gastronomy_mastery'],
            'energy' => ['culinary_fire'],
            'spirit' => ['family_bond'],
        ],
        'resources' => [
            'material_tags' => ['spice_legend', 'ancestral_recipe'],
            'currencies' => ['prestige_points', 'restaurant_rating'],
        ],
        'narrative' => [
            'tone' => 'slice_of_life',
            'keywords' => ['ẩm thực', 'gia đình', 'ấm áp'],
        ],
    ],
    'showbiz_phong_van' => [
        'label' => 'Showbiz Phong Vân',
        'preset_key' => 'showbiz_phong_van',
        'power_system' => 'NONE',
        'paths' => [
            'body' => ['stage_presence'],
            'energy' => ['fan_energy'],
            'spirit' => ['charisma_influence'],
        ],
        'resources' => [
            'material_tags' => ['signature_song', 'idol_brand'],
            'currencies' => ['fandom_points', 'media_credibility'],
        ],
        'narrative' => [
            'tone' => 'entertainment',
            'keywords' => ['thần tượng', 'ánh đèn', 'tranh đấu'],
        ],
    ],
    'than_y_tai_the' => [
        'label' => 'Thần Y Tái Thế',
        'preset_key' => 'than_y_tai_the',
        'power_system' => 'NONE',
        'paths' => [
            'body' => ['medical_skill'],
            'energy' => ['remedy_flow'],
            'spirit' => ['compassion_healing'],
        ],
        'resources' => [
            'material_tags' => ['rare_medicine', 'ancient_scroll'],
            'currencies' => ['reputation', 'medical_merit'],
        ],
        'narrative' => [
            'tone' => 'medical',
            'keywords' => ['cứu người', 'y thuật', 'kỳ tích'],
        ],
    ],
    'cung_dau_truyen_ky' => [
        'label' => 'Cung Đấu Truyền Kỳ',
        'preset_key' => 'cung_dau_truyen_ky',
        'power_system' => 'NONE',
        'paths' => [
            'body' => ['etiquette_training'],
            'energy' => ['court_intrigue'],
            'spirit' => ['royal_ambition'],
        ],
        'resources' => [
            'material_tags' => ['imperial_seal', 'palace_secret'],
            'currencies' => ['favor_points', 'influence_token'],
        ],
        'narrative' => [
            'tone' => 'court_intrigue',
            'keywords' => ['hậu cung', 'kế mưu', 'quyền lực'],
        ],
    ],
    'dai_tuong_quan' => [
        'label' => 'Đại Tướng Quân',
        'preset_key' => 'dai_tuong_quan',
        'power_system' => 'NONE',
        'paths' => [
            'body' => ['martial_drill'],
            'energy' => ['battle_strategy'],
            'spirit' => ['command_aura'],
        ],
        'resources' => [
            'material_tags' => ['battle_standard', 'war_map'],
            'currencies' => ['military_merit', 'campaign_supply'],
        ],
        'narrative' => [
            'tone' => 'military',
            'keywords' => ['chiến tranh', 'binh pháp', 'trận mạc'],
        ],
    ],
    'dai_viet_phong_van' => [
        'label' => 'Đại Việt Phong Vân',
        'preset_key' => 'dai_viet_phong_van',
        'power_system' => 'NONE',
        'paths' => [
            'body' => ['martial_tradition'],
            'energy' => ['imperial_mandate'],
            'spirit' => ['patriotic_soul'],
        ],
        'resources' => [
            'material_tags' => ['ancient_relic', 'mandate_scroll'],
            'currencies' => ['tribute', 'imperial_credit'],
        ],
        'narrative' => [
            'tone' => 'dynasty',
            'keywords' => ['triều đình', 'biên cương', 'trung nghĩa'],
        ],
    ],
    'hai_tac_vien_duong' => [
        'label' => 'Hải Tặc Viễn Dương',
        'preset_key' => 'hai_tac_vien_duong',
        'power_system' => 'NONE',
        'paths' => [
            'body' => ['sea_leg'],
            'energy' => ['wind_harness'],
            'spirit' => ['pirate_code'],
        ],
        'resources' => [
            'material_tags' => ['ancient_map', 'curse_artifact'],
            'currencies' => ['doubloon', 'bounty'],
        ],
        'narrative' => [
            'tone' => 'pirate',
            'keywords' => ['đại dương', 'khám phá', 'phiêu lưu'],
        ],
    ],
    'tinh_ha_chien_ky' => [
        'label' => 'Tinh Hà Chiến Ký',
        'preset_key' => 'tinh_ha_chien_ky',
        'power_system' => 'COSMIC_RULE',
        'paths' => [
            'body' => ['void_operator', 'stellar_pilot'],
            'energy' => ['quantum_flux', 'gravity_control'],
            'spirit' => ['cosmic_oath', 'stellar_manifest'],
        ],
        'resources' => [
            'material_tags' => ['dark_matter_core', 'stellar_relic'],
            'currencies' => ['galactic_credit', 'influence_shard'],
        ],
        'narrative' => [
            'tone' => 'space_opera',
            'keywords' => ['thiên hà', 'tinh không', 'đế quốc'],
        ],
    ],
    'kiem_than_online' => [
        'label' => 'Kiếm Thần Online',
        'preset_key' => 'kiem_than_online',
        'power_system' => 'VR_SIMULATION',
        'paths' => [
            'body' => ['avatar_sync', 'reaction_boost'],
            'energy' => ['virtual_mana', 'system_buff'],
            'spirit' => ['sentient_link', 'npc_trust'],
        ],
        'resources' => [
            'material_tags' => ['legendary_drop', 'system_artifact'],
            'currencies' => ['raid_token', 'system_exp'],
        ],
        'narrative' => [
            'tone' => 'vr_world',
            'keywords' => ['thế giới ảo', 'bản vá', 'guild'],
        ],
    ],
    'tien_hoa_mat_the' => [
        'label' => 'Tiến Hoá Mạt Thế',
        'preset_key' => 'tien_hoa_mat_the',
        'power_system' => 'EVOLUTION',
        'paths' => [
            'body' => ['gene_spike', 'mutant_resilience'],
            'energy' => ['bio_energy', 'rage_burst'],
            'spirit' => ['apocalypse_instinct', 'survivor_will'],
        ],
        'resources' => [
            'material_tags' => ['mutant_crystal', 'bio_sample'],
            'currencies' => ['survival_point', 'research_credit'],
        ],
        'narrative' => [
            'tone' => 'post_apocalyptic',
            'keywords' => ['tận thế', 'tiến hóa', 'sinh tồn'],
        ],
    ],
    'nhan_gia_dai_chien' => [
        'label' => 'Nhẫn Giả Đại Chiến',
        'preset_key' => 'nhan_gia_dai_chien',
        'power_system' => 'CHAKRA',
        'paths' => [
            'body' => ['taijutsu_form', 'chakra_network'],
            'energy' => ['ninjutsu_channel', 'senjutsu_state'],
            'spirit' => ['will_of_fire', 'clan_pact'],
        ],
        'resources' => [
            'material_tags' => ['chakra_scroll', 'summon_contract'],
            'currencies' => ['mission_rank', 'village_credit'],
        ],
        'narrative' => [
            'tone' => 'anime_power',
            'keywords' => ['nhẫn giả', 'nghi thức', 'gia tộc'],
        ],
    ],
    'he_thong_thuc_tinh' => [
        'label' => 'Hệ Thống Thức Tỉnh',
        'preset_key' => 'he_thong_thuc_tinh',
        'power_system' => 'SYSTEM_STATS',
        'paths' => [
            'body' => ['stat_training', 'quest_body'],
            'energy' => ['skill_upgrade', 'artifact_sync'],
            'spirit' => ['system_contract', 'hidden_class'],
        ],
        'resources' => [
            'material_tags' => ['system_core', 'artifact_fragment'],
            'currencies' => ['system_coin', 'experience'],
        ],
        'narrative' => [
            'tone' => 'system',
            'keywords' => ['thức tỉnh', 'level up', 'quest'],
        ],
    ],
    'do_thi_di_nang' => [
        'label' => 'Đô Thị Dị Năng',
        'preset_key' => 'do_thi_di_nang',
        'power_system' => 'ANOMALY',
        'paths' => [
            'body' => ['latent_awaken'],
            'energy' => ['ability_flux'],
            'spirit' => ['contract_circle'],
        ],
        'resources' => [
            'material_tags' => ['ability_core', 'urban_artifact'],
            'currencies' => ['awakening_point', 'guild_credit'],
        ],
        'narrative' => [
            'tone' => 'urban_fantasy',
            'keywords' => ['thức tỉnh', 'đô thị', 'dị năng'],
        ],
    ],
    'da_vu_tru_hanh' => [
        'label' => 'Đa Vũ Trụ Hành',
        'preset_key' => 'da_vu_tru_hanh',
        'power_system' => 'MULTIVERSE',
        'paths' => [
            'body' => ['dimension_shift'],
            'energy' => ['timeline_anchor'],
            'spirit' => ['multiverse_insight'],
        ],
        'resources' => [
            'material_tags' => ['dimension_key', 'timeline_shard'],
            'currencies' => ['anomaly_token', 'chronicle_credit'],
        ],
        'narrative' => [
            'tone' => 'multiverse_saga',
            'keywords' => ['đa vũ trụ', 'dị giới', 'hành trình'],
        ],
    ],
    'vong_lap_van_menh' => [
        'label' => 'Vòng Lặp Vận Mệnh',
        'preset_key' => 'vong_lap_van_menh',
        'power_system' => 'NONE',
        'paths' => [
            'body' => ['loop_resilience'],
            'energy' => ['time_sense'],
            'spirit' => ['fate_defiance'],
        ],
        'resources' => [
            'material_tags' => ['time_fragment', 'destiny_thread'],
            'currencies' => ['loop_memory', 'destiny_point'],
        ],
        'narrative' => [
            'tone' => 'time_loop',
            'keywords' => ['vòng lặp', 'định mệnh', 'thay đổi tương lai'],
        ],
    ],
];
