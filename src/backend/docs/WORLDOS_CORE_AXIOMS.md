# WORLDOS CORE AXIOMS (Luật Hiến Pháp Tối Cao)

**Version:** 2.0 (Computational Complex System Edition)  
**Status:** Inviolate (Bất khả xâm phạm)  
**Scope:** Layer 0 (Core Dynamics) & System-wide Architecture  

Tài liệu này định nghĩa các Tiên đề (Axioms) tuyệt đối của WorldOS. Nó bảo vệ kiến trúc của hệ thống khỏi việc trở thành một cỗ máy sinh truyện (story generator) hỗn loạn, hoặc ngược lại, một phương trình logistic quá nhàm chán. 

WorldOS là một **Evolutionary Meta-simulator of Civilizations**. Nó tồn tại ở **Rìa của Cõi Hỗn mang (Edge of Chaos)**, nơi sự ổn định (Order) nuôi dưỡng sự phức tạp (Complexity), sự phức tạp sinh ra áp lực sụp đổ (Entropy/Fragility), và Sụp đổ (Collapse) là mồi lửa cho Tiến hóa (Evolution). 

---

## AXIOM 1: THE CORE DIMENSIONALITY IS FIXED
*(Tiên đề về Bất biến Chiều cốt lõi)*

**Điều kiện:**
1. Trục Vector trạng thái cốt lõi (Core State Vector) $x$ chứa hữu hạn số chiều bắt buộc (Order, Complexity, Resources, Cohesion). $x \in \mathbb{R}^{n_{core}}$.
2. Giá trị $n_{core}$ là cố định và không bao giờ thay đổi trên toàn bộ World Tree.

**Hệ quả đối với Plugin/Genre:**
* Không một Genre nào (Xianxia, Sci-Fi) được phép thêm chiều vào hệ cơ sở $x$. 
* Mọi yếu tố đặc thù phải nằm ở State phụ trợ $z \in \mathbb{R}^{n_{genre}}$ và chịu sự giới hạn (bounded) của $x$.

---

## AXIOM 2: REGIONALLY STABLE, BOUNDARY CRITICAL
*(Tiên đề Về Sự Sống Ở Rìa Hỗn Mang - Edge of Chaos)*

**Điều kiện:**
1. Trái với các hệ thống contractive nhàm chán toàn cục, WorldOS cho phép Self-Organized Criticality (SOC).
2. Trong vùng sinh tồn (Survival Basin $D$): Bán kính phổ (Spectral Radius) $\rho(J) < 1$. Hệ có tính hướng đích (Attractor-seeking).
3. Tại biên ranh giới ($\partial D$): Bán kính phổ $\rho(J) \approx 1$. 
4. Hệ thống phải bị ép tiến ra biên bởi lực Khám phá (Exploration Force / Complexity Growth). Bất kỳ sự kiểm duyệt tĩnh nào khóa kín Kernel vĩnh viễn ở mức $\rho(J) \le 1 - \delta$ là vi phạm luật.

**Hệ quả Thiết kế:**
* Hệ thống sẽ tự nhiên sinh ra các hiện tượng *Period-doubling*, *Slow oscillation*, và thỉnh thoảng đột phá thành *Golden Age* (Singularity Spikes).

---

## AXIOM 3: THE ENERGY BUDGET CONSTRAINT
*(Tiên đề Quản Chế Khối Lượng Hỗn Loạn Toàn Cục)*

**Điều kiện:**
1. Hỗn loạn cục bộ (Local Chaos) được phép diễn ra, nhưng Năng lượng Toàn cục của hệ thống $E(x)$ không được phép bùng nổ vô tận (Runaway infinite explosion).
2. Phải luôn tồn tại một Bất biến Giới hạn: $E(x_{total}) \le E_{max}$.

**Hệ quả đối với Matrix Updates:**
* Ngay cả khi Agent đẩy hệ thống vào Chaos, các hàm Saturation (Non-linear Damping, ví dụ Cubic Damping như $-\mu x^3$) bắt buộc phải có mặt trong Kernel để triệt tiêu năng lượng khi vượt quá mốc phi mã. 

---

## AXIOM 4: ABSOLUTE DETERMINISM 
*(Tiên đề về Tính Tiền định và Khả Năng Khôi Phục)*

**Điều kiện:**
1. Cho một tập hợp Hạt giống (Seed), Trạng thái khởi tạo ($x_0$), Tập tham số ($\theta$), và Danh sách Plugin theo thứ tự.
2. Kernel **phải luôn** sinh ra chính xác Chuỗi trạng thái $x(t)$.
3. Sự khác biệt do lỗi làm tròn Point Float (Floating-point drift) xuyên nền tảng, nếu có, phải cực nhỏ $\le \epsilon_{machine}$.

**Hệ quả:**
* Không can thiệp sửa đổi dữ liệu $x$ thủ công. Nếu mất tính xác định (Determinism), mọi nghiên cứu so sánh (Path Dependency / Bifurcation paths) sẽ trở thành vô nghĩa.

---

## AXIOM 5: STRUCTURAL COLLAPSE AS EVOLUTIONARY INCENTIVE
*(Tiên đề Về Dấu Chấm Hết Cấu Trúc Bắt Buộc)*

**Điều kiện:**
Sụp đổ (Collapse) không được kích hoạt qua hàm if-else thủ công (ví dụ: `if (health < 0)`). 
Sụp đổ là hệ quả tất yếu của cơ chế **Dual-Entropy Accumulator**:
1. $S_{structural}$ (Mất cân bằng Tài nguyên/Phân rã Vật lý).
2. $S_{cognitive}$ (Quá tải độ rườm rà/Complexity/Fragility).

**Chỉ báo Sụp Đổ Thực Sự (True Collapse Indicator):**
Collapse xảy ra khi $S_{total} \ge S_{threshold}$ VÀ Hệ thống đánh mất vĩnh viễn Trạng thái Hút (Loss of Attractor), biểu hiện qua việc Top Lyapunov Exponent $> 0$ trong không gian dài.

**Hệ quả:**
* World hiện tại sẽ Đóng Băng vĩnh viễn (Frozen Snapshot). DB chuyển sang trạng thái Phân nhánh (Branching) trên Cây Đa Vũ Trụ. 

---

## AXIOM 6: SURVIVAL AS CONSTRAINT, EXPLORATION AS OBJECTIVE
*(Tiên đề Tối Thượng Của Các Agent Nhận Thức)*

**Điều kiện:**
1. Mọi thực thể nhận thức Meta (AI Agents) sẽ cạnh tranh để điều khiển hệ thống thông qua Param $\theta$.
2. Hàm Mục tiêu: Maximize(DistanceToStableCore), tức là tò mò đẩy hệ thống ra khỏi vùng an nhàn để tìm kiếm tiến hóa.
3. Hàm Ràng buộc: $SurvivalProbability > \tau$. Agent phải rút lui nếu máy dò báo động hệ thống sắp rơi vào Vực thẳm Không thể đảo ngược (Irreversible Collapse).

**Hệ quả:**
* Cấm thiết lập Agent tự sát hoặc Agent chỉ biết co cụm bảo thủ. Sự giằng xé giữa Khám phá và Sinh tồn sẽ tự động dệt nên Toàn cảnh Lịch sử (Narrative History) mà không cần lập trình viên can thiệp bằng Text Cứng.

---

## AXIOM 7: PERTURBATION PROJECTION CONSTRAINT
*(Tiên đề Khoanh Vùng Nhiễu Loạn Ngoại Lai)*

**Điều kiện:**
Plugin/Genre Models chỉ là các khối Nhiễu (Perturbation) chiếu lên hệ Core. Bất kỳ sự cập nhật Vector $\Delta x$ nào đều phải thỏa mãn Bound Matrix trước khi được áp dụng.
$$ \| \Delta x_{plugin} \| \le f(R, C, \gamma_{cap}) $$

**Hệ quả:**
* Plugin không có quyền Override Kernel Matrix $\mathbf{J}$. Zombie Infection hay Xianxia Tribulation không được phép phá vỡ định luật Năng lượng Core (Axiom 3).

---

## AXIOM 8: THE MEMORY RESIDUE PREMISE 
*(Tiên đề Về Dấu Ấn Di Sản Xuyên Vũ Trụ)*

**Điều kiện:**
1. Khi một World sụp đổ và Cây Thế Giới (World Tree) mọc ra thế hệ con (Children Branches), chúng ta **KHÔNG LÀM NGƯỜI LẠ (Total Reset)**. 
2. Một lượng nhỏ Di sản (Memory Residue / Meta-knowledge) bắt buộc được giữ lại và chuyển giao cho Agent hoặc Parameter Vector của thế hệ mới.

**Hệ quả:**
* Cấu trúc World Tree là các Bậc Thang Tiến Hóa (Evolutionary Ladder), chứ không phải là Rừng Phân dạng lặp lại sai lầm vĩnh viễn (Fractal blind forest). Hệ thống sẽ tự học hỏi từ cái chết để kéo dãn Chu kỳ Bùng Nổ trong các Iteration sau.

---

**[CHỮ KÝ DUYỆT KIẾN TRÚC SƯ]**
*Axioms Updated: Transition from Global Contraction to Edge-of-Chaos Dynamics.*
