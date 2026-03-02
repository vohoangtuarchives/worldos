//! Cultural State Vector Domain
//! Represents the historical accumulation layer of societal beliefs
//! and structural psychological drift over generations.

use serde::{Deserialize, Serialize};

pub const CULTURAL_DIMENSIONS: usize = 6;
pub const BASE_CULTURAL_DRIFT_RATE: f64 = 0.005;

/// Các chiều văn hoá:
/// Index 0: Tradition Rigidity
/// Index 1: Innovation Openness
/// Index 2: Collective Trust
/// Index 3: Violence Tolerance
/// Index 4: Institutional Respect
/// Index 5: Myth Intensity
#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct CulturalStateVector {
    pub values: [f64; CULTURAL_DIMENSIONS],
}

impl CulturalStateVector {
    pub fn new(seed_values: [f64; CULTURAL_DIMENSIONS]) -> Self {
        Self {
            values: seed_values,
        }
    }
    
    pub fn default_start() -> Self {
        Self {
            values: [0.5, 0.5, 0.5, 0.5, 0.5, 0.5],
        }
    }

    /// Trôi dạt nội hạt (Slow Internal Drift)
    /// Hệ bị kéo dần về các xu hướng cố hữu với tốc độ cực kì chậm
    pub fn apply_internal_drift(&mut self, attractors: &[f64; CULTURAL_DIMENSIONS], stress_factor: f64) {
        for i in 0..CULTURAL_DIMENSIONS {
            let diff = attractors[i] - self.values[i];
            // Inertia: Khi tiệm cận biên (0.0 hoặc 1.0), tốc độ thay đổi tụt dần về 0
            let inertia = self.values[i] * (1.0 - self.values[i]) * 4.0; // max 1.0 at 0.5
            
            // Stress ép thay đổi nhanh hơn
            let drift_speed = BASE_CULTURAL_DRIFT_RATE * (1.0 + stress_factor) * inertia;
            self.values[i] += diff * drift_speed;
            
            // Re-bound
            if self.values[i] < 0.0 { self.values[i] = 0.0; }
            if self.values[i] > 1.0 { self.values[i] = 1.0; }
        }
    }

    /// Tính toán áp lực ly khai dựa trên Divergence và Material Stress
    pub fn calculate_secession_pressure(&self, capital_culture: &Self, material_stress: f64) -> f64 {
        // Divergence
        let mut divergence = 0.0;
        for i in 0..CULTURAL_DIMENSIONS {
            divergence += (self.values[i] - capital_culture.values[i]).abs();
        }
        divergence /= CULTURAL_DIMENSIONS as f64; // normalize 0..1

        let institutional_trust = self.values[2]; // Index 2
        
        // P_z = a*D_z + b*S_z - c*Trust
        let pressure = (1.5 * divergence) + (2.0 * material_stress) - (1.0 * institutional_trust);
        pressure.max(0.0)
    }
}
