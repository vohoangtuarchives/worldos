# WORLDOS KERNEL FORMAL SPECIFICATION

**Phiên bản:** 1.2  
**Ngày:** 2026-02-24  
**Trạng thái:** Chính thức  

Tài liệu này cung cấp đặc tả toán học chính thức của kernel mô phỏng WorldOS. Mục tiêu là định hình WorldOS thành một **nền tảng nghiên cứu nguyên khối về động lực học văn minh có giới hạn (a formal computational framework for bounded civilizational dynamics)** dựa trên nguyên tắc **Absolute Stability (Engine-First)**. Quá trình tiến hóa luôn đảm bảo tính tất định, bị chặn và ổn định tuyệt đối trên tất cả các thể loại và plugin. 

---

## 1. Định nghĩa trạng thái (State Definition)

Trạng thái của một Vũ trụ tại thời điểm rời rạc $t$ bao gồm không gian ẩn (latent state) và không gian quan sát được (observable state).

**Latent state:** 
$$ \mathbf{x}(t) \in \mathbb{R}^n, \quad n \ge 6 $$
Không gian ẩn này không bị chặn theo toán học.

**Observable state:** 
$$ \mathbf{S}(t) = \sigma(\mathbf{x}(t)) $$
với $\sigma$ là hàm sigmoid từng phần tử:
$$ \sigma(x_i) = \frac{1}{1 + e^{-x_i}} \in (0, 1) $$
Điều này loại bỏ hard clamp, đảm bảo tính khả vi và tránh mất thông tin. Snapshot lưu trữ $\mathbf{x}(t)$, còn $\mathbf{S}(t)$ được tính khi cần.

Các chiều cơ sở (Core dimensions) tổi thiểu bao gồm:
- $S_1$: Entropy
- $S_2$: Order (Trật tự)
- $S_3$: Innovation (Đổi mới)
- $S_4$: Cohesion (Gắn kết)
- $S_5$: Inequality (Bất bình đẳng)
- $S_6$: Trauma (Tổn thương)

Các chiều bổ sung có thể được thêm vào tùy theo Vũ trụ nhưng tổng số chiều không vượt quá $n_{\max}$ (ví dụ: 64).

---

## 2. Phương trình cập nhật dạng Contraction Map

### 2.1 Cập nhật latent state
Ta định nghĩa phương trình cập nhật như sau:
$$ \mathbf{x}(t+1) = \mathbf{x}(t) + \alpha \cdot \mathbf{F}(\mathbf{x}(t), \mathbf{u}(t)) $$
với:
- $0 < \alpha < 1$ là hệ số giảm chấn (damping factor).
- $\mathbf{u}(t)$ là tổng ảnh hưởng từ actor, material, plugin (đã qua kiểm soát).

### 2.2 Thành phần nội tại (Physics + Diffusion)
Thành phần nội tại được tách thành hai phần:

**a) Averaging term:** Ma trận $\mathbf{A} \in \mathbb{R}^{n \times n}$ là row-stochastic:
$$ A_{ij} \ge 0, \quad \sum_j A_{ij} = 1 \quad \forall i $$
Khi đó $\mathbf{Ax}$ là trung bình có trọng số, và bán kính phổ $\rho(\mathbf{A}-\mathbf{I}) \le 1$.

**b) Diffusion term:** Dùng graph Laplacian cho các vùng (Region). Nếu có $R$ vùng, ta mở rộng latent state thành $\mathbf{x} = (\mathbf{x}_1, \dots, \mathbf{x}_R)$. Ma trận Laplacian toàn cục:
$$ \mathbf{L} = \mathbf{L}_{\text{graph}} \otimes \mathbf{I}_n $$
trong đó $\mathbf{L}_{\text{graph}}$ là Laplacian của đồ thị vùng (đối xứng, positive semi-definite). Khi đó $-\lambda \mathbf{L} \mathbf{x}$  có tính chất tiêu tán (dissipative).

Tổng hợp thành phần nội tại (Thêm **Intrinsic Damping** để đảm bảo strict contraction):
$$ \mathbf{F}_{\text{int}}(\mathbf{x}) = (\mathbf{A} - \mathbf{I})\mathbf{x} - \lambda \mathbf{L} \mathbf{x} - \eta \mathbf{x} $$
với:
- $\lambda \ge 0$ là hệ số khuếch tán.
- $\eta > 0$ là hệ số cản nội tại (intrinsic damping). **Bắt buộc $\eta > 0$** để đảm bảo hệ co giãn tuyệt đối (strictly contractive) thay vì chỉ ổn định cận biên (marginally stable) khi biểu diễn các ma trận Laplacian có nullspace.

### 2.3 Thành phần ngoại sinh (Actor, Pressure, Plugin)
Tất cả các ảnh hưởng bên ngoài được gom vào một vector điều khiển $\mathbf{u}(t)$ với:
$$ \|\mathbf{u}(t)\| \le \gamma_{\text{cap}} $$
và được scale bằng hệ số $\beta \ll 1$:
$$ \mathbf{F}_{\text{ext}} = \beta \mathbf{u}(t) $$

### 2.4 Phương trình cập nhật hoàn chỉnh
$$ \mathbf{F}(\mathbf{x}, \mathbf{u}) = (\mathbf{A} - \mathbf{I})\mathbf{x} - \lambda \mathbf{L} \mathbf{x} - \eta \mathbf{x} + \beta \mathbf{u} $$
Cập nhật đầy đủ:
$$ \mathbf{x}(t+1) = \mathbf{x}(t) + \alpha \left[ (\mathbf{A} - \mathbf{I})\mathbf{x}(t) - \lambda \mathbf{L} \mathbf{x}(t) - \eta \mathbf{x}(t) + \beta \mathbf{u}(t) \right] $$
Hoặc viết gọn dưới dạng ma trận Jacobian:
$$ \mathbf{x}(t+1) = \mathbf{J}\mathbf{x}(t) + \alpha \beta \mathbf{u}(t) $$
với $\mathbf{J} = \mathbf{I} + \alpha [(\mathbf{A} - \mathbf{I}) - \lambda \mathbf{L} - \eta \mathbf{I}]$.
*(Quy ước: Nếu không có multi-region, đặt $\lambda = 0$ và bỏ qua $\mathbf{L}$).*

### 2.5 Chuyển về observable state
Sau khi cập nhật latent state, observable state được xác định bởi hàm Sigmoid:
$$ \mathbf{S}(t) = \sigma(\mathbf{x}(t)) $$

> **Lưu ý quan trọng về không gian Observable:**  
> Mọi chứng minh toán học về tính co giãn (contraction proofs) chỉ áp dụng thuần túy trong không gian ẩn $\mathbf{x}$. Không gian quan sát được $\mathbf{S}$ kế thừa tính bị chặn nhưng **không bảo toàn tính tuyến tính**. Bất kỳ module mở rộng nào thao tác trực tiếp trên $\mathbf{S}$ cần nhận thức sự thay đổi của độ lớn lực (Jacobian của $\sigma(x)$ có max = 0.25).

---

## 3. Điều kiện ổn định (Stability Conditions)

### 3.1 Điều kiện contraction (khi không có ngoại sinh)
Khi $\mathbf{u} = \mathbf{0}$, hệ trở thành hệ tuyến tính:
$$ \mathbf{x}(t+1) = \mathbf{J} \mathbf{x}(t) $$
với
$$ \mathbf{J} = \mathbf{I} + \alpha \left[ (\mathbf{A} - \mathbf{I}) - \lambda \mathbf{L} - \eta \mathbf{I} \right] $$
Để hệ là contraction (nghiệm hội tụ về 0), ta cần:
$$ \|\mathbf{J}\| < 1 $$
Một điều kiện đủ (dùng chuẩn phổ) được thỏa mãn khi:
- $\mathbf{A}$ là row-stochastic $\Rightarrow \rho(\mathbf{A}-\mathbf{I}) \le 1$
- $\mathbf{L}$ là PSD $\Rightarrow \rho(-\lambda \mathbf{L}) \le \lambda \cdot \|\mathbf{L}\|$

Chọn $\alpha$ đủ nhỏ sao cho $\rho(\mathbf{J}) < 1$. Trong thực hành, ta chọn:
- $\alpha \le 0.1$
- $\lambda \le 0.5$
- $\beta \le 0.05$

Khi đó, $\mathbf{J}$ là ma trận co (contraction mapping) trên toàn bộ không gian.

### 3.2 Ổn định với đầu vào bị chặn (Input-to-State Stability - ISS)
Khi có $\mathbf{u} \neq \mathbf{0}$ và bị chặn, hệ trở thành:
$$ \mathbf{x}(t+1) = \mathbf{J} \mathbf{x}(t) + \alpha \beta \mathbf{u}(t) $$
Vì $\rho(\mathbf{J}) < 1$, hệ là ổn định (ISS ở discrete-time). Hệ thỏa mãn ràng buộc:
$$ \|\mathbf{x}(t)\| \le K \rho^t \|\mathbf{x}(0)\| + \frac{K}{1 - \rho} \sup_{0 \le \tau \le t} \|\alpha \beta \mathbf{u}(\tau)\| $$
Trong đó $\rho = \rho(\mathbf{J}) < 1$. Điều này chứng minh rằng trạng thái luôn được giới hạn an toàn miễn là đầu vào ngoại suy bị chặn. Hệ thống không có chu kỳ giới hạn (limit cycles) và không thể rơi vào trạng thái hỗn loạn (emergent chaos) nếu chỉ dựa vào core engine. Hỗn loạn nếu có chỉ là "controlled chaos" thông qua đầu vào $\mathbf{u}$.

---

## 4. Lyapunov Proof Template

Để chứng minh chính thức tính ổn định, ta xây dựng một hàm Lyapunov toàn cục.

### 4.1 Chọn hàm Lyapunov
Chọn hàm toàn phương:
$$ V(\mathbf{x}) = \mathbf{x}^\top \mathbf{P} \mathbf{x} $$
với $\mathbf{P}$ là ma trận đối xứng, xác định dương. Thông thường chọn $\mathbf{P} = \mathbf{I}$.

### 4.2 Tính chênh lệch Lyapunov
$$ \Delta V = V(\mathbf{x}(t+1)) - V(\mathbf{x}(t)) $$
Thay $\mathbf{x}(t+1) = \mathbf{J}\mathbf{x}(t) + \mathbf{c}$, với $\mathbf{c} = \alpha \beta \mathbf{u}(t)$:
$$ V(t+1) = \mathbf{x}^\top \mathbf{J}^\top \mathbf{P} \mathbf{J} \mathbf{x} + 2 \mathbf{c}^\top \mathbf{P} \mathbf{J} \mathbf{x} + \mathbf{c}^\top \mathbf{P} \mathbf{c} $$

### 4.3 Trường hợp không có ngoại sinh ($\mathbf{c} = \mathbf{0}$)
$$ \Delta V = \mathbf{x}^\top (\mathbf{J}^\top \mathbf{P} \mathbf{J} - \mathbf{P}) \mathbf{x} $$
Nếu tồn tại ma trận xác định dương $\mathbf{Q}$ sao cho:
$$ \mathbf{J}^\top \mathbf{P} \mathbf{J} - \mathbf{P} = -\mathbf{Q} $$
thì $\Delta V = -\mathbf{x}^\top \mathbf{Q} \mathbf{x} \le 0$, và hệ ổn định tiệm cận. Đây là phương trình Lyapunov rời rạc.

> **Tính toán thực tế (Constructive Computation):**
> Vì $\rho(\mathbf{J}) < 1$, chuỗi sẽ hội tụ. Nếu ta chọn $\mathbf{Q} = \mathbf{I}$, thì ma trận $\mathbf{P}$ chính là nghiệm của chuỗi hội tụ vô hạn:
> $$ \mathbf{P} = \sum_{k=0}^{\infty} (\mathbf{J}^\top)^k \mathbf{J}^k $$

### 4.4 Trường hợp có ngoại sinh
Sử dụng bất đẳng thức, ta có:
$$ V(t+1) \le (1 - \delta) V(t) + K \|\mathbf{c}\|^2 $$
với $\delta > 0$ và $K$ là hằng số. Từ đó suy ra:
$$ \limsup_{t \to \infty} V(t) \le \frac{K}{\delta} \gamma_{\text{cap}}^2 $$
Chỉ ra rằng trạng thái bị chặn đều, và nếu $\mathbf{u} \to \mathbf{0}$ thì $V \to 0$.

---

## 5. Kiến trúc triển khai 3 lớp (3-Layer Architecture)

Để tách bạch trách nhiệm và đảm bảo an toàn toán học, kernel được tổ chức thành ba lớp:

### 5.1 Layer 1 – MathCore (Thuần toán học)
- Không biết thông tin về governance, plugin, actor.
- Hoàn toàn deterministic, pure function.
- Thực hiện phép tính: $\mathbf{x}_{\text{new}} = \mathbf{x} + \alpha [(\mathbf{A} - \mathbf{I})\mathbf{x} - \lambda \mathbf{L} \mathbf{x} - \eta \mathbf{x} + \beta \mathbf{u}]$
- Đầu vào: $\mathbf{x}, \mathbf{u}, \mathbf{A}, \mathbf{L}, \alpha, \lambda, \eta, \beta$. Đầu ra: $\mathbf{x}_{\text{new}}$.

### 5.2 Layer 2 – GovernanceGuard
- Kiểm tra các ràng buộc trước khi gọi MathCore:
  - $\|\mathbf{u}\| \le \gamma_{\text{cap}}$
  - $\mathbf{A}$ có row-stochastic không?
  - $\mathbf{L}$ có đối xứng và PSD không?
- Kiểm tra các bất biến (invariants) từ plugin.
- **Spectral Safety Bound:** Yêu cầu $\rho(\mathbf{J}) \le 1 - \delta$ với $\delta \ge 0.05$. Thay vì phân tích toàn diện phổ hệ thống trong PHP (tốn kém tài nguyên), hệ thống kiểm tra bán kính phổ thông qua **Định lý vòng tròn Gershgorin** ($O(n^2)$ độ phức tạp) trước khi cho phép cập nhật:
  $$ |J_{ii}| + \sum_{j \neq i} |J_{ij}| < 1 \quad \forall i $$
- **Stability Budget:** Tính tỷ lệ Năng lượng $energy\_ratio = \|\mathbf{x}(t+1)\| / \|\mathbf{x}(t)\|$. Nếu tỷ lệ này vượt quá ngưỡng dự kiến ($> 1 - \delta + \epsilon$), từ chối cập nhật và yêu cầu rollback snapshot.

### 5.3 Layer 3 – ExtensionOrchestrator
- Quản lý vòng đời của Plugin, Actor, Material.
- Thu thập và tổng hợp các đóng góp $\mathbf{u}$ từ các extension.
- **Tích hợp các module động (từ v1.0):**
  - **Generative Coupling Matrix:** Quản lý thay đổi của ma trận $\mathbf{A}$ một cách giới hạn.
  - **Fractal Human / Actor System:** Mỗi Actor tự tính $\mathbf{u}_i$. Tỷ lệ đóng góp $\gamma_i \mathbf{u}_i$ được Orchestrator kiểm duyệt sao cho tổng chuẩn $\le \gamma_{\text{cap}}$.
  - **Multi-region:** Graph Laplacian $\mathbf{L}$ mô phỏng tương tác (migration, trade, war). Orchestrator đảm nhận xây cấu trúc $\mathbf{L}$.
  - **Plugin Architecture:** Đóng góp vào $\mathbf{u}$ hoặc sửa đổi $\mathbf{A}, \mathbf{L}$ nhưng phải luôn thông qua GovernanceGuard.
- Gọi luồng: `ExtensionOrchestrator` $\to$ `GovernanceGuard` $\to$ `MathCore` $\to$ Cập nhật `snapshot`. Tóm lại, điều phối tất cả hoạt động.

---

## 6. Kết luận

1. Kernel trở thành một hệ động lực rời rạc co (strictly contractive discrete dynamical system) nhờ tích hợp intrinsic damping ($-\eta\mathbf{x}$).
2. Có thể chứng minh tính ổn định toàn cục bằng Lyapunov ở dạng Discrete-Time Input-to-State Stability.
3. Mọi mở rộng (plugin, actor, matter) đều phải đóng góp qua $\mathbf{u}$ và bị chặn nghiêm ngặt, không thể phá vỡ tính co của hệ.
4. Không còn can thiệp cắt cụt (hard clamp), đảm bảo không mất mát thông tin và tránh sinh ra các attractor giả, đồng thời tuân thủ các quy luật toán học về sự hội tụ theo thời gian.

Đây là nền tảng toán học vững chắc để WorldOS phát triển thành một civilization operating system thực thụ, có thể mở rộng vô hạn nhưng luôn đảm bảo tính ổn định và tất định toán học tuyệt đối.
