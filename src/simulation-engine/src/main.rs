pub mod math;
pub mod governance;
pub mod cascade;
pub mod server;

pub mod simulation {
    tonic::include_proto!("simulation");
}

use std::env;
use tonic::transport::Server;
use simulation::simulation_engine_server::SimulationEngineServer;
use server::MySimulationEngine;

#[tokio::main]
async fn main() -> Result<(), Box<dyn std::error::Error>> {
    println!("Simulation Engine starting up...");

    // Connect to Redis
    let redis_url = env::var("REDIS_URL").unwrap_or_else(|_| "redis://redis:6379".to_string());
    let redis_client = redis::Client::open(redis_url.clone())?;
    let redis_con = redis_client.get_multiplexed_tokio_connection().await?;
    println!("Connected to Redis at {}", redis_url);

    // Start gRPC Server
    let port = env::var("SIMULATION_ENGINE_PORT").unwrap_or_else(|_| "50051".to_string());
    let addr = format!("0.0.0.0:{}", port).parse()?;
    println!("Simulation Engine gRPC listening on {}", addr);

    let engine = MySimulationEngine::new(redis_con);

    Server::builder()
        .add_service(SimulationEngineServer::new(engine))
        .serve(addr)
        .await?;

    Ok(())
}
