"use client";

import Link from "next/link";
import { useSeriesList } from "./useSerialApi";
import { Card, CardHeader, CardTitle } from "@/components/ui/card";

export function SerialSeriesList() {
  const { data: series, isLoading, error } = useSeriesList();
  if (isLoading) return <p className="text-muted-foreground">Loading…</p>;
  if (error) return <p className="text-destructive">Failed to load series.</p>;
  if (!series?.length) return <p className="text-muted-foreground">Chưa có series nào. Hãy khởi tạo bằng Series Factory.</p>;
  return (
    <div className="grid gap-4 md:grid-cols-2">
      {series.map((s) => (
        <Card key={s.id}>
          <CardHeader className="pb-2">
            <CardTitle className="text-base">
              <Link href={"/serial/series/" + s.id} className="text-primary hover:underline">
                {s.title}
              </Link>
            </CardTitle>
            <p className="text-xs text-muted-foreground">
              {s.genre_key ?? "auto"} · Chapters: {s.total_chapters_generated ?? 0} · Universe: {s.universe_id ?? "unbound"}
            </p>
          </CardHeader>
        </Card>
      ))}
    </div>
  );
}
