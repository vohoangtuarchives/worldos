//! Parallel Simulation Execution Loop
//! Uses Rayon to process micro-ticks for all zones independently mapping them to Deltas
//! and then reducing them sequentially.

use rayon::prelude::*;
use crate::domain::universe::{Universe, SimulationZone, ZoneId};
use serde::{Deserialize, Serialize};

#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct ZoneDelta {
    pub zone_id: ZoneId,
    pub generated_entropy: f64,
    pub knowledge_loss_fragment: f64,
    pub mythification_fragment: f64,
    pub emitted_stress: f64,
}

pub struct SimulationEngineLoop;

impl SimulationEngineLoop {
    /// Chạy 1 Epoch Tick bằng Rayon
    pub fn execute_parallel_tick(universe: &mut Universe) {
        // --- PHASE 1: MAP (Local Update) ---
        // Biến đổi trạng thái bên trong của mỗi Zone một cách hoàn toàn song song
        let deltas: Vec<ZoneDelta> = universe.zones
            .par_iter_mut() // Yêu cầu biến `zones` của SlotMap hỗ trợ iter_mut song song
            .map(|(id, zone)| Self::process_zone(id, zone))
            .collect(); // Phải collect ra ngoài trước khi apply lên cái chung

        // --- PHASE 2: REDUCE (Global Synchronization) ---
        // Chạy tuần tự Single-thread trên các Delta sinh ra để tránh Race-condition 
        // với Global Entropy hoặc Knowledge Core 
        Self::apply_global_reduction(universe, &deltas);

        // --- PHASE 3: COMMAND QUEUE FLUSH (Structural Changed) ---
        // Giải quyết việc Vùng ly khai, Vùng thay đổi quyền lực...
        universe.sync_structural_changes();
    }

    /// Xử lý logic vòng tuần hoàn cục bộ trong một Vùng
    fn process_zone(id: ZoneId, zone: &mut SimulationZone) -> ZoneDelta {
        let start_entropy = zone.material.entropy;
        
        // 1. Phân rã vật chất
        zone.material.apply_natural_decay();
        
        // Tái khai thác nếu còn năng lượng tự do và stress ít
        if zone.material_stress < 0.5 {
            let effort = zone.material.free_energy * 0.1;
            zone.material.extract_and_build(effort);
        }

        let entropy_delta = (zone.material.entropy - start_entropy).max(0.0);
        
        // 2. Chảy máu tri thức dựa theo entropy hiện tại
        let old_hard = zone.knowledge.hard_tech_level;
        zone.knowledge.apply_decay(zone.material.entropy);
        let hard_loss = old_hard - zone.knowledge.hard_tech_level;

        // 3. Trôi dạt văn hoá nội sinh ngẫu nhiên 
        // Lấy tâm hội tụ ngẫu nhiên (sẽ replace bằng attractor chuẩn sau)
        let local_attractors = [0.5, 0.5, 0.5, 0.5, 0.5, 0.5];
        zone.culture.apply_internal_drift(&local_attractors, zone.material_stress);
        
        // Cập nhật lại áp lực Stress cục bộ 
        zone.material_stress = zone.material.entropy * 0.6 + hard_loss * 0.4;
        
        ZoneDelta {
            zone_id: id,
            generated_entropy: entropy_delta,
            knowledge_loss_fragment: hard_loss * 0.1,  // Chỉ giữ 10% dư ảnh
            mythification_fragment: entropy_delta * 0.5, // Entropy càng cao, tri thức càng méo
            emitted_stress: zone.material_stress,
        }
    }

    /// Bước tổng hợp các Gradient / Môi trường chung ngập tràn 
    fn apply_global_reduction(universe: &mut Universe, deltas: &[ZoneDelta]) {
        use crate::domain::knowledge::KnowledgeResidual;

        let mut total_entropy_rise = 0.0;

        for delta in deltas {
            total_entropy_rise += delta.generated_entropy;
            
            // Xả tàn dư tri thức lên vũ trụ
            if delta.knowledge_loss_fragment > 0.0001 {
                let residual = KnowledgeResidual {
                    fragmented_tech: delta.knowledge_loss_fragment,
                    mythification_ratio: delta.mythification_fragment,
                };
                universe.knowledge_core.absorb_residual(&residual);
            }
            
            // Nếu Stress quá cao, Zone có thể sụp đổ (Emit lệnh đập vỡ cấu trúc) 
            if delta.emitted_stress > 0.95 {
                universe.emit_command(crate::domain::universe::ZoneCommand::UpdateRegimeOwner(delta.zone_id, None));
            }
        }

        // Entropy thế giới = Tổng uỷ thác từ các khu vực.
        universe.global_entropy += total_entropy_rise * 0.001; 
    }
}
