pub mod math;
pub mod governance;
pub mod cascade;
pub mod domain;
pub mod engine;
pub mod server;

pub mod simulation {
    tonic::include_proto!("simulation");
}
