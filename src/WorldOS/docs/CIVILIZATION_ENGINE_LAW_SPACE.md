# Civilization Engine & Law Space — Thiết kế toán học và vật lý

Tài liệu tham chiếu thiết kế: **Law Space 17D**, hệ cascade văn minh, động lực học phi tuyến, và engine sinh lịch sử. Giữ nguyên **công thức và định luật toán học / vật lý** từ nguồn thiết kế.

**Phiên bản:** 0.1.0  
**Tham chiếu:** WorldOS v0.1.0 — Tài liệu Backend chính thức

---

# Phần I — Taxonomy và Law Space

## 1. Phân tầng chuẩn (vật lý)

| Layer | Thuật ngữ | Định nghĩa |
|-------|-----------|------------|
| 0 | Mathematical Substrate | Ω = Space of all consistent mathematical structures (conceptual upper bound) |
| 1 | Multiverse 𝓜 | 𝓜 = { U_i \| each U_i solves a distinct law-set L_i } |
| 2 | Universe U | U = (M, g, Φ, L, I, B): spacetime manifold M, metric g, field Φ, law-set L, initial I, boundary B |
| 3 | Cosmological Structure | Galaxy, star system, planet |
| 4 | Biosphere | Bio(U) ⊂ U |
| 5 | Civilization | Civ ⊂ Bio(U) |

**Định nghĩa World (cho WorldOS):**

- **World W** = Law-Space Generator: W = (Θ, G) với Θ = parameter space, G = rule generator.
- **Universe U** = instance: U = G(θ, seed). World không phải reality; World = generative domain.
- **Ký hiệu:** World = Generative Structure; Universe = Solved Physical Instance. Không được hoán đổi.

## 2. Law Space 17D — Định nghĩa

**Không gian luật (chuẩn hóa):**

$$
\mathcal{L} \subset \mathbb{R}^{17}, \qquad \theta = (\theta_1, \ldots, \theta_{17}), \qquad \theta_i \in [0,1]
$$

**Phân nhóm 17 chiều:**

**A. Fundamental Physics (θ₁–θ₅)**  
| Ký hiệu | Ý nghĩa | Ghi chú |
|---------|---------|--------|
| θ₁ | Dimensionality | D = 2 + round(θ₁×4) → {2..6} |
| θ₂ | Causality Rigidity | 0 = chaotic, 1 = deterministic |
| θ₃ | Energy Stability | 0 = vacuum unstable |
| θ₄ | Interaction Strength | scale multiplier |
| θ₅ | Entropy Growth | dS/dt |

**B. Structure (θ₆–θ₉)**  
θ₆ Matter Complexity Threshold, θ₇ Self-Organization Bias, θ₈ Stability Basin Depth, θ₉ Collapse Probability.

**C. Biological (θ₁₀–θ₁₃)**  
θ₁₀ Abiogenesis, θ₁₁ Mutation Volatility, θ₁₂ Adaptation Efficiency, θ₁₃ **Cognitive Ceiling** (trần sức mạnh).

**D. Cultural (θ₁₄–θ₁₇)**  
θ₁₄ Myth Formation, θ₁₅ Memory Persistence, θ₁₆ Technological Accumulation Rate, θ₁₇ Meta-System Awareness.

**Instance Universe:**

$$
U = G(\theta, \mathrm{seed})
$$

**Evolution operator:**

$$
U(t+1) = T(U(t), \theta)
$$

với T là state transition operator.

## 3. Feasibility Function F(θ)

Universe chỉ tồn tại nếu F(θ) = 1.

**Structural feasibility:**

$$
S_1 = \theta_3 \cdot \theta_6 \cdot \theta_8
$$
Nếu \( S_1 < 0.2 \Rightarrow \) reject.

**Entropy constraint:**

$$
\theta_5 < \theta_8 + 0.3
$$
(Entropy tăng quá nhanh so với stability basin → collapse.)

**Biological feasibility:**

$$
\theta_{10} \cdot \theta_{12} > 0.15
$$

**Cognitive constraint:**

$$
\theta_{13} \leq f(\theta_3, \theta_4), \quad \text{vd: } \theta_{13} \leq 0.8\,\theta_3 + 0.2\,\theta_4
$$

**Kết luận:** F(θ) = 1 nếu và chỉ nếu tất cả điều kiện trên thỏa.

## 4. State vector và Evolution (dạng đơn giản)

**State:**

$$
U(t) = (\mathrm{Energy}, \mathrm{Structure}, \mathrm{Life}, \mathrm{Civilization})
$$

**Energy dynamics:**

$$
E(t+1) = E(t) - \theta_5 \cdot E(t)
$$

**Structure:**

$$
\mathrm{Structure}(t+1) = \mathrm{Structure}(t) + \theta_7(1 - \mathrm{Structure}(t)) - \theta_9 \cdot \mathrm{CollapseFactor}
$$

**Life:**

$$
\mathrm{Life}(t+1) = \mathrm{Life}(t) + \theta_{10}\mathrm{Structure}(t) - \theta_{11}\cdot\mathrm{randomness}
$$

**Civilization:**

$$
\mathrm{Civ}(t+1) = \mathrm{Civ}(t) + \theta_{12}\mathrm{Life}(t) + \theta_{16}\cdot\mathrm{TechMultiplier}
$$

## 5. Stability metric σ(U)

$$
\sigma(U) = w_1\,\mathrm{avg}(\mathrm{Structure}) + w_2\,\mathrm{avg}(\mathrm{Life}) + w_3\,\mathrm{avg}(\mathrm{Civ}) - w_4\,\mathrm{entropy}
$$

Chuẩn hóa \( \sigma \in [0,1] \):

- \( \sigma < 0.1 \Rightarrow \) collapse  
- \( \sigma > 0.7 \Rightarrow \) stable high-order universe  

**Meta-level constraint:** Universe chỉ được evolve nếu σ(U(t)) không giảm liên tục 10 bước (nếu giảm liên tục ⇒ deterministic collapse).

## 6. Power Ceiling (trần sức mạnh)

$$
\mathrm{Power}_{\max} = \theta_{13} \cdot (1 - \theta_5) \cdot \theta_3
$$

Giải thích: Cognitive ceiling cao, entropy thấp, energy ổn định ⇒ civilization có thể vượt ngưỡng.

---

# Phần II — Law Space hướng Civilizational (cascade)

## 7. Law Space 17D — Phiên bản cascade (Physics → Culture)

**Năm tầng tiến hóa bắt buộc:**

Physics → Chemistry → Biology → Cognition → Culture.

**State vector:**

$$
X = [P, C, B, N, K]^T
$$

với P = Physical order index, C = Chemical complexity, B = Biological density, N = Neural complexity, K = Cultural accumulation; tất cả chuẩn hóa [0,1].

**Hệ cascade (điều kiện kích hoạt):**

- **Physics → Chemistry:** C_active nếu P > τ₁  
  $$ \frac{dC}{dt} = \theta_5 \cdot P \cdot (1 - C); \quad \text{nếu } \theta_3 \text{ cao: } C \mathrel{-}= \theta_3 \cdot \mathrm{decay\_factor} $$

- **Chemistry → Biology:** B_active nếu C > τ₂  
  $$ \frac{dB}{dt} = \theta_8 \cdot C \cdot (1 - B) - \mathrm{instability}(\theta_9) $$  
  Mutation window: θ₉ < 0.2 ⇒ stagnation; θ₉ > 0.8 ⇒ chaos extinction.

- **Biology → Cognition:** N_active nếu B > τ₃  
  $$ \frac{dN}{dt} = \theta_{12} \cdot B \cdot \mathrm{social\_factor}(\theta_{13}) $$

- **Cognition → Culture:** K_active nếu N > τ₄  
  $$ \frac{dK}{dt} = \theta_{15}\theta_{16} N + \theta_{17}\cdot\mathrm{feedback}(K) $$  
  Nếu θ₁₄ (memory) thấp ⇒ K_decay cao.

**Tổng hợp:**

$$
\frac{dP}{dt} = -\theta_3 \cdot \mathrm{entropy\_drain}
$$
$$
\frac{dC}{dt} = f(P, \theta_5, \theta_6), \quad \frac{dB}{dt} = f(C, \theta_8, \theta_9, \theta_{10}), \quad \frac{dN}{dt} = f(B, \theta_{12}, \theta_{13}), \quad \frac{dK}{dt} = f(N, \theta_{15}, \theta_{16}, \theta_{17})
$$

Quan trọng: nếu tầng dưới sụp ⇒ tầng trên tự suy giảm.

## 8. Jacobian và ổn định (cascade một chiều)

**Tuyến tính hóa quanh equilibrium X\***:

$$
\frac{d(\delta X)}{dt} \approx J \cdot \delta X, \qquad J = \frac{\partial F}{\partial X} \quad (5\times5)
$$

**Cấu trúc Jacobian (cascade → gần tam giác dưới):**

$$
J \sim \begin{pmatrix} a & 0 & 0 & 0 & 0 \\ b & c & 0 & 0 & 0 \\ 0 & d & e & 0 & 0 \\ 0 & 0 & f & g & 0 \\ 0 & 0 & 0 & h & i \end{pmatrix}
$$

Eigenvalues λ = {a, c, e, g, i}. **Hệ ổn định khi Re(λᵢ) < 0 với mọi i.**

- Physics ổn định ⇔ a < 0.  
- Chemistry ⇔ c < 0.  
- Biology ⇔ e < 0.  
- Cognition ⇔ g < 0.  
- Culture ⇔ i < 0.  

**Bifurcation:** Nếu tại θ nào đó λ = 0 ⇒ hệ đổi pha (vd. e → dương ⇒ bùng nổ sinh học; i > 0 ⇒ runaway civilization).

**Stability theo θ:**

$$
\sigma(\theta) = \max_i \mathrm{Re}(\lambda_i)
$$

- σ < 0 ⇒ stable; σ ≈ 0 ⇒ meta-stable; σ > 0 ⇒ divergence / collapse / explosion.

## 9. Feedback ngược (K → P, B, N) — Dark Age & Renaissance

**Hệ có feedback:**

$$
\frac{dP}{dt} = -\alpha P + \phi_1(K), \quad \frac{dC}{dt} = f(P) - \mathrm{decay}, \quad \frac{dB}{dt} = f(C) - \mathrm{extinction} + \phi_2(K)
$$
$$
\frac{dN}{dt} = f(B) + \phi_3(K), \quad \frac{dK}{dt} = \mathrm{growth}(N) - \mathrm{overload}(K)
$$

với φ₁ = environmental backreaction, φ₂ = biotech effect, φ₃ = knowledge acceleration.

**Dark Age:** overload(K) = β K² ⇒ khi K lớn, \( \frac{dK}{dt} = \theta_{15}\theta_{16} N - \beta K^2 \) bị chi phối bởi −βK² ⇒ K giảm mạnh.

**Renaissance:** Sau khi K giảm, overload yếu; nếu N, B chưa collapse ⇒ K có thể tăng lại (limit cycle).

**Self-destruction:** Thêm \( \frac{dP}{dt} \mathrel{-}= \gamma K \). Nếu γK > αP ⇒ P sụp ⇒ cascade ngược ⇒ total collapse.

**Oscillation (dao động):** Tại equilibrium, nếu trace(J) < 0, det(J) > 0, discriminant < 0 ⇒ eigenvalues phức ⇒ dao động (Dark Age ↔ Renaissance).

## 10. TSDE (Technological Self-Destruction Event)

**State mở rộng:**

$$
X = [P, C, B, N, K, R, D]
$$

R = Risk Accumulation, D = Damage Index.

**Risk:**

$$
\frac{dR}{dt} = a_1 K + a_2 \theta_{16} - a_3 \theta_{17}
$$

**Damage trigger:** Nếu R > R_c ⇒ damage event.  
$$ \frac{dD}{dt} = \kappa (R - R_c) $$

**Damage feedback:**

$$
\frac{dP}{dt} \mathrel{-}= \mu_1 D, \quad \frac{dB}{dt} \mathrel{-}= \mu_2 D, \quad \frac{dK}{dt} \mathrel{-}= \mu_3 D
$$

**Điều kiện tránh TSDE (ổn định dài hạn):**

$$
\limsup_{t\to\infty} R(t) < R_c \quad \text{đủ nếu } \quad a_1 K^* + a_2 \theta_{16} - a_3 \theta_{17} \leq 0
$$

⇒ \( \theta_{17} > (a_1 K^* + a_2 \theta_{16})/a_3 \). Nghĩa là: **meta-awareness phải tăng nhanh hơn công nghệ.**

## 11. Stochastic shocks và AI

**Shock (Wiener):**

$$
dP = (\cdots)\,dt + \sigma\, dW_t
$$

**Điều kiện sống sót:** resilience > σ; hoặc Var(P) < P_min thì không extinction.

**Biến AI:** A = Artificial Intelligence density  
$$ \frac{dA}{dt} = \beta K - \mathrm{decay} $$  
Thêm: \( \frac{dR}{dt} \mathrel{+}= \gamma A \), \( \frac{d\theta_{17}}{dt} \mathrel{+}= \delta A \).  
- AI destabilize: γ > δ.  
- AI stabilize: δ > γ.  

**Post-biological:** Nếu A ≫ B thì extinction của B không làm collapse K (attractor mới).

## 12. Dynamic Law Manifold (DLM) và Meta-cycle

**Luật động:**

$$
\frac{d\theta}{dt} = G(X, \theta)
$$

**Self-stabilizing:** \( \frac{\partial G}{\partial R} < 0 \) (R tăng ⇒ luật thay đổi để giảm rủi ro).  
Ví dụ: \( \frac{d\theta_{16}}{dt} = -\alpha R \), \( \frac{d\theta_{17}}{dt} = +\beta R \).

**Hệ hai tầng (Coupled Meta-Dynamical System):**

- Tầng 1: \( \frac{dX}{dt} = F(X, \theta) \)  
- Tầng 2: \( \frac{d\theta}{dt} = G(X, \theta) \)  

**Meta-cycle (Civilization Selection):**  
Fitness(θ) = longevity_of_civilization. Sinh N universe từ 𝓛_civ, chạy evolution, đo Fitness, giữ top k, đột biến θ, lặp (Evolutionary Search in Law Space).

**Attractor Basin:** vùng trong không gian tham số mà mọi quỹ đạo gần đó hội tụ về cùng trạng thái ổn định.

**Phase Transition Surface (Cosmic Phase Boundary):**  
S = { θ : λ_max(θ) = 0 } là biên giữa civilization basin và extinction basin.

## 13. Monte Carlo và giảm chiều

**Monte Carlo 17D:** Với mỗi θ (random trong bounds), khởi tạo X₀, tích phân \( \frac{dX}{dt}, \frac{dR}{dt} \); nếu R > R_c ⇒ collapse; ghi longevity; tính λ_max tại equilibrium.

**Giảm chiều (PCA):** Sau Monte Carlo, dataset (θ_i → longevity_i) → Cov(θ) → PCA → θ ≈ α₁v₁ + α₂v₂ + … (3–5 vector đủ). Mục đích: Effective Law Subspace, visualization, tìm phase boundary.

## 14. Quantum vacuum instability

**Biến:** V = vacuum stability index  
$$ \frac{dV}{dt} = -\varepsilon + \mathrm{noise} $$  
Nếu V < V_c ⇒ Vacuum decay (P,C,B,K → 0; universe reset).  
Trong Monte Carlo: p_decay ≈ exp(−S/ℏ) (S = action barrier).

---

# Phần III — Mục tiêu đa dạng văn hóa và Meme / War

## 15. Cultural Diversity Functional và Historical Depth

**Mục tiêu:** Tối đa đa dạng văn hóa + lịch sử phong phú (không tối đa longevity).

**Cultural Diversity Functional:**

$$
\mathcal{D} = \int_0^T H(K(t))\,dt
$$

với H(·) = Shannon entropy của phân bố cultural states: \( H = -\sum_i p_i \log p_i \).

**Edge of Chaos:** λ_max ≈ 0⁻ (vùng tối ưu sát biên phase boundary). λ_max ≪ 0 ⇒ đóng băng; λ_max > 0 ⇒ diverge / self-destruction.

**Historical Depth:**

$$
\mathcal{H} = \text{number of distinct regime transitions}
$$

**Hàm mục tiêu tổng hợp:**

$$
\mathrm{Objective} = \alpha \mathcal{D} + \beta \mathcal{H}
$$

## 16. Meme Engine (Formal)

**Meme space:** 𝓜 ⊂ ℝ^d; mỗi meme m ∈ ℝ^d.  
**Phân bố:** ρ(m, t) = mật độ xác suất meme trong xã hội.  
**Entropy văn hóa:** \( H(t) = -\int \rho(m,t) \log \rho(m,t)\,dm \).

**Replicator–Mutation Equation:**

$$
\frac{\partial \rho}{\partial t} = \rho\,\big(f(m) - \langle f \rangle\big) + \mu \Delta\rho
$$

f(m) = fitness của meme, ⟨f⟩ = fitness trung bình, μ = θ₁₂ (mutation bandwidth), Δ = Laplacian.  
Diversity cao khi μ ≈ critical value (eigenvalue operator ≈ 0 ⇒ Edge of Chaos).

**Fitness:** f(m) = g(environment, power_structure, tech_level); ví dụ f(m) = aK + b·alignment_with_P + c·social_pressure.

## 17. War như High Energy Selection Event (HESE)

**Chia civilization thành n nhóm:** ρ_i(m, t).  
**Inter-group:** \( \frac{d\rho_i}{dt} \mathrel{+}= \sum_j W_{ij} \rho_i \rho_j \); W_ij > 0 hợp tác, W_ij < 0 xung đột. War khi ‖W‖ vượt threshold.

**War intensity:**

$$
\frac{dW}{dt} = \kappa\,\mathrm{variance}(K) - \mathrm{damping}
$$

War tác động lên meme: f(m) → f(m) + selection_pressure; war đủ lớn ⇒ attractor cũ phá vỡ ⇒ phase transition.  
Điều kiện tối ưu diversity: W_low < W < W_critical.

## 18. Kinh tế như trường năng lượng và Địa lý

**Economic energy density:** E(x, t)  
$$ \frac{\partial E}{\partial t} = \alpha K - \beta\,\mathrm{population} + D_E \Delta E $$  
(production − consumption + diffusion/trade).  
**Bất bình đẳng:** I(t) = Var(E(x,t)); I cao ⇒ tăng W.

**Meme diffusion không gian:**  
$$ \frac{\partial \rho}{\partial t} = \mathrm{replicator} + \mu\Delta_m \rho + D_{\mathrm{space}} \Delta_x \rho $$  
**Geographic barrier:** G(x); G lớn ⇒ diffusion giảm ⇒ Cultural Speciation.

## 19. Tôn giáo (low-frequency attractor)

**R_rel(x,t):**  
$$ \frac{\partial R_{\mathrm{rel}}}{\partial t} = \mathrm{slow\_growth} - \mathrm{decay} + \mathrm{shock\_coupling} $$  
Tôn giáo = mode tần số thấp trong spectrum meme; giảm war cục bộ, tăng stability, nhưng quá mạnh ⇒ giảm entropy.

## 20. Thương mại toàn cầu và Công nghệ thông tin

**Trade coupling g(t):**  
$$ \frac{\partial E}{\partial t} \mathrel{+}= g(t)(\bar{E} - E), \quad \frac{\partial \rho}{\partial t} \mathrel{+}= g(t)(\bar{\rho} - \rho) $$  
g lớn ⇒ đồng bộ ⇒ giảm diversity (Monoculture Risk).

**Metric compression (info):** d_eff = d_geo / (1 + η I); I = information level ⇒ diffusion tăng phi tuyến, speciation giảm, systemic risk tăng.

## 21. Cách mạng = Bifurcation

**Revolution:** attractor hiện tại mất stability ⇔ λ_max crosses 0 (bifurcation point).  
Loại: saddle-node (chế độ cũ biến mất), Hopf (dao động), pitchfork (chia hai nhánh).  
Điều kiện: inequality + war + info_flow > control_capacity ⇒ control_parameter < critical_value ⇒ eigenvalue dương.

## 22. Đo Entropy lịch sử (schema)

**Regime:** cluster state K(x,t) theo thời gian; mỗi cluster ổn định = 1 regime.  
**Historical entropy:** \( H_{\mathrm{history}} = -\sum_r p_r \log p_r \), p_r = thời lượng regime r / tổng thời gian.  
**Composite score:** Score = α∫ H_culture dt + β𝓗 − γ·collapse_events.

---

# Phần IV — Engine thực thi (Pressure, Crisis, Memory, Tech)

## 23. Composite Pressure Index (CPI) — Phi tuyến

**Thành phần chuẩn hóa [0,1]:** inequality I, legitimacy L, eliteCohesion E, warIntensity W.

**Không dùng tổng tuyến tính.** Dùng nonlinear coupling:

$$
\mathrm{base} = \mathrm{weighted\_sum}(I, 1-L, 1-E, W)
$$
$$
\mathrm{interaction} = I \cdot (1-L), \quad \mathrm{elite\_break} = (1-E)^2, \quad \mathrm{war\_amp} = W \cdot \mathrm{base}
$$
$$
P = \mathrm{clamp}\big(\mathrm{base} + \mathrm{interaction} + \mathrm{elite\_break} + \mathrm{war\_amp}\big)
$$

**Phase:** P < 0.4 stable; 0.4–0.65 tense; 0.65–0.8 unstable; > 0.8 critical regime (entropy tăng mạnh, crisis probability tăng theo mũ).

## 24. Instability phi tuyến và Adaptive Entropy

**Instability (aggregate world):**

$$
\mathrm{instability} = \mathrm{structuralPressure}\cdot 0.4 + (1-\mathrm{legitimacy})\cdot 0.3 + \mathrm{eliteFragmentation}\cdot 0.2 + \mathrm{warIntensity}\cdot 0.1
$$

**Nonlinear (threshold):** ví dụ  
\( \mathrm{instability\_from\_inequality} = (\mathrm{inequality})^3 \) hoặc nếu inequality > 0.65: instability += (inequality − 0.65)·3.

**Adaptive entropy:**

$$
\mathrm{entropyLevel} = \mathrm{baseEntropy} + \mathrm{instability} \cdot \mathrm{entropyAmplifier}
$$

Ví dụ baseEntropy = 0.05, entropyAmplifier = 0.3; cap entropyLevel ≤ 0.35.

## 25. Crisis Trigger — Hybrid (xác suất + persistence)

**Persistence:** criticalTicks: nếu P > 0.8 ⇒ criticalTicks++; else ⇒ criticalTicks decay (max(0, criticalTicks − 1)).  
**Probability:**

$$
\mathrm{baseProbability} = P^4, \quad \mathrm{persistenceBoost} = \min(\mathrm{criticalTicks}/50,\ 1), \quad \mathrm{crisisChance} = \mathrm{baseProbability} \cdot \mathrm{persistenceBoost}
$$

**Sau crisis:** structural shock + damping; cooldown (vd. 50 ticks) trước khi crisis gate mở lại.

## 26. Crisis Archetypes và Severity emergent

**Bốn archetype:** (1) Reform Crisis, (2) Revolution, (3) War Collapse, (4) Slow Decline.  
**Severity:** severity = f(P, criticalTicks, entropyLevel) ∈ [0,1].  
**Effect emergent:** redistribution = severity·(1 − eliteCohesion); legitimacyReset = severity·entropyLevel; eliteReshuffle = severity·instability (không hardcode outcome cố định).

**Collapse vs Reform (hybrid outcome):**  
collapseProbability = (1 − institutionStrength)·0.5 + (1 − eliteCohesion)·0.3 + warRecentImpact·0.2; random so sánh với collapseProbability ⇒ CollapseEvent hoặc ReformEvent.

## 27. Partial reset và Structural Memory

**Reset mạnh (ngắn hạn):** Legitimacy, Elite cohesion, War intensity.  
**Reset vừa (trung hạn):** Inequality, Resource distribution, Political structure — ví dụ inequality = inequality·(1 − severity·0.5).  
**Không reset (dài hạn):** Tech level, Cultural diversity base, Institutional learning — institutionCapacity += severity·learningFactor.  
**Entropy damping:** entropyLevel *= (1 − severity·0.6).

## 28. Tech dual-layer và Cultural mutation

**T_structural (không giảm):** T_structural += innovationRate·stabilityFactor.  
**T_operational (có thể giảm):** T_operational = T_structural · institutionStrength · (1 − collapseDamage); collapseDamage = severity·0.5 khi crisis.  
**Innovation diminishing return:** innovationRate *= 1/(1 + T_structural·0.15).  
**Tech growth:** techGrowth = baseInnovation · institutionCapacity · (1 − instability) · entropyModifier.

**Cultural mutation:**  
mutationRate = entropyLevel · T_operational · (1 − institutionStrength).  
Cultural fragmentation cao ⇒ Legitimacy↓, Elite cohesion↓, PressureIndex↑.

## 29. Strategic War (World-level)

**GeopoliticalState:** externalThreatLevel, relativePower, resourcePressure.  
**War intent:**  
warIntent = eliteConcentration·0.3 + structuralPressure·0.3 + resourcePressure·0.2 + relativePower·0.2; consider war nếu warIntent > 0.65.  
**Win probability:** winChance = relativePower·0.5 + institutionStrength·0.3 − structuralPressure·0.2.  
**Feedback:** thắng ⇒ legitimacy↑, eliteConcentration↑, resourcePressure↓, inequality↑; thua ⇒ legitimacy↓ mạnh, eliteCohesion↓, institutionStrength↓, structuralPressure↑.

## 30. Stop condition và Semi-deterministic

**Stop:** min(MaxTicks, MaxWallTime, StructuralDeath, NarrativeDeath).  
Structural: deltaEntropy < ε trong N tick liên tiếp. Narrative: không crisis/war/regime shift trong X tick ⇒ narratively dead.

**Random 2 lớp:** (1) Structural RNG (seed chính) cho economic cycle, institutional decay, power concentration, war intent baseline. (2) Micro entropy RNG: microSeed = hash(structuralSeed + currentTick + noiseIndex) cho outcome variance, crisis intensity, cultural branching. Tỉ lệ: structural 80%, micro 20%.

---

# Phần V — Chính trị nội sinh, Quân sự, Dân số, Đa vùng, Văn hóa

*(Bổ sung từ thiết kế engine sinh lịch sử / văn hóa; giữ dạng công thức và điều kiện.)*

## 31. Political State (World-level, emergent regime)

**State vector chính trị (không dùng enum regime):**

$$
\mathbf{S}_{\mathrm{pol}} = \big( \mathrm{eliteConcentration},\ \mathrm{eliteCohesion},\ \mathrm{institutionStrength},\ \mathrm{massParticipation},\ \mathrm{coercionLevel},\ \mathrm{legitimacy} \big)^T
$$

Tất cả chuẩn hóa [0,1]. Regime = projection của cấu trúc quyền lực (classifier chỉ dùng cho metrics/narrative).

**Inertia (smoothing):**

$$
x_{\mathrm{new}} = x_{\mathrm{old}} + \alpha\,\Delta, \quad \alpha \approx 0.15
$$

**Structural pressure (aggregate):**

$$
\mathrm{structuralPressure} = \mathrm{inequality}\cdot 0.5 + \mathrm{economicVolatility}\cdot 0.3 + (1 - \mathrm{legitimacy})\cdot 0.2
$$

Crisis gate mở khi structuralPressure > ngưỡng và legitimacy < ngưỡng.

## 32. Reform và Revolution

**Reform potential:**

$$
\mathrm{ReformPotential} = \mathrm{pressure} \cdot \mathrm{entropy} \cdot \mathrm{reformistAlignment} \cdot \mathrm{militaryLoyalty}
$$

Reformist alignment từ innovationBias cao, hierarchyAcceptance thấp, eliteIdeology gần popularIdeology. Reform chỉ xảy ra khi militaryLoyalty > threshold.

**Reform success (xác suất):**

$$
\mathrm{successProb} = \mathrm{eliteCohesion} \cdot \mathrm{militaryLoyalty} \cdot \mathrm{reformistAlignment} - \mathrm{inequality}
$$

Fail ⇒ entropy↑, pressure↑, ideologyGap↑, coup/revolution prob↑.

**Revolution:** Reset elite từ emerging faction (không random); inequality giảm mạnh nhưng không về 0:

$$
\mathrm{inequality} \leftarrow \mathrm{inequality} \cdot (0.3 \sim 0.6)
$$

**Authoritarian sau revolution:**

$$
\mathrm{authoritarianProb} = \mathrm{entropy} + \mathrm{warExhaustion} + \mathrm{militaryInfluence} - \mathrm{civicInstitutionStrength}
$$

Revolution không reset culture/tech/entropy hoàn toàn; chỉ cấu trúc quyền lực.

## 33. Military loyalty và Coup

**Military loyalty:** \( L_m \in [0,1] \); 0 = theo mass, 1 = theo elite.

$$
L_m = \mathrm{base} + \mathrm{ideologyAlignmentWithElite} - \mathrm{pressure} - \mathrm{inequality}
$$

**Coup condition:** militaryLoyalty < threshold và eliteCohesion thấp và entropy cao ⇒ coup.

**Military power (không tăng trực tiếp từ economy/culture):**

$$
\mathrm{MilitaryPower} = (\mathrm{population} \cdot \mathrm{tech})^\beta, \quad \beta \approx 0.8\sim 1
$$

**Effective war power:**

$$
\mathrm{EffectiveWarPower} = \mathrm{MilitaryPower} \cdot \mathrm{SustainFactor} \cdot \mathrm{MobilizationFactor} \cdot L_m
$$

với SustainFactor = f(log economy), MobilizationFactor = 1 + militarism·entropy.

## 34. War outcome (stochastic), Exhaustion và Scar

**Baseline advantage (log để tránh snowball):**

$$
\Delta = \log P_i - \log P_j
$$

**Noise phụ thuộc entropy:** \( \eta \sim \mathcal{N}(0, \sigma) \), \( \sigma = k \cdot \overline{\mathrm{entropy}}_{ij} \), k nhỏ (0.1–0.3).

**Win probability:**

$$
\mathrm{WinProb}_i = \mathrm{sigmoid}(\Delta + \eta)
$$

**War exhaustion (ngắn hạn):** mỗi tick đang chiến tranh

$$
\mathrm{warExhaustion} \mathrel{+}= a\cdot\mathrm{casualtyRate} + b\cdot\mathrm{economicCostRatio} + c\cdot\mathrm{entropy}
$$

Clamp [0,1]. Sau peace: decay tuyến tính chậm (không reset). Ảnh hưởng: pressure↑, eliteCohesion↓, militaryLoyalty↓.

**War scar (dài hạn, permanent partial):**

$$
\mathrm{warScar} \mathrel{+}= \mathrm{warExhaustion} \cdot \mathrm{scarFactor}, \quad \mathrm{scarFactor} \approx 0.1\sim 0.3
$$

warScar không decay (hoặc cực chậm); ảnh hưởng culture, ideology crystallization. War: population giảm; innovationPressure có thể tăng (qua innovationBias·entropy); inequality có thể tăng (thắng) hoặc giảm (thua/redistribution).

## 35. Dân số và Carrying capacity

**Carrying capacity:**

$$
K = \mathrm{baseResourceCapacity} \cdot \big(1 + \log(1 + T_{\mathrm{structural}})\big) \cdot \mathrm{resourceStability} \cdot \mathrm{geographyFactor}
$$

Crisis: K có thể nhân (1 − severity·0.2).

**Logistic growth:**

$$
\frac{dP}{dt} \propto r \cdot P \cdot \left(1 - \frac{P}{K}\right)
$$

**Youth pressure (political multiplier):**

$$
\mathrm{youthPressure} \mathrel{+}= \mathrm{populationGrowth}\cdot 0.5 - \mathrm{economicGrowth}\cdot 0.3; \quad \mathrm{youthPressure} \in [0,1]
$$

PressureIndex += youthPressure·0.3; revolution/crisis severity tăng khi youthPressure cao. Overshoot: P > K ⇒ youthPressure↑, instability↑.

**Lựa chọn mô hình population (khóa cho WorldOS):** Trong bốn lựa chọn — (1) Aggregate, (2) Age-structured, (3) Urban vs rural, (4) Hybrid đơn giản — WorldOS dùng **4. Hybrid đơn giản (population + youth pressure)** như trên. Lý do: tạo structural lag (dân số tăng trước kinh tế → inequality, youth bulge → revolution prob) mà không tăng complexity như age-structure hay spatial urban/rural. Mở rộng tiếp: **Population dynamics** hợp lý cho bước sau (tăng realism, không đòi spatial); **Trade network** khi đã có multi-agent world; **Climate / resource constraint** phase sau (coupling nặng với tech, war, economy).

| Layer mở rộng | Tăng chiều sâu | Tăng complexity | Giai đoạn |
|---------------|----------------|------------------|-----------|
| Population (đã có: P, K, youth) | ⭐⭐⭐⭐ | ⭐⭐ | Đã tích hợp §35 |
| Trade network | ⭐⭐⭐ | ⭐⭐⭐ | Khi có multi-agent |
| Climate / resource | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ | Phase sau |

## 36. Multi-region và Climate (cluster-correlated)

**Region state (ví dụ):** population, economy, technology, inequality, eliteCohesion, entropy, pressure, geographyFactor, connectivity.

**Trade coupling:**

$$
\mathrm{TradeFlow}_{ij} \propto \mathrm{connectivity}_i \cdot \mathrm{connectivity}_j \cdot (\mathrm{tech}_i - \mathrm{tech}_j)
$$

**War probability (cặp):** WarProb_ij = f(powerGap, pressure_i, pressure_j). **Crisis contagion:** region j nhận entropy shock từ region i: entropy_j += connectivity_ij · shockFactor.

**Climate (cluster-correlated):** Cluster k có driver

$$
C_k(t) = 1 + A_k \sin(2\pi t / T_k + \phi_k) + \mathrm{slowDrift}_k(t)
$$

Region i: \( C_i(t) = C_{\mathrm{cluster}(i)}(t) + \varepsilon_i(t) \). Chỉ ảnh hưởng effective K: effectiveK_i = baseK_i · geography_i · C_i(t).

**Cluster merge/split (meta-topology):** Merge khi integrationScore(A,B) > threshold (trade↑, war↓, connectivity↑). Split khi fragmentationScore cao (entropy, war nội cluster, connectivity↓).

## 37. Văn hóa hai tầng: Cultural Field và Ideology

**Cultural field (vector nền):** collectivism, militarism, innovationBias, hierarchyAcceptance, cosmopolitanism ∈ [0,1] hoặc [-1,1] tùy chiều.

**Effective impact (culture override structure khi entropy cao):**

$$
\mathrm{EffectiveImpact} = S + E^\alpha \cdot C, \quad \alpha > 1
$$

S = structural force, C = cultural force, E = entropy. E thấp ⇒ cultural term ≈ 0; E cao ⇒ culture có thể override.

**Crystallization (ideology xuất hiện):**

$$
\mathrm{crystallizationScore} = \mathrm{entropy} \cdot \mathrm{pressure} \cdot \mathrm{culturalAlignment}
$$

Vượt threshold ⇒ ideology hình thành. Ideology decay khi entropy/pressure giảm lâu dài.

**Elite vs Popular ideology (derived):**  
EliteIdeology = transform(CultureVector, inequality, eliteCohesion).  
PopularIdeology = transform(CultureVector, pressure, inequality).  
DominantIdeology = elite nếu elite thắng, else popular (sau revolution/coup/reform).

**Ideology gap:**

$$
\mathrm{IdeologyGap} = \mathrm{distance}(\mathrm{EliteIdeology},\ \mathrm{PopularIdeology})
$$

Gap cao + pressure cao + entropy cao ⇒ revolution probability tăng mạnh.

## 38. Culture shift sau Revolution và Subculture

**Culture shift (phase transition):**

$$
\mathrm{cultureShift} = \mathrm{ideologyGap} \cdot \mathrm{entropy} \cdot \mathrm{revolutionaryIntensity}
$$

Culture không reset hoàn toàn; vector giá trị xoay mạnh.

**Subculture:** Nation = { dominantCulture, subCultures[] }. Mỗi subculture có population share, ideology vector, influence.

**Influence:**

$$
\mathrm{influence} \mathrel{+}= \mathrm{innovationOutput} + \mathrm{crisisAdaptation} - \mathrm{repression}
$$

Influence đủ lớn ⇒ có thể thay dominantCulture.

**Diffusion (lan sang nation khác):**

$$
\mathrm{diffusionRate} = \mathrm{tradeConnection} + \mathrm{migrationFlow} + \mathrm{communicationTech} - \mathrm{ideologicalResistance}
$$

Vượt threshold ⇒ subculture clone/merge sang nation khác.

**Decay (tránh explosion):**

$$
\mathrm{decayRate} = (1 - \mathrm{pressure}) \cdot \mathrm{repression} \cdot \mathrm{assimilationForce}
$$

influence < minThreshold ⇒ dissolve. Subculture có thể: tạo economic specialization, military faction, elite riêng, narrative lịch sử riêng (tùy thiết kế độ sâu).

## 39. Subculture sâu: Economic specialization, Internal elite, Narrative

**Economic specialization (động cơ vật chất):** Mỗi subculture có skillBias (nông nghiệp / thương mại / công nghệ…), tradePreference, productionEfficiency modifier.

$$
\mathrm{economicOutput} = \mathrm{baseOutput} \cdot \mathrm{skillBias} \cdot \mathrm{techLevel} \cdot \mathrm{socialStability}
$$

Một subculture có thể giàu hơn dominant culture hoặc marginal nhưng có niche power ⇒ giai cấp thương nhân, trí thức, vùng công nghiệp, thành bang thương mại.

**Internal elite:** Subculture = { internalElite, cohesion, resourceControl }. Khi pressure cao:

$$
\text{if } \mathrm{internalElitePower} > \mathrm{centralEliteControl} \Rightarrow \mathrm{politicalChallenge} \mathrel{+}+
$$

⇒ Phe phái, phong trào cải cách, lực lượng cách mạng. Không có elite riêng ⇒ subculture chỉ là dân số thụ động.

**Narrative (chiều sâu văn hóa):** Mỗi subculture có originMyth, grievanceScore, futureVision. Grievance tăng khi repression, inequality, resourceExclusion. Narrative kết dính nội bộ, chính đáng hóa hành động, tăng revolutionaryIntensity. Grievance mạnh ⇒ subculture tự xem “quốc gia trong quốc gia” ⇒ ly khai, nội chiến, tôn giáo cải cách, chủ nghĩa dân tộc.

**Ràng buộc kiến trúc (tránh combinatorial explosion):** Population abstraction (không mô phỏng từng cá nhân); culture vector dimension cố định; subculture cap (giới hạn số subculture cùng lúc); merge rule (subculture tương đồng có thể hợp nhất).

## 40. Elite Dynamics: Ambition, Coexistence, Corruption drift

**Power ambition (xu hướng chiếm central power):**

$$
\mathrm{powerAmbition} = \mathrm{resourceControl} \cdot \mathrm{cohesion} \cdot \mathrm{grievance}
$$

powerAmbition > threshold ⇒ elite subculture bắt đầu cạnh tranh central power (hành động còn phụ thuộc stability & military balance).

**Coexistence (tránh world = war machine):**

$$
\mathrm{coexistenceUtility} = \mathrm{tradeBenefit} + \mathrm{politicalAccess} - \mathrm{repression} - \mathrm{ideologyGap}
$$

Nếu coexistenceUtility > rebellionUtility ⇒ elite chọn thương lượng, liên minh, chia sẻ quyền lực, coalition government ⇒ thể chế đa đảng, liên bang, thỏa hiệp chính trị.

**Corruption drift (elite mới tha hóa khi lên nắm quyền):**

$$
\mathrm{corruptionDrift} = \mathrm{powerConcentration} \cdot \mathrm{entropy} \cdot (1 - \mathrm{institutionStrength})
$$

Drift cao ⇒ hierarchyAcceptance↑, inequality↑, repression↑, innovationBias↓. Elite mới dần giống elite cũ. Không có drift ⇒ “revolution giải phóng vĩnh viễn” (không realistic).

## 41. StateStructure (xương sống nation)

**Định nghĩa:** Không phải elite, không phải culture. StateStructure = bộ máy hành chính, thu thuế, hạ tầng, luật pháp, institutional memory. Tồn tại độc lập tương đối với elite: elite thay đổi, state không mất hoàn toàn.

**State capacity (ví dụ):** stateCapacity hoặc (bureaucracyLevel, institutionalStrength, memoryDepth).

**(1) Suy yếu sau war:**

$$
\mathrm{stateCapacity} \mathrel{-}= \mathrm{warIntensity} \cdot \mathrm{duration}
$$

⇒ Thu thuế kém, quản lý yếu, subculture dễ nổi lên. Không giảm ⇒ war chỉ cosmetic.

**(2) Suy yếu sau revolution:** Revolution giảm bureaucracyLevel, institutionalStrength, tăng entropy; không đưa state về 0 (nếu về 0 ⇒ reset civilization). Hậu cách mạng thường hỗn loạn.

**(3) Tăng dần theo tech & time:**

$$
\mathrm{stateCapacity} \mathrel{+}= \mathrm{techLevel} \cdot \mathrm{administrativeInvestment}
$$

Công nghệ cao ⇒ ghi chép, truyền thông, kiểm soát tốt hơn ⇒ empire hiện đại ổn định hơn cổ đại.

**(4) Có thể quá mạnh và kìm innovation:**

$$
\mathrm{innovationPenalty} = \mathrm{stateCapacity} \cdot \mathrm{regulationDensity} \cdot \mathrm{hierarchyAcceptance}
$$

stateCapacity cao + hierarchyAcceptance cao ⇒ bureaucracy inertia↑, risk-taking↓, innovationBias↓ ⇒ đế chế già trì trệ, trật tự quá ổn định giết sáng tạo. Không có (4) ⇒ thế giới tăng trưởng mãi.

**Chu kỳ macro:** Subculture hình thành → Elite tích lũy → Power ambition ↑ → Coexist hoặc challenge → Thắng ⇒ central elite → Corruption drift → Inequality ↑ → Pressure ↑ → Reform hoặc Revolution → Lặp lại.

## 42. Mô hình thời gian: Discrete Tick và Multi-scale

**Không làm continuous physics.** Engine là macro-history: bước thời gian rời rạc, dễ kiểm soát, debug, snapshot, scale (queue job theo tick). Continuous ⇒ tự giết performance.

**Discrete tick (1 tick = 1 năm hoặc 5 năm):** Mỗi tick cập nhật economy, culture drift, elite power, state capacity, pressure, entropy. Snapshot mới mỗi tick; không mutate bản ghi cũ.

**Multi-scale (Macro + Micro):**

- **Macro layer (mỗi năm):** GDP change, inequality drift, culture shift nhỏ, state growth, diffusion. Đây là vòng lặp chính.
- **Micro layer (Event Resolution):** Khi trigger War, Revolution, Reform, Crisis, Civil war ⇒ mở sub-loop. Ví dụ WarDuration = 3 năm ⇒ simulate 3 micro-steps nội bộ ⇒ merge lại macro state. Không simulate từng ngày; chỉ zoom khi có biến cố.

Luồng: Tick t → detect event (pressure > threshold, borderConflictHigh, …) → enter Resolver (WarResolver, RevolutionResolver, …) → apply outcome → return to macro. Chỉ A (discrete) ⇒ boring incremental; chỉ C (micro) ⇒ chaos event spam. A + C ⇒ lịch sử có nhịp.

**Thực thi (gợi ý Laravel):** (1) **WorldSnapshot:** world_id, year, serialized_state_vector; mỗi tick tạo snapshot mới. (2) **Engine layer** (domain, tách khỏi controller): EconomyProcessor, CultureProcessor, PoliticalProcessor, WarResolver, RevolutionResolver. (3) **Event queue:** Macro tick → push potential events (RevolutionCandidate, WarCandidate); event resolver xử lý riêng. Logic không nằm trong controller.

## 43. Deterministic Core vs Stochastic Surface

**Deterministic core (xương sống):** Không random. next_state = f(current_state). Cùng seed ⇒ cùng evolution nếu không có event. Bao gồm: population growth (công thức), tech growth (innovationPressure), inequality drift, state capacity growth, culture inertia drift, elite power accumulation. Đây là phần “quy luật”.

**Stochastic layer (biến cố):** Random chỉ ở war outcome, revolution/reform success, leader personality, subculture mutation, crisis timing. Điều kiện: seed-based; ràng buộc bởi state. Ví dụ:

$$
\mathrm{revolutionSuccessProb} = \mathrm{eliteCohesion} \cdot \mathrm{militaryLoyalty} - \mathrm{inequality}
$$

Random quyết định trong khoảng xác suất đó; không random vô căn cứ. Quy luật tạo structure, random tạo narrative. Chỉ deterministic ⇒ không thú vị; chỉ random ⇒ không pattern. Hybrid ⇒ empire rise/fall, unexpected war, failed reform, accidental authoritarian, nhưng vẫn có logic.

**Kỹ thuật:** (1) **Seeded RNG:** WorldRandom::withSeed($world_id); cùng world ⇒ cùng chuỗi; không dùng random() trực tiếp. (2) **Pure simulation:** next(WorldState): WorldState — không query DB, không side effect; pure domain logic. (3) **Event resolver:** Macro tick ⇒ collectEvents() → resolveEvents() → applyStateChanges(); mỗi event có condition, probability, resolution strategy.

## 44. Deep simulation (3 tầng) và ràng buộc

**“Chi tiết” đúng nghĩa:** Quan hệ nhân quả sâu, lớp trung gian rõ, state giàu thông tin, event resolution nhiều bước — không phải tick theo ngày hay hàng nghìn biến.

**Ba tầng thời gian:**

1. **Structural layer (chậm):** geography, culture base vector, institution depth, long-term tech paradigm. Cập nhật mỗi 5–10 năm.
2. **Socio-political layer (trung bình):** elite factions, inequality, pressure, subculture influence, state capacity. Cập nhật mỗi năm.
3. **Event micro-simulation (zoom):** Khi War, Revolution, Civil conflict, Collapse ⇒ sub-simulation nhiều bước (vd. War: mobilization → resource strain → battle outcome → post-war restructuring) ⇒ merge lại macro. Không lưu mọi micro-step vào DB; không query DB trong vòng lặp; không để Model chứa logic. Engine thuần domain; DB chỉ snapshot.

**AI / decision layer (tùy chọn):** Faction/elite không chỉ if đơn giản; tối ưu utility = powerGain − risk − instabilityCost + ideologicalDrive; có thể bắt đầu rule-based, sau dùng RL. Nếu engine rất chi tiết mà không có lớp quyết định ⇒ chỉ là máy toán khô.

**Performance:** Chạy simulation trong Queue Job; không trong HTTP request; batch tick theo block; cache state trong memory khi simulate nhiều năm. WorldOS = background computation engine.

**Rủi ro khi chi tiết:** Subculture/event/faction explosion ⇒ chậm, hỗn loạn, mất pattern. Cần: cap faction count, cap subculture count, merge rule, decay rule.

**Đủ để build (không cần toán nặng):** Vector state rõ ràng, hàm chuyển trạng thái sạch, threshold logic, feedback loop hiểu sâu.

---

# Phần VI — Audit, 20% biến, Phase boundary, Actor & Observer

*(Tham chiếu đánh giá kiến trúc và tối giản hóa; giữ công thức chính.)*

## 45. Điểm mạnh và cảnh báo kiến trúc

**Điểm mạnh:** (1) World = (Θ, G), Universe = G(θ, seed) — tách bạch; Universe không mutate law trực tiếp (trừ DLM có kiểm soát). (2) F(θ) constraint — Law Space là manifold có ràng buộc. (3) Cascade Jacobian tam giác ⇒ eigenvalues = đường chéo ⇒ dễ phân tích bifurcation. (4) CPI phi tuyến (interaction = I·(1−L), elite_break = (1−E)², war_amp) — crisis không tuyến tính.

**Vấn đề nghiêm trọng:** (1) Không có global conserved quantity (energy budget) — dễ runaway hoặc chết im; cần Ω(t) = f(P,C,B,N,K,StateCapacity) để subsystem compete. (2) Meme PDE (replicator–mutation) quá nặng cho macro tick — nên thay bằng 5–8 meme attractor states, entropy trên cluster proportions, mutation như Markov transition. (3) DLM dθ/dt = G(X,θ) làm Law Space không ổn định; nếu giữ DLM thì chỉ 2–3 θ drift, timescale cực chậm. (4) Quá nhiều second-order feedback → Jacobian dense, không còn cascade thuần. Đề xuất refactor: Layer 1 Law Engine (pure math), Layer 2 Civilizational Core (5–7 biến: P,B,N,K,StateCapacity,Entropy,Inequality), Layer 3 Narrative Adapter, Layer 4 Visualization/LLM. AI = control system: quan sát σ(U), λ_max, R, Entropy; policy π(A) → sửa θ₁₇, θ₁₆ nhẹ (RL agent).

## 46. 20% biến quyết định 80% hành vi

**Law Space (17D):** Chỉ 4 chiều quyết định phase — θ₅ (Entropy Growth), θ₁₃ (Cognitive Ceiling), θ₁₆ (Tech Accumulation), θ₁₇ (Meta-System Awareness). Critical Law Subspace ≈ (θ₅, θ₁₃, θ₁₆, θ₁₇).

**Civilizational Core:** 5 biến — Entropy E, Inequality I, StateCapacity (InstitutionStrength), Tech T_structural, Pressure P. Core engine thật = [E, I, StateCapacity, Tech, Pressure].

**Political gate:** Elite Cohesion, Military Loyalty L_m, Ideology Gap. Tổng ~10–11 biến; phần còn lại chủ yếu texture.

## 47. Phase boundary (hệ rút gọn)

**State rút gọn:** X = [K, I, E, S, R] với K = cultural/tech complexity, I = inequality, E = entropy, S = state capacity, R = risk. Các phương trình dạng: K_dot = aK(1−K/K_max) − bEK − cRK; I_dot ≈ dK − eS; E_dot ≈ gI − iS; S_dot ≈ jK − kE; R_dot = pK − qθ₁₇.

**Equilibrium:** Giải K_dot = I_dot = E_dot = S_dot = R_dot = 0. **Phase boundary chính** khi λ_K = 0 (bifurcation). Điều kiện tương đương surface:

$$
K^* = \frac{a + q\theta_{17}}{p + bj/k + a/K_{\max}}, \qquad \theta_{17} \sim \frac{1}{q}\big[(p + \mathrm{entropy\_loop})K^* - a\big]
$$

**Ý nghĩa:** Collapse region khi K tăng nhanh, Risk tăng, entropy loop mạnh, θ₁₇ thấp ⇒ λ_K > 0. Stable basin khi qθ₁₇ > pK* + entropy_loop − a ⇒ λ_K < 0. Tỷ lệ (Tech acceleration + Entropy amplification) / Meta-awareness quyết định phase boundary.

## 48. Actor: xuất hiện, Hybrid, multi-layer, predictive belief

**Định nghĩa:** Actor = subsystem có internal model, tối ưu objective, tác động ngược lên hệ; không nhất thiết là con người. **Xuất hiện khi:** positive feedback ∂N_dot/∂N > 0, nonlinearity, entropy không quá cao (Edge of Chaos). **Emergent:** A_dot = βKA − γEA; actor khi K > (γ/β)E.

**Hybrid (C):** Emergent layer (A khi K > K_crit, E < E_crit) + Policy layer u_m = π(X). Dynamics: X_dot = F(X) + B·u. Policy chỉ được sửa θ₁₇, redistribution, regulation; không sửa entropy/K trực tiếp. Objective J = ∫(αD + βH − γR)dt. **Multi-layer:** Micro u_i (local), Meso u_f (faction, X_macro), Meta u_m (Θ,X); ∥B_m∥ < ∥B_f∥ < ∥B_i∥. **Predictive (B):** X_hat(t+H) = simulate(X,u,k); u_m = argmax J(X_hat). **Imperfect belief:** F_hat ≠ F; meta tối ưu trên X_hat nhưng hệ chạy F(X) + B_m u_m ⇒ policy drift, self-fulfilling prophecy. **Multiple belief:** F_hat_1..n, u_m = Σ w_i u_i; merge khi ∥F_hat_i − F_hat_j∥ < δ, split khi Var(error_i) cao. **Distortion:** D_i từ entropy cao (E > E_crit) và từ meta overfitting; D = Σ w_i ∥F_hat_i − F∥. **Oscillatory:** D_dot = aE + bOverfit − cM − dD; M_dot = e·Crisis − f·Comfort; meta-recovery khi error lớn (giảm dogma, tăng learning). **Chaotic:** Phi tuyến D³, delay τ trong M_dot; Lyapunov > 0 nhưng bounded; không bao giờ ổn định hoàn toàn (nền epsilon_0 hoặc M² saturation).

## 49. Multiverse và Observer (không select, chỉ quan sát)

**Multiverse:** Không selection, không fitness cull. Mỗi universe chạy X_dot_u = F_θ_u(X_u). Chaos có thể lan (law drift, belief topology leak, meta-resonance) nhưng không ép. Multiverse = trajectory cloud; mapping Φ(θ) → {stable, oscillatory, chaotic, divergent}.

**Observer:** Tách epistemology khỏi ontology; không tác động ngược. (1) Metrics per universe: E, D, M, belief entropy, Lyapunov estimate (hoặc heuristic), phase label. (2) Phase detector: threshold + derivative; chaotic khi λ_est > 0 và Var(E) > V_min; divergence khi |E_t − E_{t−1}| > Δ_crit. (3) Phase event logger: ghi from/to, không can thiệp. **Heuristic (A):** Không đo Lyapunov thật; dùng 4 metric — **V** (Volatility = Var(E window)), **DP** (Directional Persistence = |ΣΔE|/Σ|ΔE|), **SFR** (Sign Flip Rate của ΔE), **AG** (Acceleration Growth = mean(|ΔE_t| − |ΔE_{t−1}|)). Regime: Stable (V thấp, DP thấp, SFR thấp); Oscillatory (V trung, DP thấp, SFR cao); Chaotic-like (V cao, DP trung, SFR trung); Divergent (V tăng, DP cao, SFR thấp, AG > 0). **Export:** Snapshot + phase event stream + windowed features; tách layer; không cho AI đọc DB trực tiếp. Observer = phase-space cartographer; làm chaos trở nên đo được và tạo bản đồ law manifold.

---

# Phụ lục — Ký hiệu và bảng tra nhanh

| Ký hiệu | Ý nghĩa |
|---------|--------|
| θ, θ_i | Law vector 17 chiều, chiều thứ i |
| P,C,B,N,K | Physical, Chemical, Biological, Neural, Cultural index |
| R, D | Risk accumulation, Damage index |
| σ(U), σ(θ) | Stability metric (heuristic hoặc max Re(λᵢ)) |
| λ, λ_max | Eigenvalue (Jacobian), eigenvalue lớn nhất |
| F(θ) | Feasibility function |
| T | Evolution operator / state transition |
| 𝓓, 𝓗 | Cultural diversity functional, Historical depth (regime count) |
| CPI, P | Composite Pressure Index (trong Phần IV) |
| **Phần V** | |
| S_pol | Political state vector (eliteConcentration, institutionStrength, …) |
| L_m | Military loyalty [0,1] |
| K | Carrying capacity (dân số) |
| warExhaustion, warScar | War exhaustion (decay chậm), scar dài hạn |
| ReformPotential | Điều kiện reform (pressure·entropy·reformistAlignment·militaryLoyalty) |
| IdeologyGap | Khoảng cách elite vs popular ideology |
| EffectiveImpact | S + E^α·C (structure + culture khi entropy cao) |
| cultureShift | ideologyGap·entropy·revolutionaryIntensity |
| diffusionRate | Lan truyền subculture (trade + migration + tech − resistance) |
| **§39–41** | |
| economicOutput | baseOutput·skillBias·techLevel·socialStability (subculture) |
| internalElitePower, politicalChallenge | Elite subculture vs central; challenge khi internal > central |
| grievanceScore | repression, inequality, resourceExclusion → narrative/mobilization |
| powerAmbition | resourceControl·cohesion·grievance (xu hướng chiếm quyền) |
| coexistenceUtility, rebellionUtility | So sánh để chọn coexist vs challenge |
| corruptionDrift | powerConcentration·entropy·(1−institutionStrength) (elite tha hóa) |
| stateCapacity | Bureaucracy, institutional strength, memory; yếu sau war/revolution, tăng theo tech |
| innovationPenalty | stateCapacity·regulationDensity·hierarchyAcceptance (kìm innovation) |
| **§42** | |
| tick | Bước thời gian rời rạc (1 tick = 1 năm hoặc 5 năm); macro layer = vòng chính, micro = event sub-loop |
| **§43–44** | |
| revolutionSuccessProb | eliteCohesion·militaryLoyalty − inequality (stochastic nhưng state-bound) |
| Deterministic core | Population, tech, inequality, state, culture inertia, elite — next = f(state); seeded RNG cho event |
| 3 tầng | Structural (5–10 năm) / Socio-political (năm) / Event micro-simulation (zoom rồi merge) |
| utility (faction) | powerGain − risk − instabilityCost + ideologicalDrive (AI/decision layer) |
| **Phần VI** | |
| X (rút gọn) | [K, I, E, S, R]: complexity, inequality, entropy, state capacity, risk |
| λ_K | Eigenvalue theo K tại equilibrium; λ_K = 0 ⇒ phase boundary |
| K*, entropy_loop | Equilibrium K; loop strength bj/k trong surface θ₁₇ |
| F, F_hat | Dynamics thật F(X); belief model F_hat_i(X_hat) ≠ F |
| u_i, u_f, u_m | Action micro (local), meso (faction), meta (Θ,X); ∥B_m∥ < ∥B_f∥ < ∥B_i∥ |
| A_dot | βKA − γEA (emergent actor density); actor khi K > (γ/β)E |
| D, M | Distortion (belief), Meta-awareness; D_dot, M_dot oscillatory/chaotic |
| V, DP, SFR, AG | Volatility, Directional Persistence, Sign Flip Rate, Acceleration Growth (Observer heuristic) |
| regime | Stable / Oscillatory / Chaotic-like / Divergent (4 metric heuristic) |
| Φ(θ) | Mapping law → behavioral regime (phase diagram) |

*Hết tài liệu. Công thức và định luật được giữ nguyên từ nguồn thiết kế; có thể bổ sung ví dụ số và mapping sang code trong tài liệu khác.*
