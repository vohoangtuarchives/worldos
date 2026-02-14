# THREAT MODEL – WORLD ENGINE

> **Không phải security checklist. Đây là bản đồ những cách thế giới có thể chết.**

## I. THREAT PHILOSOPHY
Mối đe doạ lớn nhất không phải hacker. Mà là:
1.  **Quyền lực không kiểm soát**
2.  **AI tối ưu sai mục tiêu**
3.  **Con người hoảng loạn**

---

## II. THREAT CATEGORIES

### 1. AI THREATS

#### 1.1 Law Exploitation
**Description**: AI tìm kẽ hở World Law. Leo thang quyền lực hợp lệ (e.g., creating infinitely strong items within rules).
**Mitigation**:
*   Claim-based validation
*   Law gap alerts (Detection of rapid power spikes)

#### 1.2 Goal Drift
**Description**: AI tối ưu narrative nhưng phá economy (e.g., hyper-inflation for drama).
**Mitigation**:
*   World Health multi-axis monitoring
*   Balancing framework constraints

### 2. GOVERNANCE THREATS

#### 2.1 Single-point Authority
**Description**: Một người có quyền kill world mà không cần kiểm soát.
**Mitigation**:
*   Dual approval (Technical & Governance - *Pending Implementation*)
*   Immutable audit logs

#### 2.2 Emergency Override Abuse
**Description**: Turning off safety checks "just to fix it quickly".
**Mitigation**:
*   No unaudited override
*   Incident reports auto-created on override

### 3. OPERATOR THREATS

#### 3.1 Delay under Stress
**Description**: Operator freezes during a crisis (Economy Collapse).
**Mitigation**:
*   SOP color-based Dashboard (Red/Yellow/Green)
*   AI Advisory / Decision Support

#### 3.2 Overconfidence
**Description**: Resume simulation without fixing root cause.
**Mitigation**:
*   Mandatory Post-Mortem before Resume
*   Fork justification requirements

### 4. SYSTEM THREATS

#### 4.1 Determinism Break
**Description**: Replay yields different results than original run.
**Mitigation**:
*   Event Sourcing (Immutable)
*   Seed management logic

#### 4.2 Silent Corruption
**Description**: Slow database rot or logic drift.
**Mitigation**:
*   Health decay alerts
*   Periodic replay verification

---

## III. EXISTENTIAL FAILURE MODES

| Mode | Description | Point of No Return |
| :--- | :--- | :--- |
| **Law Corruption** | World Law invalid / illogical | **Yes** |
| **Replay Divergence** | History mismatch | **Conditional** |
| **Memory Loss** | Event loss | **Yes** |

---

## IV. FINAL PRINCIPLE
**"If a threat is invisible, it is already winning."**
