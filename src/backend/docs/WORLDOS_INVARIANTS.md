# WORLDOS INVARIANTS REGISTRY

**Version:** 1.0  
**Status:** Mandatory  
**Scope:** Kernel Layer (MathCore + GovernanceGuard)  

## 1. Mục tiêu
Tài liệu này định nghĩa các bất biến (invariants) bắt buộc của WorldOS Kernel. Mọi tick, mọi experiment, mọi plugin đều phải tuân thủ nghiêm ngặt.
Nếu một invariant bị vi phạm:
- Tick bị reject
- Snapshot rollback
- Experiment bị đánh dấu invalid

Không có ngoại lệ.

## 2. Stability Invariants
### 2.1 Spectral Margin Invariant
Kernel update matrix:
$$ \mathbf{J} = \mathbf{I} + \alpha [(\mathbf{A} - \mathbf{I}) - \lambda \mathbf{L} - \eta \mathbf{I}] $$
Phải thỏa mãn:
$$ \rho(\mathbf{J}) \le 1 - \delta $$
với: $\delta \ge 0.05$

Trong runtime, có thể dùng Gershgorin bound thay cho eigen decomposition để tối ưu hiệu năng:
Với mọi $i$:
$$ |J_{ii}| + \sum_{j \neq i} |J_{ij}| < 1 $$
Nếu không thỏa $\rightarrow$ reject update.

### 2.2 Intrinsic Damping Invariant
$$ \eta > 0 $$
Và:
$$ \alpha \eta < 1 $$
Đảm bảo hệ thống luôn suy giảm tự nhiên (damped) và không bị hiện tượng overshoot.

### 2.3 Diffusion Positivity Invariant
Nếu multi-region enabled:
- $\mathbf{L}$ phải là symmetric matrix (đối xứng).
- $\mathbf{L}$ phải là positive semi-definite (bán xác định dương).
- $\lambda \ge 0$

Nếu không thỏa $\rightarrow$ reject configuration.

## 3. Input Control Invariants
### 3.1 Input Norm Bound
Tổng lực tác động ngoại sinh bị chặn:
$$ \|\mathbf{u}(t)\| \le \gamma_{\text{cap}} $$
Không cho phép bất kỳ plugin hay actor nào bypass giới hạn này.

### 3.2 Stability Budget Invariant
Định nghĩa tỷ lệ gia tăng năng lượng giữa 2 tick:
$$ r(t) = \frac{\|\mathbf{x}(t+1)\|}{\|\mathbf{x}(t)\|} $$
Phải thỏa mãn:
$$ r(t) \le 1 - \delta + \epsilon $$
với $\epsilon$ siêu nhỏ (ví dụ 0.01).
Nếu vượt $\rightarrow$ hệ thống cảnh báo + reject.

## 4. Boundedness Invariant
Vì hệ thống là contractive, ta có:
$$ \sup_t \|\mathbf{x}(t)\| < \infty $$
Trong runtime, trạng thái bị cứng giới hạn:
- Nếu $\|\mathbf{x}(t)\| > R_{\max}$ (configurable) $\rightarrow$ hard stop simulation.
- $R_{\max}$ phải được định nghĩa tường minh trong experiment config.

## 5. Determinism Invariants
### 5.1 Tick Determinism
Cho trước:
- cùng initial state $\mathbf{x}(0)$
- cùng system parameters
- cùng static PRNG seed
- cùng plugin execution order

Yêu cầu phải tạo ra:
- identical $\mathbf{x}(t)$
- identical snapshot hash

Nếu không trùng khớp $\rightarrow$ bug nghiên cứu nghiêm trọng (xem lại precision hoặc source of randomness).

### 5.2 Execution Order Determinism
Thứ tự thực thi plugin phải:
- Explicitly sorted (sắp xếp rõ ràng, ví dụ theo UUID phân loại chữ cái lexicographical).
- Deterministic.
- Tuyệt đối không phụ thuộc vào container resolution order (thứ tự tiêm của Dependency Injection Container).

## 6. Snapshot Integrity Invariants
### 6.1 Hash Chain
Mỗi tick tạo ra một link không thể phá vỡ:
$$ \text{hash}_t = \text{SHA256}(\text{hash}_{t-1} + \text{serialize}(\mathbf{x}_t)) $$
Tuyệt đối không cho phép sửa snapshot cũ (Append-Only).

### 6.2 Parameter Immutability
Sau khi một experiment bắt đầu, các tham số cấu trúc:
$\alpha, \beta, \lambda, \eta, \gamma_{\text{cap}}$ **không được phép thay đổi**.
Regime change (chuyển đổi thể loại/kỷ nguyên) yêu cầu:
- Đóng experiment hiện tại (Seal Ledger).
- Mở experiment mới (New Genesis với thông số mới).

## 7. Numerical Precision Invariant
Phải đảm bảo tính nhất quán toán học:
- Khác biệt tính toán dấu phẩy động (Floating-point drift) $\le$ dung sai $\epsilon_{\text{machine}}$.
- Hoặc sử dụng Fixed Precision Layer (như `bcmath` trong PHP).
Nếu chạy cross-machine (đối chiếu đa máy chủ) cho ra kết quả khác nhau quá dung sai $\rightarrow$ experiment invalid.

## 8. Complexity Invariant
Thiết kế tối ưu thuật toán. Cho không gian vũ trụ số chiều $n$:
- Update complexity phải đạt $O(n^2)$ hoặc tốt hơn.
- Memory growth phải tuyến tính $O(n)$.
Mọi plugin có độ phức tạp vượt ngưỡng $O(n^3)$ $\rightarrow$ lập tức reject.

## 9. Governance Authority
Lớp `GovernanceGuard` độc lập và có toàn quyền:
- Reject tick.
- Rollback state.
- Disable plugin vi phạm.
- Terminate experiment.
`MathCore` chỉ thực hiện phép tính, không có quyền tự kiểm soát (Pure Functionality).

## 10. The Core Principle
Simulation Kernel bắt buộc phải duy trì 6 đặc tính tối thượng:
1. **Contractive** (Luôn Co giãn)
2. **Input-bounded** (Lực Tác Động Có Giới Hạn)
3. **Deterministic** (Tất Định Hoàn Toàn)
4. **Auditable** (Truy Vết Mọi Step)
5. **Finite-memory** (Tiêu Thụ Bộ Nhớ Tuyến Tính Định Tuyến)
6. **Parameter-explicit** (Mọi Tham Số Phải Rõ Ràng)

Nếu một Pull Request hay tính năng mới vô tình làm mất 1 trong 6 tính chất trên $\rightarrow$ **KHÔNG MERGE**. 
