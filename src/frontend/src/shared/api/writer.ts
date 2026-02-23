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
  config?: Record<string, any>;
  gene_vector?: Record<string, any>;
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
  id: string;
  world_id: string;
  year: number;
  entropy?: number;
  stability?: number;
  energy?: number;
  created_at?: string;
};

export type WorldSnapshotPayload = {
  id: string;
  year: number;
  entropy?: number;
  stability?: number;
  energy?: number;
  tension?: number;
  resonance?: number;
};

export type WorldEventItem = {
  id: string;
  tick: number;
  type: string;
  payload?: unknown;
  created_at?: string;
};

export type WorldHero = {
  id: string;
  world_id: string;
  name: string;
  other_names?: string[];
  archetype: string;
  dimensions?: Record<string, number>;
  impact_score: number;
  biography?: string;
  era?: string;
  is_generated: boolean;
  status: string;
  spawned_at_tick: number;
  created_at?: string;
};

export type GenesisPreset = { id: string; name: string;[key: string]: unknown };

/** Material template (wiki entry). */
export type MaterialTemplate = {
  id: string;
  code: string;
  ontology: string;
  function: string;
  default_lifecycle?: string | null;
  preconditions?: string[];
  incompatible_with?: string[];
  mutation_axes?: string[];
};

/** Material instance in a world. */
export type MaterialInstanceItem = {
  id: string;
  material_code: string;
  ontology?: string;
  function?: string;
  strength_level: number;
  degradation_level?: number;
  activation_epoch?: number | null;
  is_active: boolean;
  is_retired: boolean;
  mutation_state?: Record<string, unknown>;
  historical_traces?: unknown[];
};

/** Mutation pathway. */
export type MutationPathway = {
  target_code: string;
  trigger_condition: string;
  strength_transfer: number;
  description: string;
};

/** Material detail response. */
export type MaterialDetail = MaterialTemplate & {
  pressure_inputs?: Record<string, unknown>;
  pressure_outputs?: Record<string, unknown>;
  mutation_pathways?: MutationPathway[];
  affinity?: { archetypes?: string[]; drift_modifier?: number; activation_threshold?: number; character_archetypes?: string[] };
  usage?: { total_instances: number; active_instances: number };
};

/** Material catalog response. */
export type MaterialCatalog = {
  catalog: Record<string, Record<string, MaterialTemplate[]>>;
  totals: { materials: number; by_ontology: Record<string, number>; by_function: Record<string, number> };
};

/** Material timeline event. */
export type MaterialTimelineEvent = {
  type: 'activation' | 'mutation' | 'deactivation';
  epoch: number;
  material_code?: string;
  description: string;
  from?: string;
  to?: string;
  pathway?: string;
  icon?: string;
  timestamp?: string;
};



type AIFeatureConfig = {
  id: string;
  feature_key: string;
  agent_name: string;
  provider: string;
  model: string | null;
  system_prompt: string | null;
  options?: { temperature?: number };
  enabled: boolean;
  created_at?: string;
  updated_at?: string;
};

type AIRequestLogItem = {
  id: string;
  provider: string;
  model: string | null;
  feature_key: string | null;
  agent_name: string | null;
  status: string;
  http_status: number | null;
  duration_ms: number | null;
  created_at: string;
};

type AIRequestLogDetail = AIRequestLogItem & {
  system_prompt?: string | null;
  user_prompt?: string | null;
  request_payload?: string | null;
  response_payload?: string | null;
};

export const writerApi = {
  sagas: {
    list: () => api.get<Saga[]>("/api/writer/sagas"),
    create: (data: { name: string }) => api.post<{ id: string; name: string }>("/api/v4/tuzy/sagas", data),
    stats: () => api.get<{ success: boolean; data: Record<string, unknown> }>("/api/writer/sagas/stats").then(r => r.data),
    show: (sagaId: string) => api.get<SagaDetail>(`/api/writer/sagas/${sagaId}`),
    createFromActive: (body?: { universe_id?: string }) => api.post<Saga>("/api/writer/sagas/create-from-active", body),
    tree: (id: string) => api.get<{ nodes: SagaTreeNode[] }>(`/api/writer/saga/${id}/tree`),
    advance: (sagaId: string, ticks?: number) =>
      api.post<{ success: boolean; message: string; ticks: number }>(`/api/writer/saga/${sagaId}/advance`, { ticks: ticks ?? 10 }),
    run: (id: string) => api.post(`/api/writer/saga/${id}/run`),
  },
  universes: {
    list: () => api.get<Universe[]>("/api/writer/universes"),
    create: (data: { name: string; world_id: string; saga_id: string }) =>
      api.post<{ id: string; name: string }>("/api/v4/tuzy/universes", data),
    snapshots: (universeId: string) =>
      api.get<{ success: boolean; data: { snapshots: UniverseSnapshotItem[] } }>(`/api/writer/universes/${universeId}/snapshots`).then((r) => r.data?.snapshots ?? []),
    metrics: (universeId: string) =>
      api.get<{ age: number; state_vector: Record<string, number>; entropy?: number | null; stability_index?: number | null; status: string }>(
        `/api/v4/tuzy/universes/${universeId}`
      ).then(u => ({
        ...u,
        tick: u.age, // Backward compatibility for UI
        phase: u.status // Backward compatibility for UI
      })),
    fork: (universeId: string, tick: number, sagaId?: string) =>
      api.post<{ success: boolean; message: string; data: { id: string, name: string } }>(`/api/writer/universes/${universeId}/fork`, { tick, saga_id: sagaId }),
    evaluate: (universeId: string) =>
      api.post<{ success: boolean; data: { recommendation: string; ip_score: number; suggestion?: { type: string; intensity: number } } }>(`/api/writer/universes/${universeId}/evaluate`),
    update: (id: string, data: {
      name: string;
      age: number;
      status: string;
      entropy: number;
      stability_index: number;
    }) => api.patch<{ success: boolean }>(`/api/v4/tuzy/universes/${id}`, data),
    delete: (id: string) => api.delete<{ success: boolean }>(`/api/v4/tuzy/universes/${id}`),
    applyPressure: (universeId: string, type: string, intensity: number) =>
      api.post<{ success: boolean; message: string }>(`/api/writer/universes/${universeId}/pressure`, { type, intensity }),
    style: (universeId: string) =>
      api.get<{ success: boolean; data: { id: string; name: string; style_vector: Record<string, number>; version: number } }>(`/api/writer/universes/${universeId}/style`).then(r => r.data),
  },
  governance: {
    proposals: (worldId: string) =>
      api.get<{ success: boolean; data: any[] }>(`/api/writer/governance/proposals/${worldId}`).then(r => r.data),
    approve: (id: string) =>
      api.post<{ success: boolean; message: string }>(`/api/writer/governance/proposals/${id}/approve`),
    reject: (id: string) =>
      api.post<{ success: boolean; message: string }>(`/api/writer/governance/proposals/${id}/reject`),
  },
  worlds: {
    list: () => api.get<World[]>("/api/v4/tuzy/worlds"),
    show: (id: string) => api.get<WorldDetail>(`/api/v4/tuzy/worlds/${id}`),
    create: (data: { name: string; preset: string; origin_type: string }) =>
      api.post<{ id: string; name: string }>("/api/v4/tuzy/worlds", data),
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
    emergency: (id: string, action: string, params?: Record<string, unknown>) =>
      api.post<{ success: boolean; message: string; universe_id?: string }>(`/api/writer/worlds/${id}/emergency/${action}`, params),
    update: (id: string, data: {
      name: string;
      status: string;
      health_status: string;
      current_tick: number;
      origin_type: string;
      preset: string;
    }) => api.patch<{ success: boolean }>(`/api/v4/tuzy/worlds/${id}`, data),
    delete: (id: string) => api.delete<{ success: boolean }>(`/api/v4/tuzy/worlds/${id}`),
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
    getHeroes: (id: string) => api.get<{ data: WorldHero[] }>(`/api/writer/worlds/${id}/heroes`).then(r => r.data),
  },
  materials: {
    catalog: () =>
      api.get<{ success: boolean; data: MaterialCatalog }>("/api/writer/materials/catalog").then(r => r.data),
    detail: (code: string) =>
      api.get<{ success: boolean; data: MaterialDetail }>(`/api/writer/materials/${code}/detail`).then(r => r.data),
    worldInstances: (worldId: string) =>
      api.get<{ success: boolean; data: { world_id: string; world_name: string; instances: MaterialInstanceItem[]; lifecycle: Record<string, number>; total: number } }>(`/api/writer/worlds/${worldId}/materials`).then(r => r.data),
    worldAnalytics: (worldId: string) =>
      api.get<{ success: boolean; data: Record<string, unknown> }>(`/api/writer/worlds/${worldId}/materials/analytics`).then(r => r.data),
    timeline: (worldId: string) =>
      api.get<{ events: MaterialTimelineEvent[] }>(`/api/writer/worlds/${worldId}/materials/timeline`),
    activate: (worldId: string, materialId: string, strengthLevel: number) =>
      api.post(`/api/writer/worlds/${worldId}/materials/activate`, { material_id: materialId, strength_level: strengthLevel }),
    adjustStrength: (instanceId: string, strengthLevel: number) =>
      api.patch(`/api/writer/materials/${instanceId}/strength`, { strength_level: strengthLevel }),
    retire: (instanceId: string) =>
      api.post(`/api/writer/materials/${instanceId}/retire`),
  },
  genesis: {
    presets: () => api.get<{ data: any[] }>("/api/v4/tuzy/genesis/presets").then(r => r.data),
  },
  ai: {
    getMetrics: () =>
      api.get<{ success: boolean; data: { tokens: { prompt: number; completion: number; total: number }; estimated_cost_usd: number; generations_count: number; success_rate: number } }>("/api/writer/ai/metrics").then(r => r.data),
    getGenerations: () =>
      api.get<{ success: boolean; data: Array<Record<string, unknown>> }>("/api/writer/ai/generations").then(r => r.data),
    getAgents: () =>
      api.get<{ success: boolean; data: { summary: Array<Record<string, unknown>>; roster: Array<Record<string, unknown>> } }>("/api/writer/ai/agents").then(r => r.data),
    getFeatureConfigs: () =>
      api.get<{ success: boolean; data: AIFeatureConfig[] }>("/api/writer/ai/feature-configs").then(r => r.data),
    upsertFeatureConfig: (payload: { feature_key: string; agent_name: string; provider: string; model?: string; system_prompt?: string; temperature?: number; enabled?: boolean }) =>
      api.post<{ success: boolean; message: string; data: AIFeatureConfig }>("/api/writer/ai/feature-configs", payload).then(r => r.data),
    deleteFeatureConfig: (featureKey: string) =>
      api.delete<{ success: boolean; deleted: boolean; feature_key: string }>(`/api/writer/ai/feature-configs/${encodeURIComponent(featureKey)}`),
    getRequestLogFilters: () =>
      api.get<{ success: boolean; data: { feature_keys: string[]; agent_names: string[]; statuses: string[] } }>("/api/writer/ai/request-logs/filters").then(r => r.data),
    getRequestLogs: (params?: { feature_key?: string; agent_name?: string; status?: string; per_page?: number; page?: number }) => {
      const sp = new URLSearchParams();
      if (params?.feature_key) sp.set('feature_key', params.feature_key);
      if (params?.agent_name) sp.set('agent_name', params.agent_name);
      if (params?.status) sp.set('status', params.status);
      if (params?.per_page != null) sp.set('per_page', String(params.per_page));
      if (params?.page != null) sp.set('page', String(params.page));
      const q = sp.toString();
      return api.get<{ success: boolean; data: { data: AIRequestLogItem[]; current_page: number; last_page: number; total: number } }>(`/api/writer/ai/request-logs${q ? `?${q}` : ''}`).then(r => r.data);
    },
    getRequestLogDetail: (id: string) =>
      api.get<{ success: boolean; data: AIRequestLogDetail }>(`/api/writer/ai/request-logs/${id}`).then(r => r.data),
    intervene: (worldId: string, instruction: string) =>
      api.post<{ success: boolean; message: string }>("/api/writer/ai/intervene", { world_id: worldId, instruction }),
  },
};
