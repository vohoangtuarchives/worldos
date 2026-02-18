# Setup AI (multi-platform LLM) cho sinh truyện và narrative

App dùng **LLMService** (config tại `config/llm.php`) với nhiều **driver**: OpenAI, Alibaba DashScope, v.v. Khi **chưa** cấu hình API key cho driver đang chọn, app dùng **FakeLLMService** (placeholder).

## Chọn platform (driver)

Trong `.env` đặt **`LLM_DRIVER`**: `openai` hoặc `alibaba`. Mọi platform dùng **cùng một bộ biến** (không đổi tên theo service):

| Biến | Mô tả |
|------|--------|
| `LLM_DRIVER` | `openai` \| `alibaba` (mặc định openai) |
| `LLM_API_KEY` | API key (OpenAI hoặc Alibaba) |
| `LLM_BASE_URL` | Endpoint (mặc định khác nhau theo driver) |
| `LLM_MODEL` | Model (vd: gpt-4-turbo-preview, qwen-turbo) |
| `LLM_TIMEOUT` | Timeout giây (mặc định 120) |
| `LLM_RESPONSE_FORMAT` | 1 = JSON, 0 = plain text |
| `LLM_ENABLE_THINKING` | Chỉ Alibaba: 0 = tắt deep thinking (mặc định) |

**Tương thích .env cũ:** Nếu chưa set `LLM_*`, app đọc fallback từ `OPENAI_*` (OPENAI_API_KEY, OPENAI_BASE_URL, ...).

## 1. Lấy API key

- Đăng ký / đăng nhập [OpenAI](https://platform.openai.com/).
- Vào [API keys](https://platform.openai.com/api-keys) → Create new secret key.
- Copy key (dạng `sk-...`).

## 2. Cấu hình trong backend

**Cách 1: File `.env` (khuyến nghị)**

Trong thư mục `src/backend`, tạo hoặc sửa file `.env` (copy từ `.env.example` nếu chưa có), thêm hoặc bỏ comment:

```env
LLM_DRIVER=openai
LLM_API_KEY=sk-proj-xxxxxxxxxxxxxxxx
LLM_MODEL=gpt-4-turbo-preview
```

**Cách 2: Biến môi trường (Docker)**

- Trong `docker-compose.yml`, phần `environment` của service backend: `LLM_DRIVER`, `LLM_API_KEY`, `LLM_MODEL`, ... Hoặc dùng `.env` và `env_file: .env`.

## 3. Biến môi trường (một bộ cho mọi platform)

| Biến | Mặc định | Mô tả |
|------|----------|--------|
| `LLM_API_KEY` | (trống) | Bắt buộc. Trống = dùng FakeLLM. |
| `LLM_MODEL` | theo driver | Model (gpt-4-turbo-preview / qwen-turbo...) |
| `LLM_BASE_URL` | theo driver | Endpoint API. |
| `LLM_TIMEOUT` | 120 | Timeout (giây). |
| `LLM_RESPONSE_FORMAT` | 1 (openai), 0 (alibaba) | 1 = JSON, 0 = plain text. |
| `LLM_ENABLE_THINKING` | 0 | Chỉ Alibaba: bật/tắt deep thinking. |
| `LLM_LOG_REQUESTS` | 0 | 1 = ghi toàn bộ request (system + user prompt) và response vào log để debug / cải thiện prompt. |

Có thể dùng tên cũ `OPENAI_*` (fallback) nếu chưa đổi sang `LLM_*`. Config: `config/llm.php`.

### Debug request tới AI (cải thiện prompt)

Để xem **toàn bộ request** gửi lên LLM (system prompt, user prompt, payload) và response:

1. Trong `.env` đặt **`LLM_LOG_REQUESTS=1`**.
2. Restart backend.
3. Thực hiện thao tác sinh chương (hoặc bất kỳ flow nào gọi LLM).
4. Mở `storage/logs/laravel-YYYY-MM-DD.log` và tìm dòng **`[LLM REQUEST]`** và **`[LLM RESPONSE]`**:
   - Request: `payload_meta`, `system_prompt`, `user_prompt`, và hai block `--- SYSTEM PROMPT ---` / `--- USER PROMPT ---` (full nội dung).
   - Response: `raw_preview` (2000 ký tự đầu), `raw_length`, `usage`.

API key **không** được ghi vào log, chỉ nội dung prompt và cấu trúc payload.

## 4. Kiểm tra

- Restart backend (hoặc container backend) sau khi sửa `.env`.
- Tạo series truyện dài kỳ và bấm **Sinh chương tiếp**: nếu cấu hình đúng, nội dung chương sẽ do OpenAI sinh (không còn dòng placeholder "[Placeholder — Chưa cấu hình OpenAI...]").

## 5. Dùng Alibaba DashScope (Tongyi)

1. Lấy API key từ [Alibaba Cloud Model Studio (DashScope)](https://www.aliyun.com/product/bailian) / [API-Keys](https://dashscope.console.aliyun.com/).
2. Trong `.env` đặt:

```env
LLM_DRIVER=alibaba
LLM_API_KEY=<key_từ_Alibaba>
LLM_BASE_URL=https://dashscope.aliyuncs.com/compatible-mode/v1
LLM_MODEL=qwen-turbo
LLM_TIMEOUT=120
```

- **Vùng:** Singapore `https://dashscope-intl.aliyuncs.com/compatible-mode/v1`, US Virginia `https://dashscope-us.aliyuncs.com/compatible-mode/v1`, Trung Quốc `https://dashscope.aliyuncs.com/compatible-mode/v1`.
- **Model:** `qwen-turbo`, `qwen-plus`, `qwen-max`, v.v.
- Nếu API báo lỗi liên quan `response_format` / `json_object`, hoặc chương sinh ra toàn số/vô nghĩa, đặt `LLM_RESPONSE_FORMAT=0` rồi restart backend.

**Deep thinking (Alibaba):** Các model Qwen hỗ trợ [deep thinking](https://www.alibabacloud.com/help/en/model-studio/deep-thinking). App mặc định gửi `enable_thinking: false`. Bật: đặt `LLM_ENABLE_THINKING=1` (response chậm hơn).

**Lỗi "Incorrect API key provided":** Key phải là key **DashScope** (từ Alibaba), không phải key OpenAI. Kiểm tra đã bật dịch vụ DashScope và copy đúng key trong console Alibaba.

**Lỗi "Operation timed out" (cURL 28):** Thử `LLM_TIMEOUT=180`; chạy `php artisan config:clear` rồi restart; hoặc đổi `LLM_BASE_URL` sang vùng khác (Singapore / Virginia / Trung Quốc).

## 6. Lưu ý

- **Bảo mật:** Không commit `.env` hoặc API key lên git. `.env` thường đã nằm trong `.gitignore`.
- **Testing:** Trong môi trường `testing`, app luôn dùng FakeLLMService (không gọi OpenAI) để test ổn định.
- **Lỗi:** Nếu key sai hoặc hết quota, log lỗi nằm ở `storage/logs/laravel-*.log` và response có thể throw exception.
