# Myth & Scar Governance

> **Purpose**: Permanent consequences and emergent mythology tracking.

---

## I. MYTH SYSTEM

### What is a Myth?

**Myth = Crystallized belief pattern** that has reached critical mass.

Formation:
```
Multiple Beliefs (same theme)
 → Strength accumulation
 → Threshold reached
 → Myth emerges
```

### Myth States

1. **ACTIVE** - Myth is growing
2. **DECAYING** - Supporting beliefs weakening
3. **MERGED** - Combined with another myth

### Governance Rules

**Myths are SEMI-MUTABLE:**
- ✅ Can decay naturally
- ✅ Can merge with other myths
- ❌ Cannot be manually deleted
- ❌ Cannot have strength artificially boosted

**Immutability Principle:**
> "Myths reflect collective belief. Operators cannot fabricate faith."

---

## II. SCAR SYSTEM

### What is a Scar?

**Scar = Permanent consequence** of world events.

Formation:
```
Critical Event (high severity)
 → ScarFactory triggered
 → WorldScar created (IMMUTABLE)
```

### Scar Properties

- **Source Event**: Linked to `world_events`
- **Weight**: Severity (1-10)
- **Immutability**: **ABSOLUTE** - Cannot edit/delete

### Governance Rules

**Scars are IMMUTABLE:**
- ❌ Cannot be healed
- ❌ Cannot be forgotten
- ❌ Cannot be undone
- ✅ Can be viewed/analyzed only

**Immutability Principle:**
> "History cannot be rewritten. Consequences are permanent."

### Code Enforcement

```php
protected static function booted(): void
{
    static::updating(fn () => throw new Exception('WorldScar is immutable.'));
    static::deleting(fn () => throw new Exception('WorldScar is immutable.'));
}
```

---

## III. OPERATOR CONTROLS

### What Operators CAN Do

**Myths:**
- View myth emergence
- Analyze myth clusters (AI)
- Track decay patterns

**Scars:**
- View scar history
- Analyze scar clusters (AI)
- Monitor accumulation

### What Operators CANNOT Do

**Myths:**
- ❌ Create myths manually
- ❌ Force merge
- ❌ Boost strength

**Scars:**
- ❌ Delete scars
- ❌ Edit weight
- ❌ Heal consequences

---

## IV. AI ANALYSIS

### MythOvergrowthAnalyzer

Detects when myths dominate world narrative.

**Alert**: `MYTH_OVERGROWTH`

### ScarClusterAnalyzer

Detects when scars accumulate dangerously.

**Alert**: `SCAR_CLUSTER`

---

## V. FAILURE MODES

**Without Governance:**

❌ Operators delete inconvenient scars
❌ Myths fabricated for story convenience
❌ History rewritten post-hoc

**With Governance:**

✅ Permanent consequence tracking
✅ Organic myth emergence
✅ Authentic world history

---

## GOVERNANCE LAW

> **Myths emerge from belief.
> Scars emerge from actions.
> Neither can be undone.**
