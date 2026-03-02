//! Universe Topology and Deferred Command Pattern
//! Manages fixed zones using SlotMap to ensure memory stability and 
//! $O(1)$ lookup while allowing safe multi-threading data transformations.

use serde::{Deserialize, Serialize};
use slotmap::{new_key_type, SlotMap};

use super::material::ZoneMaterial;
use super::knowledge::{EmbodiedKnowledge, KnowledgeCoreSignature};
use super::culture::CulturalStateVector;
use super::institution::{PoliticalEntity, MythScar};

// Định nghĩa khoá an toàn thay vì dùng raw id index
new_key_type! { pub struct ZoneId; }

/// Đại diện cho một phân khu lõi - Bất diệt theo mặt Topology
#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct SimulationZone {
    pub material: ZoneMaterial,
    pub knowledge: EmbodiedKnowledge,
    pub culture: CulturalStateVector,
    /// Bị chiếm hữu bởi chế độ nào? (None nghĩa là Vô Chính Phủ/Hỗn loạn)
    pub owner_regime: Option<u64>, 
    /// Mức độ căng thẳng do đói nghèo vật chất/áp bức
    pub material_stress: f64,
}

impl SimulationZone {
    pub fn new(material: ZoneMaterial) -> Self {
        Self {
            material,
            knowledge: EmbodiedKnowledge::new(),
            culture: CulturalStateVector::default_start(),
            owner_regime: None,
            material_stress: 0.0,
        }
    }
}

/// Commands cho Deferred Execution (Đảm bảo Thread Safety khi thêm/xoá/đổi cấu trúc vùng)
#[derive(Debug, Clone, Serialize, Deserialize)]
pub enum ZoneCommand {
    UpdateRegimeOwner(ZoneId, Option<u64>),
    ApplyMythScar(ZoneId, MythScar),
    /// Khảo cứu cho tương lai - Tạo đại dương/phá huỷ vùng đất vĩnh viễn
    ObliterateZone(ZoneId), 
}

/// Bảng thiết kế Siêu Vũ Trụ (Single Source of Truth)
#[derive(Serialize, Deserialize)]
pub struct Universe {
    /// Quản lý theo Map an toàn
    pub zones: SlotMap<ZoneId, SimulationZone>,
    pub knowledge_core: KnowledgeCoreSignature,
    pub entities: Vec<PoliticalEntity>,
    pub global_entropy: f64,
    /// Chứa danh sách lệnh để thực thi sau ở Phase 2 (Reduce) của Tick
    pub pending_commands: Vec<ZoneCommand>,
}

impl Universe {
    pub fn new() -> Self {
        Self {
            zones: SlotMap::with_key(),
            knowledge_core: KnowledgeCoreSignature::new(),
            entities: Vec::new(),
            global_entropy: 0.0,
            pending_commands: Vec::new(),
        }
    }

    /// Đẩy command vào hàng đợi thay vì update trực tiếp
    pub fn emit_command(&mut self, cmd: ZoneCommand) {
        self.pending_commands.push(cmd);
    }
    
    /// Apply tất cả queue thay đổi Topology sau khi Phase 1 (Parallel loop) đã chạy xong 
    pub fn sync_structural_changes(&mut self) {
        let commands = std::mem::take(&mut self.pending_commands);
        for cmd in commands {
            match cmd {
                ZoneCommand::UpdateRegimeOwner(zone_id, new_owner) => {
                    if let Some(zone) = self.zones.get_mut(zone_id) {
                        zone.owner_regime = new_owner;
                    }
                },
                ZoneCommand::ApplyMythScar(_zone_id, _scar) => {
                    // TODO: Găm vết sẹo vào hệ thống
                },
                ZoneCommand::ObliterateZone(zone_id) => {
                    self.zones.remove(zone_id); // SlotMap xoá trả về an toàn
                }
            }
        }
    }
}
