"use client";

/**
 * @deprecated Use useAuth().logout() from AuthProvider instead.
 */
export async function logout() {
  const { api, clearToken } = await import("@/shared/api/client");
  try {
    await api.post("/api/logout");
  } catch {
    // Swallow
  }
  clearToken();
  window.location.href = "/login";
}
