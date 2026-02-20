# Refactor Backend theo Tactical DDD + Clean Architecture — Design

> Tài liệu thiết kế đã được duyệt qua brainstorming. Implementation plan tạo riêng bằng writing-plans skill.

## Chốt yêu cầu

- **Tactical DDD**: Aggregates, Entities, Value Objects, Domain Events, Repositories (interface trong domain), Domain Services.
- **Persistence**: Domain entity tách hẳn khỏi Eloquent; Repository interface trong Domain, Infrastructure map Entity ↔ Eloquent.
- **Phạm vi**: Toàn bộ bounded contexts (World, Runtime, Saga, Cosmology, Evolution, Narrative, Mutation, Vietnamese, …) cùng một chuẩn.
- **Clean Architecture**: Các lớp tương ứng Entities, Use Cases, Interface Adapters (Presentation + Infrastructure), Frameworks & Drivers (Laravel). Dependency hướng vào trong.
- **Root namespace & thư mục**: **Tuzy** — code DDD và Presentation nằm trong `src/Tuzy/`, namespace `Tuzy\*`. Presentation layer thuộc Tuzy (không nằm `app/Http`).

---

## 1. Kiến trúc tổng quan & layout

### Nguyên tắc

- **Domain** (Tuzy): Pure PHP, không phụ thuộc Laravel. Chứa Entity, Value Object, Aggregate Root, Domain Event, Repository interface (port), Domain Service.
- **Application** (Tuzy): Use cases / Handlers; orchestration, gọi Domain; không chứa logic nghiệp vụ.
- **Infrastructure** (Tuzy): Repository implementation (Eloquent ↔ Entity), event dispatch adapter, external services.
- **Presentation** (Tuzy): HTTP controllers, Requests, Resources — trong `src/Tuzy/Presentation/`, không trong `app/Http`.
- **Laravel** (`app/`): Chỉ bootstrap, routing (trỏ tới Tuzy controllers), providers (binding port → adapter), Console kernel. Có thể giữ Eloquent models trong `app/Models` cho Infrastructure dùng, hoặc chuyển vào `Tuzy\Infrastructure\Persistence\Eloquent`.

### Thư mục & namespace (root = Tuzy)

```
src/Tuzy/
├── Domain/
│   ├── World/
│   │   ├── Entity/
│   │   ├── ValueObject/
│   │   ├── Event/
│   │   ├── Repository/   (interface)
│   │   └── Service/
│   ├── Runtime/
│   ├── Saga/
│   ├── Cosmology/
│   ├── Evolution/
│   ├── Narrative/
│   ├── Mutation/
│   ├── Vietnamese/
│   └── Shared/
├── Application/
│   ├── World/
│   ├── Runtime/
│   └── ...
├── Infrastructure/
│   ├── Persistence/
│   └── ...
└── Presentation/
    └── Http/
        ├── Controllers/
        ├── Requests/
        └── Resources/
```

### Dependency rule

- Domain ← không phụ thuộc Application, Infrastructure, Laravel.
- Application ← Domain; không phụ thuộc Infrastructure (chỉ qua interface).
- Infrastructure ← Domain (và Application nếu có port).
- Presentation ← Application only.

---

## 2. Thành phần từng layer

### Domain

- **Entity / Aggregate Root**: Class PHP thuần, có identity, logic nghiệp vụ.
- **Value Object**: Bất biến, so sánh theo giá trị.
- **Domain Event**: Class mô tả sự kiện đã xảy ra.
- **Repository interface**: Chỉ khai báo (port); implementation ở Infrastructure.
- **Domain Service**: Logic không gắn một entity (tùy context).

### Application

- **Handler / Use Case**: Một class một hành động; nhận Command/DTO, gọi Domain và port, trả DTO.
- **DTO / Command / Query**: Input/output của use case.

### Infrastructure

- **Persistence**: Repository impl; map Entity ↔ Eloquent, đọc/ghi DB.
- **Event dispatch**: Domain event → Laravel event bus (adapter).
- **External**: HTTP client, AI client, … implement interface từ Domain/Application.

### Presentation

- **Controllers**: Build Command, gọi Handler, map result → HTTP response.
- **Requests**: Validation input.
- **Resources**: Transform output cho API.

---

## 3. Data flow

- **Ghi**: HTTP → Presentation (Controller) → Application (Handler) → Domain (Entity + Repository interface) → Infrastructure (Repository impl) → DB. Domain events (nếu có) từ entity → Infrastructure adapter → Laravel events.
- **Đọc**: HTTP → Controller → Handler → Repository (hoặc read model) → response.
- **Domain events**: Entity ghi event; sau persist, Infrastructure đọc và dispatch sang Laravel.

---

## 4. Error handling

- **Domain**: Chỉ ném domain exception (ví dụ `WorldNotFoundException`, `InvalidWorldStateException`).
- **Application**: Để exception bubble hoặc map sang application exception.
- **Presentation**: Global handler map domain/application exception → HTTP status (404, 422, 409, 500).
- **Infrastructure**: Lỗi DB/external wrap thành exception; Presentation map.

---

## 5. Testing

- **Domain**: Unit test entity, VO, domain service — pure PHP, không Laravel.
- **Application**: Unit test Handler với repository fake/mock.
- **Infrastructure**: Integration test repository (Entity ↔ Eloquent, DB thật hoặc SQLite in-memory).
- **Presentation**: Feature test HTTP → Handler → response; hoặc controller unit với handler mock.

Cấu trúc test: `tests/Unit/Tuzy/Domain/`, `tests/Unit/Tuzy/Application/`, `tests/Integration/Tuzy/Infrastructure/`, `tests/Feature/Tuzy/Presentation/`.

---

## Tài liệu tham chiếu

- CONTEXT_MAP.md, DOMAIN_ARCHITECTURE.md, BACKEND_REFACTOR_PLAN_MODULAR.md (backend docs).
- Bounded contexts: WorldContext, RuntimeContext, SagaContext; quan hệ World → Runtime → Saga.
