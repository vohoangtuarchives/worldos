# Access Zones — Phân vùng truy cập

Chỉ hai vùng: **Public** (không đăng nhập) và **Protected** (cần đăng nhập). Dùng khi bật auth (Phase 2+).

## Vùng (Zones)

| Vùng | Mô tả |
|------|--------|
| **Public** | Không cần đăng nhập. Mọi người truy cập được. |
| **Protected** (vùng cần bảo vệ) | Bắt buộc đăng nhập. Chỉ user đã xác thực được vào. |

*(Sau này nếu cần phân quyền chi tiết theo role có thể dùng thêm cột `users.role`.)*

---

## API (api_vietnamese.php)

### Public (không auth)

- **GET** `/api/cosmology/universes`, `/api/cosmology/universe/{id}`, `/api/cosmology/universe/{id}/chronicle`
- **GET** `/api/cosmology/meta`, `/api/cosmology/fleets`
- **GET** `/api/marketplace/artifacts`
- **GET** `/api/vietnamese-heroes/*` — toàn bộ read

### Protected (auth bắt buộc)

- **POST** `/api/cosmology/*` — universes, tick, defy-fate, intervene, halt, fork, update-laws, summon-agent, fleet, defend, faction edict
- **GET/POST** `/api/writer/*` — sagas, worlds, instances, inject, canonize, …
- **POST** `/api/marketplace/artifact/{id}/infuse`
- **GET/POST** `/api/admin/*` — stats, universes, lock, audit-logs, alerts

---

## Frontend Next.js (routes)

- **Public:** `/`, `/marketplace`, trang xem cosmology / vietnamese-heroes (chỉ xem).
- **Protected:** `/writer`, `/writer/worlds`, `/writer/sagas`, `/admin`, `/cosmology` (đầy đủ thao tác). Redirect về login nếu chưa đăng nhập (khi bật auth).

---

## Hiện trạng (Phase 1)

- API **chưa** gắn middleware auth — toàn bộ đang mở. Doc trên là chuẩn để sau gắn `auth:sanctum` cho toàn bộ vùng Protected (một middleware cho “đã đăng nhập” là đủ).
