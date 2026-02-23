# Tầm nhìn Thiết kế Người Tương Tác (Frontend UI V4)

Trên cơ sở hệ thống kiến trúc Toán học cốt lõi v4, Giao diện tương tác hệ thống (Frontend UI) cũng cần được đập bỏ hoàn toàn thiết kế cũ để phù hợp với quy mô tầm cỡ God Console mới. 

Thiết kế "Saphire Premium Light" ở v3 chỉ phù hợp với nền tảng quản trị thông thường. Ở v4, WorldOS áp dụng ngôn ngữ thiết kế **Dark Cosmic / Sci-Fi Terminal**. Toàn bộ trải nghiệm sẽ giống như bạn đang ngồi trước bộ thu thập và điều khiển hệ thống lượng tử của một Trung Tâm Chiến Lược Không Gian (Mission Control Dashboard).

---

## 1. Hệ Thống Màu Độc Ngữ (Color System & Aesthetics)

Xuyên suốt dự án, không gian chủ đạo sẽ nhấn chìm vào khoảng không vũ trụ để làm nổi bật hệ thống dữ liệu:
*   **Background (Nebula Dark):** Không sử dụng đen tuyệt đối, mà dùng `hsl(220 20% 6%)` (Deep Slate / Dark Blue) với pattern lưới grid hoặc radial bóng sáng.
*   **Foreground (Data Glow):** Các chỉ số (Metrics/Tick) sử dụng font chữ Terminal cứng cáp (như `JetBrains Mono` hoặc `Fira Code`).
*   **Primary Highlights (Hologram Colors):** Teal/Cyan `hsl(185 70% 50%)` tạo cảm giác viễn tưởng.
*   **Accent/Alerts:** Amber/Gold dành cho Sagas và Các nút bấm cảnh báo xung đột nguy hiểm (Collapse).
*   **Chất liệu Glassmorphism Mới:** Sử dụng CSS `backdrop-blur-xl`, bảng nền đen mờ `bg-black/40`, bo góc lớn `rounded-xl`, kèm viền border siêu mỏng (1px mờ) để nổi bật từng bảng dữ liệu (Glass Panels). Phủi bóng quang (Glow Shadows) xuất hiện khi Hover.

---

## 2. World Blueprint Forge (Giao Diện Khởi Tạo)

Trang Genesis (Khởi tạo hệ thống) giờ đây không làm rối người dùng bằng hàng tá Form dài chữ nhỏ.
*   Thay vì tạo liền 3 thực thể, Hệ thống đưa ra giao diện như Lò rèn hạt giống thế giới (World Forge).
*   Bố cục màn hình rộng: Bên Phải liệt kê Danh sách các Archetype / Preset (Thư viện Gen Lịch sử), Bên Trái (Fix Layout) hiển thị Cấu hình chi tiết bộ Gen bao gồm Thể loại (Genre - Tiên Hiệp, Huyền Huyễn) Hệ năng lượng và Limit Dị Số (Seed Vector Bounds). 
*   **Auto-Naming:** Việc đặt tên World có thể tùy chỉnh nhưng tên gốc sẽ được sinh ra từ Gene kết hợp Dấu mộc thời gian: `W_[GENRE]_[POWER]_[TIMESTAMP]`.

---

## 3. World Domain Hub & Ignite Timeline (Trạm Dừng World)

Sau khi định hình xong Khung của World. Chuyển sang thiết lập các Universe / Timeline.
*   **Thẻ Thông Số Gốc:** Hiển thị trần phát triển mà World cấp phép cho các Universe. Dữ liệu này không thể sửa sau khi đã Big Bang.
*   **Giao Diện Danh Sách Parallel Timelines:** Các Universe con sẽ xuất hiện dưới dạng Thẻ Board vuông và trải trên một Grid. Nếu đang chạy vòng lặp Event Loop, một Badge `Running` bằng hiệu ứng Ping/Pulse (Đèn nháy nhịp tim) sẽ phát sáng.
*   **Ignite Timeline / Spawn Button:** Một thanh Beam phát sáng rực rỡ để God quyết định đánh lửa - sinh ra Nhánh nhân quả mới. Kèm theo hiệu ứng Spinner Radar báo hiệu việc Xoay xổ số (Quantum Roll) và Unfurling cho ra ma trận Matrix đầu tiên tại Tick $0$.

---

## 4. Giao diện Máy Gia Tốc Tiến Hóa (Evolution God Console)

Universe Controller là Trang Dashboard quan trọng nhất. Đây là Máy gia tốc - Nơi thời gian chạy vòng lặp mỗi N mili-giây và biểu đồ dữ liệu được đẩy liên tục qua Event Streams. Dành không gian rất lớn cho:
1.  **Chỉ số Thời gian thực (Hyper-Realtime Vectors):** 17 con số liên tục giao động.
2.  **Meta-Indicators (Chuyển biến Toàn cầu):** Hai chỉ số **Entropy** và **Stability Index (Lyapunov/Jacobian)** luôn nằm ở trọng tâm, có thanh tiến độ cảnh báo nếu màu Xanh chuyển mờ sang Đỏ.
3.  **Hệ Thống Phân Đoạn Kỳ (Live Chronicle Node):** Nơi hiện text log mỗi khi có một Văn minh mới sinh ra, một Trận chiến quét sạch Lãnh thổ, hay khi Kỷ Nguyên Resets. Log sẽ bóc tách liên tục theo Tick thay vì Event rải rác.
4.  **Bảng Material Harvest (Ledger Thu Hoạch Vật Chất):** Đóng vai trò là Tủ lưu trữ. Hiển thị Lịch sử "Thức tỉnh Material", "Tiến hóa", "Bị phế truất" của vô số vũ khí, cơ chế trên giang hồ trải qua ngàn năm tiến hóa. 

## Kết Luận
Việc xây dựng một giao diện đẳng cấp Sci-Fi Terminal không chỉ làm hài lòng thị giác người dùng (WOW Effect) mà còn phản ánh đúng sự chuyển biến sâu sắc tại V4: Chuyển từ việc đọc - ghi dữ liệu tĩnh sang Trải nghiệm trực tiếp Một Dòng Thời Gian Lịch Sử Sống Động.
