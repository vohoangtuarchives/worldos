# CHANGELOG - WorldOS 1.0.2

Phát hành bản nâng cấp quan trọng cho **Simulation Engine** (Rust gRPC) và cấu trúc Domain kết nối bên phía PHP. Tập trung vào tính chính xác toán học, tăng cường bảo mật sự kiện (Governance Guard) và tương thích môi trường chuẩn.

## [1.0.2] - 2026-02-26

**Mục tiêu**: Nâng cấp hạt nhân toán học `MathCore` và cơ chế phòng thủ `GovernanceGuard` của Simulation Engine bằng cơ chế tính Spectral Radius chính xác thay vì ước lượng trên viền Gershgorin.

### Added
- **`compute_spectral_radius()` (Rust)**: Thêm thuật toán *Power Iteration* (giới hạn 100 vòng) bên trong `math/core.rs` để tính chính xác Spectral Radius (Eigenvalue lớn nhất tuyệt đối, $\rho(J)$). Xử lý Rayleigh quotient approximation với sai số hội tụ `1e-8`.
- **`verify_contraction()` (Rust)**: Bổ sung method xác thực tính chất contraction map dựa theo Invariant #2 của AXIOM 1: Mọi State Matrix (D) hợp lệ đều phải có $\rho(J) \leq 1 - \delta$.
- **`check_lyapunov_stability()` (Rust)**: Thêm Lyapunov stability verification logic vào thư viện `governance/guard.rs` (nhận tham số đầu vào là `spectral_radius`).
- **Rust Unit Tests**: Thêm module test `math/tests.rs` và `governance/tests.rs` để kiểm chứng các cases của AXIOM (identity logic, zero logic, contraction map limit checks, energy budget checks, ...).

### Changed
- **Khối Governance `server.rs`**: Vòng check *Governance Check 2* chính thức gỡ bỏ `check_spectral_margin_gershgorin` và thay bằng call `check_lyapunov_stability(spectral_radius)`. Giảm thiểu 100% false-positive (ví dụ các ma trận lệch không chéo lớn nhưng thực chất Re($\lambda$) vẫn nhỏ hơn 1).
- **gRPC Response Data**: `TICK_COMPLETED` Redis event Payload giờ có kèm field mới `spectral_radius` (giúp dashboard v6 sau này dễ dàng biểu diễn mức độ ổn định của phase).
- **PHP gRPC Client Payload**: Hàm `runTick()` của `GrpcSimulationEngineClient` giờ trả về array đa cấu trúc thay vì array phẳng: `['state' => x_next, 'cascade' => cascade_next]`.
- **SimulationTickCommand.php**: Update parser của Response API để trích xuất `$nextCascade` và truyền vào `SnapshotRepository::storeSnapshot` (giờ đây snapshot DB sẽ lưu đầy đủ cả Physics layer lẫn Culture layer).
- **Rust Dockerfile**: Nâng cấp *Rust base image* cho simulation engine builder từ `1.83-slim` lên **`1.85-slim`** (cho phép tương thích Crate Feature edition 2024 của dependency `getrandom v0.4.1`).

### Removed
- Xoá method `check_spectral_margin_gershgorin` (Gershgorin disc upper-bound algorithm) cũ trên `guard.rs` do không đảm bảo tính chính xác chặt chẽ với điều kiện biên lớn.

---
*End of file.*
