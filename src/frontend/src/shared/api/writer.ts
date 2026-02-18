import { api } from "./client";

/** Saga id is always UUID (string). */
export type Saga = {
  id: string;
  name: string;
  status: string;
  world_count?: number;
  saga_worlds_count?: number;
  current_universe_id?: string | null;
  created_at?: string;
  updated_at?: string;
};

export type SagaWorld = {
  id: string;
  world_id: string | null;
  world_name: string;
  universe_id: string | null;
  sequence: number;
  status: string;
  universe_age: number | null;
  universe_entropy: number | null;
  universe_stability_index: number | null;
  universe_status: string | null;
};

export type SagaDetail = Saga & {
  saga_worlds: SagaWorld[];
};

export type SagaTreeNode = {
  id: string;
  parentId: string | null;
  name: string;
  current_era?: string;
  status?: string;
  has_collapsed?: boolean;
  sequence?: number;
  universe_id?: string | null;
  age?: number | null;
  universe_status?: string | null;
};

/** World id: string (UUID or numeric string from API). */
export type World = {
  id: string;
  name: string;
  health_status?: string;
  status?: string;
  current_tick?: number;
  preset?: string;
  genre?: string;
  created_at?: string;
  updated_at?: string;
};

/** Universe = runtime instance of a World (v3). */
export type Universe = {
  id: string;
  name: string;
  age: number;
  state_vector?: Record<string, unknown>;
  entropy?: number | null;
  stability_index?: number | null;
  status?: string;
  is_archived?: boolean;
  created_at?: string;
};

export type UniverseSnapshotItem = {
  id: string;
  universe_id: string;
  tick: number;
  state_vector?: Record<string, unknown>;
  entropy?: number | null;
  stability_index?: number | null;
  metrics?: Record<string, unknown> | null;
  created_at?: string;
};

export type WorldDetail = World & {
  runtime_instances?: Universe[];
};

export type WorldSnapshotItem = {
  id: number;
  world_id: number;
  year: number;
  entropy?: number;
  stability?: number;
  energy?: number;
  created_at?: string;
};

export type WorldSnapshotPayload = {
  id: number;
  year: number;
  entropy?: number;
  stability?: number;
  energy?: number;
  tension?: number;
  resonance?: number;
};

export type WorldEventItem = {
  id: number;
  tick: number;
  type: string;
  payload?: unknown;
  created_at?: string;
};

export type GenesisPreset = { id: string; name: string;[key: string]: unknown };

export const writerApi = {
  sagas: {
    list: () => api.get<Saga[]>("/api/writer/sagas"),
    stats: () => api.get<{ success: boolean; data: any }>("/api/writer/sagas/stats").then(r => r.data),
    show: (sagaId: string) => api.get<SagaDetail>(`/api/writer/sagas/${sagaId}`),
    createFromActive: () => api.post<Saga>("/api/writer/sagas/create-from-active"),
    tree: (id: string) => api.get<{ nodes: SagaTreeNode[] }>(`/api/writer/saga/${id}/tree`),
    advance: (sagaId: string, ticks?: number) =>
      api.post<{ success: boolean; message: string; ticks: number }>(`/api/writer/saga/${sagaId}/advance`, { ticks: ticks ?? 10 }),
    run: (id: string) => api.post(`/api/writer/saga/${id}/run`),
  },
  universes: {
    snapshots: (universeId: string) =>
      api.get<{ success: boolean; data: { snapshots: UniverseSnapshotItem[] } }>(`/api/writer/universes/${universeId}/snapshots`).then((r) => r.data?.snapshots ?? []),
    metrics: (universeId: string) =>
      api.get<{ tick: number; state_vector: Record<string, number>; entropy?: number | null; stability_index?: number | null; phase: string }>(
        `/api/writer/universes/${universeId}/metrics`
      ),
    fork: (universeId: string, tick: number, sagaId?: string) =>
      api.post<{ success: boolean; message: string; data: { id: string, name: string } }>(`/api/writer/universes/${universeId}/fork`, { tick, saga_id: sagaId }),
    evaluate: (universeId: string) =>
      api.post<{ success: boolean; data: { recommendation: string; ip_score: number; suggestion?: { type: string; intensity: number } } }>(`/api/writer/universes/${universeId}/evaluate`),
    applyPressure: (universeId: string, type: string, intensity: number) =>
      api.post<{ success: boolean; message: string }>(`/api/writer/universes/${universeId}/pressure`, { type, intensity }),
  },
  worlds: {
    list: () => api.get<World[]>("/api/writer/worlds"),
    show: (id: string) => api.get<WorldDetail>(`/api/writer/worlds/${id}`),
    createInstance: (id: string) => api.post(`/api/writer/worlds/${id}/instances`),
    freeze: (id: string) => api.post(`/api/writer/worlds/${id}/freeze`),
    resume: (id: string) => api.post(`/api/writer/worlds/${id}/resume`),
    step: (id: string) => api.post(`/api/writer/worlds/${id}/step`),
    rollback: (id: string) => api.post(`/api/writer/worlds/${id}/rollback`),
    getGodConsoleMetrics: (id: string) =>
      api.get<{ tick: number; state_vector: Record<string, number>; phase: string }>(
        `/api/writer/worlds/${id}/god-console/metrics`
      ),
    intervene: (id: string, body: { action: string }) =>
      api.post(`/api/writer/worlds/${id}/god-console/intervene`, body),
    snapshots: {
      list: (id: string) =>
        api.get<{ success: boolean; data: { snapshots: WorldSnapshotItem[] } }>(`/api/writer/worlds/${id}/snapshots`).then((r) => r.data?.snapshots ?? []),
      compare: (id: string, yearA: number, yearB: number) =>
        api.get<{ success: boolean; data: { snapshot_a: WorldSnapshotPayload | null; snapshot_b: WorldSnapshotPayload | null } }>(
          `/api/writer/worlds/${id}/snapshots/compare?year_a=${yearA}&year_b=${yearB}`
        ).then((r) => r.data),
      create: (id: string) => api.post(`/api/writer/worlds/${id}/snapshots`),
    },
    events: {
      list: (id: string, params?: { page?: number; per_page?: number; type?: string }) => {
        const sp = new URLSearchParams();
        if (params?.page != null) sp.set("page", String(params.page));
        if (params?.per_page != null) sp.set("per_page", String(params.per_page));
        if (params?.type != null) sp.set("type", params.type);
        const q = sp.toString();
        return api.get<{
          success: boolean;
          data: { events: WorldEventItem[]; meta: { current_page: number; per_page: number; total: number } };
        }>(`/api/writer/worlds/${id}/events${q ? `?${q}` : ""}`).then((r) => r.data);
      },
      replay: (id: string, fromTick: number) =>
        api.post(`/api/writer/worlds/${id}/events/replay`, { from_tick: fromTick }),
    },
  },
  genesis: {
    presets: () => api.get<{ categories?: Record<string, GenesisPreset[]> }>("/api/writer/genesis/presets"),
    create: (body: { name: string; preset_key?: string;[key: string]: unknown }) =>
      api.post<{ saga_id: string; name: string; message: string }>("/api/writer/genesis", body),
  },
  ai: {
    getMetrics: () =>
      api.get<{ success: boolean; data: { tokens: { prompt: number; completion: number; total: number }; estimated_cost_usd: number; generations_count: number; success_rate: number } }>("/api/writer/ai/metrics").then(r => r.data),
    getGenerations: () =>
      api.get<{ success: boolean; data: any[] }>("/api/writer/ai/generations").then(r => r.data),
    getAgents: () =>
      api.get<{ success: boolean; data: { summary: any[]; roster: any[] } }>("/api/writer/ai/agents").then(r => r.data),
    intervene: (worldId: string, instruction: string) =>
      api.post<{ success: boolean; message: string }>("/api/writer/ai/intervene", { world_id: worldId, instruction }),
  },
};
