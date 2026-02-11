<?php

namespace App\Domains\World\Enums;

/**
 * Power System Types — based on Tinh-Khí-Thần theory.
 *
 * Phái Tinh-Khí-Thần: Most power systems follow the causal cycle
 *   Tinh (Body) → Khí (Energy) → Thần (Spirit) → Tinh
 *
 * Phái Quy Tắc: Rule-based systems exist above Tinh-Khí-Thần
 * Phái Công Nghệ: Technology-based systems bypass biological limits
 */
enum PowerSystemType: string
{
    // === Phái Tinh (Thể Xác / Essence) ===
    case INTERNAL_QI = 'INTERNAL_QI';           // Nội Kình — Kiếm hiệp
    case EVOLUTION = 'EVOLUTION';               // Tiến Hoá — Biến dị sinh học
    case ANOMALY = 'ANOMALY';                   // Dị Năng — Đột biến bẩm sinh

    // === Phái Khí (Năng Lượng / Energy) ===
    case SPIRITUAL_QI = 'SPIRITUAL_QI';         // Linh Khí — Tu chân
    case MANA = 'MANA';                         // Ma Lực — Harry Potter style
    case DARK_MANA = 'DARK_MANA';               // Hắc Ám — Cấm thuật, hiến tế
    case DEMONIC_QI = 'DEMONIC_QI';             // Ma Khí — Quỷ tu
    case NEN = 'NEN';                           // Niệm — Hunter x Hunter
    case CHAKRA = 'CHAKRA';                     // Chakra — Naruto style

    // === Phái Thần (Tinh Thần / Spirit) ===
    case SPIRITUAL_SENSE = 'SPIRITUAL_SENSE';   // Linh Giác — Tâm linh
    case OCCULT_RITUAL = 'OCCULT_RITUAL';       // Nghi Thức — Tế đàn, triệu hồi
    case TALISMAN = 'TALISMAN';                 // Phù Lục — Bùa chú, trấn yểm

    // === Phái Quy Tắc (Rule-based) ===
    case COSMIC_RULE = 'COSMIC_RULE';           // Quy Tắc Vũ Trụ
    case MULTIVERSE = 'MULTIVERSE';             // Đa Vũ Trụ
    case SYSTEM_STATS = 'SYSTEM_STATS';         // Hệ Thống — LitRPG

    // === Phái Công Nghệ ===
    case TECH_IMPLANT = 'TECH_IMPLANT';         // Cấy Ghép — Cyber
    case VR_SIMULATION = 'VR_SIMULATION';       // VR Giả Lập — SAO

    // === Special ===
    case NONE = 'NONE';                         // Không có hệ sức mạnh
    case MIXED = 'MIXED';                       // Kết hợp nhiều hệ

    /**
     * Get the pillar this power system belongs to.
     */
    public function pillar(): string
    {
        return match($this) {
            self::INTERNAL_QI, self::EVOLUTION, self::ANOMALY => 'tinh',
            self::SPIRITUAL_QI, self::MANA, self::DARK_MANA,
            self::DEMONIC_QI, self::NEN, self::CHAKRA => 'khi',
            self::SPIRITUAL_SENSE, self::OCCULT_RITUAL, self::TALISMAN => 'than',
            self::COSMIC_RULE, self::MULTIVERSE, self::SYSTEM_STATS => 'quy_tac',
            self::TECH_IMPLANT, self::VR_SIMULATION => 'cong_nghe',
            default => 'none',
        };
    }

    /**
     * Vietnamese label for display.
     */
    public function label(): string
    {
        return match($this) {
            self::NONE => 'Không',
            self::INTERNAL_QI => '🥋 Nội Kình',
            self::EVOLUTION => '🧬 Tiến Hoá',
            self::ANOMALY => '⚡ Dị Năng',
            self::SPIRITUAL_QI => '☯️ Linh Khí',
            self::MANA => '🪄 Ma Lực (Mana)',
            self::DARK_MANA => '🌑 Hắc Ám',
            self::DEMONIC_QI => '👹 Ma Khí',
            self::NEN => '🎯 Niệm (Nen)',
            self::CHAKRA => '🌀 Chakra',
            self::SPIRITUAL_SENSE => '👁️ Linh Giác',
            self::OCCULT_RITUAL => '📿 Nghi Thức',
            self::TALISMAN => '🔮 Phù Lục',
            self::COSMIC_RULE => '⚖️ Quy Tắc Vũ Trụ',
            self::MULTIVERSE => '🌌 Đa Vũ Trụ',
            self::SYSTEM_STATS => '📊 Hệ Thống',
            self::TECH_IMPLANT => '🤖 Cấy Ghép',
            self::VR_SIMULATION => '🎮 VR Giả Lập',
            self::MIXED => '🔀 Hỗn Hợp',
        };
    }
}
