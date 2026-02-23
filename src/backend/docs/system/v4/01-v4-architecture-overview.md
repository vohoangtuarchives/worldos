# WorldOS v4 - Architecture Overview

## Mục Đích Của V4 (The Vision)
Hệ thống WorldOS V4 là một bước tiến vượt bậc từ v3 (vốn chỉ là mô phỏng tĩnh các chỉ số). V4 được định hình là một **Artificial History Generator** và **Evolutionary Geopolitical Ecosystem**. 

Thay vì chỉ quản lý dữ liệu tĩnh, V4 là một Multi-World Parallel Simulation Engine. Nó ứng dụng Toán Học Động Lực Hệ (Nonlinear Dynamical Systems) và Thuyết Tiến Hóa Trò Chơi (Evolutionary Game Theory) để mô phỏng sự thăng trầm, cạnh tranh liên tục (Continuous Pressure) và sự sụp đổ/kế thừa của các nền văn minh.

## Trụ Cột Kiến Trúc (Architectural Pillars)

### 1. Phân Tách World - Saga - Universe (Domain-Driven Design)
Khắc phục sai lầm của v3, v4 định hình rõ ràng ranh giới của 3 thực thể khởi nguồn:
*   **World (Blueprint / Genome):** Bản thiết kế tĩnh. Xác định luật chơi, trần phát triển, hệ thống phép thuật, và không gian giới hạn của các biến số (Gene Vector / Seed Vector). World là bất biến trong suốt quá trình chạy.
*   **Saga (Chronicle / Container):** Dòng thời gian tường thuật chính. Một Saga đại diện cho một bộ truyện, một series, hoặc một "Vũ trụ điện ảnh" chứa nhiều phiên bản/cành nhánh thời gian song song.
*   **Universe (Timeline / Run Instance):** Nhánh nhân quả trực tiếp đang chuyển động. Universe được khởi tạo (Spawn) từ World và nằm trong một Saga. Universe có tuổi (Age/Tick), có Entropy, có Stability Index và State Vector thực tế.

### 2. Tầng Toán Học Cốt Lõi (Pure Math Core)
Việc mô phỏng không còn phụ thuộc vào các vòng lặp if-else cảm tính.
*   Toàn bộ State Vector được chuẩn hóa về khoảng `[-1, 1]`.
*   Trạng thái phân nhánh (Bifurcation) hay sụp đổ (Criticality) được tính toán tự động thông qua Ma trận Jacobian và Trị riêng lớn nhất ($\max|\lambda|$).
*   Lyapunov Stability: Tính toán tổng năng lượng hệ thống để theo dõi biến động Entropy vĩ mô của Universe.

### 3. Động Lực Cạnh Tranh Liên Tục (Continuous Pressure)
Lịch sử không phát triển qua các "sự kiện tĩnh" (Event-based). Lịch sử tiến lên nhờ áp lực liên tục.
*   Các nền văn minh không ngừng tương tác/bóc lột tài nguyên thông qua Graph Topology.
*   Sự cạn kiệt tài nguyên (Scarcity), bất bình đẳng (Inequality) và tập trung quyền lực (Centralization) sẽ là những áp lực nội tại định hình sự sống còn của thế giới.

### 4. Tiến Hóa và Tái Trình Tự Kỷ Nguyên (Evolution & Epoch Resets)
*   **Evolutionary Doctrine**: Các học thuyết chiến lược (Mercantile, Aggressive, Defensive...) sẽ đột biến (Mutate) thông qua Replicator Dynamics (nhân rộng cái hiệu quả, đào thải cái kém).
*   **Probabilistic Epoch Reset**: Khi Căng thẳng vĩ mô chạm ngưỡng giới hạn của World (World Bounds), hệ thống không chết hẳn mà trải qua Reset. Một kỷ nguyên kết thúc, Lãnh thổ nứt vỡ, mở ra một thời kỳ Hậu tận thế hoặc Phục hưng, kế thừa một phần di sản (Materials/Legacy).

## So sánh v3 và v4

| Tiêu Chí | WorldOS v3 (Legacy) | WorldOS v4 (Artificial History) |
| :--- | :--- | :--- |
| **Logic Cốt Lõi** | Tĩnh (Event-driven) | Động lực hệ (Nonlinear Math) |
| **Sự phát triển** | Tăng tuyến tính (Linear) | Chu kỳ sinh diệt (Cyclical/Bifurcation) |
| **Khởi tạo (Genesis)** | Gộp chung World & Universe | Tách biệt hoàn toàn (Blueprint vs Instance) |
| **Bản chất Universe** | Lưu dữ liệu World tại 1 thời điểm | Một Simulation Instance chủ động chạy |
| **Chiến tranh** | Tung xúc xắc rời rạc | Áp lực khai thác liên tục (Continuous Graph) |
