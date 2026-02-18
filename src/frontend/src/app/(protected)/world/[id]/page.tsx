"use client";

import { useParams, useSearchParams } from "next/navigation";
import { WorldHubView } from "@/features/writer/WorldHubView";
import { UniverseControlCenter } from "@/features/world/UniverseControlCenter";

export default function WorldOverviewPage() {
  const params = useParams();
  const searchParams = useSearchParams();
  const id = typeof params?.id === "string" ? params.id : "";
  const universeId = searchParams.get("universe");

  if (!id) return <p className="p-6 text-destructive">Invalid world.</p>;

  return (
    <div className="p-6 space-y-8">
      {universeId ? (
        <>
          <div className="flex items-center gap-4">
            <h1 className="text-2xl font-bold tracking-tight">Universe Inspection</h1>
            <Badge variant="secondary" className="font-mono">{universeId}</Badge>
          </div>
          <UniverseControlCenter worldId={id} universeId={universeId} />
          <hr className="border-border/50" />
          <div className="mt-8">
            <h3 className="text-lg font-bold mb-4">World Context</h3>
            <WorldHubView worldId={id} refetchInterval={8000} />
          </div>
        </>
      ) : (
        <WorldHubView worldId={id} refetchInterval={8000} />
      )}
    </div>
  );
}

import { Badge } from "@/components/ui/badge";
