pub struct CascadeThresholds {
    pub physics_to_chemistry: f64,
    pub chemistry_to_biology: f64,
    pub biology_to_cognition: f64,
    pub cognition_to_culture: f64,
}

impl CascadeThresholds {
    pub fn from_slice(data: &[f64]) -> Self {
        if data.len() < 4 {
            // Fallback defaults if not provided correctly
            return Self {
                physics_to_chemistry: 0.3,
                chemistry_to_biology: 0.4,
                biology_to_cognition: 0.5,
                cognition_to_culture: 0.6,
            };
        }
        Self {
            physics_to_chemistry: data[0],
            chemistry_to_biology: data[1],
            biology_to_cognition: data[2],
            cognition_to_culture: data[3],
        }
    }
}

pub struct LawVector {
    pub energy_stability: f64,       // theta_3
    pub entropy_growth: f64,         // theta_5
    pub stability_basin_depth: f64,  // theta_8
    pub collapse_probability: f64,   // theta_9
    pub abiogenesis: f64,            // theta_10
    pub mutation_volatility: f64,    // theta_11
    pub adaptation_efficiency: f64,  // theta_12
    pub cognitive_ceiling: f64,      // theta_13
    pub memory_persistence: f64,     // theta_15
    pub tech_accumulation_rate: f64, // theta_16
    pub meta_system_awareness: f64,  // theta_17
    pub self_organization: f64,      // theta_7
}

impl LawVector {
    pub fn from_slice(data: &[f64]) -> Self {
        if data.len() < 17 {
            // Fallback (zeroes) if vector is incomplete. Real logic requires full 17D.
            return Self {
                energy_stability: 0.0, entropy_growth: 0.0, stability_basin_depth: 0.0,
                collapse_probability: 0.0, abiogenesis: 0.0, mutation_volatility: 0.0,
                adaptation_efficiency: 0.0, cognitive_ceiling: 0.0, memory_persistence: 0.0,
                tech_accumulation_rate: 0.0, meta_system_awareness: 0.0, self_organization: 0.0,
            };
        }
        Self {
            energy_stability: data[2],       // theta_3 (1-based index 3 -> array index 2)
            entropy_growth: data[4],         // theta_5
            self_organization: data[6],      // theta_7
            stability_basin_depth: data[7],  // theta_8
            collapse_probability: data[8],   // theta_9
            abiogenesis: data[9],            // theta_10
            mutation_volatility: data[10],   // theta_11
            adaptation_efficiency: data[11], // theta_12
            cognitive_ceiling: data[12],     // theta_13
            memory_persistence: data[14],    // theta_15
            tech_accumulation_rate: data[15],// theta_16
            meta_system_awareness: data[16], // theta_17
        }
    }
}

pub struct CascadeState {
    pub physics: f64,
    pub chemistry: f64,
    pub biology: f64,
    pub cognition: f64,
    pub culture: f64,
}

impl CascadeState {
    pub fn from_slice(data: &[f64]) -> Self {
        if data.len() < 5 {
            return Self { physics: 0.0, chemistry: 0.0, biology: 0.0, cognition: 0.0, culture: 0.0 };
        }
        Self {
            physics: data[0],
            chemistry: data[1],
            biology: data[2],
            cognition: data[3],
            culture: data[4],
        }
    }

    pub fn to_vec(&self) -> Vec<f64> {
        vec![self.physics, self.chemistry, self.biology, self.cognition, self.culture]
    }
    
    pub fn apply_deltas(&self, deltas: &[f64; 5]) -> Self {
        Self {
            physics: (self.physics + deltas[0]).clamp(0.0, 1.0),
            chemistry: (self.chemistry + deltas[1]).clamp(0.0, 1.0),
            biology: (self.biology + deltas[2]).clamp(0.0, 1.0),
            cognition: (self.cognition + deltas[3]).clamp(0.0, 1.0),
            culture: (self.culture + deltas[4]).clamp(0.0, 1.0),
        }
    }
}

pub fn evolve_cascade(
    state: &CascadeState,
    law: &LawVector,
    thresholds: &CascadeThresholds,
) -> CascadeState {
    let mut deltas = [0.0; 5];

    // Physics layer
    deltas[0] = law.energy_stability * (1.0 - state.physics) * law.self_organization * 0.1
        - law.collapse_probability * state.physics * 0.05;

    // Chemistry layer
    if state.physics > thresholds.physics_to_chemistry {
        let activation = (state.physics - thresholds.physics_to_chemistry)
            / (1.0 - thresholds.physics_to_chemistry);
        deltas[1] = law.entropy_growth * state.physics * (1.0 - state.chemistry) * 0.08 * activation
            - (1.0 - law.energy_stability) * state.chemistry * 0.02;
    } else {
        deltas[1] = -state.chemistry * 0.05;
    }

    // Biology layer
    if state.chemistry > thresholds.chemistry_to_biology {
        let activation = (state.chemistry - thresholds.chemistry_to_biology)
            / (1.0 - thresholds.chemistry_to_biology);
        deltas[2] = law.stability_basin_depth * state.chemistry * (1.0 - state.biology) * 0.06
            * activation * law.abiogenesis
            - law.collapse_probability * state.biology * 0.03;
    } else {
        deltas[2] = -state.biology * 0.04;
    }

    // Cognition layer
    if state.biology > thresholds.biology_to_cognition {
        let activation = (state.biology - thresholds.biology_to_cognition)
            / (1.0 - thresholds.biology_to_cognition);
        let social_factor = law.cognitive_ceiling * (1.0 - state.cognition);
        deltas[3] = law.adaptation_efficiency * state.biology * social_factor * 0.05 * activation
            - law.mutation_volatility * state.cognition * 0.02;
    } else {
        deltas[3] = -state.cognition * 0.03;
    }

    // Culture layer
    if state.cognition > thresholds.cognition_to_culture {
        let activation = (state.cognition - thresholds.cognition_to_culture)
            / (1.0 - thresholds.cognition_to_culture);
        let tech_drive = law.memory_persistence * law.tech_accumulation_rate * state.cognition * 0.04;
        let meta_feedback = law.meta_system_awareness * state.culture * (1.0 - state.culture) * 0.03;
        deltas[4] = (tech_drive + meta_feedback) * activation
            - (1.0 - law.memory_persistence) * state.culture * 0.02;
    } else {
        deltas[4] = -state.culture * 0.02;
    }

    // Cascade reverse
    if state.physics < thresholds.physics_to_chemistry && state.chemistry > 0.1 {
        deltas[1] -= (thresholds.physics_to_chemistry - state.physics) * 0.1;
    }
    if state.chemistry < thresholds.chemistry_to_biology && state.biology > 0.1 {
        deltas[2] -= (thresholds.chemistry_to_biology - state.chemistry) * 0.08;
    }
    if state.biology < thresholds.biology_to_cognition && state.cognition > 0.1 {
        deltas[3] -= (thresholds.biology_to_cognition - state.biology) * 0.06;
    }
    if state.cognition < thresholds.cognition_to_culture && state.culture > 0.1 {
        deltas[4] -= (thresholds.cognition_to_culture - state.cognition) * 0.05;
    }

    state.apply_deltas(&deltas)
}
