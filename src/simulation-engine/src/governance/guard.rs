use ndarray::Array1;

pub struct GovernanceGuard {
    pub delta_target: f64,
    pub gamma_cap: f64,
    pub r_max: f64,
    pub energy_rate_limit: f64, // 1 - delta + epsilon
}

impl GovernanceGuard {
    pub fn new(delta_target: f64, gamma_cap: f64, r_max: f64, energy_rate_limit: f64) -> Self {
        Self { delta_target, gamma_cap, r_max, energy_rate_limit }
    }

    pub fn check_input_norm(&self, u_t: &Array1<f64>) -> Result<(), String> {
        let norm = u_t.dot(u_t).sqrt();
        if norm > self.gamma_cap {
            return Err(format!("Input norm {:.4} exceeds gamma_cap {:.4}", norm, self.gamma_cap));
        }
        Ok(())
    }

    /// Verifies absolute stability (Lyapunov): ρ(J) <= 1 - delta_target
    /// This uses MathCore's power iteration to get the exact spectral radius instead of 
    /// the Gershgorin upper-bound, significantly reducing false positives.
    pub fn check_lyapunov_stability(&self, spectral_radius: f64) -> Result<(), String> {
        let margin = 1.0 - self.delta_target;
        if spectral_radius > margin {
            return Err(format!("Spectral radius {:.4} violates Lyapunov absolute stability margin {:.4}", spectral_radius, margin));
        }
        Ok(())
    }

    pub fn check_state_bounds(&self, x_t: &Array1<f64>) -> Result<(), String> {
        let norm = x_t.dot(x_t).sqrt();
        if norm > self.r_max {
             return Err(format!("State norm {:.4} exceeds R_max {:.4}", norm, self.r_max));
        }
        Ok(())
    }

    pub fn check_energy_budget(&self, x_t: &Array1<f64>, x_next: &Array1<f64>) -> Result<(), String> {
        let norm_t = x_t.dot(x_t).sqrt();
        if norm_t < 1e-6 {
            return Ok(()); // Avoid division by zero
        }
        let norm_next = x_next.dot(x_next).sqrt();
        let r = norm_next / norm_t;
        if r > self.energy_rate_limit {
            return Err(format!("Energy rate {:.4} exceeds limit {:.4}", r, self.energy_rate_limit));
        }
        Ok(())
    }
}
