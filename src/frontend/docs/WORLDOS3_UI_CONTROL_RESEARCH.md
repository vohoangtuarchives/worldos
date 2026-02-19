# Nghiên cứu cập nhật UI điều khiển hệ thống theo WorldOS V3

## Mục tiêu
Chuẩn hóa UI điều khiển (control plane) để phản ánh đúng trạng thái backend WorldOS V3 hiện tại: **Universe-centric**, tránh hiển thị pseudo-metric và phân biệt rõ dữ liệu runtime thật vs dữ liệu stub.

## Hiện trạng API control plane
Dựa trên `ClusterController`:

1. `GET /api/cluster/snapshot`
   - Có dữ liệu runtime thật: danh sách world, `current_tick`, `entropy`, `stability`, `status`, `health_status`.
   - Có tổng quan cụm: `clusterStats.total`, `clusterStats.running`.

2. `GET /api/cluster/governor`
   - Đang là stub: `pressureScore=0`, `throttleLevel=normal`, `emergencyMode=false`, `costBurnRate=null`.

3. `GET /api/cluster/system`
   - Đang là stub: `cpuPercent`, `memoryPercent`, `queueLength` đều `null`.

4. `POST /api/cluster/emergency-freeze`
   - Đang là stub side-effect, mới trả message xác nhận yêu cầu.

## Vấn đề UX quan trọng
- UI cũ trộn dữ liệu thật và số liệu mặc định/hư cấu (`42%`, `98.4%`, log thời gian giả), gây hiểu nhầm vận hành.
- Chưa thể hiện rõ triết lý V3: ưu tiên quan sát runtime world/universe thay vì dashboard “màu mè”.
- Hành động emergency freeze chưa có side-effect thật nhưng UI chưa nhấn mạnh rủi ro này.

## Định hướng UI theo WorldOS V3

### 1) Runtime-first
- Ưu tiên hiển thị các trường có dữ liệu thật từ snapshot:
  - Running/Total worlds
  - Average entropy
  - Average stability
  - Total tick (cộng `current_tick`)
- Mọi metric chưa có collector phải hiển thị trạng thái `Stub / Chưa nối telemetry`.

### 2) Rõ hợp đồng điều khiển (control contract)
- Nhóm riêng “Governor contract (stub)” và “System telemetry (stub)”.
- Kèm chú thích hành động freeze hiện đang “request-only”.

### 3) Điều tra sự cố nhanh
- Giữ world matrix + drill-down tới `/world/[id]`.
- Bổ sung bảng “Ưu tiên theo entropy” để operator biết world nào cần can thiệp trước.

## Phạm vi cập nhật frontend trong đợt này
- Refactor `CommandCenter` theo nguyên tắc runtime-first, loại bỏ pseudo-metric mặc định.
- Cập nhật copy trên trang cluster để phản ánh “WorldOS V3 Control Plane”.

## Backlog tiếp theo (đề xuất)
1. Thêm endpoint V3 cho Universe-level snapshots (không chỉ world summary).
2. Wire governor/system metrics thật (Prometheus/OpenTelemetry) để bỏ nhãn stub.
3. Chuẩn hóa audit cho `emergency-freeze` (accepted/applied/rejected + trace id).
4. Thêm “incident timeline” liên thông world event stream và cluster control actions.
