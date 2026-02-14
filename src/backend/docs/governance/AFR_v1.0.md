# ARCHITECTURE FREEZE RECORD (AFR) - V1.0

> **VERSION**: World Engine v1.0
> **FREEZE DATE**: 2026-02-10
> **STATUS**: FROZEN - NO NEW CONCEPTS ALLOWED

## I. DEFINITION OF FREEZE
Architecture Freeze nghĩa là:
1.  **Đóng ranh giới quyền lực**: Không thêm Role, không thêm Quyền, không thay đổi flow Approval.
2.  **Đóng luồng dữ liệu cốt lõi**: Event Sourcing là luồng duy nhất. Không shortcut, không "direct DB write" ngoài luồng này.
3.  **Đóng nguyên lý vận hành**: Simulator Loop (Physics -> Seed -> Rules -> Action -> Econ -> Balance) là bất biến.

## II. IMMUTABLE PILLARS (KHÔNG ĐƯỢC SỬA)

### 🔒 1. World Law & Governance
*   **WorldLawProfile**: Cấu trúc dữ liệu của luật là tối thượng.
*   **Claim Model**: Mọi AI generation bắt buộc phải qua Claim Extraction.
*   **Kill/Fork Rules**: Chỉ được Fork khi có Incident Report + Approval.

### 🔒 2. Core Simulation Contract
*   **Event Sourcing**: Replay Engine phải trả về kết quả 100% giống Original Run (Determinism).
*   **Simulator Loop**: Thứ tự các Phase không được thay đổi.

### 🔒 3. Authority Flow
*   **Flow**: `Alert -> Incident -> AI Assessment -> Human Decision -> Audit`.
*   Không được bỏ qua `Incident` step khi có vấn đề Critical.

## III. NON-GOALS (CHÚNG TA KHÔNG LÀM GÌ?)
Những tính năng sau bị **CẤM** phát triển trong v1.x để bảo vệ tính toàn vẹn của hệ thống:

1.  ❌ **Real-time MMO Scale**: Hệ thống thiết kế cho Narrative Simulation, không phải Twitch-reflex gaming.
2.  ❌ **Player-Controlled Law**: Người chơi (User) không bao giờ được sửa World Law trực tiếp. Chỉ Operator/Admin.
3.  ❌ **AI Self-Modifying Governance**: AI không bao giờ được phép tự sửa Constitution hoặc params của Governance.
4.  ❌ **"Flexible" Consistency**: Không bao giờ hy sinh Event Consistency để lấy tốc độ.

## IV. ALLOWED AREAS (VẪN ĐƯỢC LÀM)
*   ✅ Performance Tuning (Caching Simulator state, Indexing Events).
*   ✅ UI/UX Improvements (Dashboard đẹp hơn, thao tác nhanh hơn).
*   ✅ Observability (Thêm Alert metrics, thêm Logging).
*   ✅ Content (Thêm Seeds, Thêm Faction Templates - *nhưng không đổi logic Faction*).

---

## V. FINAL SIGN-OFF
> "Freeze là kỷ luật, không phải trạng thái kỹ thuật."

System is now in **MAINTENANCE & OPTIMIZATION** mode.
Any architectural change request must trigger a **V2.0** discussion, not a patch.
