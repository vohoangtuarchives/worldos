/**
 * API client with Sanctum Bearer-token authentication.
 *
 * Requests go directly to the backend URL (NEXT_PUBLIC_API_URL), no Next.js proxy.
 * Every authenticated request includes `Authorization: Bearer {token}`.
 */

const DEFAULT_API_PORT = "80";

function getApiBaseUrl(): string {
  if (typeof window === "undefined") return "";
  const env = process.env.NEXT_PUBLIC_API_URL?.trim();
  if (env) return env.replace(/\/$/, "");
  const { hostname, protocol } = window.location;
  if (hostname === "localhost" || hostname === "127.0.0.1") {
    return `${protocol}//${hostname}:${DEFAULT_API_PORT}`;
  }
  return window.location.origin;
}

/** Base URL for API (no trailing slash). Use with paths like "/api/login". */
export function getApiBase(): string {
  return getApiBaseUrl();
}

// ── Token storage (cookie — accessible by both JS and proxy.ts) ────

const TOKEN_COOKIE = "auth_token";

export function getToken(): string | null {
  if (typeof document === "undefined") return null;
  const match = document.cookie.match(
    new RegExp(`(?:^|;\\s*)${TOKEN_COOKIE}=([^;]*)`),
  );
  return match ? decodeURIComponent(match[1]) : null;
}

export function setToken(token: string): void {
  // 7-day expiry, accessible by JS and proxy.ts
  document.cookie = `${TOKEN_COOKIE}=${encodeURIComponent(token)}; path=/; max-age=${60 * 60 * 24 * 7}; SameSite=Lax`;
}

export function clearToken(): void {
  document.cookie = `${TOKEN_COOKIE}=; path=/; max-age=0; SameSite=Lax`;
}

// ── Generic fetch wrapper ──────────────────────────────────────────

export type ApiOptions = Omit<RequestInit, "body"> & {
  params?: Record<string, string>;
};

async function request<T = unknown>(
  path: string,
  options: ApiOptions & { body?: BodyInit | null } = {},
): Promise<T> {
  const { params, ...init } = options;

  const base = getApiBaseUrl();
  const pathNorm = path.startsWith("http") ? path : path.startsWith("/") ? path : `/${path}`;
  const url = pathNorm.startsWith("http") ? new URL(pathNorm) : new URL(base + pathNorm);
  if (params) {
    Object.entries(params).forEach(([k, v]) => url.searchParams.set(k, v));
  }

  const headers: Record<string, string> = {
    Accept: "application/json",
    ...(init.headers as Record<string, string>),
  };

  const method = (init.method ?? "GET").toUpperCase();
  if (!["GET", "HEAD", "OPTIONS"].includes(method)) {
    headers["Content-Type"] ??= "application/json";
  }

  // Attach Bearer token on every request
  const token = getToken();
  if (token) {
    headers["Authorization"] = `Bearer ${token}`;
  }

  const res = await fetch(url.toString(), {
    ...init,
    headers,
  });

  if (!res.ok) {
    const text = await res.text();
    let body: unknown;
    try {
      body = JSON.parse(text);
    } catch {
      body = { message: text };
    }
    throw Object.assign(new Error(`API ${res.status}`), {
      status: res.status,
      body,
    });
  }

  const ct = res.headers.get("content-type");
  if (ct?.includes("application/json")) return res.json() as Promise<T>;
  return res.text() as Promise<T>;
}

// ── Public API ─────────────────────────────────────────────────────

export const api = {
  get: <T = unknown>(path: string, opts?: ApiOptions) =>
    request<T>(path, { ...opts, method: "GET" }),

  post: <T = unknown>(path: string, body?: unknown, opts?: ApiOptions) =>
    request<T>(path, {
      ...opts,
      method: "POST",
      body: body != null ? JSON.stringify(body) : undefined,
    }),

  put: <T = unknown>(path: string, body?: unknown, opts?: ApiOptions) =>
    request<T>(path, {
      ...opts,
      method: "PUT",
      body: body != null ? JSON.stringify(body) : undefined,
    }),

  patch: <T = unknown>(path: string, body?: unknown, opts?: ApiOptions) =>
    request<T>(path, {
      ...opts,
      method: "PATCH",
      body: body != null ? JSON.stringify(body) : undefined,
    }),

  delete: <T = unknown>(path: string, opts?: ApiOptions) =>
    request<T>(path, { ...opts, method: "DELETE" }),
};
