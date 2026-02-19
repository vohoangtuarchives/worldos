import { api } from "./client";

export const adminApi = {
  stats: () => api.get<Record<string, unknown>>("/api/admin/stats"),
  evolutionOverview: () =>
    api.get<{ success: boolean; data: { generations_per_hour: number; collapse_rate_percent: number; frontier_size: number; ai_enabled: boolean; updated_at: string } }>("/api/admin/evolution/overview"),
  toggleAIEvolution: (enabled: boolean) =>
    api.post<{ success: boolean; data: { ai_enabled: boolean }; message: string }>("/api/admin/evolution/ai-toggle", { enabled }),
  universes: () => api.get<unknown[]>("/api/admin/universes"),
  toggleLock: (id: string) => api.post("/api/admin/universe/" + id + "/lock"),
};
