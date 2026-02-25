# WorldOS: Báo Cáo Chuyển Đổi Kiến Trúc (Computational Laboratory)
**Thời gian thực hiện:** 24/02/2026 - 25/02/2026  
**Mục tiêu:** Chính thức chuyển đổi WorldOS từ một dự án "cảm hứng/trò chơi" sang một **Computational Laboratory (Phòng thí nghiệm Tính toán)** chuẩn Research-grade.

Dưới đây là tổng hợp toàn bộ các quyết định chiến lược, tài liệu được soạn thảo, và mã nguồn (source code) đã được xây dựng từ tối qua đến nay.

---

## I. Thay Đổi Triết Lý Cốt Lõi (Core Philosophy Shift)
WorldOS không còn là ứng dụng Laravel thông thường. Định hướng cấu trúc dài hạn hiện tại là **Hybrid Rust/PHP high-performance scientific platform**:
- **PHP/Laravel:** Đóng vai trò Orchestrator (Điều phối), Governance (Kiểm duyệt), Database & API.
- **Python (Microservice tương lai):** Đảm nhiệm AI Meta-Layer và Constrained Bayesian Optimization.
- **Rust (Tương lai):** Sẽ offload xử lý CPU-bound cho Ma trận nhiều chiều ($n > 200$).
- **Nguyên tắc "Thử nghiệm Lạnh lùng":** Bất kỳ simulation run (tick) nào có dấu hiệu bùng nổ (phân kỳ, vi phạm ngân sách ổn định) đều bị từ chối triệt để. Không có sự thỏa hiệp vì các hành vi "thú vị nhưng sai toán học".

---

## II. Bộ 3 Văn Bản "Hiến Pháp" (Foundation Documents)
Chúng ta đã soạn thảo 3 văn bản nền tảng quy định sự vận hành của lõi hệ thống:

1. **`WORLDOS_KERNEL_FORMAL_SPEC.md` (v1.2)**
   - Cập nhật phương trình cập nhật trạng thái dựa trên **Contraction Map (Ánh xạ có ngót)**.
   - Bắt buộc phải có yếu tố **Intrinsic Damping ($-\eta\mathbf{x}$ với $\eta > 0$)** để ép buộc hệ thống luôn có xu hướng tiêu hao năng lượng, tránh bùng nổ hàm mũ.
   - Định nghĩa mô hình kiến trúc 3 lớp: MathCore, GovernanceGuard, và ExtensionOrchestrator.

2. **`WORLDOS_INVARIANTS.md`**
   - Đề ra 10 "Bất biến" (Invariants) khắt khe mà simulation phải tuân theo. 
   - Điển hình: Phổ biên an toàn (Spectral Margin) $\rho(\mathbf{J}) \le 1 - \delta$; Chặn giới hạn Input (Input Cap) $\|\mathbf{u}(t)\| \le \gamma_{\text{cap}}$; Determinism (Tính tiền định: Chạy 100 lần cùng tham số phải ra cùng 1 Hash kết quả).

3. **`WORLDOS_EXPERIMENT_PROTOCOL.md`**
   - Giao thức chuẩn hóa mọi thử nghiệm (experiment). 
   - Bất kỳ Run nào cũng phải ghi chú minh bạch hạt giống nhiễu (Seed), phiên bản Kernel, cấu hình phần cứng vật lý, độ trễ và Memory peak. Các tham số ($\alpha, \beta, \lambda, \eta$) bị đóng băng (Freeze parameters) khi đang chạy.

---

## III. Phân Lớp AI Tối Ưu Hóa (AI Meta-Layer Blueprint)
- **`WORLDOS_META_OPTIMIZATION_SPEC.md`**: Bản thiết kế cách hệ thống sẽ tự học bằng **Stability-Aware Bayesian Optimization (SABO)**. Thay vì brute-force quét tất cả tham số bị bùng nổ, một Surrogate Model (Gaussian Process) sẽ dự đoán trước liệu tham số đó có an toàn không, biến WorldOS thành hệ thống **Self-optimizing AI simulator**.

---

## IV. Thực Thi Triển Khai: Tầng Lõi Tính Toán (Domain/MathCore)
Sử dụng phương pháp **Domain-Driven Design (DDD)**, chúng ta đã xây dựng một lõi hoàn toàn **Framework-agnostic** (Không phụ thuộc vào Laravel Eloquent hay Container) phục vụ việc tính toán:

- **`MathCore.php`**: Xử lý phương trình động lực học hệ thống không có Side-effects. Thuần Deterministic.
- **`KernelMatrixBuilder.php` & `MatrixOperator.php`**: Tính toán ma trận Jacobian $\mathbf{J}$ kiểu Lazy-Loading. Tránh việc Materialize một mảng khổng lồ 2 chiều trên RAM (Giải quyết bài toán Memory rò rỉ $O(n^2)$ về thành $O(n)$).
- **`GershgorinAnalyzer.php`**: Đánh giá an toàn nhanh phổ biên của ma trận thông qua định lý Vòng cấm Gershgorin.
- **`SpectralEstimator.php`**: Không dùng phương pháp rã trị riêng (Eigen Decomposition) tốn kém $O(N^3)$, thay vào đó dùng thuật toán **Power Iteration** O($N^2$) siêu tối ưu để tìm giới hạn phổ tiệm cận $\rho(\mathbf{J})$.
- **`StabilityBudgetMonitor.php`**: Trigger còi báo động nếu tỉ lệ năng lượng giữa Tick hiện tại và Tick tương lai $r(t) = \frac{\|\mathbf{x}(t+1)\|}{\|\mathbf{x}(t)\|}$ vượt qua trần ngân sách cho phép.

---

## V. Cấu trúc Dữ Liệu Cho Phòng Nghiên Cứu (Infrastructure DB)
Đã thiết kế và tạo các bảng Migration trong Database với tâm thế tạo ra Data Pipeline chuẩn bị cho việc huấn luyện AI sau này:

- **`kernel_experiments`**: Lưu metadata của một experiment, trạng thái thành công/thất bại, các thông số $\alpha, \lambda, \eta$ bất biến, và thời gian chạy. 
- **`kernel_experiment_snapshots`**: Bảng **Append-Only** đặc biệt chứa **Hash Chain**. Hash của Tick $N$ được dựa trên thông tin trạng thái và Hash của Tick $N-1$ được băm qua hàm SHA256 để bảo vệ tính bất biến, không thể bị hack hay sửa đổi bởi developer.
- **`kernel_experiment_metrics`**: Lưu Timeseries dữ liệu $r(t)$ và trạng thái Vector ẩn tịnh tiến theo thời gian.
- **`kernel_stability_features`**: Lưu kết xuất Feature Engineering. Các chỉ số về Trace Laplacian, Gershgorin Bound được nén thành một mảng phẳng chuẩn bị cho thao tác Import vào XGBoost/Python FastAPI.

---

## VI. Tầng Ứng Dụng Chấp Pháp (Application / Governance)
- **`GovernanceGuard.php`**: "Cảnh sát" bắt buộc các cấu hình và dữ liệu đi vào/đi ra khỏi MathCore phải thoả mãn 10 Invariants. Có quyền năng Panic Error và xoá bỏ thí nghiệm nết phát hiện vi phạm luật.
- **`SimulationRunner.php`**: Hệ thống động cơ tổng quản. Vận hành quy trình sống của một Game Tick:
   `Init -> Check Guard -> Run MathCore -> Calc Hash -> Write Snapshots to DB`
- **Các Repositories (`HashChainService.php`, `ExperimentRepository.php`, `SnapshotRepository.php`)**: Đảm nhiệm tách bạch việc ghi DB ra khỏi logic thuần. 

---

## VII. Phát Triển Công Cụ Tự Động (CLI Tools)
- **`WorldOSSweepCommand.php`**: Công cụ Console Terminal có khả năng quét lưới siêu tham số (Hyperparameter Grid Search Sweep). 
   - Ví dụ lệnh: `php artisan worldos:sweep --alpha=0.01-0.1 --eta=0.01-0.05 --steps=10`
   - Mục đích: Ra lệnh cho PHP tự động thay đổi từng thông số cực nhỏ, chạy song song mô phỏng nhiều lần, lưu kết quả thất bại hay thành công vào DB, từ đó chúng ta quan sát được **Topology (Biểu đồ Pha/Phase Diagram)** khu vực ổn định của hệ thống.

---

**Trạng Thái Hiện Tại:** Toàn bộ Core (Domain, App, DB, CLI) đã được Code và Verify Syntax (Không tồn tại lỗi biên dịch hay syntax linter nào của PHP). WorldOS đã sở hữu đầy đủ Foundation để bắt đầu sweep và generate ra khối lượng dữ liệu khổng lồ chuẩn bị cho AI.
