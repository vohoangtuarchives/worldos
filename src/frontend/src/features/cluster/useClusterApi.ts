"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { clusterApi } from "@/shared/api/cluster";

const REFETCH_INTERVAL_MS = 15_000;

export function useClusterSnapshot() {
  return useQuery({
    queryKey: ["cluster", "snapshot"],
    queryFn: () => clusterApi.snapshot(),
    refetchInterval: REFETCH_INTERVAL_MS,
  });
}

export function useClusterGovernor() {
  return useQuery({
    queryKey: ["cluster", "governor"],
    queryFn: () => clusterApi.governor(),
    refetchInterval: REFETCH_INTERVAL_MS,
  });
}

export function useClusterSystem() {
  return useQuery({
    queryKey: ["cluster", "system"],
    queryFn: () => clusterApi.system(),
    refetchInterval: REFETCH_INTERVAL_MS,
  });
}

export function useClusterEmergencyFreeze() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: () => clusterApi.emergencyFreeze(),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["cluster", "governor"] });
      qc.invalidateQueries({ queryKey: ["cluster", "snapshot"] });
    },
  });
}
