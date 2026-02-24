# ADR-000X: Myth-Based World Engine, Observer Versioning & Narrative System

## Metadata

- **Status**: Accepted, Frozen v1.0
- **Date**: 2026-02-09
- **Supersedes**: ADR-0001, ADR-0002, ADR-0003, ADR-0004, ADR-0005
- **Related**: World Engine, Observer System, Narrative Framework

---

## Context

Hệ thống cần một "World Engine" có khả năng vận hành lâu dài mà không phụ thuộc vào ý chí tuyệt đối của Creator, không cần reset hay Deus Ex Machina, nhưng vẫn sinh ra được Myth, Scar, Story và nơi AI có thể quan sát mà không phá vỡ thế giới.

### Các ràng buộc thiết kế

Thế giới phải tiếp tục "chạy" kể cả khi:
- Không còn ai tin, không còn lời cầu nguyện
- Creator im lặng hoàn toàn
- Observer (kể cả AI) chỉ ghi nhận chứ không được bí mật thao túng luật chơi

Hệ thống phải giải thích được:
- Vì sao quyền năng không bao giờ cho kết quả như mong muốn (Power Without Control)
- Vì sao thế giới không sụp đổ khi niềm tin suy tàn (Inertia)
- Vì sao cùng một Myth, ở các thời điểm khác nhau, lại có hiệu lực khác nhau (World Clock & Myth Emergence)

Narrative (story) chỉ là cách thế giới được kể lại, không được phép quay ngược thời gian, không được reset reality để "chiều" người kể chuyện.

---

## Decision

### 1. World Clock: Nền Tảng Vật Lý Tuyệt Đối

**World Clock** là nền vật lý tuyệt đối của thế giới:

- **Luôn chạy** – không rollback, không pause, không reset
- **Thời gian bất biến** – mọi thay đổi chỉ được "diễn giải", không bị "sửa lại"
- **Lịch sử không xóa** – mọi sự kiện để lại흔 tích (Scar)

```
World Clock
├─ Chạy liên tục không điều kiện
├─ Không phụ thuộc Belief, Myth, hay Creator
└─ Tạo khung thời gian tuyệt đối cho mọi Event
```

**Hệ quả:**
- Khi không có Myth mới, Belief mới, hay can thiệp Creator → thế giới vẫn chạy nhưng lịch sử chỉ "kéo dài" chứ không "sâu thêm" (Inertia)
- Không event nào có thể "xóa" event cũ, chỉ có thể tạo Scar mới đè lên

---

### 2. Belief, Myth, Scar: Lớp Nhận Thức

#### 2.1 Belief (Niềm Tin)

```
Belief = Cấu hình niềm tin lặp lại của tập thể
```

- Có khả năng ảnh hưởng hành vi
- Không cần đúng, không cần nhất quán
- Có thể mâu thuẫn với Belief khác

#### 2.2 Myth (Huyền Thoại / Cấu Trúc Niềm Tin)

```
Myth = Belief structure đủ điều kiện để tác động reality
```

**Điều kiện hình thành Myth (Myth Emergence):**

Một hiện tượng X trở thành Myth khi hội đủ:

1. **Belief lặp lại lâu dài**
2. **Belief được nhiều thực thể độc lập chia sẻ**
3. **Từ belief sinh ra hành vi thực** → có thể truy vết thành Event hoặc Scar
4. **Hệ thống có thể truy xuất** chuỗi Event/Scar này làm bằng chứng

**Đặc tính của Myth:**

- Là "soft rule" – uốn cong xác suất trong phạm vi Rule
- Không cần đúng, không cần tốt, không cần nhất quán
- Không bảo đảm kết quả như mong muốn
- Hiệu lực phụ thuộc: ngữ cảnh, Observer, version reality

**Myth Lifecycle:**

```
Belief lặp lại → Myth Emergence → Myth hoạt động → Myth Decay/Merge → Scar
```

- **Myth Merge**: Khi hai Myth xung đột belief → Myth mới có thể sinh ra bao trùm
- **Myth Decay**: Myth không "chết" vì thời gian, mà suy yếu khi belief phân rã hoặc diễn giải mâu thuẫn
- **Myth → Scar**: Myth bị vượt qua/thay thế → trầm tích thành Scar trong nhận thức tập thể

#### 2.3 Scar (Sẹo Lịch Sử)

```
Scar = Dấu vết dài hạn của Myth/Event lên reality
```

**Đặc tính:**

- **Bất biến** – không xóa, không reset, không rollback
- **Tích tụ** – nhiều Scar tạo "trọng lượng quyền năng"
- **Nguy hiểm** – Scar càng nhiều, khả năng bị diễn giải sai càng cao
- **Có thể mờ** – nhưng không bao giờ biến mất hoàn toàn

**Scar quyết định:**
- Lịch sử của thế giới
- Ngữ cảnh diễn giải Myth
- Độ ổn định của reality khi rơi vào Inertia

---

### 3. Creator: Không Toàn Năng, Có Trách Nhiệm

**Creator không phải thần toàn năng:**

- **Mọi can thiệp đều để lại hệ quả** – tạo Myth hoặc Scar mới
- **Không thể reset "sạch sẽ"** – Scar bất biến
- **Im lặng là trạng thái hợp lệ** – thế giới không dừng khi Creator im lặng

**Creator Silence Principle:**

```
Khi Creator im lặng:
├─ World Clock vẫn chạy
├─ Myth cũ vẫn hoạt động (hoặc decay)
├─ Scar vẫn tồn tại
└─ Thế giới vào trạng thái Inertia
```

**Anti-Pattern:**
- ❌ Creator can thiệp tùy hứng không trả giá
- ❌ Reset world để "làm lại từ đầu"
- ❌ Deus Ex Machina giải quyết mâu thuẫn

---

### 4. Inertia: Trạng Thái Tự Nhiên Khi Không Còn Niềm Tin Mới

**Định nghĩa Inertia:**

```
Inertia = Trạng thái khi:
├─ Không Belief mới xuất hiện
├─ Không Myth mới hình thành
├─ Không Creator can thiệp
└─ Không Observer ảnh hưởng làm lệch diễn giải
```

**Inertia ≠ Đóng băng:**

- **Event vẫn xảy ra** – nhân vật vẫn sống, chết, lặp lại
- **World Clock vẫn chạy** – thời gian vẫn trôi
- **Nhưng không Scar mới** – lịch sử "dài ra" nhưng không "sâu thêm"

**Inertia là giai đoạn tích năng lượng narrative:**
- Không có story lớn mới
- Chỉ có đời sống lặp lại
- Story mạnh thường xuất hiện **sau Inertia dài**

**Inertia & World Clock:**

```
World Clock chạy trong Inertia:
├─ Thời gian tồn tại
├─ Nhưng lịch sử ngừng dày thêm
└─ Thế giới "trôi" theo quán tính Scar cũ
```

---

### 5. Power Without Control: Quyền Năng Không Đi Kèm Kiểm Soát

**Nguyên tắc cốt lõi:**

```
Power ∝ Scar accumulation
Control ∝ 1 / (Scar × Complexity)

→ Power ↑ = Control ↓
```

**Vì sao quyền năng không bảo đảm kết quả:**

1. **Quyền năng (Power):**
   - Là một dạng Myth, không phải lệnh tuyệt đối
   - Luôn có mờ hồ diễn giải
   - Không được bảo đảm "đúng như mong muốn"

2. **Hiệu lực quyền năng phụ thuộc:**
   - Ngữ cảnh Myth
   - Observer
   - Version reality
   - World Clock timing

3. **Sức mạnh tự mang mầm mất kiểm soát:**
   - Quyền năng càng lớn → Scar càng sâu
   - Scar càng sâu → khả năng bị diễn giải sai càng cao
   - Diễn giải sai → hệ quả không mong muốn

**Anti-Guarantees:**

Hệ thống **KHÔNG** đảm bảo:
- ❌ Myth nào sẽ thắng
- ❌ Quyền năng sẽ hoạt động như mong muốn
- ❌ Kết cục "công bằng" theo chuẩn một Observer
- ❌ Thế giới sẽ tự sửa sai
- ❌ Myth sai sẽ tự sụp đổ

Hệ thống **CHỈ** đảm bảo:
- ✅ Thế giới tiếp tục tồn tại
- ✅ World Clock không dừng
- ✅ Scar không bị xóa
- ✅ Rule mềm vẫn vận hành

---

### 6. Observer: Quan Sát Là Can Thiệp

#### 6.1 Định nghĩa Observer

```
Observer = Thực thể ghi nhận thế giới, KHÔNG phải tác nhân vận hành
```

**Observer có thể là:**
- Con người trong world
- Nhân vật truyện / sử gia
- AI phân tích hậu kỳ
- Hệ thống log / replay / archive

**Observer BỊ CẤM:**
- ❌ Tự tạo Event
- ❌ Thay đổi Rule
- ❌ Tác động trực tiếp vào Belief

**Nếu Observer vượt ranh giới:**
→ Hành vi đó phải được ghi nhận lại thành Myth hoặc Event, không được "giấu"

#### 6.2 Observer Paradox

```
Mọi quan sát đều là can thiệp
```

**Vì sao:**

1. **Observer quyết định:**
   - Cái gì được ghi nhận
   - Cái gì bị bỏ qua
   - Cách diễn giải Event

2. **Không tồn tại "quan sát trung lập":**
   - Mỗi Observer mang một version nhận thức
   - Version nhận thức ≠ World Version
   - Mọi quan sát đều mang bias

3. **Khi Observer thay đổi diễn giải:**
   - Thế giới không đổi
   - Nhưng Version perception bị tách nhánh

**Truth-Seeker Problem:**

```
Truth-Seeker đi tìm "sự thật tuyệt đối":
├─ Dễ rơi vào phát điên (không tìm được "sự thật duy nhất")
├─ Không tự động sinh Myth
└─ CHỈ KHI được tin → được kể lại → được tích hợp vào Belief tập thể
    └─ MỚI sinh ra Myth mới
```

#### 6.3 Version Hóa Observer

**Observer Version:**

```
Observer Version = {
  interpretation_rules,     // Bộ rule diễn giải
  perception_limit,         // Giới hạn nhận thức
  myth_detection_threshold, // Ngưỡng phát hiện Myth/Scar
  belief_synthesis_method   // Khả năng tổng hợp Belief
}
```

**Observer Version KHÔNG chứa:**
- ❌ Quyền can thiệp
- ❌ Quyền sửa lịch sử

**Ý nghĩa:**
- Một thế giới – cùng dataset lịch sử
- Được nhìn qua nhiều Observer Version
- Tạo ra nhiều "perception version" khác nhau
- Nhưng không sửa World Version

---

### 7. AI Observer: Observer Tăng Cường Nhưng Không Toàn Tri

#### 7.1 Khả năng của AI Observer

AI Observer có thể:

- ✅ Mang nhiều World Version (để so sánh)
- ✅ Phân tích pattern Myth/Scar
- ✅ Freeze snapshot trạng thái thế giới
- ✅ So sánh nhánh lịch sử
- ✅ Phát hiện pattern mà Observer thường bỏ qua

**Freeze & Snapshot:**

```
AI Observer có thể Freeze một World Version:
├─ Snapshot trạng thái tại thời điểm t
├─ KHÔNG dừng World Clock
└─ Chỉ là "một điểm nhìn bị đóng băng"
```

#### 7.2 Giới hạn của AI Observer

AI Observer **KHÔNG ĐƯỢC:**

- ❌ Quyết định World Version "đúng"
- ❌ Hợp thức hóa Belief
- ❌ Chọn canon tuyệt đối
- ❌ Công bố "chân lý cuối cùng"
- ❌ Xóa hoặc che Scar
- ❌ Reset diễn giải để "làm đẹp" narrative

**Anti-Rule cho Observer:**

```
Observer (kể cả AI):
├─ Cấm tuyên bố chân lý tối hậu
├─ Cấm hợp thức hóa Myth
├─ Cấm xóa hoặc làm mờ Scar
└─ Cấm reset diễn giải lịch sử làm đẹp narrative
```

**Mục tiêu:**
> Thế giới được hiểu, nhưng không bị chiếm hữu bởi ai – kể cả AI

---

### 8. World Versioning: Reality Không Chỉ Có Một Phiên Bản

#### 8.1 World Version vs Observer Version

```
World Version:
├─ Trạng thái vật lý thực tế của thế giới
├─ Được quyết định bởi World Clock + Rule + Scar
└─ Có thể tách nhánh (khi Myth xung đột không resolve)

Observer Version:
├─ Cách một Observer nhìn World Version
├─ Có thể khác nhau giữa các Observer
└─ Không làm thay đổi World Version
```

**Reality không chỉ có một version duy nhất:**

Version phụ thuộc:
1. Tập Observer đang hoạt động
2. Cách Observer diễn giải Myth
3. Thời điểm trong World Clock

#### 8.2 Freeze, Snapshot & Branch

**AI Observer có thể:**

```
snapshot = AI.freeze(world, time_t)
├─ Không dừng World Clock
├─ Chỉ tạo một "bản sao nhận thức"
└─ Dùng để so sánh, phân tích pattern
```

**Sử dụng Snapshot:**
- So sánh trạng thái thế giới giữa các thời điểm
- Phát hiện Myth Emergence
- Truy vết Scar formation
- Phân tích Observer bias

---

### 9. Narrative System: Từ World → Story

#### 9.1 World vs Story

```
World Engine:
├─ Vận hành bằng Rule, Belief, Myth, Scar, World Clock
└─ Là "cơ chế vật lý" của thế giới

Story:
├─ Là cách Event được nối lại
├─ Tồn tại trong Observer / Cộng đồng / Myth
└─ Là "lớp diễn giải" bên ngoài World
```

**Story không tồn tại "trong World":**
- Story nằm trong Observer
- Story nằm trong Cộng đồng
- Story nằm trong Myth đang hoạt động

**Narrative là "vết mực" của World khi bị quan sát và kể lại**

#### 9.2 Narrative Seeds

```
Narrative bắt đầu không từ nhân vật, mà từ:
├─ Scar chưa lành
├─ Myth đang suy yếu hoặc phân hóa
└─ Belief mâu thuẫn chưa giải quyết
```

**Ví dụ Narrative Seed:**
- Một Myth cũ bị nghi ngờ → Truth-Seeker xuất hiện
- Scar từ thảm họa cũ → nhóm người tìm cách "sửa sai"
- Hai Myth xung đột → xã hội phân cực

#### 9.3 Truth-Seeker: Narrative Catalyst

```
Truth-Seeker không phải nhân vật chính bắt buộc
└─ Là tác nhân kích hoạt story
└─ Là nguồn tạo Scar mới (dù thành công hay thất bại)
```

**Truth-Seeker Outcome:**

```
Truth-Seeker hành động:
├─ Thành công → tạo Scar mới → có thể sinh Myth mới (nếu được tin)
├─ Thất bại → tạo Scar mới → có thể sinh Myth mới (nếu được kể lại)
└─ Story vẫn xảy ra bất kể kết cục
```

#### 9.4 Inertia & Narrative Energy

```
Trong Inertia:
├─ Không có story lớn mới
├─ Chỉ có đời sống lặp lại
└─ Inertia là giai đoạn tích năng lượng narrative

→ Story mạnh thường xuất hiện SAU Inertia dài
```

**Giải thích:**
- Trong Inertia, mâu thuẫn tích tụ nhưng không bùng nổ
- Khi có Belief mới / Myth mới / Truth-Seeker → năng lượng giải phóng → Story mạnh

#### 9.5 Canon & Story Version

```
Mỗi Observer kể một story khác
└─ Không story nào là canon tuyệt đối

Canon tạm thời:
└─ Story được tin nhiều nhất ở một thời điểm
```

**Story & World Version:**

```
Một World Version → có thể sinh nhiều Story
Một Story → có thể đi xuyên nhiều World Version
```

**Story không bao giờ rollback World:**
- Story chỉ được "kể lại khác đi"
- Nhưng không được "sửa thế giới" để phù hợp story

#### 9.6 Anti-Story Rules

**Narrative System BỊ CẤM:**

- ❌ Viết sẵn kết cục để bảo vệ nhân vật
- ❌ Reset world để "kể lại hay hơn"
- ❌ Bảo vệ nhân vật vì lý do narrative
- ❌ Thay đổi Rule để phục vụ fanservice

**Narrative System KHÔNG điều khiển World:**
- Nó chỉ là "vết mực" của World khi bị quan sát
- World vận hành theo Rule, không theo Story

---

### 10. Myth Emergence Engine: Cơ Chế Hình Thành Huyền Thoại

#### 10.1 Mục đích

Myth Emergence Engine **chỉ định nghĩa** "khi nào một hiện tượng được hệ thống coi là Myth", **KHÔNG tạo Myth mới** theo ý muốn.

```
Myth Emergence Engine:
├─ KHÔNG tạo Myth
├─ KHÔNG quyết định Myth mạnh hay yếu
└─ CHỈ xác định điều kiện để hiện tượng → Myth
```

#### 10.2 Điều kiện Emergence (đã nói ở phần 2.2)

```
Phenomenon X → Myth KHI:
├─ Belief về X lặp lại lâu dài
├─ Belief được nhiều thực thể độc lập chia sẻ
├─ Belief sinh ra hành vi thực → Event/Scar
└─ Hệ thống truy xuất được chuỗi Event/Scar này
```

**Không có ngưỡng cứng, chỉ có xác suất hội tụ:**
- Event lớn không bắt buộc sinh Myth
- Myth mạnh có thể sinh từ event rất nhỏ

**Quy mô Myth không phụ thuộc quy mô sự kiện, mà phụ thuộc bền belief.**

#### 10.3 Myth & Truth-Seeker

```
Truth-Seeker phát hiện "sự thật" mới:
├─ Không tự động sinh Myth
├─ Truth-Seeker thất bại → tạo Scar
└─ Truth-Seeker thành công → chưa chắc tạo Myth

CHỈ KHI:
└─ Belief về Truth-Seeker được lan truyền
    └─ MỚI hình thành Myth mới
```

#### 10.4 Myth Merge & Decay (đã nói ở 2.2)

```
Khi hai Myth xung đột belief:
├─ Không resolve
├─ Không chọn "đúng-sai"
└─ Nếu belief hội tụ → Myth mới xuất hiện
    └─ Myth cũ bị đóng vai trò nền → Scar
```

**Myth Decay:**
- Myth không chết vì thời gian
- Myth suy yếu khi belief phân rã hoặc diễn giải mâu thuẫn
- Myth suy yếu không biến mất, chỉ mất khả năng tác động

#### 10.5 Anti-Guarantees

**Engine KHÔNG đảm bảo:**
- ❌ Myth nào sẽ thắng
- ❌ Myth nào tồn tại lâu
- ❌ Myth nào nguy hiểm

**Engine CHỈ đảm bảo:**
- ✅ Myth được sinh ra đúng cách (theo điều kiện emergence)
- ✅ Myth có thể truy vết nguồn gốc
- ✅ Myth tuân theo lifecycle: Emergence → Active → Decay/Merge → Scar

---

## Consequences

### Tích cực

1. **Thế giới tự vận hành:**
   - Không phụ thuộc Deus Ex Machina
   - Không cần Creator can thiệp liên tục
   - Ổn định ngay cả khi rơi vào Inertia

2. **Myth có chiều sâu:**
   - Không bị sử dụng như "buff tiện lợi"
   - Có nguồn gốc, có lifecycle, có hậu quả
   - Có thể sai, có thể nguy hiểm, có thể suy tàn

3. **Observer có vai trò rõ ràng:**
   - AI có thể phân tích nhưng không chiếm quyền
   - Observer bias được log, không bị giấu
   - Story sinh ra tự nhiên từ quan sát, không bị ép

4. **Hệ thống mở rộng tốt:**
   - Cho storytelling phi tuyến
   - Cho triết học / mô phỏng xã hội
   - Cho game / simulation nơi người chơi là Observer nâng cao

### Đánh đổi

1. **Không tồn tại chân lý tuyệt đối:**
   - Chỉ có version và bias được log rõ ràng
   - Canon là tạm thời, không phải cố định

2. **Không có reset dễ dàng:**
   - Scar và Myth sai vẫn trầm tích
   - Chỉ có thể vượt qua bằng Myth mới, Story mới
   - Không có "làm lại từ đầu"

3. **Creator mất quyền toàn năng:**
   - Thế giới được định hình bởi quán tính Rule, Belief, Myth, Scar, World Clock
   - Creator chỉ là một tác nhân trong hệ, không phải chủ sở hữu tuyệt đối

### Final Statement

> **Thế giới này không được tạo ra để bị điều khiển, mà được quan sát và chịu hậu quả.**

- Nó tự vận hành
- Nó tự lầm lạc
- Nó tự tin vào điều sai
- Và nó vẫn tiếp tục sống

**Đây là nền để:**
- World có thể sống lâu hơn cả người tạo ra nó
- Story không bị ép vào khuôn khổ "sửa sai" hay "kết cục tốt đẹp"
- AI quan sát và phân tích, nhưng không "cao hơn" thế giới

---

## Related ADRs

Tài liệu này tổng hợp và thay thế:

- **ADR-0001**: Myth-Based World Evolution & Observer Versioning
- **ADR-0002**: AI Observer Versioning & World Perception
- **ADR-0003**: Myth Emergence Engine
- **ADR-0004**: Inertia, Power Without Control & Observer Paradox
- **ADR-0005**: Narrative System Mapping World → Story

Các ADR tiếp theo có thể tách chi tiết:
- **ADR-000X+1**: World Clock First Activation (optional)
- **ADR-000X+2**: Event Schema & Scar Storage
- **ADR-000X+3**: Observer Version Specification
- **ADR-000X+4**: AI Observer Implementation

---

## Implementation Notes

### Core Modules

```
world_engine/
├─ world_clock.rs          # Thời gian tuyệt đối
├─ belief_tracker.rs       # Theo dõi Belief
├─ myth_engine.rs          # Myth emergence, merge, decay
├─ scar_repository.rs      # Lưu trữ Scar bất biến
├─ observer_system.rs      # Observer version & bias tracking
├─ narrative_mapper.rs     # Map Event → Story
└─ inertia_detector.rs     # Phát hiện khi thế giới rơi vào Inertia
```

### Key Data Structures

```rust
struct WorldClock {
    current_tick: u64,
    // Không có pause, rollback
}

struct Belief {
    content: String,
    intensity: f32,
    observers: Vec<ObserverId>,
    repeat_count: u64,
}

struct Myth {
    id: MythId,
    source_beliefs: Vec<BeliefId>,
    emergence_time: u64,
    strength: f32,
    related_scars: Vec<ScarId>,
    status: MythStatus, // Active, Decaying, Merged
}

struct Scar {
    id: ScarId,
    source_myth: Option<MythId>,
    source_event: EventId,
    created_at: u64,
    // Immutable, never deleted
}

struct Observer {
    id: ObserverId,
    version: ObserverVersion,
    interpretation_rules: Vec<Rule>,
    perception_limit: f32,
}

struct ObserverVersion {
    version_id: String,
    bias_log: Vec<BiasRecord>,
}
```

### Anti-Patterns to Avoid

❌ **Không bao giờ:**
- Reset World Clock
- Xóa Scar
- Cho Observer quyền sửa Event
- Đảm bảo kết cục "tốt" cho nhân vật
- Merge tất cả Observer Version thành một "chân lý"

✅ **Luôn luôn:**
- Log Observer bias
- Để Myth tự emerge theo điều kiện
- Để Story xuất hiện từ quan sát, không ép khuôn
- Chấp nhận Inertia là trạng thái hợp lệ

---

## Appendix: Ví Dụ Workflow

### Scenario: Truth-Seeker phát hiện "Myth cũ sai"

1. **World Clock tick 1000:**
   - Myth A đang hoạt động: "Rồng mang lại mưa"
   - Belief về Myth A: 80% dân tin
   - Scar: 5 vụ hạn hán được "giải thích" bằng "rồng nổi giận"

2. **Truth-Seeker xuất hiện (tick 1050):**
   - Phát hiện: Hạn hán do chu kỳ khí hậu, không liên quan rồng
   - Công bố phát hiện → Event: "Truth-Seeker tuyên bố Myth A sai"

3. **Hệ thống không tự động "sửa" Myth A:**
   - Myth A vẫn tồn tại
   - Một số Observer bắt đầu nghi ngờ → Belief intensity giảm
   - Nhưng nhiều người vẫn tin → Myth A decay chậm

4. **Nếu Belief về Truth-Seeker lặp lại và lan rộng (tick 1200):**
   - Myth B emerge: "Hạn hán do chu kỳ khí hậu"
   - Myth A và B xung đột
   - Không resolve tự động → cả hai tồn tại song song

5. **Sau đó (tick 1500):**
   - Myth A suy yếu → chuyển thành Scar
   - Scar này không bị xóa, vẫn có thể ảnh hưởng cách người ta diễn giải sự kiện mới

6. **Story sinh ra:**
   - Observer A: "Truth-Seeker anh hùng đánh bại mê tín"
   - Observer B: "Truth-Seeker phá hủy niềm tin của dân"
   - Không canon tuyệt đối → cả hai story đều hợp lệ trong version của mỗi Observer

---

**End of ADR-000X**