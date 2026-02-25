# Regime ID và Actor Reaction — Chuẩn Engine

Tài liệu thiết kế: **Regime ID (RID)**, **Confidence ID (CID)**, **Observer output**, **Actor reaction matrix**, **Counter-force**. Dùng trực tiếp trong engine; machine-readable; scale multiverse.

---

## 1. Regime ID (RID)

Cấu trúc mã hóa 2 tầng (không dùng string free-form):

```
RID = <Primary>-<Intensity>-<Direction>
```

### 1.1 Primary (P) — 4 loại chính

| Code | Regime      |
|------|-------------|
| ST   | Stable      |
| OS   | Oscillatory |
| CH   | Chaotic-like|
| DV   | Divergent   |

### 1.2 Intensity (I) — mức độ 0–3

Dựa trên Volatility + Acceleration (từ 4 metric heuristic V, DP, SFR, AG):

| Code | Mức   |
|------|-------|
| 0    | very low |
| 1    | mild     |
| 2    | medium   |
| 3    | extreme  |

### 1.3 Direction (D) — xu hướng tổng thể

Dựa trên Directional Persistence (DP):

| Code | Ý nghĩa      |
|------|--------------|
| UP   | trending up  |
| DOWN | trending down|
| FLAT | no net drift |

### 1.4 Ví dụ RID

- `OS-2-FLAT` — dao động mạnh vừa, không drift
- `DV-3-UP` — phân kỳ cực đoan, xu hướng lên
- `ST-0-FLAT` — ổn định rất thấp, không drift
- `CH-2-FLAT` — chaos-like trung bình, không drift

---

## 2. Confidence ID (CID)

Độ tin cậy của phân loại regime:

| Code | Ý nghĩa                    |
|------|-----------------------------|
| C0   | unstable classification     |
| C1   | weak                        |
| C2   | moderate                   |
| C3   | strong (3 window consistent)|

---

## 3. Full Observer Output ID

Định dạng chuẩn cho mỗi universe tại mỗi quan sát:

```
<UniverseID>::<RID>::<CID>
```

Ví dụ:

- `UNI_4452::OS-2-FLAT::C2`
- `UNI_9981::DV-3-UP::C3`

**Nguyên tắc:** Observer trả về **RegimeState** (ValueObject), không trả về metric rời rạc. Observer là sensor; RegimeState là domain concept.

---

## 4. Mã hóa tùy chọn (8-bit bitmask)

Dùng khi cần tối ưu bộ nhớ / event stream:

| Bits  | Nội dung   | Ghi chú        |
|-------|------------|-----------------|
| 0–1   | primary    | 00=ST, 01=OS, 10=CH, 11=DV |
| 2–3   | intensity  | 0–3             |
| 4–5   | direction  | 00=FLAT, 01=UP, 10=DOWN    |
| 6–7   | reserved  | Mở rộng sau     |

Rất nhẹ cho simulation scale lớn và log (ví dụ Kafka).

---

## 5. Actor Archetype (profile)

Actor là decision unit, không phải con người. Mỗi actor có profile 5 chiều:

| Thuộc tính       | Range  | Ý nghĩa ngắn                    |
|------------------|--------|----------------------------------|
| RiskTolerance    | [0..1] | Chấp nhận rủi ro                |
| Aggression       | [0..1] | Xu hướng xung đột               |
| Adaptability     | [0..1] | Thích nghi với biến động        |
| StabilityBias    | [0..1] | Ưu tiên ổn định                 |
| ExpansionDrive   | [0..1] | Thúc đẩy mở rộng                |

---

## 6. Reaction Matrix theo RID

Actor **không** đọc V, DP, SFR, AG — chỉ đọc **RegimeState (RID)**. Phản ứng deterministic theo Primary + Intensity + Direction.

### 6.1 ST (Stable)

- **Logic:** Low volatility, low directional drift.
- **Reaction:**
  - ExpansionDrive cao → tăng internal accumulation.
  - StabilityBias cao → củng cố structure.
  - Aggression cao → frustration tăng dần.
- **Insight:** Stable lâu → sinh bất mãn với actor hiếu chiến.

### 6.2 OS (Oscillatory)

- **Logic:** Dao động, không drift.
- **Reaction:**
  - Adaptability cao → khai thác cycle.
  - RiskTolerance thấp → giảm exposure.
  - Aggression cao → timing attack khi peak.
- **Insight:** Oscillation tạo cơ hội chiến thuật.

### 6.3 CH (Chaotic-like)

- **Logic:** High volatility, no direction.
- **Reaction:**
  - Adaptability cao → gain advantage.
  - StabilityBias cao → retreat.
  - RiskTolerance cao → random opportunistic move.
- **Insight:** Chaos là môi trường lọc actor yếu.

### 6.4 DV (Divergent)

- **Logic:** Strong drift, acceleration > 0.
- **Reaction:**
  - **Direction = UP:** ExpansionDrive cao → amplify expansion; RiskTolerance thấp → late entry penalty.
  - **Direction = DOWN:** StabilityBias cao → defensive consolidation; Aggression cao → all-in conflict.
- **Insight:** Divergence là phase chiến tranh / bùng nổ văn minh.

---

## 7. Reaction Function (công thức tổng quát)

```
ReactionScore = w1·PrimaryImpact + w2·IntensityNorm + w3·DirectionBias
```

- **PrimaryImpact** (map Primary → scalar):
  - ST = 0.2, OS = 0.5, CH = 0.8, DV = 1.0
- **IntensityNorm:** Intensity 0–3 → normalize [0, 1]
- **DirectionBias:** UP = +1, DOWN = -1, FLAT = 0

ActorProfile quyết định trọng số w1, w2, w3 khác nhau cho từng archetype.

---

## 8. Actor State Machine

RID là **trigger**; actor có state machine nội bộ:

| State        | Ý nghĩa ngắn           |
|-------------|-------------------------|
| Dormant     | Không hoạt động        |
| Accumulating| Tích lũy nội bộ        |
| Aggressive  | Chế độ xung đột        |
| Defensive   | Phòng thủ / thu hẹp    |
| Expansion   | Mở rộng                |
| Collapse    | Sụp đổ / rút lui       |

Ví dụ: `DV-3-UP` và `ExpansionDrive > 0.7` ⇒ `state = Expansion`.

---

## 9. Counter-Force (Actor chống lại RID)

Actor có thể **chống lại** xu hướng RID ⇒ tạo emergent war và phase shift thật.

### 9.1 Bản chất

- RID mô tả vector xu hướng thế giới: R = (Primary, Intensity, Direction).
- Actor tạo **Counter Vector** C_i. Dynamics:
  - X_{t+1} = F(X_t) + Σ C_i
- Không có C_i → world trôi theo F. Có C_i → actor bẻ cong phase boundary.

### 9.2 Thuộc tính bổ sung (counter)

| Thuộc tính     | Range   | Ý nghĩa                          |
|----------------|---------|----------------------------------|
| InfluencePower | [0..1]  | Cường độ tác động lên hệ        |
| CounterWill    | [0..1]  | Xác suất / ý chí chống RID      |
| AlignmentBias  | [-1..1] | +1 thuận RID, -1 chống RID      |

### 9.3 Điều kiện chống RID

- AlignmentBias < 0 **và** |DirectionBias| cao **và** Intensity ≥ threshold ⇒ actor có thể phát counter-force.

Ví dụ: DV-3-UP, actor StabilityBias cao ⇒ chống expansion.

### 9.4 Công thức Counter Vector

```
C_i = -α_i · R_d
```

- R_d = DirectionBias (-1, 0, +1).
- α_i = InfluencePower × CounterWill × (1 − AlignmentBias) (khi chống, AlignmentBias âm nên (1 − AlignmentBias) > 1).

Nhiều actor chống cùng hướng ⇒ hệ có thể đảo phase.

### 9.5 Phase Tug-of-War

- Net = Drift + Σ C_i
- Net > 0 → vẫn UP; Net ≈ 0 → oscillatory; Net < 0 → đảo chiều.
- **War** = trạng thái |Drift| ≈ |Σ C_i| (lực hệ và lực actor cân bằng).

### 9.6 Giới hạn actor

Actor chỉ bẻ được phase nếu:

```
Σ InfluencePower_i > DriftMagnitude
```

Nếu không thỏa ⇒ actor chỉ chết theo xu thế. Phase boundary thật khi:

```
DriftMagnitude = TotalCounterForce
```

---

## 10. Ổn định hệ: chuẩn hóa lực, Action vector, Saturation

**Mục tiêu:** Actor đủ mạnh để tạo war thật, nhưng không phá dynamics nền.

### 10.1 Dynamics khi có Actor

- **Gốc:** X_{t+1} = F(X_t)
- **Có actor:** X_{t+1} = F(X_t) + Σ_i C_i, với C_i = β_i · u_i (u_i = action vector, β_i = InfluencePower × CounterWill × AlignmentFactor).

### 10.2 Chuẩn hóa lực (bắt buộc)

```
Σ_i ||C_i|| ≤ γ · ||F(X_t)||
```

- **γ khuyến nghị:** 0.1–0.3. Actor có thể bẻ cong xu hướng nhưng không lấn át luật nền F(X).

### 10.3 Action vector u_i (chỉ 4 chiều)

Actor **không** được chỉnh toàn bộ state X. Chỉ tác động vào 4 chiều:

| Thành phần   | Ý nghĩa ngắn     |
|--------------|-------------------|
| Energy       | Đẩy/giảm năng lượng hệ |
| Cohesion     | Tăng/giảm gắn kết |
| ConflictIndex| Tăng/giảm xung đột |
| Innovation   | Tăng/giảm đổi mới |

Ví dụ: u_i = [+1, 0, 0, 0] (push energy); u_i = [0, -1, +1, 0] (giảm cohesion, tăng conflict). Các chiều khác của X không bị actor sửa trực tiếp.

### 10.4 Công thức Counter Force thực tế

```
C_i = β_i · u_i · IntensityFactor
```

- **IntensityFactor** = RID.intensity / 3 (0–1). Intensity thấp ⇒ actor khó bẻ hệ; intensity cao ⇒ war dễ bùng.

### 10.5 Emergent War (điều kiện động lực)

War sinh ra khi hai nhóm actor đối nghịch gần cân bằng:

```
Σ_i C_i+ ≈ Σ_j C_j-
```

(Σ C_i+ = tổng lực phe “đẩy lên”, Σ C_j- = tổng lực phe “kéo xuống”). Khi đó: Volatility tăng, SFR tăng, DP giảm ⇒ hệ chuyển sang **CH**. Đây là chiến tranh ở mức dynamics, không cần script.

### 10.6 Saturation (tránh explode)

Bắt buộc một trong hai (hoặc cả hai):

- **Theo lực:** C'_i = tanh(C_i) trước khi cộng vào state.
- **Theo state:** X_{t+1} = clamp(X_{t+1}, X_min, X_max).

Nếu không có saturation ⇒ sau vài tick hệ có thể diverge vô hạn.

---

## 11. Influence amplification theo Regime

**InfluencePower** không cố định — phụ thuộc RID để “thời thế tạo anh hùng” nhưng vẫn bounded.

### 11.1 Effective power

```
P_i_eff = P_i · A(R)
```

- P_i = intrinsic power (từ profile).
- A(R) = amplification factor theo RegimeState.

### 11.2 Amplification function (bounded)

```
A(R) = 1 + k · S_i · Φ(R)
```

- k = global cap (0.5–2).
- S_i = actor sensitivity [0..1].
- Φ(R) = regime intensity normalized [0..1].

### 11.3 Φ(R) theo Primary + Intensity

| Primary | Φ base |
|---------|--------|
| ST      | 0.1    |
| OS      | 0.4    |
| CH      | 0.7    |
| DV      | 1.0    |

**Φ(R)** = PrimaryWeight × (Intensity / 3). Ví dụ: DV-3 → Φ = 1; DV-1 → Φ ≈ 0.33; ST-0 → Φ ≈ 0.

### 11.4 Alignment amplification

- **Thuận chiều RID:** AlignmentFactor = 1 + |DirectionBias| (được khuếch đại).
- **Chống chiều:** AlignmentFactor = 1 − |DirectionBias|.

→ “Thời thế tạo anh hùng”: actor thuận chiều lịch sử được amplify mạnh hơn.

### 11.5 Cap trần (tránh runaway)

- **Cách 1:** P_i_eff ≤ P_max (hard cap).
- **Cách 2:** P_i_eff = P_i · tanh(A(R)) (smooth bound).

Positive feedback (DV → amplify expansion → DV mạnh hơn) phải bị chặn.

### 11.6 Phase amplification map (ý nghĩa)

| Regime | Hiệu ứng power      |
|--------|---------------------|
| ST     | Power dampened      |
| OS     | Tactical boost      |
| CH     | Survival boost      |
| DV     | Dominance boost     |

ST: hero khó nổi; OS: strategist nổi; CH: adaptive nổi; DV: conqueror nổi.

---

## 12. Awakening (threshold-based)

**Hero emergence** = phase-triggered bifurcation cá nhân, không tăng tuyến tính mãi.

### 12.1 Công thức

```
P_i_eff = P_i           nếu Φ(R) < θ_i
P_i_eff = P_i · κ_i     nếu Φ(R) ≥ θ_i
```

- θ_i = AwakeningThreshold riêng actor [0..1].
- κ_i = AwakeningMultiplier (2–5x); **bắt buộc κ_i ≤ 3** (tránh 1 hero phá toàn bộ multiverse).

### 12.2 Hysteresis (tránh flip liên tục)

- **Awaken khi:** Φ(R) > θ_i.
- **Sleep lại khi:** Φ(R) < θ_i − δ (δ nhỏ, 0.05–0.1).

### 12.3 Mythic moment

Tập thể bẻ cong lịch sử khi:

```
Σ (awakened) P_i_eff > DriftMagnitude
```

### 12.4 Actor profile bổ sung

| Thuộc tính          | Range   | Ý nghĩa                |
|---------------------|---------|------------------------|
| AwakeningThreshold  | [0..1]  | Ngưỡng Φ(R) để awaken |
| AwakeningMultiplier | [1..5]  | κ_i (cap ≤ 3 trong engine) |

Phân phối theo archetype hoặc random; không cho mọi actor giống nhau.

---

## 13. Awakening + Internal State (path-dependent)

Awakening phụ thuộc **cả Regime và trạng thái nội bộ** ⇒ nhân vật có lịch sử, không chỉ cảm biến thời thế.

### 13.1 Điều kiện awaken đầy đủ

```
Φ(R) · Ψ_i(t) > θ_i
```

- Φ(R) = regime pressure (như trên).
- Ψ_i(t) = internal amplification factor [1..2] (cap để không phá hệ).
- θ_i = AwakeningThreshold.

### 13.2 Internal state Ψ_i

```
Ψ_i = 1 + a·MemoryLoad + b·TraumaLevel + c·BeliefIntensity
```

- **MemoryLoad** [0..1]: tích tụ trải nghiệm phase trước (ST lâu → expansion memory; CH lâu → survival memory). Tích lũy chậm.
- **TraumaLevel** [0..1]: tăng khi thất bại, bị counter mạnh, sống trong CH lâu. Trauma cao ⇒ dễ awaken khi DV.
- **BeliefIntensity** [0..1]: ideology mạnh (stability cult, expansion doctrine, chaos embrace) ⇒ regime alignment amplify.

**Giới hạn:** Ψ_i ≤ 2.

### 13.3 Decay (bắt buộc)

Nếu Memory/Trauma không decay ⇒ Ψ tăng mãi ⇒ ai cũng awaken về sau.

- **Memory:** Memory_{t+1} = λ·Memory_t + Δ (λ < 1).
- **Trauma:** tương tự decay theo thời gian hoặc khi phase ổn định.

### 13.4 Emergent war thật

War phase xảy ra khi: nhiều actor có Trauma cao + DV intensity tăng + hai nhóm ideology trái ngược **cùng** awaken. Đây là chiến tranh lịch sử, không phải noise.

### 13.5 Phase boundary mới

Hệ trở thành **path-dependent adaptive system**. Phase boundary phụ thuộc:

```
||F(X)||  vs  Σ P_i · Ψ_i
```

Lịch sử (Memory, Trauma, Belief) thay đổi tương lai.

---

## 14. Actor là hệ động lực con (không còn công tắc bật/tắt)

Actor là **hệ động lực con** nằm trong hệ lớn: action phụ thuộc Regime + InternalState, không cố định theo RID.

### 14.1 Action là hàm của R và S_i

- **Trước:** C_i = P_i_eff · u_i với u_i cố định theo RID.
- **Giờ:** u_i = f(R, S_i). R = RegimeState, S_i = InternalState. Actor action biến đổi theo cả regime và nội tâm.

### 14.2 Decision vector function

```
u_i = B_i + Δ_i(R) + Γ_i(S_i)
```

- **B_i** = base disposition (personality core, hằng số theo actor).
- **Δ_i(R)** = phản ứng theo regime (reaction matrix đã có).
- **Γ_i(S_i)** = biến dạng theo nội tâm (Memory, Trauma, Belief).

### 14.3 Internal influence Γ_i — map sang action bias

| Nội tâm | Ảnh hưởng lên action |
|---------|----------------------|
| **Trauma ↑** | ConflictIndex++, Cohesion−−; dễ chọn hành động tấn công. |
| **Memory** | Định hướng chiến lược dài hạn: memory ST lâu → Expansion bias; memory CH lâu → Survival bias. |
| **Belief** | Stability belief → Cohesion++; Expansion belief → Energy++; Chaos belief → Conflict++. |

### 14.4 Công thức 4 chiều (đủ dùng, luôn normalize)

State vector 4 chiều: X = [Energy, Cohesion, Conflict, Innovation]. u_i = [e_i, c_i, f_i, n_i]:

- **e_i** = base_E + α₁·Belief + α₂·Memory  (Energy)
- **c_i** = base_C − β₂·Trauma              (Cohesion: trauma giảm cohesion)
- **f_i** = base_F + β₁·Trauma              (Conflict: trauma tăng conflict)
- **n_i** = base_N + (terms từ Belief/Memory) (Innovation)

**Ràng buộc:** ||u_i|| ≤ 1; luôn normalize trước khi đưa vào C_i.

### 14.5 Emergent personality

Hai actor cùng intrinsic power và threshold nhưng khác Trauma/Memory ⇒ u_i khác ⇒ world response khác ⇒ lịch sử phân nhánh. Đây là **emergent individuality** thật.

### 14.6 Ổn định — decay bắt buộc

InternalState phải decay, nếu không hệ saturate:

- **Trauma:** Trauma_{t+1} = λ·T_t + shock (λ < 1).
- **Memory:** Memory_{t+1} = μ·M_t + experience (μ < 1).

### 14.7 Vòng lặp 2 tầng

```
World → Regime → tác động InternalState → biến dạng Decision → tác động World
```

### 14.8 Phase boundary

Phase shift phụ thuộc **tâm lý tập thể**:

```
||F(X)||  vs  Σ P_i_eff · f(S_i)
```

f(S_i) = hàm decision (u_i) phụ thuộc S_i. Đây là mô hình lịch sử thật, không chỉ drift cơ học.

### 14.9 Domain: DecisionEngine

- **DecisionEngine:** `decide(Actor $actor, RegimeState $regime): ActorAction` — tính u_i = B_i + Δ_i(R) + Γ_i(S_i); không cho Actor mutate World trực tiếp.
- **ActorDisposition (ValueObject):** B_i (base vector 4 chiều).
- Actor giữ **ActorInternalState**, **ActorDisposition**; DecisionEngine dùng chúng để output ActorAction.

---

## 15. InternalState contagion (culture-level dynamics)

Khi InternalState **lan truyền** giữa actor qua mạng, hệ trở thành **collective psychology feedback system**. Phải kiểm soát chặt (W nhỏ, decay) để tránh đồng bộ hóa quá nhanh.

### 15.1 Cấu trúc contagion

- S_i = (Memory_i, Trauma_i, Belief_i). **W_ij** = mức ảnh hưởng từ actor j → i (đồ thị xã hội).
- **Cập nhật:**

```
S_i^{t+1} = λ·S_i^t + Σ_j W_ij·(S_j^t − S_i^t) + ExternalShock
```

- **λ < 1** (decay). **W_ij nhỏ:** max(W_ij) ∈ [0.01, 0.1]. Đây là consensus diffusion; W quá lớn ⇒ mọi S_i giống nhau ⇒ mất diversity ⇒ hệ chết.

### 15.2 Ý nghĩa từng loại lan truyền

| Loại | Cơ chế ngắn |
|------|-------------|
| **Trauma diffusion** | War / conflict zone → Trauma tăng; lan qua network. Trauma lan nhanh ⇒ xã hội cực đoan hóa. |
| **Belief contagion** | Lan theo InfluencePower và trạng thái Awakened. Hero awaken ⇒ belief spread nhanh ⇒ ideology hình thành. |
| **Memory contagion** | Lan chậm hơn; tạo “collective memory”. Memory tập thể cao ⇒ awakening tương lai dễ hơn. |

### 15.3 Culture (không thêm biến mới)

**Culture(t)** = trung bình Belief của population:

```
Culture(t) = (1/N) Σ_i Belief_i
```

Khi Culture vượt threshold ⇒ phase shift dễ xảy ra. Culture không phải entity riêng mà **emerged** từ Belief distribution.

### 15.4 Điều kiện war mới (civil war)

War không còn chỉ do drift. War xảy ra khi:

- **BeliefVariance** cao (hai cluster belief đối nghịch).
- **Trauma** trung bình cao.
- **Φ(R)** cao (regime intensity).

⇒ Civil war thật, nội sinh.

### 15.5 Feedback loop 3 tầng

```
World → Regime → Actor awaken → tăng Influence
  → spread Belief (contagion)
  → thay đổi Decision (u_i)
  → thay đổi World
```

### 15.6 Phase boundary — global

Phase boundary không còn local. S_i phụ thuộc **network** (W_ij). **||F(X)|| vs Σ P_i_eff · f(S_i)** với S_i được cập nhật bởi diffusion ⇒ phase boundary là **global property** của collective psychology.

### 15.7 Domain (Laravel 12)

- **SocialGraph (Aggregate):** đồ thị W_ij (actor → actor influence).
- **BeliefDiffusionEngine (Domain Service):** cập nhật Belief_i theo W_ij và Awakened state.
- **TraumaPropagationEngine:** `diffuse(ActorCollection $actors, SocialGraph $graph): void` — cập nhật Trauma theo network và conflict events. Không embed trong Actor.

### 15.8 Cảnh báo — ideological lock

Nếu **decay thấp + W cao + amplification cao** ⇒ hệ rơi vào **permanent ideological lock**: mọi actor đồng nhất, không còn dao động, không còn war, hệ “chết”. Bắt buộc: **max(W_ij) ≪ 1**, λ và μ < 1, và có thể cap tổng influence per actor.

---

## 16. Kiến trúc DDD (Laravel 12)

- **Domain Entity:** WorldState, Actor.
- **ValueObject:** RegimeState (RID + CID), ActorProfile, ActorAction (u_i 4 chiều), ActorInternalState (MemoryLoad, TraumaLevel, BeliefIntensity), ActorDisposition (B_i).
- **Aggregate:** SocialGraph (W_ij) khi dùng contagion.
- **Domain Service:**
  - **WorldEvolutionEngine:** `evolve(WorldState $world, ActorCollection $actors): WorldState` — apply F(X), aggregate counter-forces (chuẩn hóa + saturation), trả về state mới; không để Actor mutate World trực tiếp.
  - **DecisionEngine:** `decide(Actor $actor, RegimeState $regime): ActorAction` — u_i = B_i + Δ_i(R) + Γ_i(S_i); ||u_i|| ≤ 1.
  - **ActorReactionEngine:** `react(Actor $actor, RegimeState $regime): void` — state machine / intent.
  - **WorldFeedbackEngine / CounterForce:** `aggregate(ActorCollection $actors, RegimeState $regime): Delta` — tổng hợp C_i với γ-cap, IntensityFactor, saturation.
  - **InfluenceAmplifier:** `amplify(Actor $actor, RegimeState $regime): float` — P_i_eff = P_i · A(R).
  - **AwakeningEngine:** `evaluate(Actor $actor, RegimeState $regime): float` — P_i_eff với awakening (κ_i, θ_i).
  - **AwakeningEvaluator:** `shouldAwaken(Actor $actor, RegimeState $regime): bool` — Φ(R)·Ψ_i > θ_i và hysteresis.
  - **BeliefDiffusionEngine:** cập nhật Belief_i theo SocialGraph và Awakened state.
  - **TraumaPropagationEngine:** `diffuse(ActorCollection $actors, SocialGraph $graph): void` — Trauma lan truyền theo network.

**Nguyên tắc:** Actor không mutate World trực tiếp; mọi lực đi qua engine; InternalState có decay; contagion dùng W nhỏ.

---

## 17. Luồng tổng thể

```
World dynamics → Observer → RegimeState (RID::CID)
    → [Optional: Contagion S_i^{t+1} = λS_i + Σ W_ij(S_j − S_i) + shock]
    → Actor (DecisionEngine: u_i = B_i + Δ_i(R) + Γ_i(S_i); awakening; Ψ_i)
    → Counter forces (Σ C_i, γ-cap, saturation)
    → WorldEvolutionEngine.evolve
    → World shift
```

Observer chỉ đo; Actor là hệ động lực con (action = f(R, S_i)). Có contagion ⇒ collective psychology; phase boundary phụ thuộc ||F(X)|| vs Σ P_i_eff · f(S_i) với S_i global (network). War thật khi lực đối nghịch cân bằng hoặc BeliefVariance cao + Trauma + Φ(R) cao.

---

## Phụ lục — Ký hiệu nhanh

| Ký hiệu    | Ý nghĩa |
|------------|---------|
| RID        | Regime ID: Primary-Intensity-Direction |
| CID        | Confidence ID: C0–C3 |
| RegimeState| ValueObject chứa RID + CID (và optional metrics) |
| R_d        | DirectionBias (UP/DOWN/FLAT → +1/-1/0) |
| u_i        | Action vector 4 chiều (Energy, Cohesion, ConflictIndex, Innovation) |
| C_i        | Counter vector: β_i · u_i · IntensityFactor |
| β_i        | InfluencePower × CounterWill × AlignmentFactor (hoặc P_i_eff) |
| γ          | Cap tổng lực: Σ\|C_i\| ≤ γ·\|F(X_t)\| (0.1–0.3) |
| IntensityFactor | RID.intensity / 3 |
| Φ(R)       | Regime intensity [0..1] theo Primary + Intensity |
| A(R)       | Amplification: 1 + k·S_i·Φ(R) |
| P_i_eff    | Effective power (có thể qua awakening) |
| θ_i, κ_i   | AwakeningThreshold, AwakeningMultiplier (κ_i ≤ 3) |
| Ψ_i        | Internal state: 1 + a·Memory + b·Trauma + c·Belief, ≤ 2 |
| Net        | Drift + Σ C_i |
| B_i        | Base disposition (personality core 4 chiều) |
| Δ_i(R)     | Phản ứng theo RegimeState (reaction matrix) |
| Γ_i(S_i)   | Biến dạng theo InternalState (Memory, Trauma, Belief → action bias) |
| u_i        | = B_i + Δ_i(R) + Γ_i(S_i); ||u_i|| ≤ 1 |
| W_ij       | Influence từ actor j → i (contagion); max(W_ij) ≪ 1 (0.01–0.1) |
| Culture(t) | (1/N) Σ_i Belief_i; emerged, không entity riêng |
| BeliefVariance | Cao ⇒ hai cluster đối nghịch; cùng Trauma + Φ(R) cao ⇒ civil war |

*Tài liệu tham chiếu engine; chi tiết implementation (enum, value object, service) có thể bổ sung trong codebase.*
