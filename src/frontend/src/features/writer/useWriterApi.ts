"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { writerApi } from "@/shared/api/writer";

export function useSagas() {
  return useQuery({
    queryKey: ["writer", "sagas"],
    queryFn: () => writerApi.sagas.list(),
  });
}

export function useSagaStats() {
  return useQuery({
    queryKey: ["writer", "sagas", "stats"],
    queryFn: () => writerApi.sagas.stats(),
  });
}

export function useSaga(sagaId: string | null, options?: { refetchInterval?: number }) {
  return useQuery({
    queryKey: ["writer", "saga", sagaId],
    queryFn: () => writerApi.sagas.show(sagaId!),
    enabled: sagaId != null,
    refetchInterval: options?.refetchInterval ?? 0,
  });
}

export function useSagaTree(id: string | null) {
  return useQuery({
    queryKey: ["writer", "saga", id, "tree"],
    queryFn: () => writerApi.sagas.tree(id!),
    enabled: id != null,
  });
}

export function useCreateSagaFromActive() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: () => writerApi.sagas.createFromActive(),
    onSuccess: () => qc.invalidateQueries({ queryKey: ["writer", "sagas"] }),
  });
}

export function useSagaAdvance(sagaId: string | null) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (ticks?: number) => writerApi.sagas.advance(sagaId!, ticks),
    onSuccess: (_, __, ___) => {
      if (sagaId) {
        qc.invalidateQueries({ queryKey: ["writer", "saga", sagaId] });
        qc.invalidateQueries({ queryKey: ["writer", "saga", sagaId, "tree"] });
        qc.invalidateQueries({ queryKey: ["writer", "sagas"] });
      }
    },
  });
}

export function useRunSaga() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (id: string) => writerApi.sagas.run(id),
    onSuccess: (_, id) => {
      qc.invalidateQueries({ queryKey: ["writer", "saga", id, "tree"] });
      qc.invalidateQueries({ queryKey: ["writer", "sagas"] });
    },
  });
}

export function useWorlds() {
  return useQuery({
    queryKey: ["writer", "worlds"],
    queryFn: () => writerApi.worlds.list(),
  });
}

export function useWorld(id: string | null, options?: { refetchInterval?: number }) {
  return useQuery({
    queryKey: ["writer", "worlds", id],
    queryFn: () => writerApi.worlds.show(id!),
    enabled: id != null,
    refetchInterval: options?.refetchInterval ?? 0,
  });
}

export function useWorldGodConsoleMetrics(worldId: string | null, options?: { refetchInterval?: number }) {
  return useQuery({
    queryKey: ["writer", "worlds", worldId, "god-console", "metrics"],
    queryFn: () => writerApi.worlds.getGodConsoleMetrics(worldId!),
    enabled: worldId != null,
    refetchInterval: options?.refetchInterval ?? 0,
  });
}

export function useUniverseSnapshots(universeId: string | null) {
  return useQuery({
    queryKey: ["writer", "universes", universeId, "snapshots"],
    queryFn: () => writerApi.universes.snapshots(universeId!),
    enabled: universeId != null,
  });
}

export function useUniverseMetrics(universeId: string | null, options?: { refetchInterval?: number }) {
  return useQuery({
    queryKey: ["writer", "universes", universeId, "metrics"],
    queryFn: () => writerApi.universes.metrics(universeId!),
    enabled: universeId != null,
    refetchInterval: options?.refetchInterval ?? 0,
  });
}

export function useUniverseFork() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ universeId, tick, sagaId }: { universeId: string, tick: number, sagaId?: string }) =>
      writerApi.universes.fork(universeId, tick, sagaId),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["writer", "sagas"] });
      qc.invalidateQueries({ queryKey: ["writer", "worlds"] });
    },
  });
}

export function useUniverseEvaluate() {
  return useMutation({
    mutationFn: (universeId: string) => writerApi.universes.evaluate(universeId),
  });
}

export function useUniverseApplyPressure() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ universeId, type, intensity }: { universeId: string, type: string, intensity: number }) =>
      writerApi.universes.applyPressure(universeId, type, intensity),
    onSuccess: (_, { universeId }) => {
      qc.invalidateQueries({ queryKey: ["writer", "universes", universeId, "metrics"] });
    },
  });
}

export function useWorldSnapshots(worldId: string | null) {
  return useQuery({
    queryKey: ["writer", "worlds", worldId, "snapshots"],
    queryFn: () => writerApi.worlds.snapshots.list(worldId!),
    enabled: worldId != null,
  });
}

export function useWorldSnapshotsCompare(worldId: string | null, yearA: number, yearB: number) {
  return useQuery({
    queryKey: ["writer", "worlds", worldId, "snapshots", "compare", yearA, yearB],
    queryFn: () => writerApi.worlds.snapshots.compare(worldId!, yearA, yearB),
    enabled: worldId != null && yearA !== yearB,
  });
}

export function useWorldSnapshotsCreate(worldId: string | null) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: () => writerApi.worlds.snapshots.create(worldId!),
    onSuccess: () => {
      if (worldId) qc.invalidateQueries({ queryKey: ["writer", "worlds", worldId, "snapshots"] });
    },
  });
}

export function useWorldEvents(worldId: string | null, params?: { page?: number; per_page?: number; type?: string }) {
  return useQuery({
    queryKey: ["writer", "worlds", worldId, "events", params?.page ?? 1, params?.per_page, params?.type],
    queryFn: () => writerApi.worlds.events.list(worldId!, params),
    enabled: worldId != null,
  });
}

export function useWorldHeroes(worldId: string | null) {
  return useQuery({
    queryKey: ["writer", "worlds", worldId, "heroes"],
    queryFn: () => writerApi.worlds.getHeroes(worldId!),
    enabled: worldId != null,
  });
}

export function useWorldEventsReplay(worldId: string | null) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (fromTick: number) => writerApi.worlds.events.replay(worldId!, fromTick),
    onSuccess: () => {
      if (worldId) qc.invalidateQueries({ queryKey: ["writer", "worlds", worldId, "events"] });
    },
  });
}

export function useWorldAction(
  action: "freeze" | "resume" | "step" | "rollback" | "createInstance"
) {
  const qc = useQueryClient();
  const fn =
    action === "createInstance"
      ? (worldId: string) => writerApi.worlds.createInstance(worldId)
      : action === "freeze"
        ? (worldId: string) => writerApi.worlds.freeze(worldId)
        : action === "resume"
          ? (worldId: string) => writerApi.worlds.resume(worldId)
          : action === "step"
            ? (worldId: string) => writerApi.worlds.step(worldId)
            : (worldId: string) => writerApi.worlds.rollback(worldId);
  return useMutation({
    mutationFn: fn,
    onSuccess: (_, worldId: string) => {
      qc.invalidateQueries({ queryKey: ["writer", "worlds"] });
      qc.invalidateQueries({ queryKey: ["writer", "worlds", worldId] });
    },
  });
}

export function useWorldEmergency() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ worldId, action, params }: { worldId: string; action: string; params?: Record<string, unknown> }) =>
      writerApi.worlds.emergency(worldId, action, params),
    onSuccess: (_, { worldId }) => {
      qc.invalidateQueries({ queryKey: ["writer", "worlds", worldId] });
      qc.invalidateQueries({ queryKey: ["writer", "worlds"] });
    },
  });
}

export function useGenesisPresets() {
  return useQuery({
    queryKey: ["writer", "genesis", "presets"],
    queryFn: () => writerApi.genesis.presets(),
  });
}

export function useCreateGenesis() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (body: { name: string; preset_key?: string;[key: string]: unknown }) =>
      writerApi.genesis.create(body),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["writer", "sagas"] });
      qc.invalidateQueries({ queryKey: ["writer", "worlds"] });
    },
  });
}

export function useAIMetrics(options?: { refetchInterval?: number }) {
  return useQuery({
    queryKey: ["writer", "ai", "metrics"],
    queryFn: () => writerApi.ai.getMetrics(),
    refetchInterval: options?.refetchInterval ?? 0,
  });
}

export function useAIGenerations(options?: { refetchInterval?: number }) {
  return useQuery({
    queryKey: ["writer", "ai", "generations"],
    queryFn: () => writerApi.ai.getGenerations(),
    refetchInterval: options?.refetchInterval ?? 0,
  });
}

export function useAIAgents(options?: { refetchInterval?: number }) {
  return useQuery({
    queryKey: ["writer", "ai", "agents"],
    queryFn: () => writerApi.ai.getAgents(),
    refetchInterval: options?.refetchInterval ?? 0,
  });
}

export function useAIIntervene() {
  return useMutation({
    mutationFn: ({ worldId, instruction }: { worldId: string, instruction: string }) =>
      writerApi.ai.intervene(worldId, instruction),
  });
}


export function useAIFeatureConfigs(options?: { refetchInterval?: number }) {
  return useQuery({
    queryKey: ["writer", "ai", "feature-configs"],
    queryFn: () => writerApi.ai.getFeatureConfigs(),
    refetchInterval: options?.refetchInterval ?? 0,
  });
}

export function useUpsertAIFeatureConfig() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (payload: { feature_key: string; agent_name: string; provider: string; model?: string; system_prompt?: string; temperature?: number; enabled?: boolean }) =>
      writerApi.ai.upsertFeatureConfig(payload),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["writer", "ai", "feature-configs"] });
    },
  });
}

export function useDeleteAIFeatureConfig() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (featureKey: string) => writerApi.ai.deleteFeatureConfig(featureKey),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["writer", "ai", "feature-configs"] });
    },
  });
}

export function useAIRequestLogFilters() {
  return useQuery({
    queryKey: ["writer", "ai", "request-logs", "filters"],
    queryFn: () => writerApi.ai.getRequestLogFilters(),
  });
}

export function useAIRequestLogs(params: { feature_key?: string; agent_name?: string; status?: string; per_page?: number; page?: number }) {
  return useQuery({
    queryKey: ["writer", "ai", "request-logs", params],
    queryFn: () => writerApi.ai.getRequestLogs(params),
  });
}

export function useAIRequestLogDetail(id?: string) {
  return useQuery({
    queryKey: ["writer", "ai", "request-log", id],
    queryFn: () => writerApi.ai.getRequestLogDetail(id as string),
    enabled: Boolean(id),
  });
}
