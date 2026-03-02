//! Macro Events Engine
//! Xử lý các sự kiện lớn như tách vùng, nổi loạn, tạo áp lực ly khai.
//! Được gọi khi có biến cố lớn (Macro-Event Trigger).

use crate::domain::universe::{Universe, ZoneCommand, SimulationZone};
use crate::domain::institution::{PoliticalEntity, MythScar};
use std::collections::HashMap;

pub struct MacroEventEngine;

impl MacroEventEngine {
    /// Đánh giá áp lực ly khai cho toàn bộ bản đồ
    pub fn evaluate_secession(&self, universe: &mut Universe) {
        // Tái tạo lại culture của các thủ đô hiện hành để so sánh Divergence
        // (Trong thực tế cần query từ Zone là thủ đô của Regime)
        let mut capital_cultures = HashMap::new();
        for entity in &universe.entities {
            let ref_culture = crate::domain::culture::CulturalStateVector::new(entity.ideology_vector);
            capital_cultures.insert(entity.id, ref_culture);
        }

        let mut commands_to_issue = Vec::new();

        for (id, zone) in universe.zones.iter() {
            if let Some(owner) = zone.owner_regime {
                if let Some(capital_culture) = capital_cultures.get(&owner) {
                    let pressure = zone.culture.calculate_secession_pressure(capital_culture, zone.material_stress);
                    
                    // Nổi loạn/Ngưỡng tột cùng (Destabilized / Split phase)
                    if pressure > 1.8 {
                        // Tước quyền kiểm soát của Empire cũ
                        commands_to_issue.push(ZoneCommand::UpdateRegimeOwner(id, None));
                        
                        // Để lại vết sẹo chấn thương tại khu vực đó (Trauma tích luỹ)
                        if let Some(entity) = universe.entities.iter().find(|e| e.id == owner) {
                            let scar = MythScar::new(entity);
                            commands_to_issue.push(ZoneCommand::ApplyMythScar(id, scar));
                        }
                    }
                }
            }
        }

        // Đẩy Queue để Thread-Safe
        for cmd in commands_to_issue {
            universe.emit_command(cmd);
        }
    }
}
