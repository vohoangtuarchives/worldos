import { api } from "./client";

export type ClusterWorldSnapshot = {
  id: string;
  name: string;
  status: string | null;
  health_status?: string;
  current_tick: number;
  entropy: number | null;
  stability: number | null;
  preset?: string | null;
  genre?: string | null;
  updated_at: string | null;
};

export type ClusterStats = {
  total: number;
  running: number;
};

export type ClusterSnapshotResponse = {
  success: boolean;
  data: {
    worlds: ClusterWorldSnapshot[];
    clusterStats: ClusterStats;
  };
};

export type GovernorState = {
  pressureScore: number;
  throttleLevel: string;
  emergencyMode: boolean;
  costBurnRate: number | null;
};

export type SystemState = {
  cpuPercent: number | null;
  memoryPercent: number | null;
  queueLength: number | null;
};

export const clusterApi = {
  snapshot: () =>
    api
      .get<ClusterSnapshotResponse>("/api/cluster/snapshot")
      .then((r) => r.data),

  governor: () =>
    api
      .get<{ success: boolean; data: GovernorState }>("/api/cluster/governor")
      .then((r) => r.data),

  system: () =>
    api
      .get<{ success: boolean; data: SystemState }>("/api/cluster/system")
      .then((r) => r.data),

  emergencyFreeze: () =>
    api.post<{ success: boolean; message?: string }>("/api/cluster/emergency-freeze"),
};
