# 05 — Lớp AI đánh giá và quyết định (v3)

## 5.1 Nguyên tắc

AI không sửa state_vector trực tiếp. Luồng: metrics từ snapshot → Evaluator → recommendation + MutationSuggestion → DecisionEngine thực thi (archive / fork / continue hoặc apply pressure qua kernel).

## 5.2 MetricsExtractor

- Vị trí: `App\Domains\Runtime\Evaluation\MetricsExtractor`
- Input: UniverseSnapshotRepository (getLatest hoặc snapshot). Output: UniverseMetrics (entropyTrend, complexityIndex, stabilityScore, collapseRisk, …).
- fromLatestSnapshot(universe_id) hoặc fromSnapshot(UniverseSnapshot).

## 5.3 UniverseEvaluatorInterface

- Input: UniverseMetrics. Output: EvaluationResult (recommendation: continue|fork|archive; optional MutationSuggestion: type, intensity).
- runBatchWithEvaluation gọi evaluator->evaluate(metrics) rồi DecisionEngine::execute(universe, result).

## 5.4 DecisionEngine

- Vị trí: `App\Domains\Runtime\Evaluation\DecisionEngine`
- execute(Universe, EvaluationResult): archive → set status archived; fork → SagaService::fork từ latest snapshot; nếu có mutationSuggestion → kernel->validateMutation rồi kernel->applyPressure, cosmologyRepository->save.

## 5.5 Kernel (AI boundary)

- validateMutation(World, MutationSuggestion): kiểm tra law_profile mutation_types và intensity.
- applyPressure(Universe, selectionPressure, intensity): áp thay đổi lên state; caller gọi save.

## 5.6 Thứ tự runBatchWithEvaluation

1. runBatch (advance, snapshot mỗi tick).
2. Với mỗi universe: fromLatestSnapshot → evaluate → execute (archive|fork|continue hoặc applyPressure+save).
