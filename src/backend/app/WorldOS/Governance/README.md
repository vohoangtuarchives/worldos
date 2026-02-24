# Governance Module
## 📋 Overview
Acts as the meta-evaluator for the simulation. Periodically extracts metrics (Entropy Trend, Collapse Risk, IP Score) from Universe history and makes recommendations (CONTINUE, FORK, ARCHIVE) via the `DecisionEngine`.

## 🏗️ Architecture
- **Service Layer**: `MetricsExtractor`, `DecisionEngine`, `RuleBasedEvaluator`
- **Contracts**: `UniverseEvaluatorInterface`
- **Listeners**: `EvaluateOnTickListener` (runs every N ticks)

## 📐 Structure
- `Services/` - Extractor and Evaluator logic
- `ValueObjects/` - `UniverseMetrics`, `EvaluationResult`

## 🔗 Integration
- Triggered by **Runtime** events.
- Output actions coordinate with **Saga** orchestration layer.
