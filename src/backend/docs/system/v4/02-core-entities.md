# WorldOS v4 - Core Entities (Thực Thể Cốt Lõi)

Trong phiên bản V4, kiến trúc Domain-Driven Design (DDD) được tuân thủ nghiêm ngặt để phân tách rõ ràng trách nhiệm của việc định hình (Blueprint), tường thuật (Chronicle) và vận hành mô phỏng (Run Instance).

Kiến trúc này giải quyết bài toán: Làm sao một thế giới (World) có thể sinh ra vô tận các nhánh thời gian (Universes) mà không bị xung đột dữ liệu cấu hình, và làm sao tổ chức các nhánh thời gian đó theo một cốt truyện mạch lạc (Sagas).

---

## 1. World (Bản Thiết Kế Thế Giới / Blueprint & Genome)

`World` không phải là một "vũ trụ đang chạy". `World` giống như một bộ quy tắc của một trò chơi. Nó định nghĩa mọi giới hạn (Bounds) mà các vũ trụ con sinh ra từ nó phải tuân theo.

*   **Tính chất:** Bất biến về mặt thời gian (Time-invariant). Tick luôn bằng $0$ hoặc không có ý nghĩa vận hành. Không chứa `state_vector` thực tế.
*   **Trách nhiệm chính:**
    *   **Cấu hình hệ thống (Config):** Quy định loại khởi nguồn (Cosmic, Conceptual), Preset khuôn mẫu nền tảng.
    *   **Bộ Gen Thế Giới (Gene Vector & Seed Vector):** Mọi World định ra các khoảng Random Min-Max (Bounds) cho từng chiều tồn tại (Ontology, Epistemic, Civilization, Energy). Ví dụ: `Causality_strength: [0.62, 0.82]`. Trần phép thuật, Thể loại (Genre - Xianxia, Cyberpunk) và mức phát triển công nghệ cao nhất (Tech Ceiling).
*   **Genesis:** Được sinh ra đầu tiên tại Lò Rèn Kiến Tạo (World Blueprint Forge).

---

## 2. Saga (Trường Ca / Chronicle Container)

`Saga` giải quyết bài toán "kể chuyện". Nhằm nhóm lồng ghép một hoặc nhiều Universes thành một tác phẩm có tính gắn kết.

*   **Tính chất:** Thực thể nhóm logic (Logical Container). Không trực tiếp chạy thuật toán toán học nào. Chứa các "Node" nhánh cốt truyện (`SagaWorld` / `SagaTreeNode`).
*   **Trách nhiệm chính:**
    *   Lưu trữ một Series các Universes (Từ lúc sinh ra, phân nhánh, đến tận diệt).
    *   Theo dõi tiến trình tường thuật chung: Bao nhiêu thế giới đã sinh ra, bao nhiêu nhánh đã Collapse (sập). Đại diện cho một Kỷ nguyên (Epoch) lớn hoặc một Multiverse Narrative có tính liêm kết.
*   **Liên kết:** Trong cấu trúc cây phân nhánh đa vũ trụ (Tree of Parallel Timelines), Saga quản lý root và các fork của Universe.

---

## 3. Universe (Hệ Thống Động Đang Chạy / Run Instance)

`Universe` (hay Timeline) là sự hiện thực hóa của `World`. Là môi trường thời gian thực, nơi không gian toán học (State Space) thực sự giao động và Event Loop quay vòng.

*   **Tính chất:** Một Hệ Thống Động phi tuyến (Nonlinear Dynamical System). Không ngừng thay đổi biến số qua mỗi Tick (Age).
*   **Trích xuất Gene (Quantum Roll / Spark):** Khi một Universe được Ignite/Spawn từ một World, nó sẽ rút các tham số từ `Gene Vector / Seed Vector` giới hạn của World đó, dùng hàm số ngẫu nhiên (hoặc hạt giống lượng tử) để tạo ra `state_vector` cụ thể tại `Tick 0`. (Ví dụ World cho `Causality_strength` từ 0.62 - 0.82, Universe A có thể random ra 0.70, Universe B random ra 0.65).
*   **Trách nhiệm chính:**
    *   Sở hữu Biến số Lượng hóa cụ thể (`state_vector` với hàng chục Dimensions) tại mỗi một thời điểm (Tick / Age).
    *   Theo dõi sự sống còn thông qua 2 chỉ số vĩ mô: **Entropy** (Độ hỗn loạn) và **Stability Index** (Mức độ ổn định - qua vi phân ma trận Jacobian).
    *   Có thể bị rạn nứt/hủy diệt (Collapsed/Destroyed status) khi năng lượng nội tại vượt quá ngưỡng hoặc Entropy chạm đáy. Quá trình chạy kết thúc và dừng Loop.
*   **Snapshot:** Toàn bộ trạng thái tại mỗi vài Ticks được chụp lại thành `UniverseSnapshot` / `WorldSnapshot` phục vụ tua ngược (Rewind / Fork).

---

## Mối Quan Hệ Giữa 3 Thực Thể

Luồng Workflow chuẩn mực (Đã áp dụng tại Phase 28 UI V4):

1. **DEFINE:** Ở màn hình Genesis, User cấu hình `Gene Vector` (Genre, Hệ tư tưởng, Trần phép thuật). Hệ thống sinh ra một bản ghi `World`.
2. **CONTEXTUALIZE:** Chọn `World` đó. User quyết định tạo một Chương mới (Tạo hoặc chọn 1 `Saga` có sẵn).
3. **IGNITE:** Nhấn nút "Spawning First Timeline". Một `Universe` được khai sinh, ôm trọn Rule của `World`, ghi tên vào `Saga`, và bộ đếm Tick bắt đầu nhảy số liên tục.

Cấu trúc này cho phép: Một World có Cửu Vĩ Thiên Hồ làm đỉnh cấp sức mạnh, có thể có `Saga 1` kịch bản Main chết đứt chuỗi lúc Tick 500 (`Universe 1`), User dễ dàng lưu Saga đó, chọn lại World và rẽ nhánh chạy `Universe 2` thuộc `Saga 2` tại đúng quy chuẩn thế giới y hệt.
