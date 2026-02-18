import { CommandCenter } from "@/features/cluster/CommandCenter";

export default function ClusterPage() {
  return (
    <div className="space-y-6">
      <div className="flex flex-col gap-1">
        <h1 className="text-2xl font-bold tracking-tight text-foreground">Trung Tâm Điều Hành</h1>
        <p className="text-sm text-muted-foreground uppercase tracking-wider font-medium">Reactor Control Cluster • Phase 32</p>
      </div>
      <CommandCenter />
    </div>
  );
}
