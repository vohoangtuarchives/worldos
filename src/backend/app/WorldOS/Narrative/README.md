# Narrative Module (IP Factory)
## 📋 Overview
Closes the loop between Simulation and the Human Writer. Wraps simulation states into prose via the `LLMChronicler`, tracks story content in `NarrativeSeriesEntity` and `SerialChapterEntity`, and translates canonized text back into `WorldMyths`.

## 🏗️ Architecture
- **Domain Layer**: `NarrativeSeriesEntity`, `SerialChapterEntity`, `StoryBibleEntity`, `ChapterStatus`
- **Service Layer**: `LLMChroniclerInterface`, `NarrativeFeedbackService`
- **Application Layer**: Create, Generate, Canonize actions

## 📐 Structure
- `Services/` - Null LLM Stub and Feedback service
- `Actions/` - APIs for generation and canonization

## 🔗 Integration
- Reads from **Runtime** state.
- Generates **CivilizationMemory** constructs during canonization.
- Completes the RFC-DCE loop.
