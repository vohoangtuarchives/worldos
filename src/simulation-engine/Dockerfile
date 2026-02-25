FROM rust:1.93-slim
WORKDIR /usr/src/app

# Install dependencies for compiling
RUN apt-get update && \
    apt-get install -y protobuf-compiler libprotobuf-dev pkg-config libssl-dev && \
    rm -rf /var/lib/apt/lists/*

COPY . .

# Run the simulation engine
CMD ["cargo", "run"]
