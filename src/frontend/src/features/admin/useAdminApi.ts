"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { adminApi } from "@/shared/api/admin";

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

export function useAdminToggleLock() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (id: number) => adminApi.toggleLock(id),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["admin", "universes"] });
      qc.invalidateQueries({ queryKey: ["admin", "stats"] });
    },
  });
}
