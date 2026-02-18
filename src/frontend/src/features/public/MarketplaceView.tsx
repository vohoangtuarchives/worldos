"use client";

import { useQuery } from "@tanstack/react-query";
import { publicApi } from "@/shared/api/public";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

export function MarketplaceView() {
  const { data: artifacts, isLoading, error } = useQuery({
    queryKey: ["public", "marketplace", "artifacts"],
    queryFn: () => publicApi.marketplace.artifacts(),
  });
  if (isLoading) return <p className="text-muted-foreground">Loading…</p>;
  if (error) return <p className="text-destructive">Failed to load artifacts.</p>;
  const list = Array.isArray(artifacts) ? artifacts : [];
  if (!list.length) return <p className="text-muted-foreground">No artifacts.</p>;
  return (
    <div className="grid gap-4 md:grid-cols-2">
      {list.map((a: { id: number; name?: string }) => (
        <Card key={a.id}>
          <CardHeader className="pb-2">
            <CardTitle className="text-base">{String(a.name ?? "Artifact #" + a.id)}</CardTitle>
          </CardHeader>
          <CardContent className="text-sm text-muted-foreground">Sign in to infuse.</CardContent>
        </Card>
      ))}
    </div>
  );
}
