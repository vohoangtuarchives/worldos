import { api } from "./client";

export type SerialSeries = {
  id: number;
  title: string;
  genre_key?: string;
  universe_id?: string;
  current_book_index?: number;
  total_chapters_generated?: number;
  updated_at?: string;
};

export type SerialChapter = {
  id: number;
  series_id: number;
  chapter_index: number;
  title?: string;
  content?: string;
  structured_summary?: unknown;
  needs_review?: boolean;
  created_at?: string;
};

export const serialApi = {
  series: {
    list: () =>
      api.get<{ success: boolean; data: { series: SerialSeries[] } }>("/api/serial/series").then((r) => r.data.series),
    show: (id: number) =>
      api.get<{ success: boolean; data: { series: SerialSeries & { chapters?: SerialChapter[] } } }>("/api/serial/series/" + id).then((r) => r.data.series),
    create: (body: { title: string; genre_key?: string; universe_id?: string }) =>
      api.post("/api/serial/series", body),
    update: (id: number, body: Partial<SerialSeries>) =>
      api.patch("/api/serial/series/" + id, body),
    generateNextChapter: (id: number) =>
      api.post("/api/serial/series/" + id + "/generate-next-chapter"),
    generateOutline: (id: number) =>
      api.post("/api/serial/series/" + id + "/outline/generate"),
    arcs: (id: number) =>
      api.get<{ success: boolean; data?: { arcs: unknown[] } }>("/api/serial/series/" + id + "/arcs").then((r) => r.data?.arcs ?? []),
  },
  storyBible: {
    show: (seriesId: number) =>
      api.get("/api/serial/series/" + seriesId + "/story-bible"),
    update: (seriesId: number, body: unknown) =>
      api.put("/api/serial/series/" + seriesId + "/story-bible", body),
    generateFromPremise: (seriesId: number, body?: { premise?: string }) =>
      api.post("/api/serial/series/" + seriesId + "/story-bible/generate-from-premise", body ?? {}),
  },
};
