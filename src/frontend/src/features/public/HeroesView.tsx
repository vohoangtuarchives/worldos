"use client";

import { useQuery } from "@tanstack/react-query";
import { publicApi } from "@/shared/api/public";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

export function HeroesView() {
  const { data: heroes, isLoading, error } = useQuery({
    queryKey: ["public", "heroes"],
    queryFn: () => publicApi.heroes.list(),
  });
  if (isLoading) return <p className="text-muted-foreground">Loading…</p>;
  if (error) return <p className="text-destructive">Failed to load heroes.</p>;
  const list = Array.isArray(heroes) ? heroes : [];
  if (!list.length) return <p className="text-muted-foreground">No heroes.</p>;
  return (
    <div className="grid gap-4 md:grid-cols-2">
      {list.slice(0, 20).map((h) => (
        <Card key={h.id}>
          <CardHeader className="pb-2">
            <CardTitle className="text-base">{String(h.name ?? "Hero #" + h.id)}</CardTitle>
          </CardHeader>
          <CardContent className="text-sm text-muted-foreground">
            Read-only catalog.
          </CardContent>
        </Card>
      ))}
      {list.length > 20 && <p className="text-sm text-muted-foreground">+ {list.length - 20} more.</p>}
    </div>
  );
}
