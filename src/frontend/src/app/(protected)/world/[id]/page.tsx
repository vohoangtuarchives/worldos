"use client";

import { useParams } from "next/navigation";
import { WorldHubView } from "@/features/writer/WorldHubView";

export default function WorldOverviewPage() {
  const params = useParams();
  const id = typeof params?.id === "string" ? params.id : "";
  if (!id) return <p className="p-6 text-destructive">Invalid world.</p>;
  return (
    <div className="p-6">
      <WorldHubView worldId={id} />
    </div>
  );
}
