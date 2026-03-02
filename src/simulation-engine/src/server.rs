use tonic::{Request, Response, Status};
use crate::simulation::simulation_engine_server::SimulationEngine;
use crate::simulation::{TickRequest, TickResponse};
use crate::math::core::MathCore;
use crate::governance::guard::GovernanceGuard;
use crate::cascade::{CascadeState, CascadeThresholds, LawVector, evolve_cascade};
use ndarray::{Array1, Array2};
use redis::AsyncCommands;
use serde_json::json;
use tokio::sync::Mutex;
use std::sync::Arc;

pub struct MySimulationEngine {
    pub redis: Arc<Mutex<redis::aio::MultiplexedConnection>>,
}

impl MySimulationEngine {
    pub fn new(redis: redis::aio::MultiplexedConnection) -> Self {
        Self {
            redis: Arc::new(Mutex::new(redis)),
        }
    }
}

#[tonic::async_trait]
impl SimulationEngine for MySimulationEngine {
    async fn run_tick(
        &self,
        request: Request<TickRequest>,
    ) -> Result<Response<TickResponse>, Status> {
        let req = request.into_inner();
        
        let dim = req.dimension as usize;
        if req.current_state.len() != dim || req.control_vector.len() != dim {
             return Err(Status::invalid_argument("Vector length mismatch"));
        }
        if req.a_matrix.len() != dim * dim || req.l_matrix.len() != dim * dim {
             return Err(Status::invalid_argument("Matrix length mismatch"));
        }

        let x_t = Array1::from(req.current_state);
        let u_t = Array1::from(req.control_vector);
        
        let a_matrix = Array2::from_shape_vec((dim, dim), req.a_matrix)
            .map_err(|e| Status::internal(format!("Failed to parse A Matrix: {}", e)))?;
            
        let l_matrix = Array2::from_shape_vec((dim, dim), req.l_matrix)
            .map_err(|e| Status::internal(format!("Failed to parse L Matrix: {}", e)))?;

        let math_core = MathCore::new(req.alpha, req.lambda, req.eta, req.beta);
        let guard = GovernanceGuard::new(req.delta_target, req.gamma_cap, req.r_max, req.energy_rate_limit);

        // --- Governance Check 1: Input norm ---
        if let Err(reason) = guard.check_input_norm(&u_t) {
            self.publish_event("GOVERNANCE_VIOLATION", &req.universe_id, &reason).await;
            return Ok(Response::new(TickResponse {
                universe_id: req.universe_id,
                success: false,
                next_state: vec![],
                next_cascade_state: vec![],
                error_message: reason,
            }));
        }

        let jacobian = math_core.compute_jacobian(&a_matrix, &l_matrix);
        let spectral_radius = math_core.compute_spectral_radius(&jacobian);
        
        // --- Governance Check 2: Lyapunov Absolute Stability ---
        if let Err(reason) = guard.check_lyapunov_stability(spectral_radius) {
            self.publish_event("GOVERNANCE_VIOLATION", &req.universe_id, &reason).await;
            return Ok(Response::new(TickResponse {
                universe_id: req.universe_id,
                success: false,
                next_state: vec![],
                next_cascade_state: vec![],
                error_message: reason,
            }));
        }

        let x_next = math_core.compute_next_state(&jacobian, &x_t, &u_t);

        // --- Governance Check 3: State bounds ---
        if let Err(reason) = guard.check_state_bounds(&x_next) {
            self.publish_event("GOVERNANCE_VIOLATION", &req.universe_id, &reason).await;
            return Ok(Response::new(TickResponse {
                universe_id: req.universe_id,
                success: false,
                next_state: vec![],
                next_cascade_state: vec![],
                error_message: reason,
            }));
        }

        // --- Governance Check 4: Energy budget ---
        if let Err(reason) = guard.check_energy_budget(&x_t, &x_next) {
            self.publish_event("GOVERNANCE_VIOLATION", &req.universe_id, &reason).await;
            return Ok(Response::new(TickResponse {
                universe_id: req.universe_id,
                success: false,
                next_state: vec![],
                next_cascade_state: vec![],
                error_message: reason,
            }));
        }

        let x_next_vec = x_next.to_vec();

        // --- Run Cascade Engine ---
        let cascade_state = CascadeState::from_slice(&req.current_cascade);
        let cascade_thresholds = CascadeThresholds::from_slice(&req.cascade_thresholds);
        let law_vector = LawVector::from_slice(&req.law_vector);

        let next_cascade = evolve_cascade(&cascade_state, &law_vector, &cascade_thresholds);
        let next_cascade_vec = next_cascade.to_vec();

        // --- V1.1.0 Init Universe from JSON ---
        let mut universe = if !req.zone_topology_json.is_empty() {
            serde_json::from_str(&req.zone_topology_json)
                .unwrap_or_else(|_| crate::domain::universe::Universe::new())
        } else {
            crate::domain::universe::Universe::new()
        };

        // --- Run Compute Layer Phase 2 (Parallel Map-Reduce) ---
        crate::engine::sim_loop::SimulationEngineLoop::execute_parallel_tick(&mut universe);
        let mut macro_event = crate::engine::events::MacroEventEngine;
        macro_event.evaluate_secession(&mut universe);
        crate::engine::meta_cycle::MetaCycleEngine::trigger_metacycle_if_needed(&mut universe);

        // --- Package updated universe ---
        let updated_topology_json = serde_json::to_string(&universe).unwrap_or_default();
        let current_global_entropy = universe.global_entropy;

        let payload = json!({
            "type": "TICK_COMPLETED",
            "universe_id": req.universe_id,
            "x_next": x_next_vec,
            "next_cascade": next_cascade_vec,
            "spectral_radius": spectral_radius,
            "global_entropy": current_global_entropy,
        }).to_string();
        {
            let mut con = self.redis.lock().await;
            let _: () = con.publish("simulation_events", payload).await.unwrap_or(());
        }

        Ok(Response::new(TickResponse {
            universe_id: req.universe_id,
            success: true,
            next_state: x_next_vec,
            next_cascade_state: next_cascade_vec,
            error_message: "".to_string(),
            zone_topology_json: updated_topology_json,
            global_entropy: current_global_entropy,
        }))
    }
}

impl MySimulationEngine {
    async fn publish_event(&self, event_type: &str, universe_id: &str, reason: &str) {
        let payload = json!({
            "type": event_type,
            "universe_id": universe_id,
            "reason": reason,
        }).to_string();

        let mut con = self.redis.lock().await;
        let _: () = con.publish("simulation_events", payload).await.unwrap_or(());
    }
}
