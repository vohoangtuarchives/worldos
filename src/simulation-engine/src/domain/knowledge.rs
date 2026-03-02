//! Knowledge Envelope Domain Model
//! Implements Tech Ceiling, Culture-Material limitations, and residual generation.

use serde::{Deserialize, Serialize};

/// Hệ số méo mó tri thức khi lõi nền văn minh bị ép vào Meta-Cycle
pub const KNOWLEDGE_DISTORTION_FACTOR: f64 = 0.1;

/// Tầng lưu trữ tri thức gắn kết hiện tại với Zone (sống chung với cơ thể hạ tầng).
#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct EmbodiedKnowledge {
    /// Định mức kỹ thuật cứng (Hard Tech) - Liên đới trực tiếp với structured_mass
    pub hard_tech_level: f64,
    /// Định mức kỹ năng mềm/tổ chức (Soft Tech)
    pub soft_tech_level: f64,
}

impl EmbodiedKnowledge {
    pub fn new() -> Self {
        Self {
            hard_tech_level: 1.0,
            soft_tech_level: 1.0,
        }
    }

    /// Suy thoái tri thức. Hard Tech rụng siêu nhanh, Soft Tech rụng chậm hơn.
    pub fn apply_decay(&mut self, structural_entropy: f64) {
        self.hard_tech_level -= self.hard_tech_level * structural_entropy * 0.5;
        self.soft_tech_level -= self.soft_tech_level * structural_entropy * 0.1;

        if self.hard_tech_level < 1.0 { self.hard_tech_level = 1.0; }
        if self.soft_tech_level < 1.0 { self.soft_tech_level = 1.0; }
    }
}

/// Tàn dư tri thức còn sót lại sau khi Zone Collapse
#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct KnowledgeResidual {
    /// Cốt lõi nhận thức của dân số tị nạn/sống sót
    pub fragmented_tech: f64,
    /// Bị bóp méo thành huyền thoại/tôn giáo
    pub mythification_ratio: f64,
}

/// Lõi Tri thức Vũ trụ (Tích luỹ vô hạn, xuyên thế hệ)
#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct KnowledgeCoreSignature {
    /// Tổng tri thức vũ trụ thực tế
    pub absolute_truth_level: f64,
    /// Phần trăm tri thức bị méo mó/thất truyền sau mỗi Meta-Cycle
    pub global_distortion_index: f64,
}

impl KnowledgeCoreSignature {
    pub fn new() -> Self {
        Self {
            absolute_truth_level: 1.0, // Base level 1.0 (Stone age basic)
            global_distortion_index: 0.0,
        }
    }

    /// Cập nhật trường Signature khi một Zone/Kỷ nguyên sụp đổ
    pub fn absorb_residual(&mut self, residual: &KnowledgeResidual) {
        // Cộng dồn nhỏ giọt vào Lõi Vũ trụ
        self.absolute_truth_level += residual.fragmented_tech * 0.01;
        // Tăng độ nén của thần thoại
        self.global_distortion_index += residual.mythification_ratio * KNOWLEDGE_DISTORTION_FACTOR;
    }
}

/// Tính toán Tech Ceiling:
/// Theoretical_Ceiling = base_physical_cap * cultural_openness * material_surplus * institutional_stability
pub fn calculate_theoretical_ceiling(
    base_physical_cap: f64,
    cultural_openness: f64,
    material_free_energy: f64,
    institutional_trust: f64,
) -> f64 {
    let surplus_factor = 1.0 + (material_free_energy * 0.01).min(5.0); // max 5x từ vật chất dôi dư
    base_physical_cap * cultural_openness * surplus_factor * institutional_trust
}
