//! Institutional Structure and Myth Scar Domain
//! Represents the persistence of political thought, organizations, and 
//! the permanent structural scars they leave behind after collapse.

use serde::{Deserialize, Serialize};

/// Thực Thể Thể Chế Bền Vững (Đế chế, Tôn giáo lớn, Vương triều)
#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct PoliticalEntity {
    pub id: u64,
    /// Trục tư tưởng đại diện
    pub ideology_vector: [f64; super::culture::CULTURAL_DIMENSIONS],
    /// Ký ức thể chế (truyền đời dài hạn, Trauma)
    pub institutional_memory: f64,
    /// Năng lực tổ chức
    pub organizational_capacity: f64,
    /// Tính chính danh
    pub legitimacy: f64,
}

impl PoliticalEntity {
    pub fn is_dead(&self) -> bool {
        // Sinh Lão Bệnh Tử: Nếu năng lực hoặc chính danh thụt tới đáy
        self.organizational_capacity < 0.1 || self.legitimacy < 0.1
    }

    /// Tự phân rã do cồng kềnh
    pub fn decay_over_time(&mut self, structural_stress: f64) {
        // Ngủ quên trên chiến thắng/đế chế già cỗi
        let base_decay = 0.001; 
        self.organizational_capacity -= self.organizational_capacity * (base_decay + structural_stress * 0.1);
        self.legitimacy -= self.legitimacy * (base_decay * 2.0 + structural_stress * 0.15);

        if self.organizational_capacity < 0.0 { self.organizational_capacity = 0.0; }
        if self.legitimacy < 0.0 { self.legitimacy = 0.0; }
    }
}

/// Vết Hằn Lịch Sử (Myth Scar)
/// Khi Institution chết đi, nó thành vết sẹo ám ảnh liên kết không gian.
#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct MythScar {
    /// Ideology cắm vào vùng đất khi chết
    pub ideology_snapshot: [f64; super::culture::CULTURAL_DIMENSIONS],
    /// Độ sâu của vết thương (Trauma)
    pub trauma_level: f64,
    /// Sự dai dẳng (Cực kì khó phai)
    pub symbolic_power: f64,
}

impl MythScar {
    pub fn new(entity: &PoliticalEntity) -> Self {
        Self {
            ideology_snapshot: entity.ideology_vector,
            trauma_level: entity.institutional_memory.max(0.5), // Ám ảnh càng lâu, chấn thương càng sâu
            symbolic_power: entity.legitimacy, 
        }
    }

    /// Rã phai nhạt qua cả ngàn thế hệ
    pub fn fade(&mut self) {
        // Rã siêu chậm
        self.symbolic_power *= 0.9999;
        self.trauma_level *= 0.9995;
    }
    
    pub fn is_forgotten(&self) -> bool {
        self.symbolic_power < 0.01 && self.trauma_level < 0.01
    }
}
