"use client";

import Link from "next/link";
import { useSeries, useGenerateNextChapter, useGenerateOutline, useGenerateChapters } from "./useSerialApi";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

export function SeriesDetailView({ seriesId }: { seriesId: string }) {
  const { data: series, isLoading, error } = useSeries(seriesId);
  const genChapter = useGenerateNextChapter(seriesId);
  const genBatch = useGenerateChapters(seriesId);
  const genOutline = useGenerateOutline(seriesId);
  if (isLoading) return <p className="text-muted-foreground">Loading…</p>;
  if (error) return <p className="text-destructive">Failed to load series.</p>;
  if (!series) return null;
  const chapters = (series as { chapters?: { id: string; chapter_index: number; title?: string }[] }).chapters ?? [];
  return (
    <div className="space-y-6">
      <Card>
        <CardHeader>
          <CardTitle>{series.title}</CardTitle>
          <p className="text-sm text-muted-foreground">
            Genre: {series.genre_key ?? "auto"} · Chapters: {series.total_chapters_generated ?? 0} · Universe: {series.universe_id ?? "unbound"}
          </p>
        </CardHeader>
        <CardContent className="flex flex-wrap gap-2">
          <Button size="sm" disabled={genChapter.isPending} onClick={() => genChapter.mutate()}>
            Sinh chương tiếp (v3)
          </Button>
          <Button size="sm" variant="outline" disabled={genOutline.isPending} onClick={() => genOutline.mutate()}>
            Sinh outline
          </Button>
          <Button size="sm" variant="secondary" disabled={genBatch.isPending} onClick={() => genBatch.mutate(3)}>
            Batch Sinh (v3 - 3 ch.)
          </Button>
          <Button size="sm" variant="secondary" disabled={genBatch.isPending} onClick={() => genBatch.mutate(5)}>
            Batch Sinh (v3 - 5 ch.)
          </Button>
          <Button size="sm" variant="outline" asChild>
            <Link href={"/serial/series/" + seriesId + "/story-bible"}>Story Bible</Link>
          </Button>
        </CardContent>
      </Card>
      {chapters.length > 0 && (
        <Card>
          <CardHeader><CardTitle>Chapters</CardTitle></CardHeader>
          <CardContent>
            <ul className="list-disc list-inside text-sm">
              {chapters.map((c) => (
                <li key={c.id}>
                  <Link href={"/serial/series/" + seriesId + "/chapters/" + c.id} className="text-primary hover:underline">
                    Ch. {c.chapter_index}: {c.title ?? "Untitled"}
                  </Link>
                </li>
              ))}
            </ul>
          </CardContent>
        </Card>
      )}
    </div>
  );
}
