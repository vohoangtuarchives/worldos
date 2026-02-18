"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { serialApi } from "@/shared/api/serial";

export function useSeriesList() {
  return useQuery({
    queryKey: ["serial", "series"],
    queryFn: () => serialApi.series.list(),
  });
}

export function useSeries(id: number | null) {
  return useQuery({
    queryKey: ["serial", "series", id],
    queryFn: () => serialApi.series.show(id!),
    enabled: id != null,
  });
}

export function useCreateSeries() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (body: { title: string; genre_key?: string; universe_id?: string }) =>
      serialApi.series.create(body),
    onSuccess: () => qc.invalidateQueries({ queryKey: ["serial", "series"] }),
  });
}

export function useGenerateNextChapter(seriesId: number) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: () => serialApi.series.generateNextChapter(seriesId),
    onSuccess: () => qc.invalidateQueries({ queryKey: ["serial", "series", seriesId] }),
  });
}

export function useGenerateOutline(seriesId: number) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: () => serialApi.series.generateOutline(seriesId),
    onSuccess: () => qc.invalidateQueries({ queryKey: ["serial", "series", seriesId] }),
  });
}

export function useStoryBible(seriesId: number | null) {
  return useQuery({
    queryKey: ["serial", "story-bible", seriesId],
    queryFn: () => serialApi.storyBible.show(seriesId!),
    enabled: seriesId != null,
  });
}
