# Stage 1: Builder
FROM rust:1.93-slim AS builder

WORKDIR /usr/src/app

# Install protobuf compiler (needed for prost/tonic code generation)
RUN apt-get update && \
    apt-get install -y protobuf-compiler libprotobuf-dev pkg-config libssl-dev && \
    rm -rf /var/lib/apt/lists/*

# Cache dependencies layer separately (faster rebuild)
COPY Cargo.toml Cargo.lock ./
RUN mkdir src && echo "fn main() {}" > src/main.rs
RUN cargo build --release && rm -f target/release/deps/simulation_engine*

# Copy actual source and rebuild
COPY . .
RUN cargo build --release

# Stage 2: Slim runtime image
FROM debian:bookworm-slim AS runtime

RUN apt-get update && \
    apt-get install -y --no-install-recommends ca-certificates libssl3 && \
    rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Copy binary from builder
COPY --from=builder /usr/src/app/target/release/simulation-engine /app/simulation-engine

# gRPC port
EXPOSE 50051

CMD ["/app/simulation-engine"]
