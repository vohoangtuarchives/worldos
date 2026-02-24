WorldOS v0.1.0
Báo cáo Phân tích Mâu thuẫn
Giữa Phần VI (Governance & Constitution) và các phần còn lại
Phạm vi kiểm tra: Phần I – X • Ngày: 2026-02-23
Tổng quan
Tài liệu này ghi nhận và phân tích các điểm mâu thuẫn, mơ hồ, và thiếu nhất quán giữa Phần VI (Governance & Constitution) và các phần còn lại trong WorldOS v0.1.0 Backend Documentation.
Phương pháp: đọc từng mệnh đề quy tắc trong Phần VI, đối chiếu với định nghĩa kỹ thuật (Phần I–V, VII–X) và DB schema (Phần VIII). Mỗi mâu thuẫn được phân loại theo mức độ ảnh hưởng và kèm đề xuất giải quyết cụ thể.
Bảng tóm tắt
#
Mâu thuẫn
Phần VI vs.
Mức độ
Hành động cần thiết
M1
Myth/Scar gắn world_id thay vì universe_id
Phần VIII (DB Schema)
NGHIÊM TRỌNG
Sửa FK trong DB schema và migration
M2
Rule 6.3 cấm AI ảnh hưởng emergence vs. AI applyPressure
Phần IX (AI System)
NGHIÊM TRỌNG
Vẽ ranh giới rõ: physics pressure ≠ myth emergence
M3
Invariant 'chỉ PressureSignal' vs. Path A Mutation
Phần V & VII
CẦN LÀM RÕ
Quyết định policy; sửa một trong hai chỗ
M4
Creator Scar (ẩn dụ) vs. Scar kỹ thuật trong DB
Phần III (Physics Engine)
CẦN LÀM RÕ
Tách biệt tên: 'CreatorMark' vs 'WorldScar'
M5
Seed EXHAUSTED + Fork: seed có 'sống lại' không?
Phần II (Core Concepts)
MƠ HỒ
Thêm quy tắc rõ ràng cho trường hợp này
M6
WTR Seed Bias 'nghiêng xác suất' vs. Rule 6.3 Determinism
Phần VI (WTR)
MƠ HỒ
Định nghĩa rõ boundary seeding vs. emergence
M1 — Myth/Scar gắn world_id thay vì universe_id
NGHIÊM TRỌNG
Phá vỡ nguyên tắc kiến trúc cốt lõi: Universe = Runtime Instance. Ảnh hưởng trực tiếp đến DB schema và tính toàn vẹn dữ liệu.
Nguồn gốc mâu thuẫn
Phần VI (6.4) — Myth & Scar Governance phát biểu:
Myth = Crystallized belief pattern đạt critical mass [trong quá trình simulation].
Scar = Permanent consequence của event. Formation: Critical Event → ScarFactory → WorldScar.
Bản chất ngữ nghĩa: cả Myth và Scar đều là sản phẩm của quá trình runtime — chúng nổi sinh từ sự kiện xảy ra trong một Universe đang chạy, không phải từ blueprint World.
Phần VIII (8.3) — DB Schema khai báo:
world_myths → world_id → worlds
scars → world_id → worlds
DB gắn cả hai bảng vào World (blueprint), không phải Universe (runtime).
Phần VII (7.2) — Nguyên tắc bất biến khẳng định:
Universe = Runtime Instance. Mọi state tại thời điểm t sống ở Universe. World không giữ snapshot, entropy, hay current_time.
Phân tích hệ quả
Nếu giữ nguyên world_id trong hai bảng này, ba vấn đề kỹ thuật xảy ra:
Hai Universe sinh từ cùng một World sẽ nhìn thấy và bị ảnh hưởng bởi cùng một tập Myth/Scar, dù lịch sử runtime của chúng hoàn toàn khác nhau.
Fork Universe tại tick T không tách được Scar history — nhánh fork mang toàn bộ Scar của nhánh gốc kể cả Scar sinh ra sau điểm fork.
Rollback từ snapshot trở về tick cũ không thể khôi phục đúng trạng thái Myth, vì Myth gắn World không gắn Universe snapshot.
Ví dụ minh họa
Universe A và Universe B cùng sinh từ World 'Đại Việt'. Universe A trải qua cuộc chiến 1000 năm và tạo ra WorldScar 'Nỗi đau phân ly'. Universe B đi theo con đường hòa bình. Với schema hiện tại, Universe B cũng bị ảnh hưởng bởi WorldScar đó vì Scar gắn World, không gắn Universe A.
Đề xuất giải quyết
Chuyển FK của cả hai bảng từ world_id sang universe_id:
-- Trước
world_myths (id, world_id, ...)
scars (id, world_id, weight, ...)
-- Sau
world_myths (id, universe_id, ...) -- FK → universes
scars (id, universe_id, weight, ...) -- FK → universes
Nếu muốn World giữ Myth/Scar làm template (blueprint) thì cần tách thành hai bảng riêng: một bảng 'myth_templates' gắn world_id, một bảng 'universe_myths' gắn universe_id. Tài liệu phải khai báo rõ sự khác biệt này.
Ghi chú migration
Cần migration script để: (1) tạo cột universe_id trong hai bảng, (2) gán universe_id dựa trên World's first Universe, (3) drop cột world_id cũ. Dữ liệu hiện tại sẽ được gắn vào Universe đầu tiên của mỗi World — đây là heuristic, không phải chính xác.
M2 — Rule 6.3 cấm AI ảnh hưởng emergence vs. AI applyPressure
NGHIÊM TRỌNG
Mâu thuẫn trực tiếp giữa Foundation Rule và thiết kế AI System. Nếu không giải quyết, implement sẽ vi phạm một trong hai.
Nguồn gốc mâu thuẫn
Phần VI (6.2) — Foundation Rule 6.3 phát biểu:
Algorithm Determinism: Myth Emergence Engine phải thuần tất định dựa trên input Belief/Events, không randomize hay hướng dẫn bởi 'AI creativity'.
Ý nghĩa rõ ràng: AI không được can thiệp vào quá trình emergence của Myth — quá trình này phải chạy thuần theo algorithm tất định.
Phần IX (9.2) — AI System khai báo UniverseEvaluatorInterface:
evaluate(UniverseMetrics) → EvaluationResult (ip_score, recommendation: fork|continue|archive, mutation_suggestion).
Phần IX (9.2) — DecisionEngine khai báo:
Từ EvaluationResult → fork / archive / continue (optional applyPressure lên Universe).
applyPressure lên Universe có nghĩa là AI tăng contradiction_index hoặc điều chỉnh pressure field — điều này ảnh hưởng gián tiếp đến điều kiện nổi sinh của Myth vì Myth Emergence Engine đọc pressure state làm input.
Phân tích hệ quả
Đây là mâu thuẫn về ranh giới ảnh hưởng. Vấn đề không phải AI có được inject pressure không — mà là ở chỗ Rule 6.3 không phân biệt hai layer:
Layer
Định nghĩa
AI được can thiệp?
Rule 6.3 nói gì
Physics Pressure Layer
contradiction_index, entropy, trauma — input cho Phase Transition Engine
Có — qua applyPressure
Không đề cập rõ
Myth Emergence Engine
Xử lý Belief → Myth theo threshold algorithm
Không — Rule 6.3 cấm
Cấm rõ ràng
Vấn đề: khi AI applyPressure lên Physics Pressure Layer, nó gián tiếp thay đổi điều kiện đầu vào của Myth Emergence Engine. Rule 6.3 có ý định bao gồm cả ảnh hưởng gián tiếp này không? Tài liệu không trả lời.
Ví dụ minh họa
AI đánh giá Universe 'quá nhàm' → applyPressure tăng contradiction_index lên 0.75 → Myth Emergence Engine đọc contradiction_index cao → nổi sinh Myth 'Cuộc Đại Loạn'. Về mặt kỹ thuật, Myth Emergence Engine chạy deterministic dựa trên input — nhưng input đó đã bị AI bẻ hướng.
Đề xuất giải quyết
Cần thêm một đoạn phân tách rõ trong cả Phần VI và Phần IX:
Rule 6.3 chỉ áp dụng cho Myth Emergence Algorithm nội bộ — thuật toán tính MythScore từ Belief/Event là tất định, không có AI creativity.
AI được phép applyPressure lên Physics Layer (contradiction_index, entropy) — đây là tác động vào môi trường vật lý, không phải vào emergence algorithm.
Ranh giới cụ thể: Myth Emergence Engine không nhận input trực tiếp từ AI. Nó chỉ nhận WorldStateVector và Belief/Event records từ DB. AI tác động gián tiếp qua Universe state.
Quy tắc đề xuất bổ sung
Thêm vào Phần VI sau Rule 6.3: 'Ghi chú: AI được phép điều chỉnh Universe physics state (contradiction_index, pressure) thông qua UniverseRuntimeService::applyPressure(). Điều này không vi phạm Rule 6.3 vì Myth Emergence Engine vẫn chạy thuần tất định trên input physics state — AI không can thiệp vào thuật toán emergence, chỉ vào điều kiện môi trường.'
M3 — Invariant 'chỉ PressureSignal' vs. Path A Direct Mutation
CẦN LÀM RÕ
Mâu thuẫn nội tại trong chính Phần VII. Phần V mô tả Path A là 'Implemented' nhưng một invariant trong Phần VII loại trừ nó.
Nguồn gốc mâu thuẫn
Phần V (5.1) — Causality Bridge khai báo hai path:
Path A — Direct Mutation: Implemented. Chapter → StoryEventExtractor → UniverseMutationService::commit()
Path B — Pressure Signal: Contract + stub. Chapter → PressureSignal → NarrativePressureBridge::injectPressure()
Phần V thừa nhận rõ Path A đang hoạt động trong production.
Phần VII (7.1) — Ba Ranh giới Tuyệt đối, Ranh giới #3:
Narrative không bao giờ ghi state_vector / Universe trực tiếp — chỉ qua UniverseMutationService hoặc NarrativePressureBridge.
Ranh giới #3 này cho phép cả hai path — có vẻ nhất quán với Phần V.
Phần VII (7.2) — Bảng Nguyên tắc Bất biến, dòng 'Narrative':
Narrative = Observer + Pressure. Đọc state; nếu ảnh hưởng runtime thì chỉ qua PressureSignal (phase transition), không mutate vector trực tiếp.
Invariant này chỉ cho phép Path B — không đề cập Path A.
Phân tích hệ quả
Trong cùng Phần VII, Ranh giới #3 (7.1) cho phép cả hai path nhưng Invariant (7.2) chỉ cho phép Path B. Đây là mâu thuẫn nội tại trong một phần duy nhất. Tùy theo cách đọc, developer có thể implement theo hai hướng ngược nhau.
Căn cứ
Kết luận
Ghi chú
Phần VII (7.1) Ranh giới #3
Path A (Mutation) được phép
Tường minh: 'chỉ qua UniverseMutationService hoặc NarrativePressureBridge'
Phần VII (7.2) Invariant
Chỉ Path B (Pressure) được phép
Tường minh: 'chỉ qua PressureSignal'
Phần V (5.1)
Path A đang Implemented
Thực tế hiện tại của codebase
Câu hỏi cần quyết định
Path A (Narrative → UniverseMutationService với magnitude giới hạn) có được phép tồn tại vĩnh viễn, hay đây là legacy cần loại bỏ khi Path B được implement đầy đủ? Đây là quyết định thiết kế, không phải lỗi kỹ thuật — nhưng phải được ghi rõ.
Đề xuất giải quyết
Một trong hai hướng, chọn rõ và ghi vào tài liệu:
Hướng A — Giữ cả hai path: Sửa Invariant (7.2) thành 'Narrative ảnh hưởng runtime chỉ qua UniverseMutationService (magnitude-limited) hoặc PressureSignal — không ghi state_vector trực tiếp.' Thêm ghi chú: Path A là escape hatch có kiểm soát, Path B là hướng dài hạn.
Hướng B — Chỉ giữ Path B: Ghi rõ Path A là deprecated, đặt timeline loại bỏ (ví dụ: sau khi NarrativePressureBridge được implement đầy đủ). Thêm @deprecated vào NarrativeToUniverseAdapter trong code.
M4 — Creator Scar (ẩn dụ) vs. WorldScar kỹ thuật
CẦN LÀM RÕ
Dùng cùng từ 'Scar' cho hai khái niệm khác nhau: một là ẩn dụ triết học, một là DB record kỹ thuật. Gây nhầm lẫn khi implement.
Nguồn gốc mâu thuẫn
Phần VI (6.2) — Foundation Rule 5.1 phát biểu:
Creator là người tham gia, không phải chủ nhân. Mọi can thiệp của Creator tạo ra Scar.
Câu này dùng 'Scar' như một ẩn dụ triết học: mọi hành động đều có hệ quả không thể xóa.
Phần III (3.6) — Physics Engine định nghĩa Scar kỹ thuật:
Scar = bản ghi lâu dài của sự kiện thảm khốc. Formation: catastrophic event → ScarFactory → WorldScar (IMMUTABLE). Weight 1–10.
Phần VI (6.4) — Myth & Scar Governance:
Code enforcement: updating và deleting throw exception.
WorldScar là DB record có weight, có source event, có ScarFactory. Nó không thể sinh ra từ một hành động quản trị như 'Kill World' hay 'Freeze World'.
Phân tích hệ quả
Vấn đề nảy sinh khi developer đọc Rule 5.1 và hỏi: nếu Creator freeze World, ScarFactory có được gọi không? Weight là bao nhiêu? Source event là gì? Kill World có tạo WorldScar hay không — và nếu có, WorldScar đó gắn vào đâu khi World đã chết?
Hành động Creator
Có tạo WorldScar kỹ thuật không?
Tài liệu trả lời không?
Freeze World
Không rõ
Không
Fork World
Không rõ
Không
Kill World
Không rõ — World đã không còn tồn tại
Không
Inject Seed
Không rõ
Không
Đề xuất giải quyết
Tách biệt hai khái niệm bằng tên khác nhau:
Giữ 'WorldScar' cho DB record kỹ thuật (ScarFactory, weight, source_event, append-only).
Đổi tên ẩn dụ trong Rule 5.1 thành 'GovernanceTrace' hoặc 'CreatorMark' — một audit record riêng biệt trong governance_logs, không phải WorldScar.
Thêm quy tắc rõ: 'Hành động quản trị (freeze, fork, kill) không gọi ScarFactory. Chúng tạo GovernanceTrace trong governance_logs với justification và audit timestamp.'
M5 — Seed EXHAUSTED khi Fork: seed có sống lại ở nhánh mới không?
MƠ HỒ
Không mâu thuẫn trực tiếp, nhưng tài liệu im lặng về một edge case quan trọng của cả Seed Governance lẫn Fork mechanics.
Nguồn gốc vùng mơ hồ
Phần VI (6.3) — Seed Governance phát biểu:
Không được reactivate seed EXHAUSTED. Xung lực mới = seed mới — đây là quy tắc bất biến.
Phần VI (6.1) — Constitution Article V phát biểu:
Fork là bảo tồn, không phải trốn tránh. Fork chỉ hợp lệ khi: có lý do rõ ràng, có post-mortem, có governance approval.
Phần II (2.2) — Universe Parameters ghi nhận:
Parameters khi fork: ancestors, event, branch_type.
Tài liệu mô tả fork clone Universe tại một tick cụ thể — nhưng không nói gì về trạng thái của các seed tại thời điểm fork.
Phân tích vùng mơ hồ
Khi Universe A fork tại tick T, Universe B (nhánh mới) kế thừa toàn bộ state tại tick T. Câu hỏi phát sinh:
Câu hỏi
Hệ quả nếu trả lời CÓ
Hệ quả nếu trả lời KHÔNG
Seed EXHAUSTED ở tick T-1 có được coi là EXHAUSTED trong Universe B không?
Universe B bắt đầu không có seed đó — timeline mới sạch hơn nhưng mất context lịch sử.
Universe B có thể reactivate seed 'EXHAUSTED' từ nhánh gốc — vi phạm quy tắc 'không reactivate EXHAUSTED'.
Seed đang ACTIVE ở tick T có tiếp tục active trong Universe B không?
Universe B thừa kế seed đang chạy — có thể là ý đồ khi fork (bảo tồn điều kiện).
Universe B fork mà mất seed context — fork sẽ không cho ra kết quả có ý nghĩa.
Đề xuất giải quyết
Thêm quy tắc rõ ràng vào Phần VI (6.3) Vòng đời Seed:
Seed đang ACTIVE tại tick T của nhánh gốc: được kế thừa sang Universe fork — fork bảo tồn điều kiện hiện tại.
Seed đã EXHAUSTED trước tick T: không được kế thừa — Universe fork bắt đầu không có seed đó và không thể reactivate.
Seed mới muốn inject vào Universe fork sau khi fork: cần governance approval mới, như bất kỳ seed nào khác.
Lý do quan trọng
Nếu không có quy tắc này, fork trở thành cơ chế lách luật để reactivate seed EXHAUSTED một cách gián tiếp: EXHAUSTED seed → Fork Universe tại tick trước khi EXHAUSTED → seed sống lại trong nhánh mới. Article V nói 'Fork là bảo tồn, không phải trốn tránh' — quy tắc seed phải đồng bộ với tinh thần đó.
M6 — WTR Seed Bias 'nghiêng xác suất' vs. Rule 6.3 Determinism
MƠ HỒ
Không mâu thuẫn trực tiếp nhưng ranh giới giữa 'seeding' (input của con người) và 'emergence' (output của engine) chưa được định nghĩa.
Nguồn gốc vùng mơ hồ
Phần VI (6.5) — WTR Seed Bias Engine (Phase 2):
Trace → Seed Bias Engine: bản năng tiến hóa — nhớ điều gì dẫn collapse/thịnh vượng; nghiêng xác suất, không cấm.
'Nghiêng xác suất' nghĩa là WTR sẽ đề xuất (hoặc tự động) bias xác suất của seed nào được chọn cho World/Universe tiếp theo, dựa trên pattern lịch sử.
Phần VI (6.2) — Foundation Rule 6.3:
Algorithm Determinism: Myth Emergence Engine phải thuần tất định dựa trên input Belief/Events, không randomize hay hướng dẫn bởi 'AI creativity'.
Phân tích vùng mơ hồ
Điểm cần làm rõ: Rule 6.3 nhắm vào Myth Emergence Engine, không nhắm vào Seed Selection. Nhưng tài liệu không phân tách rõ hai layer này, dẫn đến câu hỏi:
Layer
Deterministic?
WTR Seed Bias áp dụng?
Seed Selection (con người hoặc WTR đề xuất seed nào inject)
Không bắt buộc — đây là input
Có — đây là mục tiêu của Phase 2
Myth Emergence Engine (Belief/Event → MythScore → Myth)
Bắt buộc — Rule 6.3
Không được phép
Physics Evolution (differential equations)
Bắt buộc — seeded RNG trong V5
Không trực tiếp
Nếu WTR Seed Bias chỉ ảnh hưởng đến layer Seed Selection (input của con người), không có vi phạm Rule 6.3. Nhưng nếu WTR 'nghiêng xác suất' theo nghĩa tự động inject seed mà không có human approval, đây là vấn đề khác — vi phạm cả Human-in-the-loop contract (ADR-1003).
Đề xuất giải quyết
Thêm một đoạn định nghĩa rõ trong Phần VI (6.5) sau mô tả Seed Bias Engine:
WTR Seed Bias Engine chỉ tạo ra 'gợi ý seed' (SeedBiasSuggestion) cho human operator — không tự động inject seed.
Human operator xem xét SeedBiasSuggestion qua Governance Dashboard và quyết định có theo hay không.
Seed thực sự chỉ được inject sau khi có human approval — đây là boundary với ADR-1003 (Human-in-the-Loop).
Rule 6.3 Determinism áp dụng cho Myth Emergence Algorithm, không áp dụng cho Seed Selection flow.
Phân tách ranh giới cuối cùng
WTR Seed Bias → SeedBiasSuggestion (output) → Human Review → Human Approval → Seed Injection → Universe Physics State thay đổi → Myth Emergence Engine chạy tất định trên state mới. Rule 6.3 được bảo toàn vì Myth Emergence Engine không nhận input từ WTR — nó chỉ nhận Universe state.
Tổng kết & Hành động Cần thiết
Bảng ưu tiên
#
Mâu thuẫn
Effort
Ưu tiên
Mô tả ngắn
M1
Myth/Scar FK sai
DB Migration + code
P0 — Làm ngay
Sửa world_id → universe_id trong world_myths và scars. Ảnh hưởng mọi logic fork/rollback.
M2
Rule 6.3 vs. applyPressure
Tài liệu
P1 — Sprint này
Thêm đoạn phân tách 'physics pressure ≠ myth emergence'. Không cần code thay đổi.
M3
Path A vs. Invariant (7.2)
Tài liệu + code
P1 — Sprint này
Quyết định chính sách. Nếu giữ Path A: sửa Invariant. Nếu bỏ Path A: @deprecated code.
M4
Creator Scar vs. WorldScar
Tài liệu
P2 — Sprint sau
Đổi tên ẩn dụ thành 'GovernanceTrace'. Không ảnh hưởng logic hiện tại.
M5
Seed EXHAUSTED + Fork
Tài liệu + code
P2 — Sprint sau
Thêm quy tắc fork seed inheritance. Implement trong UniverseForkService.
M6
WTR Seed Bias vs. Determinism
Tài liệu
P3 — Trước Phase 2
Cần giải quyết trước khi bắt đầu implement WTR Seed Bias Engine (Phase 2).
Quy trình giải quyết đề xuất
M1 (P0): Tạo migration, cập nhật Eloquent relationships, sửa schema trong Phần VIII của tài liệu chính.
M2 + M3 (P1): Họp architecture review với team, quyết định chính sách, cập nhật tài liệu trong một PR duy nhất.
M4 (P2): Refactor ngôn ngữ trong Foundation Rules; thêm 'GovernanceTrace' vào governance_logs schema.
M5 (P2): Implement seed inheritance logic trong UniverseForkService; thêm unit test cho edge case.
M6 (P3): Thiết kế API của WTR Seed Bias Engine trước khi implement Phase 2 WTR.
Lưu ý cuối
Các mâu thuẫn M1–M4 đều có nguồn gốc từ quá trình tổng hợp tài liệu từ nhiều phiên bản (V3, V4, V6) — không phải lỗi thiết kế hệ thống. Kiến trúc cốt lõi nhất quán; chỉ cần đồng bộ lại ngôn ngữ và một số FK trong DB schema.