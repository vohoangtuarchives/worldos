"use client";

import { useState } from "react";
import { useStoryBible } from "./useSerialApi";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { serialApi } from "@/shared/api/serial";
import { useMutation, useQueryClient } from "@tanstack/react-query";

export function StoryBibleView({ seriesId }: { seriesId: string }) {
  const qc = useQueryClient();
  const { data: bible, isLoading, error } = useStoryBible(seriesId);
  const [premise, setPremise] = useState("");
  const generate = useMutation({
    mutationFn: (body: { premise?: string }) =>
      serialApi.storyBible.generateFromPremise(seriesId, body),
    onSuccess: () =>
      qc.invalidateQueries({ queryKey: ["serial", "story-bible", seriesId] }),
  });
  if (isLoading) return <p className="text-muted-foreground">Loading…</p>;
  if (error) return <p className="text-destructive">Failed to load story bible.</p>;
  const content = bible && typeof bible === "object" ? bible : null;
  return (
    <div className="space-y-6">
      <Card>
        <CardHeader><CardTitle>Generate from premise</CardTitle></CardHeader>
        <CardContent className="flex flex-col gap-2">
          <Input placeholder="Premise (optional)" value={premise} onChange={(e) => setPremise(e.target.value)} />
          <Button size="sm" disabled={generate.isPending} onClick={() => generate.mutate({ premise: premise || undefined })}>
            {generate.isPending ? "Generating…" : "Generate"}
          </Button>
        </CardContent>
      </Card>
      {content && (
        <Card>
          <CardHeader><CardTitle>Story Bible</CardTitle></CardHeader>
          <CardContent>
            <pre className="max-h-96 overflow-auto rounded border border-border bg-muted/30 p-3 text-xs">
              {JSON.stringify(content, null, 2)}
            </pre>
          </CardContent>
        </Card>
      )}
    </div>
  );
}
