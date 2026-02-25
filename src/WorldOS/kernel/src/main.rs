use axum::{
    extract::{Json, Query, State},
    http::StatusCode,
    response::IntoResponse,
    routing::{get, post},
    Router,
};
use serde::{Deserialize, Serialize};
use std::env;
use std::sync::Arc;
use tokio_postgres::{Client, NoTls};
use uuid::Uuid;

struct AppState {
    db: Option<Db>,
}

struct Db {
    client: Client,
}

#[derive(Clone, Debug, Serialize)]
struct HealthResponse {
    status: String,
    db: String,
}

#[derive(Clone, Debug, Deserialize)]
struct SimulateRequest {
    law_params_variant: Option<LawParamsVariant>,
    law_params: LawParamsInput,
    seed: u64,
    ticks: u32,
    initial_state: Option<StateVector>,
}

#[derive(Clone, Debug, Serialize)]
struct SimulateResponse {
    feasible: bool,
    world_id: Option<String>,
    universe_id: Option<String>,
    law_params_variant: Option<LawParamsVariant>,
    snapshots: Vec<Snapshot>,
}

#[derive(Clone, Debug, Deserialize)]
#[serde(untagged)]
enum LawParamsInput {
    Semantic(LawParams),
    Theta(ThetaParams),
}

impl LawParamsInput {
    fn into_semantic(self) -> (LawParams, LawParamsVariant) {
        match self {
            LawParamsInput::Semantic(value) => (value, LawParamsVariant::Semantic),
            LawParamsInput::Theta(value) => (
                LawParams {
                    dimensionality: value.theta1,
                    causality_rigidity: value.theta2,
                    energy_stability: value.theta3,
                    interaction_strength: value.theta4,
                    entropy_growth: value.theta5,
                    matter_complexity_threshold: value.theta6,
                    self_organization_bias: value.theta7,
                    stability_basin_depth: value.theta8,
                    collapse_probability: value.theta9,
                    abiogenesis: value.theta10,
                    mutation_volatility: value.theta11,
                    adaptation_efficiency: value.theta12,
                    cognitive_ceiling: value.theta13,
                    myth_formation: value.theta14,
                    memory_persistence: value.theta15,
                    technological_accumulation_rate: value.theta16,
                    meta_system_awareness: value.theta17,
                },
                LawParamsVariant::Theta,
            ),
        }
    }
}

#[derive(Clone, Debug, Deserialize, Serialize, PartialEq, Eq)]
#[serde(rename_all = "snake_case")]
enum LawParamsVariant {
    Semantic,
    Theta,
}

#[derive(Clone, Debug, Deserialize, Serialize)]
struct LawParams {
    dimensionality: f64,
    causality_rigidity: f64,
    energy_stability: f64,
    interaction_strength: f64,
    entropy_growth: f64,
    matter_complexity_threshold: f64,
    self_organization_bias: f64,
    stability_basin_depth: f64,
    collapse_probability: f64,
    abiogenesis: f64,
    mutation_volatility: f64,
    adaptation_efficiency: f64,
    cognitive_ceiling: f64,
    myth_formation: f64,
    memory_persistence: f64,
    technological_accumulation_rate: f64,
    meta_system_awareness: f64,
}

#[derive(Clone, Debug, Deserialize)]
struct ThetaParams {
    theta1: f64,
    theta2: f64,
    theta3: f64,
    theta4: f64,
    theta5: f64,
    theta6: f64,
    theta7: f64,
    theta8: f64,
    theta9: f64,
    theta10: f64,
    theta11: f64,
    theta12: f64,
    theta13: f64,
    theta14: f64,
    theta15: f64,
    theta16: f64,
    theta17: f64,
}

#[derive(Clone, Debug, Serialize)]
struct ApiError {
    error: String,
}

#[derive(Clone, Debug, Deserialize, Serialize)]
struct StateVector {
    p: f64,
    c: f64,
    b: f64,
    n: f64,
    k: f64,
}

#[derive(Clone, Debug, Serialize)]
struct Snapshot {
    tick: u32,
    state: StateVector,
    metrics: Metrics,
}

#[derive(Clone, Debug, Deserialize, Serialize)]
struct Metrics {
    stability: f64,
    entropy: f64,
    collapsed: bool,
}

#[derive(Clone, Debug, Deserialize)]
struct SnapshotsQuery {
    universe_id: String,
    from: Option<u32>,
    to: Option<u32>,
}

#[derive(Clone, Debug, Deserialize)]
struct LatestSnapshotQuery {
    universe_id: String,
}

#[derive(Clone, Debug, Deserialize)]
struct ListQuery {
    limit: Option<i64>,
    offset: Option<i64>,
}

#[derive(Clone, Debug, Serialize)]
struct SnapshotsResponse {
    universe_id: String,
    snapshots: Vec<SnapshotRecord>,
}

#[derive(Clone, Debug, Serialize)]
struct LatestSnapshotResponse {
    universe_id: String,
    snapshot: Option<SnapshotRecord>,
}

#[derive(Clone, Debug, Serialize)]
struct SnapshotRecord {
    tick: u32,
    state: StateVector,
    metrics: Metrics,
}

#[derive(Clone, Debug, Serialize)]
struct WorldRecord {
    id: String,
    law_params: LawParams,
    created_at: String,
}

#[derive(Clone, Debug, Serialize)]
struct UniverseRecord {
    id: String,
    world_id: String,
    seed: i64,
    current_tick: i32,
    created_at: String,
}

#[derive(Clone, Debug, Serialize)]
struct LawParamsSchemaResponse {
    variant: LawParamsVariant,
    parameters: Vec<LawParamSchema>,
}

#[derive(Clone, Debug, Serialize)]
struct LawParamSchema {
    key: String,
    theta: String,
    label: String,
    group: String,
    range: RangeSchema,
}

#[derive(Clone, Debug, Serialize)]
struct RangeSchema {
    min: f64,
    max: f64,
}

#[tokio::main]
async fn main() {
    let db = init_db().await;
    let state = Arc::new(AppState { db });
    let app = Router::new()
        .route("/health", get(health))
        .route("/simulate", post(simulate))
        .route("/schemas/law-params", get(law_params_schema))
        .route("/snapshots", get(get_snapshots))
        .route("/snapshots/latest", get(get_latest_snapshot))
        .route("/worlds", get(list_worlds))
        .route("/worlds/:id", get(get_world))
        .route("/universes", get(list_universes))
        .route("/universes/:id", get(get_universe))
        .with_state(state);

    let listener = tokio::net::TcpListener::bind("0.0.0.0:8080")
        .await
        .unwrap();
    axum::serve(listener, app).await.unwrap();
}

async fn health(State(state): State<Arc<AppState>>) -> impl IntoResponse {
    let db_status = if state.db.is_some() {
        "connected".to_string()
    } else {
        "unavailable".to_string()
    };
    (
        StatusCode::OK,
        Json(HealthResponse {
            status: "ok".to_string(),
            db: db_status,
        }),
    )
}

async fn law_params_schema() -> impl IntoResponse {
    let parameters = vec![
        schema_param(
            "dimensionality",
            "theta1",
            "Dimensionality",
            "fundamental_physics",
        ),
        schema_param(
            "causality_rigidity",
            "theta2",
            "Causality Rigidity",
            "fundamental_physics",
        ),
        schema_param(
            "energy_stability",
            "theta3",
            "Energy Stability",
            "fundamental_physics",
        ),
        schema_param(
            "interaction_strength",
            "theta4",
            "Interaction Strength",
            "fundamental_physics",
        ),
        schema_param(
            "entropy_growth",
            "theta5",
            "Entropy Growth",
            "fundamental_physics",
        ),
        schema_param(
            "matter_complexity_threshold",
            "theta6",
            "Matter Complexity Threshold",
            "structure",
        ),
        schema_param(
            "self_organization_bias",
            "theta7",
            "Self-Organization Bias",
            "structure",
        ),
        schema_param(
            "stability_basin_depth",
            "theta8",
            "Stability Basin Depth",
            "structure",
        ),
        schema_param(
            "collapse_probability",
            "theta9",
            "Collapse Probability",
            "structure",
        ),
        schema_param(
            "abiogenesis",
            "theta10",
            "Abiogenesis",
            "biological",
        ),
        schema_param(
            "mutation_volatility",
            "theta11",
            "Mutation Volatility",
            "biological",
        ),
        schema_param(
            "adaptation_efficiency",
            "theta12",
            "Adaptation Efficiency",
            "biological",
        ),
        schema_param(
            "cognitive_ceiling",
            "theta13",
            "Cognitive Ceiling",
            "biological",
        ),
        schema_param(
            "myth_formation",
            "theta14",
            "Myth Formation",
            "cultural",
        ),
        schema_param(
            "memory_persistence",
            "theta15",
            "Memory Persistence",
            "cultural",
        ),
        schema_param(
            "technological_accumulation_rate",
            "theta16",
            "Technological Accumulation Rate",
            "cultural",
        ),
        schema_param(
            "meta_system_awareness",
            "theta17",
            "Meta-System Awareness",
            "cultural",
        ),
    ];

    let response = LawParamsSchemaResponse {
        variant: LawParamsVariant::Semantic,
        parameters,
    };
    (StatusCode::OK, Json(response))
}

async fn get_snapshots(
    State(state): State<Arc<AppState>>,
    Query(query): Query<SnapshotsQuery>,
) -> impl IntoResponse {
    let db = match &state.db {
        Some(db) => db,
        None => {
            let error = ApiError {
                error: "database_unavailable".to_string(),
            };
            return (StatusCode::SERVICE_UNAVAILABLE, Json(error)).into_response();
        }
    };

    let universe_id = match Uuid::parse_str(&query.universe_id) {
        Ok(id) => id,
        Err(_) => {
            let error = ApiError {
                error: "invalid_universe_id".to_string(),
            };
            return (StatusCode::BAD_REQUEST, Json(error)).into_response();
        }
    };

    let from = query.from.unwrap_or(0).min(i32::MAX as u32) as i32;
    let to = query.to.unwrap_or(i32::MAX as u32).min(i32::MAX as u32) as i32;
    if from > to {
        let error = ApiError {
            error: "invalid_range".to_string(),
        };
        return (StatusCode::BAD_REQUEST, Json(error)).into_response();
    }

    let rows = match db
        .client
        .query(
            "select tick, state, metrics from snapshots where universe_id = $1 and tick between $2 and $3 order by tick asc",
            &[&universe_id, &from, &to],
        )
        .await
    {
        Ok(rows) => rows,
        Err(_) => {
            let error = ApiError {
                error: "query_failed".to_string(),
            };
            return (StatusCode::INTERNAL_SERVER_ERROR, Json(error)).into_response();
        }
    };

    let mut snapshots = Vec::with_capacity(rows.len());
    for row in rows {
        let tick: i32 = row.get(0);
        let state_json: serde_json::Value = row.get(1);
        let metrics_json: serde_json::Value = row.get(2);
        let state: StateVector =
            serde_json::from_value(state_json).unwrap_or(StateVector {
                p: 0.0,
                c: 0.0,
                b: 0.0,
                n: 0.0,
                k: 0.0,
            });
        let metrics: Metrics = serde_json::from_value(metrics_json).unwrap_or(Metrics {
            stability: 0.0,
            entropy: 0.0,
            collapsed: false,
        });
        snapshots.push(SnapshotRecord {
            tick: tick as u32,
            state,
            metrics,
        });
    }

    let response = SnapshotsResponse {
        universe_id: universe_id.to_string(),
        snapshots,
    };
    (StatusCode::OK, Json(response)).into_response()
}

async fn get_latest_snapshot(
    State(state): State<Arc<AppState>>,
    Query(query): Query<LatestSnapshotQuery>,
) -> impl IntoResponse {
    let db = match &state.db {
        Some(db) => db,
        None => {
            let error = ApiError {
                error: "database_unavailable".to_string(),
            };
            return (StatusCode::SERVICE_UNAVAILABLE, Json(error)).into_response();
        }
    };

    let universe_id = match Uuid::parse_str(&query.universe_id) {
        Ok(id) => id,
        Err(_) => {
            let error = ApiError {
                error: "invalid_universe_id".to_string(),
            };
            return (StatusCode::BAD_REQUEST, Json(error)).into_response();
        }
    };

    let row = match db
        .client
        .query_opt(
            "select tick, state, metrics from snapshots where universe_id = $1 order by tick desc limit 1",
            &[&universe_id],
        )
        .await
    {
        Ok(row) => row,
        Err(_) => {
            let error = ApiError {
                error: "query_failed".to_string(),
            };
            return (StatusCode::INTERNAL_SERVER_ERROR, Json(error)).into_response();
        }
    };

    let snapshot = row.map(|row| {
        let tick: i32 = row.get(0);
        let state_json: serde_json::Value = row.get(1);
        let metrics_json: serde_json::Value = row.get(2);
        let state: StateVector =
            serde_json::from_value(state_json).unwrap_or(StateVector {
                p: 0.0,
                c: 0.0,
                b: 0.0,
                n: 0.0,
                k: 0.0,
            });
        let metrics: Metrics = serde_json::from_value(metrics_json).unwrap_or(Metrics {
            stability: 0.0,
            entropy: 0.0,
            collapsed: false,
        });
        SnapshotRecord {
            tick: tick as u32,
            state,
            metrics,
        }
    });

    let response = LatestSnapshotResponse {
        universe_id: universe_id.to_string(),
        snapshot,
    };
    (StatusCode::OK, Json(response)).into_response()
}

async fn list_worlds(
    State(state): State<Arc<AppState>>,
    Query(query): Query<ListQuery>,
) -> impl IntoResponse {
    let db = match &state.db {
        Some(db) => db,
        None => {
            let error = ApiError {
                error: "database_unavailable".to_string(),
            };
            return (StatusCode::SERVICE_UNAVAILABLE, Json(error)).into_response();
        }
    };

    let limit = query.limit.unwrap_or(50).clamp(1, 200);
    let offset = query.offset.unwrap_or(0).max(0);

    let rows = match db
        .client
        .query(
            "select id, law_params, created_at::text from worlds order by created_at desc limit $1 offset $2",
            &[&limit, &offset],
        )
        .await
    {
        Ok(rows) => rows,
        Err(_) => {
            let error = ApiError {
                error: "query_failed".to_string(),
            };
            return (StatusCode::INTERNAL_SERVER_ERROR, Json(error)).into_response();
        }
    };

    let mut worlds = Vec::with_capacity(rows.len());
    for row in rows {
        let law_params_json: serde_json::Value = row.get(1);
        let law_params: LawParams =
            serde_json::from_value(law_params_json).unwrap_or(LawParams {
                dimensionality: 0.0,
                causality_rigidity: 0.0,
                energy_stability: 0.0,
                interaction_strength: 0.0,
                entropy_growth: 0.0,
                matter_complexity_threshold: 0.0,
                self_organization_bias: 0.0,
                stability_basin_depth: 0.0,
                collapse_probability: 0.0,
                abiogenesis: 0.0,
                mutation_volatility: 0.0,
                adaptation_efficiency: 0.0,
                cognitive_ceiling: 0.0,
                myth_formation: 0.0,
                memory_persistence: 0.0,
                technological_accumulation_rate: 0.0,
                meta_system_awareness: 0.0,
            });

        worlds.push(WorldRecord {
            id: row.get::<_, Uuid>(0).to_string(),
            law_params,
            created_at: row.get::<_, String>(2),
        });
    }

    (StatusCode::OK, Json(worlds)).into_response()
}

async fn list_universes(
    State(state): State<Arc<AppState>>,
    Query(query): Query<ListQuery>,
) -> impl IntoResponse {
    let db = match &state.db {
        Some(db) => db,
        None => {
            let error = ApiError {
                error: "database_unavailable".to_string(),
            };
            return (StatusCode::SERVICE_UNAVAILABLE, Json(error)).into_response();
        }
    };

    let limit = query.limit.unwrap_or(50).clamp(1, 200);
    let offset = query.offset.unwrap_or(0).max(0);

    let rows = match db
        .client
        .query(
            "select id, world_id, seed, current_tick, created_at::text from universes order by created_at desc limit $1 offset $2",
            &[&limit, &offset],
        )
        .await
    {
        Ok(rows) => rows,
        Err(_) => {
            let error = ApiError {
                error: "query_failed".to_string(),
            };
            return (StatusCode::INTERNAL_SERVER_ERROR, Json(error)).into_response();
        }
    };

    let mut universes = Vec::with_capacity(rows.len());
    for row in rows {
        universes.push(UniverseRecord {
            id: row.get::<_, Uuid>(0).to_string(),
            world_id: row.get::<_, Uuid>(1).to_string(),
            seed: row.get::<_, i64>(2),
            current_tick: row.get::<_, i32>(3),
            created_at: row.get::<_, String>(4),
        });
    }

    (StatusCode::OK, Json(universes)).into_response()
}

async fn get_world(
    State(state): State<Arc<AppState>>,
    axum::extract::Path(id): axum::extract::Path<String>,
) -> impl IntoResponse {
    let db = match &state.db {
        Some(db) => db,
        None => {
            let error = ApiError {
                error: "database_unavailable".to_string(),
            };
            return (StatusCode::SERVICE_UNAVAILABLE, Json(error)).into_response();
        }
    };

    let world_id = match Uuid::parse_str(&id) {
        Ok(value) => value,
        Err(_) => {
            let error = ApiError {
                error: "invalid_world_id".to_string(),
            };
            return (StatusCode::BAD_REQUEST, Json(error)).into_response();
        }
    };

    let row = match db
        .client
        .query_opt(
            "select id, law_params, created_at::text from worlds where id = $1",
            &[&world_id],
        )
        .await
    {
        Ok(row) => row,
        Err(_) => {
            let error = ApiError {
                error: "query_failed".to_string(),
            };
            return (StatusCode::INTERNAL_SERVER_ERROR, Json(error)).into_response();
        }
    };

    let row = match row {
        Some(row) => row,
        None => {
            let error = ApiError {
                error: "not_found".to_string(),
            };
            return (StatusCode::NOT_FOUND, Json(error)).into_response();
        }
    };

    let law_params_json: serde_json::Value = row.get(1);
    let law_params: LawParams =
        serde_json::from_value(law_params_json).unwrap_or(LawParams {
            dimensionality: 0.0,
            causality_rigidity: 0.0,
            energy_stability: 0.0,
            interaction_strength: 0.0,
            entropy_growth: 0.0,
            matter_complexity_threshold: 0.0,
            self_organization_bias: 0.0,
            stability_basin_depth: 0.0,
            collapse_probability: 0.0,
            abiogenesis: 0.0,
            mutation_volatility: 0.0,
            adaptation_efficiency: 0.0,
            cognitive_ceiling: 0.0,
            myth_formation: 0.0,
            memory_persistence: 0.0,
            technological_accumulation_rate: 0.0,
            meta_system_awareness: 0.0,
        });

    let response = WorldRecord {
        id: row.get::<_, Uuid>(0).to_string(),
        law_params,
        created_at: row.get::<_, String>(2),
    };
    (StatusCode::OK, Json(response)).into_response()
}

async fn get_universe(
    State(state): State<Arc<AppState>>,
    axum::extract::Path(id): axum::extract::Path<String>,
) -> impl IntoResponse {
    let db = match &state.db {
        Some(db) => db,
        None => {
            let error = ApiError {
                error: "database_unavailable".to_string(),
            };
            return (StatusCode::SERVICE_UNAVAILABLE, Json(error)).into_response();
        }
    };

    let universe_id = match Uuid::parse_str(&id) {
        Ok(value) => value,
        Err(_) => {
            let error = ApiError {
                error: "invalid_universe_id".to_string(),
            };
            return (StatusCode::BAD_REQUEST, Json(error)).into_response();
        }
    };

    let row = match db
        .client
        .query_opt(
            "select id, world_id, seed, current_tick, created_at::text from universes where id = $1",
            &[&universe_id],
        )
        .await
    {
        Ok(row) => row,
        Err(_) => {
            let error = ApiError {
                error: "query_failed".to_string(),
            };
            return (StatusCode::INTERNAL_SERVER_ERROR, Json(error)).into_response();
        }
    };

    let row = match row {
        Some(row) => row,
        None => {
            let error = ApiError {
                error: "not_found".to_string(),
            };
            return (StatusCode::NOT_FOUND, Json(error)).into_response();
        }
    };

    let response = UniverseRecord {
        id: row.get::<_, Uuid>(0).to_string(),
        world_id: row.get::<_, Uuid>(1).to_string(),
        seed: row.get::<_, i64>(2),
        current_tick: row.get::<_, i32>(3),
        created_at: row.get::<_, String>(4),
    };
    (StatusCode::OK, Json(response)).into_response()
}

fn schema_param(key: &str, theta: &str, label: &str, group: &str) -> LawParamSchema {
    LawParamSchema {
        key: key.to_string(),
        theta: theta.to_string(),
        label: label.to_string(),
        group: group.to_string(),
        range: RangeSchema { min: 0.0, max: 1.0 },
    }
}

async fn simulate(
    State(state): State<Arc<AppState>>,
    Json(payload): Json<SimulateRequest>,
) -> axum::response::Response {
    let (law_params, detected_variant) = payload.law_params.into_semantic();
    if let Some(requested_variant) = payload.law_params_variant.clone() {
        if requested_variant != detected_variant {
            let error = ApiError {
                error: "law_params_variant_mismatch".to_string(),
            };
            return (StatusCode::BAD_REQUEST, Json(error)).into_response();
        }
    }

    let feasible = feasibility(&law_params);
    if !feasible {
        let response = SimulateResponse {
            feasible,
            world_id: None,
            universe_id: None,
            law_params_variant: Some(detected_variant),
            snapshots: vec![],
        };
        return (StatusCode::OK, Json(response)).into_response();
    }

    let mut universe_state = payload.initial_state.unwrap_or(StateVector {
        p: 0.6,
        c: 0.2,
        b: 0.05,
        n: 0.01,
        k: 0.0,
    });

    let mut snapshots = Vec::with_capacity(payload.ticks as usize + 1);
    for tick in 0..=payload.ticks {
        let metrics = evaluate_metrics(&universe_state, &law_params);
        snapshots.push(Snapshot {
            tick,
            state: universe_state.clone(),
            metrics: metrics.clone(),
        });
        if metrics.collapsed {
            break;
        }
        universe_state = evolve(&universe_state, &law_params, payload.seed, tick);
    }

    let mut world_id: Option<Uuid> = None;
    let mut universe_id: Option<Uuid> = None;

    if let Some(db) = &state.db {
        let new_world_id = Uuid::new_v4();
        let new_universe_id = Uuid::new_v4();
        if let Err(_) = persist_simulation(
            db,
            new_world_id,
            new_universe_id,
            &law_params,
            payload.seed,
            payload.ticks,
            &snapshots,
        )
        .await
        {
            return (StatusCode::INTERNAL_SERVER_ERROR, Json(error_response())).into_response();
        }
        world_id = Some(new_world_id);
        universe_id = Some(new_universe_id);
    }

    let response = SimulateResponse {
        feasible,
        world_id: world_id.map(|v| v.to_string()),
        universe_id: universe_id.map(|v| v.to_string()),
        law_params_variant: Some(detected_variant),
        snapshots,
    };
    (StatusCode::OK, Json(response)).into_response()
}

fn feasibility(law: &LawParams) -> bool {
    let s1 = law.energy_stability * law.matter_complexity_threshold * law.stability_basin_depth;
    if s1 < 0.2 {
        return false;
    }
    if law.entropy_growth >= law.stability_basin_depth + 0.3 {
        return false;
    }
    if law.abiogenesis * law.adaptation_efficiency <= 0.15 {
        return false;
    }
    let cognitive_cap = 0.8 * law.energy_stability + 0.2 * law.interaction_strength;
    if law.cognitive_ceiling > cognitive_cap {
        return false;
    }
    true
}

fn evolve(state: &StateVector, law: &LawParams, _seed: u64, tick: u32) -> StateVector {
    let p = state.p;
    let c = state.c;
    let b = state.b;
    let n = state.n;
    let k = state.k;

    let entropy_drain = law.entropy_growth * p;
    let p_next = clamp01(p - law.energy_stability * entropy_drain + feedback_term(state, law));

    let c_decay = law.energy_stability * 0.1 * c;
    let c_next = clamp01(c + law.entropy_growth * p * (1.0 - c) - c_decay);

    let instability = law.collapse_probability * 0.05;
    let b_next = clamp01(b + law.stability_basin_depth * c * (1.0 - b) - instability);

    let social_factor = 0.5 + 0.5 * law.cognitive_ceiling;
    let n_next = clamp01(n + law.adaptation_efficiency * b * social_factor);

    let memory_decay = (1.0 - law.memory_persistence) * 0.02 * k;
    let meta_feedback =
        law.meta_system_awareness * (p + c + b + n + k) / 5.0;
    let k_growth = law.memory_persistence * law.technological_accumulation_rate * n
        + meta_feedback * k * (1.0 - k);
    let k_next = clamp01(k + k_growth - memory_decay);

    let drift = (tick as f64).min(100.0) / 10000.0;
    StateVector {
        p: clamp01(p_next - drift),
        c: c_next,
        b: b_next,
        n: n_next,
        k: k_next,
    }
}

fn feedback_term(state: &StateVector, law: &LawParams) -> f64 {
    let avg = (state.p + state.c + state.b + state.n + state.k) / 5.0;
    let backreaction = law.myth_formation * avg * 0.02;
    backreaction
}

fn evaluate_metrics(state: &StateVector, law: &LawParams) -> Metrics {
    let entropy = clamp01(1.0 - state.p + law.entropy_growth * 0.2);
    let stability = clamp01(
        0.25 * state.p
            + 0.2 * state.c
            + 0.2 * state.b
            + 0.15 * state.n
            + 0.2 * state.k
            - 0.1 * entropy,
    );
    let collapsed = stability < 0.05 || entropy > 0.98;
    Metrics {
        stability,
        entropy,
        collapsed,
    }
}

fn clamp01(value: f64) -> f64 {
    if value < 0.0 {
        0.0
    } else if value > 1.0 {
        1.0
    } else {
        value
    }
}

fn error_response() -> SimulateResponse {
    SimulateResponse {
        feasible: false,
        world_id: None,
        universe_id: None,
        law_params_variant: None,
        snapshots: vec![],
    }
}

async fn init_db() -> Option<Db> {
    let database_url = env::var("DATABASE_URL").ok()?;
    let mut attempts = 0u32;
    loop {
        let connect = tokio_postgres::connect(&database_url, NoTls).await;
        match connect {
            Ok((client, connection)) => {
                tokio::spawn(async move {
                    let _ = connection.await;
                });
                if client.batch_execute(schema_sql()).await.is_ok() {
                    return Some(Db { client });
                }
            }
            Err(_) => {}
        }

        attempts += 1;
        if attempts >= 20 {
            return None;
        }
        tokio::time::sleep(std::time::Duration::from_millis(500)).await;
    }
}

fn schema_sql() -> &'static str {
    r#"
    create table if not exists worlds (
        id uuid primary key,
        law_params jsonb not null,
        created_at timestamptz not null default now()
    );

    create table if not exists universes (
        id uuid primary key,
        world_id uuid not null references worlds(id),
        seed bigint not null,
        current_tick integer not null,
        created_at timestamptz not null default now()
    );

    create table if not exists snapshots (
        id uuid primary key,
        universe_id uuid not null references universes(id),
        tick integer not null,
        state jsonb not null,
        metrics jsonb not null,
        created_at timestamptz not null default now()
    );

    create index if not exists snapshots_universe_tick_idx
        on snapshots(universe_id, tick);
    "#
}

async fn persist_simulation(
    db: &Db,
    world_id: Uuid,
    universe_id: Uuid,
    law_params: &LawParams,
    seed: u64,
    _ticks: u32,
    snapshots: &[Snapshot],
) -> Result<(), tokio_postgres::Error> {
    let law_params = serde_json::to_value(law_params).unwrap_or_default();
    let current_tick = snapshots.last().map(|s| s.tick).unwrap_or(0) as i32;

    db.client
        .execute(
            "insert into worlds (id, law_params) values ($1, $2)",
            &[&world_id, &law_params],
        )
        .await?;
    db.client
        .execute(
            "insert into universes (id, world_id, seed, current_tick) values ($1, $2, $3, $4)",
            &[&universe_id, &world_id, &(seed as i64), &current_tick],
        )
        .await?;

    for snapshot in snapshots {
        let state_json = serde_json::to_value(&snapshot.state).unwrap_or_default();
        let metrics_json = serde_json::to_value(&snapshot.metrics).unwrap_or_default();
        db.client
            .execute(
                "insert into snapshots (id, universe_id, tick, state, metrics) values ($1, $2, $3, $4, $5)",
                &[
                    &Uuid::new_v4(),
                    &universe_id,
                    &(snapshot.tick as i32),
                    &state_json,
                    &metrics_json,
                ],
            )
            .await?;
    }

    Ok(())
}
