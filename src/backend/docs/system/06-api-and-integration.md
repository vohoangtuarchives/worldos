# 06 — API và tích hợp

## 6.1 Writer / Genesis

- GET `/api/writer/genesis/presets` — WriterGenesisController::presets() → GenesisPresetService::allByCategory().
- POST `/api/writer/genesis` — WriterGenesisController::store(): tạo Saga (metadata preset, origin_type, …), có thể gọi SagaService::genesisV3($saga).
- Controller: `App\Http\Controllers\Api\Writer\WriterGenesisController`.

## 6.2 Saga

- WriterSagaController: list/show Saga; trả về saga_worlds_count, status, current_universe_id. saga_worlds trỏ universe_id.

## 6.3 Runtime

- UniverseRuntimeService::advance thường gọi nội bộ từ SagaService::runBatch. Có thể expose POST advance(universe_id, ticks) nếu cần tick thủ công.

## 6.4 Policy

- UniverseRuntimePolicy::tickUniverse(UniverseModel, World): World không được HALTED thì mới cho phép tick.

## 6.5 Frontend

- Genesis form gọi presets và store genesis; sau genesisV3 có thể poll saga status, current_universe_id, saga_worlds.
