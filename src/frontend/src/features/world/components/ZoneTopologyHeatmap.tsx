"use client";

import { useQuery } from "@tanstack/react-query";
import { AlertTriangle, Map } from "lucide-react";

interface TopologyData {
  experiment_id: string;
  tick: number;
  global_entropy: number;
  topology?: {
    zones: Record<string, {
      material_stress: number;
      owner_regime?: number;
      material: {
          entropy: number;
          structured_mass: number;
          free_energy: number;
      }
    }>;
  };
}

export function ZoneTopologyHeatmap({ universeId }: { universeId: string }) {
  const { data, isLoading, error } = useQuery<{ success: boolean; data: TopologyData }>({
    queryKey: ["zone-culture-map", universeId],
    queryFn: async () => {
      const res = await fetch(`http://localhost:8000/api/simulation/universes/${universeId}/zone-culture-map`);
      if (!res.ok) throw new Error("Network response was not ok");
      return res.json();
    },
    enabled: !!universeId,
    refetchInterval: 4000,
  });

  if (isLoading) return <div className="animate-pulse h-32 bg-slate-800/20 rounded-xl" />;
  if (error || !data?.success) return <div className="text-red-500 text-xs">Failed to load Topology Map</div>;

  const topology = data.data.topology;
  const zones = topology?.zones ? Object.entries(topology.zones) : [];

  return (
    <div className="glass-card flex flex-col h-full overflow-hidden mt-6">
      <div className="px-6 py-4 border-b border-border/50 flex items-center justify-between bg-white/40">
        <h3 className="font-bold text-sm uppercase tracking-wider flex items-center gap-2">
          <Map className="h-4 w-4 text-orange-500" />
          Physical Zone Topology (Material Stress Heatmap)
        </h3>
        <span className="text-xs font-mono text-muted-foreground bg-slate-900/10 px-2 py-1 rounded">
          Global Entropy: {data.data.global_entropy.toFixed(4)}
        </span>
      </div>
      <div className="p-6">
        {zones.length > 0 ? (
          <div className="grid grid-cols-5 md:grid-cols-8 gap-2">
            {zones.map(([id, zone]) => {
              const stress = Math.max(0, Math.min(1, zone.material_stress || 0));
              // Color scale from green (0) to red (1)
              const hue = ((1 - stress) * 120).toString(10);
              
              return (
                <div
                  key={id}
                  className="group relative aspect-square rounded border border-white/10 overflow-hidden cursor-crosshair transition-all hover:scale-105"
                  style={{ backgroundColor: `hsl(${hue}, 80%, 40%)` }}
                >
                  {zone.owner_regime === null && (
                    <div className="absolute inset-0 flex items-center justify-center">
                       <AlertTriangle className="text-white w-4 h-4 opacity-50" />
                    </div>
                  )}
                  {/* Tooltip */}
                  <div className="absolute opacity-0 group-hover:opacity-100 transition-opacity bg-black/90 text-white p-2 rounded text-[10px] bottom-full left-1/2 -translate-x-1/2 mb-1 pointer-events-none z-10 w-32">
                    <p className="font-bold border-b border-white/20 pb-1 mb-1">Zone {id.slice(0, 4)}</p>
                    <p>Stress: {stress.toFixed(2)}</p>
                    <p>Entropy: {(zone.material?.entropy || 0).toFixed(2)}</p>
                    <p>Regime: {zone.owner_regime ?? "Rebellion"}</p>
                  </div>
                </div>
              );
            })}
          </div>
        ) : (
          <div className="text-xs text-muted-foreground text-center py-8">
            The zone topology matrix is empty. Waiting for civilization to expand...
          </div>
        )}
      </div>
    </div>
  );
}
