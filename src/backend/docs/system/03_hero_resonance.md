# Hero-Material Resonance System

## Overview
This system implements the "Great Man Theory" vs "Historical Materialism" debate. It allows the World State (Material conditions) to create Heroes, and Heroes to alter Material conditions.

## 1. Spawning Logic (`CheckHeroSpawningListener`)
The system monitors `WorldStateVector` after every tick.

### Thresholds
| Condition | Threshold | Spawns Archetype | Logic |
|-----------|-----------|------------------|-------|
| **High Entropy** | > 0.8 | `REBEL_LEADER`, `EMERGENCY_SAVIOR` | Chaos creates demand for a savior or rebel. |
| **High Order** | > 0.9 | `REFORMER`, `PHILOSOPHER_KING` | Tyranny creates demand for wisdom or reform. |
| **Low Cohesion** | < 0.3 | `CULTURAL_HERO` | Identity crisis creates demand for cultural unifiers. |
| **Time** | Every 100 Ticks | Random | General "Zeitgeist" hero. |

## 2. Procedural Generation
If no historical hero matches the context, the `HeroFactory` creates one:
- **Name**: Generates Hán Việt names (e.g., *Trần Quốc Toản*) using `VietnameseNameGenerator`.
- **Stats**:
    - **Chaos World**: High Military/Rebellion stats.
    - **Ordered World**: High Governance/Diplomacy stats.

## 3. The Bridge (`HeroMaterialBridge`)
Should a Hero appear, they immediately impact the world's materials.
- **Example**: `LEGENDARY_GENERAL` spawns.
- **Effect**:
    - Increases *VIOLENCE* material strength by 0.3.
    - Increases *PATRIOTISM* material strength by 0.4.
    - Reduces *CHAOS* (puts down rebellion) by 0.2.
