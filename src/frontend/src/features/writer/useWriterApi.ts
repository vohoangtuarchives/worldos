"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { writerApi } from "@/shared/api/writer";

export function useSagas() {
  return useQuery({
    queryKey: ["writer", "sagas"],
    queryFn: () => writerApi.sagas.list(),
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

export function useWorld(id: string | null) {
  return useQuery({
    queryKey: ["writer", "worlds", id],
    queryFn: () => writerApi.worlds.show(id!),
    enabled: id != null,
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

export function useGenesisPresets() {
  return useQuery({
    queryKey: ["writer", "genesis", "presets"],
    queryFn: () => writerApi.genesis.presets(),
  });
}

export function useCreateGenesis() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (body: { name: string; preset_key?: string }) =>
      writerApi.genesis.create(body),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["writer", "sagas"] });
      qc.invalidateQueries({ queryKey: ["writer", "worlds"] });
    },
  });
}
