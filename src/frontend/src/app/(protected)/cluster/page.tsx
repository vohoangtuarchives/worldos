import { ClusterOverview } from "@/features/cluster/ClusterOverview";

export default function ClusterPage() {
  return (
    <div className="p-6">
      <h1 className="mb-6 text-2xl font-semibold">Cluster</h1>
      <ClusterOverview />
    </div>
  );
}
