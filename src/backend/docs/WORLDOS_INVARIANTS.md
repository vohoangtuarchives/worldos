# WORLDOS INVARIANTS REGISTRY

**Phiên bản:** 1.0  
**Ngày:** 2026-02-24  
**Trạng thái:** Bắt buộc (Non-Negotiable)  

Tài liệu này định nghĩa các **bất biến (invariants)** cốt lõi của không gian mô phỏng WorldOS. Vì WorldOS được định hướng là nền tảng nghiên cứu nguyên khối về động lực học văn minh có giới hạn (a formal computational framework for bounded civilizational dynamics), các invariant này đóng vai trò như các định luật vật lý bất khả xâm phạm.

**Mọi module, câu lệnh, quá trình tiến hóa (math core), hoặc mở rộng (plugin, actor) NẾU vi phạm bất kỳ invariant nào dưới đây đều dẫn đến việc tự động từ chối (reject) tick và ném ra ngoại lệ nghiêm trọng (Halt/Panic).**

---

## Invariant 1: Spectral Margin (Stable Contraction)

**Định nghĩa:** Hệ thống phải duy trì tính co giãn tuyệt đối (strictly contractive) để mọi trạng thái đều hội tụ về một fixed point khi không có xung lực ngoại sinh.

**Toán học:** 
$$ \rho(\mathbf{J}) \le 1 - \delta $$
với $\delta \ge 0.05$ là safety margin.

**Governance Guard:**
Thay vì phân tích toàn diện phổ (đắt đỏ $O(n^3)$), `GovernanceGuard` sử dụng Định lý vòng tròn Gershgorin ($O(n^2)$) để ép buộc bất biến này trên ma trận Jacobian $\mathbf{J}$ ở mọi tick:
$$ |J_{ii}| + \sum_{j \neq i} |J_{ij}| \le 1 - \delta \quad \forall i $$

---

## Invariant 2: Input Cap (Bounded Exogenous Force)

**Định nghĩa:** Tổng lực tác động từ bên ngoài (Actor, Plugin, Material) trong một tick không bao giờ được phép thay đổi hệ thống vượt quá giới hạn an toàn. Không một thực thể đơn lẻ hay tập hợp nào có thể sinh ra "Năng lượng vô hạn".

**Toán học:**
$$ \|\mathbf{u}(t)\| \le \gamma_{\text{cap}} $$
Và chặn trên khi qua hệ số scale:
$$ \alpha \beta \gamma_{\text{cap}} < \delta \cdot R $$
(với $R$ là bán kính kỳ vọng của attractor).

**Governance Guard:**
Lớp `ExtensionOrchestrator` có nhiệm vụ thu thập $\mathbf{u}_i$ từ các module. Lớp `GovernanceGuard` đánh giá $\mathbf{u}(t) = \sum \mathbf{u}_i$. Nếu $\|\mathbf{u}(t)\|$ vượt quá $\gamma_{\text{cap}}$, vector $\mathbf{u}(t)$ sẽ bị **cắt ngắn (scale down/clip)** về đúng giới hạn $\gamma_{\text{cap}}$ trước khi đưa vào `MathCore`. 

---

## Invariant 3: Stability Budget (Energy Boundedness)

**Định nghĩa:** Hệ thống nghiêm cấm mọi sự bùng nổ hàm mũ (exponential blow-up). Sự thay đổi năng lượng của hệ (khoảng cách rời rạc của trạng thái ẩn) trong một tick không được vượt quá mức cản nội tại.

**Toán học:**
Tỷ lệ năng lượng (Energy Ratio) giữa 2 tick liên tiếp phải thỏa mãn:
$$ \frac{\|\mathbf{x}(t+1)\|}{\|\mathbf{x}(t)\|} \le 1 - \delta + \epsilon $$
*(với $\epsilon$ là dung sai nhỏ cho phép các nhiễu động số học hoặc thao tác làm tròn)*.

**Governance Guard:**
Sau khi `MathCore` tính xong $\mathbf{x}_{\text{new}}$, `GovernanceGuard` đo lường Energy Ratio. Nếu vi phạm, `GovernanceGuard` sẽ báo động (Raise Panic), hủy bỏ cập nhật $\mathbf{x}_{\text{new}}$, và rollback hệ thống về trạng thái $\mathbf{x}(t)$.

---

## Invariant 4: Mathematical Determinism

**Định nghĩa:** Quá trình tiến hóa của Vũ trụ phải hoàn toàn tất định. Nếu cung cấp cùng một Trạng thái ban đầu (State), cùng Tham số cấu hình (LawVector, Matrix), cùng Đầu vào ngoại sinh (Input $u$), và cùng một Seed (hạt giống ngẫu nhiên tĩnh), hệ thống phải **luôn luôn** cho ra cùng một trạng thái tiếp theo ở bất cứ nền tảng hay không thời gian nào.

**Điều kiện ép buộc:**
1. Mọi truy xuất lấy hàm ngẫu nhiên bên trong `MathCore` hoặc `ExtensionOrchestrator` (bao gồm hành vi của Actor/Plugin) **phải** được lấy từ instance của `SeededRNG` gắn liền với Universe, không bao giờ dùng `rand()` hay ngẫu nhiên hệ thống (system clock).
2. Các phép toán dấu phẩy động (float) phải được chuẩn hóa thông qua thư viện fixed-point toán học nếu cần, hoặc đảm bảo tuân thủ nghiêm ngặt IEEE 754 với độ chính xác double ($FP64$).
3. Thứ tự duyệt và thực thi (iterate) các `Actor`, `Plugin`, hoặc `Region` phải được sắp xếp deterministically (ví dụ: theo ID/UUID có thứ tự tử điển - lexicographical sort), không phụ thuộc vào Hash Map hay thứ tự nạp bộ nhớ.

---

## Invariant 5: Snapshot Reproducibility

**Định nghĩa:** Bức ảnh chụp trạng thái (Snapshot) của hệ thống là **Nguồn Sự thật Duy nhất (Single Source of Truth)**. Một Snapshot chứa toàn bộ thông tin cần thiết để khởi động lại (resume) Vũ trụ từ thời điểm đó mà không có bất kỳ trạng thái chìm (hidden state) nào bị sót.

**Điều kiện ép buộc:**
1. Snapshot **phải** lưu trữ chính xác vector trạng thái ẩn $\mathbf{x}(t)$, vector lực $\mathbf{u}(t)$, các hệ số cấu trúc $\alpha, \lambda, \beta, \eta$, ma trận $\mathbf{A}, \mathbf{L}$ và **state của SeededRNG** tại thời điểm $t$.
2. Không gian observable $\mathbf{S}(t) = \sigma(\mathbf{x}(t))$ không bao giờ được dùng làm input tính toán vật lý (chỉ dùng cho mục đích quan sát/dịch thuật narrative). Mọi quá trình tính toán đều đọc state từ $\mathbf{x}$. 
3. Các Actor và Plugin muốn lưu giữ trạng thái lịch sử qua nhiều tick đều phải đóng gói payload của chúng vào cấu trúc dữ liệu của Snapshot. Bộ nhớ In-Memory (ví dụ: Redis cache) không bao giờ được xem là nguồn sự thật.

---

## Phụ lục: Quyết định cơ sở

- Kernel không sinh ra "emergence chaos" nội sinh. Kịch tính (Drama) chỉ sinh ra từ lực Input $\mathbf{u}$ có kiểm soát. 
- Mọi module thay đổi bản chất hệ thống (Genre shift, Phase transition) **không** thay đổi phương trình trực tiếp, mà thực hiện qua việc **chỉnh sửa ma trận hệ số $\mathbf{A}, \lambda, \eta$** và tạo một Snapshot mới đại diện cho Region/Regime thay đổi.
