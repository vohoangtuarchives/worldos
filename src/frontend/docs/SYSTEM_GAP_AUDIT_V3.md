# System Gap Audit V3 (Docs ↔ Sourcecode)

Mục tiêu: chỉ ra các điểm **chưa đủ / chưa khớp** giữa tài liệu kiến trúc V3 và sourcecode hiện tại để làm backlog cải tiến.

## 1) AI agent roster vẫn còn dữ liệu giả trong backend

- Theo docs, AI layer là lớp evaluate/enrichment và cần observability đúng runtime.
- Tuy nhiên `AIGovernanceRepository::getAgentStats()` vẫn trả về `roster` hardcode (`Chronicler`, `Critic`, `Planner`) thay vì lấy từ DB/runtime.

**Hệ quả**
- Frontend có thể đang hiển thị cảm giác "live" nhưng dữ liệu roster không đáng tin.
- Gây nhiễu khi vận hành vì dashboard và runtime thực tế lệch nhau.

**Source**
- `src/backend/app/Domains/WorldManagement/Repositories/AIGovernanceRepository.php`

---

## 2) API intervene hiện chỉ là message mock, chưa có side-effect domain

- `WriterAIAgentController::intervene()` hiện trả về message success dạng text nhưng chưa gọi service/domain action để tác động runtime hoặc ghi audit.

**Hệ quả**
- UI có nút hành động nhưng không có tác động thật lên hệ thống.
- Khó phân biệt "simulation control" thật với "UI placebo".

**Source**
- `src/backend/app/Http/Controllers/Api/Writer/WriterAIAgentController.php`

---

## 3) Admin universe lock là stub ở backend

- `AdminController::toggleLock()` hiện trả message stub, chưa mutate trạng thái lock thật.
- Frontend trước đó có lock-manager dễ gây hiểu nhầm; hiện đã bỏ khỏi admin UI, nhưng backend gap vẫn còn.

**Hệ quả**
- Chưa có cơ chế khóa universe thực sự ở tầng admin runtime.
- Nếu cần feature lock trong tương lai, phải thiết kế contract + persistence rõ.

**Source**
- `src/backend/app/Http/Controllers/Api/AdminController.php`

---

## 4) Dashboard AI writer còn dùng dữ liệu/logic không production-safe

- `AgentDashboard.tsx` dùng `Math.random()` trong render cho token throughput (không deterministic).
- File này cũng đang là một trong các nguồn lint error của repo (`react-hooks/purity`).

**Hệ quả**
- Mất độ tin cậy số liệu hiển thị.
- Cản trở CI quality nếu muốn bật strict lint.

**Source**
- `src/frontend/src/features/writer/AgentDashboard.tsx`

---

## 5) Mismatch giữa “runtime-first observability” trong docs và phạm vi dữ liệu hiện có

- Docs nhấn mạnh Runtime = Universe và observability phải bám universe/snapshot/chronicle.
- Hiện frontend đã cải thiện đáng kể ở world/admin view, nhưng một số màn vẫn thiên về summary và thiếu drill-down chronicle/snapshot liên thông theo cùng trace-id/session.

**Hệ quả**
- Operator vẫn phải nhảy nhiều màn để điều tra incident/collapse root-cause.
- Chưa đạt trọn vẹn mục tiêu “inspectable + actionable” cho V3 lab chạy dài hạn.

**Reference docs**
- `src/backend/docs/WORLDOS_2_CLEAN_ARCHITECTURE.md`
- `src/backend/docs/WORLDOS_2_FINAL_FORM_AND_LAB.md`

---

## 6) Governance config chưa biểu diễn đầy đủ state “deterministic vs AI-enriched frontier”

- Docs mô tả frontier 2 lớp (provisional/enriched) và AI toggle không làm dừng deterministic evaluation.
- API admin hiện expose các chỉ số tổng quan (`generations_per_hour`, `collapse_rate_percent`, `frontier_size`, `ai_enabled`) nhưng chưa đủ để phân biệt chất lượng 2 lớp frontier.

**Hệ quả**
- Khi AI tắt/bật, vận hành khó thấy impact thực vào frontier enrichment.
- Thiếu KPI chuyên dụng cho mode vận hành dài hạn.

**Source/docs**
- `src/backend/app/Http/Controllers/Api/AdminController.php`
- `src/backend/docs/WORLDOS_2_FINAL_FORM_AND_LAB.md`

---

## Backlog ưu tiên đề xuất

1. **P0**: Loại bỏ dữ liệu hardcode trong `getAgentStats()`; thay bằng truy vấn runtime thật (status/throughput/source).
2. **P0**: Implement domain side-effect cho `intervene()` + audit log + response contract rõ (accepted/applied/rejected).
3. **P1**: Quy hoạch lại API admin observability để có metric frontier provisional vs enriched.
4. **P1**: Chuẩn hóa trace runtime incident (universe_id + tick + request_log_id) để debug end-to-end.
5. **P2**: Cleanup toàn bộ màn AI/Writer còn hiển thị pseudo-metric (ví dụ random token).

