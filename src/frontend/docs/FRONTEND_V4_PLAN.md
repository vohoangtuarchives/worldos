# KẾ HOẠCH TÁI THIẾT FRONTEND WORLDOS v4 (UX/UI GDD)

*Mục tiêu: Đập bỏ giao diện Web Admin tĩnh để chuyển sang trải nghiệm Hệ Điều Hành Mô Phỏng (God Simulator / Control Room).*

## 1. HỆ TƯ TƯỞNG THIẾT KẾ (Design Vibe)
- **Concept:** Bạn không "viết truyện", bạn đang ngồi trong một "Phòng Điều Khiển Đa Vũ Trụ" (Multiverse Command Center).
- **Aesthetics (Thẩm mỹ):** Glassmorphism (Kính mờ), Dark Mode sâu, Neon lines, Data Visualization dồi dào.
- **Micro-interactions:** Các biểu đồ hiển thị thông số phải có animation "nhịp thở" (breathing) để biểu thị thế giới đang sống, chứ không nằm im lìm chờ F5.

## 2. DỌN DẸP DI SẢN v3 LỖI THỜI
Trước khi xây dựng, chúng ta sẽ xóa sạch tàn dư của `Tick-based` và `Static Presets`:
- Dọn dẹp thư mục `src/frontend/src/features/world/*`.
- Xóa bỏ hàng chục hook react-query rác trong `useWriterApi.ts` đang bám vào các API không còn tồn tại ở backend.
- Loại bỏ hoàn toàn tư duy "Tạo truyện bằng Text Form".

## 3. CÁC VIEW CỐT LÕI (CORE COMPONENTS) CỦA V4

### 🚀 View 1: `GenesisSeedStation` (Buồng Tạo Mầm Vũ Trụ)
*Thay thế cho trang chọn Preset cũ.*
- **Giao diện:** Một khu vực điều chỉnh 4 Radar Charts tương ứng với 4 Vector (Ontology, Epistemic, Civilization, Energy).
- **Thao tác User:** Thay vì chọn "Tu tiên" từ Dropdown, User kéo thanh `energy_density` lên Max, thanh `epistemic_clarity` xuống Min. Biểu đồ Radar biến dạng theo thời gian thực.
- **AI Preview:** Bên cạnh có một khung kính nhỏ, AI sẽ lẩm nhẩm (Streaming Text) sinh ra một đoạn "Sấm ngữ" (Prophecy) tóm tắt Vibe của thế giới mà User vừa nặn ra bằng sỗ.
- **Action:** Nút `IGNITE BIG BANG` siêu ngầu để gọi API `POST /api/v4/genesis`.

### 👁️ View 2: `GodsEyeDashboard` (Bảng Giám Sát Chiều Không Gian)
*Giao diện trung tâm sau khi thế giới được sinh ra.*
Chia làm 3 phân vùng (Panels):

**Panel Trái (The Scales of Reality):**
- Hiển thị 4 thanh Progress Bars lớn của `WorldStateVector` (Order, Entropy, Inequality, Traumas).
- **Hiệu ứng báo động:** Nếu `Entropy` vượt 0.85, thanh bar chuyển sang Đỏ rực, viền màn hình nhấp nháy, cảnh báo "CRITICAL INSTABILITY".

**Panel Giữa (The Canonical Waterfall):**
- Đây không phải bảng tĩnh, mà là một **Event Stream** cuộn liên tục (như Terminal Log).
- Mỗi khi Backend PHP nổ ra một `WorldEvent` (Bạo loạn, Nứt gãy phép thuật), một dòng Log sẽ chảy xuống đây với timestamp.
- Người dùng có thể Pause / Resume dòng thác lịch sử này.

**Panel Phải (Hand of God - Bàn tay Can thiệp):**
- Đây là khu vực "Nhồi Áp Lực". 
- Chứa các Slider cho phép User tiêm `War Trauma` hoặc `Poverty` vào xã hội.
- Nút bấm `APPLY TENSION` -> Gửi lệnh xuống backend kích nổ `InternalPressureCalculator`.

### 🧠 View 3: `ArcSynthesisStudio` (Phòng Luyện Đan - Narrative Production)
*Nơi Giao tiếp với Não Phải (IPEngine).*
- Khi Lịch sử cuộn đến một điểm hấp dẫn (Ví dụ: Năm 3400 vừa nổ ra Đại Dịch), User bấm nút `FREEZE TIMELINE & EXTRACT ARC` (Đóng băng và Trích xuất Cốt truyện).
- Giao diện chuyển sang màn hình làm việc với AI Critic.
- AI sẽ đọc `PerceivedArchive` và trả về 3 **Seed Plots** (Hạt giống cốt truyện) dưới dạng thẻ (Cards).
- User chọn 1 thẻ, gạt thanh gạt "Độ mâu thuẫn tôn giáo", và bấm `GENERATE NOVEL`. AI bắt đầu nhả chữ.

## 4. CÔNG NGHỆ SỬ DỤNG
- **UI System:** Tailwind CSS + Shadcn UI (Customized Glassmorphism).
- **Trực quan hóa Dữ liệu (Biểu đồ):** `Recharts` hoặc Tốt nhất là `Visx` (D3.js trên React) cho các biểu đồ Radar và Network Graph mượt mà.
- **Data Fetching:** Giữ lại `React Query` nhưng cấu trúc lại cực kỳ tinh gọn, chia query key chuẩn xác theo ID Vũ trụ để Real-time Polling hoặc Server Sent Events (SSE).

---
*Kế hoạch này sẽ biến WorldOS không chỉ mạnh về lõi Toán Học, mà còn là một kiệt tác phần mềm UI cực kỳ "cuốn" đối với User.*
