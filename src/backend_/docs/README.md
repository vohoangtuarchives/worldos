# WorldOS v0.1.0 — Tài liệu Backend

Tài liệu backend **WorldOS v0.1.0** (phiên bản chính thức, kế thừa WorldOS 6) gồm **một bộ tài liệu duy nhất**. Mọi nội dung (kiến trúc, domain, governance, API, genre, IP Factory, narrative, AI Neuro, legacy, v.v.) đã được **biên soạn trực tiếp** vào file dưới đây — không dùng file tham chiếu rời.

---

## Tài liệu chính (một file)

**[WORLDOS_BACKEND_DOCUMENTATION.md](WORLDOS_BACKEND_DOCUMENTATION.md)** — *WorldOS v0.1.0 — Tài liệu Backend chính thức*

Bao gồm **toàn bộ** nội dung đã gộp từ mọi file trong `docs/` (root, system/, governance/); **các file nguồn đã xóa** sau khi tổng hợp.

**Phần A (01–14):** Kiến trúc, Core Concepts, Simulation Loop, Physics, Narrative, Domains, Context Map, **Governance đầy đủ**, Material, **Genre System**, **IP Factory & Narrative Series**, **AI Neuro**, API/Commands/Roadmap/Frontend V4, Legacy.

**Phần B (15–20) — Tài liệu bổ sung đã biên soạn:**
15. Kiến trúc V3 (IP Foundry) & V4 (GDD — Não Trái/Phải, Cascade, Civilization Residual, WorldSeed, DB transition)
16. WorldOS 2.0 Clean Architecture & Final Form (boundaries, layers, InfluencePipeline, Narrative pressure, Snapshot taxonomy, Saga meta)
17. ADR Unified Myth, Foundation Rules, World OS Constitution (ADR-1000–1004)
18. RFC-DCE, Phase Transition Engine, Distributed Consistency
19. Saga (BACKEND_SAGA), Narrative I/O, WTR, Saga Runner
20. Các tài liệu còn lại — bảng tham chiếu nguồn đã xóa (CAUSALITY_BRIDGE, BACKEND_OVERVIEW, V3/V4, Clean Architecture, Saga, Narrative I/O, v.v.)

---

## Tài liệu thiết kế (toán học / vật lý)

**[CIVILIZATION_ENGINE_LAW_SPACE.md](CIVILIZATION_ENGINE_LAW_SPACE.md)** — *Civilization Engine & Law Space*

Thiết kế tham chiếu: Law Space 17D, feasibility F(θ), stability σ(U), cascade Physics→Culture, Jacobian & eigenvalue, feedback Dark Age/Renaissance, TSDE, DLM, Monte Carlo, Cultural Diversity 𝓓, Meme/War/Economy/Geography/Religion, Revolution = bifurcation, CPI phi tuyến, Crisis hybrid, Structural Memory, Tech dual-layer, Strategic War. **Giữ đầy đủ công thức và định luật toán học / vật lý.**

---

## Cách đọc

- **Authority:** Chỉ Universe mang runtime. World là rule container. Saga chỉ orchestrate.
- **Snapshot-first:** Mọi tiến hóa ghi `universe_snapshots`.
- **AI:** Đánh giá/mutation qua kernel; không sửa state_vector trực tiếp.

## Vị trí code chính

| Thành phần | Vị trí |
|------------|--------|
| Runtime | `App\Domains\Runtime\UniverseRuntimeService` |
| Snapshot | `App\Domains\Cosmology\Repositories\UniverseSnapshotRepository` |
| Saga | `App\Domains\Saga\Services\SagaService` |
| Kernel | `App\Domains\Evolution\Kernel\WorldEvolutionKernel` |
| AI/Metrics | `App\Domains\Runtime\Evaluation\*` |
