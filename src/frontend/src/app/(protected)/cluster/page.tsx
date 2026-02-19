import { CommandCenter } from "@/features/cluster/CommandCenter";

export default function ClusterPage() {
  return (
    <div className="space-y-6">
      <div className="flex flex-col gap-1">
        <h1 className="text-2xl font-bold tracking-tight text-foreground">WorldOS V3 Control Plane</h1>
        <p className="text-sm text-muted-foreground">Runtime-first dashboard cho điều phối world/universe theo kiến trúc V3.</p>
      </div>
      <CommandCenter />
    </div>
  );
}
