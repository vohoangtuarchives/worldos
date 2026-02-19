"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { adminApi, type AdminUniverseItem } from "@/shared/api/admin";

export type { AdminUniverseItem };

export function useAdminStats() {
  return useQuery({
    queryKey: ["admin", "stats"],
    queryFn: () => adminApi.stats(),
  });
}

export function useAdminUniverses() {
  return useQuery({
    queryKey: ["admin", "universes"],
    queryFn: () => adminApi.universes(),
  });
}

export function useAdminEvolutionOverview(options?: { refetchInterval?: number }) {
  return useQuery({
    queryKey: ["admin", "evolution", "overview"],
    queryFn: () => adminApi.evolutionOverview(),
    refetchInterval: options?.refetchInterval ?? 10000,
  });
}

export function useAdminToggleAIEvolution() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (enabled: boolean) => adminApi.toggleAIEvolution(enabled),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["admin", "evolution", "overview"] });
      qc.invalidateQueries({ queryKey: ["admin", "stats"] });
    },
  });
}
