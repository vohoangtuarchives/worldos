//! Meta-Cycle Engine
//! Xác định sự sụp đổ vũ trụ (Civilizational Collapse/Reset)
//! và phân rã hàng loạt vật chất, đưa lịch sử sang Kỷ Nguyên mới.

use crate::domain::universe::Universe;

pub struct MetaCycleEngine;

impl MetaCycleEngine {
    /// Kiểm tra Coherence và kích hoạt chuyển pha nếu cần
    pub fn trigger_metacycle_if_needed(universe: &mut Universe) -> bool {
        // Structural Coherence Index - SCI (Cấu trúc chống lại Entropy)
        // Khi entropy trung bình quá cao, SCI = 0 -> Sụp đổ
        let total_zones = universe.zones.len() as f64;
        if total_zones == 0.0 { return false; }
        
        let avg_entropy = universe.global_entropy / total_zones;
        let sci = 1.0 - (avg_entropy * 0.5);

        if sci < 0.2 { // Ngưỡng nguy hiểm 20%
            Self::execute_catastrophic_reset(universe);
            return true;
        }
        
        false
    }

    /// Reset vũ trụ (Macro Shock)
    /// Xoá 80% cơ sở vật chất, Memory Residual đọng lại thành Core
    fn execute_catastrophic_reset(universe: &mut Universe) {
        for (_id, zone) in universe.zones.iter_mut() {
            // Mất 80% cấu trúc văn minh -> trả về free_energy để bắt đầu kỉ mới
            let collapsed_mass = zone.material.structured_mass * 0.8;
            zone.material.structured_mass -= collapsed_mass;
            zone.material.free_energy += collapsed_mass * 0.1; // Cháy thành tro 90%
            
            // Dọn sạch tàn tích quyền lực
            zone.owner_regime = None;
            
            // Xoá sổ kỹ năng sống
            zone.knowledge.hard_tech_level = 1.0; 
            zone.knowledge.soft_tech_level = (zone.knowledge.soft_tech_level * 0.5).max(1.0);
            
            // Đặt lại stress về an toàn do con người đã reset
            zone.material_stress = 0.0;
            zone.material.entropy = 0.0; 
        }

        // Tàn lụi Tổ chức 
        universe.entities.clear();
        
        // Cập nhật Vũ Trụ mới
        universe.global_entropy = 0.0;
    }
}
