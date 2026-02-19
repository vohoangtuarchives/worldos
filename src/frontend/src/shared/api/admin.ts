import { api } from "./client";

export const adminApi = {
  stats: () => api.get<Record<string, unknown>>("/api/admin/stats"),
  universes: () => api.get<unknown[]>("/api/admin/universes"),
  toggleLock: (id: string) => api.post("/api/admin/universe/" + id + "/lock"),
};
