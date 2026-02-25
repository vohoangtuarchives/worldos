FROM rust:1.93 AS builder
WORKDIR /app
COPY Cargo.toml .
COPY src ./src
RUN --mount=type=cache,target=/usr/local/cargo/registry \
    --mount=type=cache,target=/usr/local/cargo/git \
    --mount=type=cache,target=/app/target \
    cargo build --release && cp /app/target/release/worldos-kernel /app/worldos-kernel

FROM debian:bookworm-slim
WORKDIR /app
COPY --from=builder /app/worldos-kernel /app/worldos-kernel
ENV RUST_LOG=info
EXPOSE 8080
CMD ["/app/worldos-kernel"]
