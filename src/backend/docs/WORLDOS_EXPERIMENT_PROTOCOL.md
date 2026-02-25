# WORLDOS EXPERIMENT PROTOCOL

**Version:** 1.0  
**Status:** Mandatory  
**Scope:** All research runs using WorldOS Kernel  

## 1. Mục tiêu
Tài liệu này định nghĩa chuẩn bắt buộc cho mọi experiment thực hiện trên WorldOS.
Một experiment không tuân thủ protocol này được xem là **invalid** và không được dùng để:
- So sánh kết quả
- Rút kết luận
- Công bố nghiên cứu
- Huấn luyện AI meta-layer

## 2. Định nghĩa Experiment
Một experiment là một execution run với:
- Kernel version cố định
- Parameter set cố định
- Initial condition cố định
- Deterministic plugin set cố định
- Tick count xác định
Experiment được xem là một đơn vị nghiên cứu độc lập.

## 3. Mandatory Metadata
Mỗi experiment phải ghi lại đầy đủ các thông tin sau:

### 3.1 Kernel Information
- Kernel version (vd: 1.2.0)
- Commit hash
- MathCore checksum
- GovernanceGuard checksum

### 3.2 State Dimension
- `n` (dimension per region)
- `R` (number of regions)
- `total dimension` = $n \times R$

### 3.3 Parameters
- $\alpha$: damping step size
- $\beta$: input scaling
- $\lambda$: diffusion coefficient
- $\eta$: intrinsic damping
- $\gamma_{\text{cap}}$: input norm cap
- $\delta$: spectral margin target

### 3.4 Stability Report
- Spectral bound ($\rho_{\text{estimate}}$ hoặc Gershgorin bound)
- Verified contraction? (yes/no)
- Stability budget violation count
- Max observed $\|\mathbf{x}(t)\|$
- $R_{\max}$

### 3.5 Initial Conditions
- $\mathbf{x}(0)$ full vector hash
- Initialization method: (zero / random bounded / structured)
- Random seed

### 3.6 Runtime Configuration
- Tick count
- Snapshot frequency
- Precision mode (float / fixed / bcmath)
- Hardware: CPU model, RAM, PHP version / Rust version (if hybrid)

### 3.7 Performance Metrics
- Average time per tick
- Max time per tick
- Memory peak
- Total runtime

## 4. Execution Procedure
Every experiment must follow:
1. Load configuration
2. Validate invariants
3. Freeze parameters
4. Initialize $\mathbf{x}(0)$
5. Run deterministic tick loop
6. Persist snapshots
7. Generate final report
8. Lock experiment record

**No parameter mutation allowed mid-run.**

## 5. Reproducibility Test
Every experiment must pass:

### 5.1 Same-Machine Determinism
Re-run same configuration twice:
- snapshot hashes identical
- final state identical

### 5.2 Cross-Machine Tolerance
If floating precision used:
$$ \|\mathbf{x}_1(t) - \mathbf{x}_2(t)\| \le \epsilon_{\text{machine}} $$
Else:
Byte-identical required.

## 6. Output Requirements
Each experiment must produce:
- Final state vector hash
- Attractor estimate (if converged)
- Stability margin
- Phase classification: Convergent / Saturated / Near-boundary / Rejected (invariant violation)

## 7. Prohibited Practices
The following invalidate an experiment:
- Changing $\alpha, \lambda, \eta$ mid-run
- Bypassing GovernanceGuard
- Direct mutation of $\mathbf{x}$ by plugin
- Non-deterministic plugin ordering
- Manual snapshot editing
- Floating precision changes mid-run

## 8. Phase Diagram Protocol (Advanced)
For parameter sweep experiments:
Each point in parameter grid must:
- Run independent experiment
- Record stability result
- Record attractor magnitude
- Record runtime metrics
Results must be stored in structured matrix: Parameter grid $\rightarrow$ Stability classification map.

## 9. Complexity Reporting
Each experiment must report:
- Empirical time complexity vs $n$
- Empirical memory vs $n$
- Fit regression (O($n^2$) expected)

If growth > O($n^2$) $\rightarrow$ investigate plugin impact.

## 10. Publication Readiness Criteria
An experiment may be considered publishable if:
- All invariants satisfied
- Determinism verified
- Reproducibility verified
- Stability margin $> \delta_{\text{min}}$
- Documentation complete
- Hash chain intact

## 11. Archival Policy
Experiment records are:
- Immutable
- Append-only
- Versioned
- Hash-verified

**Deletion is prohibited.**

## 12. Research Mode Philosophy
WorldOS experiments must be:
- Deterministic
- Auditable
- Parameter-explicit
- Reproducible
- Bounded

> *Nếu một experiment tạo ra "hành vi thú vị" (interesting behavior) nhưng vi phạm invariants $\rightarrow$ nó sẽ bị vứt bỏ, không phải là thứ để ăn mừng.*
