//! Material Domain Model
//! Implement Axioms M1, M2, M3

use serde::{Deserialize, Serialize};

/// Hệ số tiêu hao khi chuyển hoá năng lượng -> Cấu trúc (Tạo ra Entropy).
pub const EXTRACTION_ENTROPY_MULTIPLIER: f64 = 0.05;
pub const DECAY_RATE_BASE: f64 = 0.01;

#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct ZoneMaterial {
    /// Lượng vật chất khả dụng tối đa (Bất biến/Không tự sinh)
    pub base_mass: f64,
    /// Phần base_mass đã được "tổ chức" (Hạ tầng, đô thị, công cụ)
    pub structured_mass: f64,
    /// Năng lượng dùng để tái tổ chức vật chất
    pub free_energy: f64,
    /// Mức độ phân rã [0.0, 1.0]
    pub entropy: f64,
}

impl ZoneMaterial {
    /// Initalize a new ZoneMaterial ensuring max boundary
    pub fn new(base_mass: f64, energy: f64) -> Self {
        Self {
            base_mass,
            structured_mass: 0.0,
            free_energy: energy,
            entropy: 0.0,
        }
    }

    /// Khai thác tài nguyên tạo cấu trúc: Chuyển `free_energy` -> `structured_mass`
    /// Có thể gây tăng entropy.
    pub fn extract_and_build(&mut self, effort: f64) {
        // Lượng có thể khai thác thực tế bị giới hạn bởi năng lượng và vật chất thô còn lại
        let available_unstructured = self.base_mass - self.structured_mass;
        if available_unstructured <= 0.0 {
            return; // Đã đạt trần vật chất
        }

        let actual_effort = effort.min(self.free_energy).min(available_unstructured);
        if actual_effort > 0.0 {
            self.free_energy -= actual_effort;
            self.structured_mass += actual_effort;
            // Tích luỹ entropy tỉ lệ thuận với lượng chuyển hoá
            self.entropy += actual_effort * EXTRACTION_ENTROPY_MULTIPLIER;
            self.validate_bounds();
        }
    }

    /// Suy thoái tự nhiên theo thời gian
    /// cấu trúc tự phân rã rụng lại thành vật chất vô định hình
    pub fn apply_natural_decay(&mut self) {
        let decay_amount = self.structured_mass * (DECAY_RATE_BASE + self.entropy * 0.1);
        self.structured_mass -= decay_amount;
        // Bức xạ nhiệt / Tự phục hồi quy luật tự nhiên, Entropy giảm nhẹ khi gỡ bỏ cấu trúc
        if self.entropy > 0.0 {
            self.entropy -= decay_amount * 0.01;
        }
        self.validate_bounds();
    }

    /// Ràng buộc Vật lý Tuyệt đối (Conservation Laws)
    fn validate_bounds(&mut self) {
        if self.structured_mass < 0.0 {
            self.structured_mass = 0.0;
        }
        if self.structured_mass > self.base_mass {
            self.structured_mass = self.base_mass;
        }
        if self.free_energy < 0.0 {
            self.free_energy = 0.0;
        }
        if self.entropy < 0.0 {
            self.entropy = 0.0;
        }
        // Entropy có thể dồn vọt lên quá 1.0 báo hiệu Crisis chuyển pha, không kẹp cứng ở 1.0
    }
}
