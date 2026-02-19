import { api } from "./client";

export type SerialSeries = {
  id: string;
  title: string;
  genre_key?: string;
  universe_id?: string;
  current_book_index?: number;
  total_chapters_generated?: number;
  updated_at?: string;
};

export type SerialGenreCatalog = {
  genres: string[];
  emergent_description?: string;
};

export type SerialUniverseOption = {
  id: string;
  name?: string;
  world_id?: string;
  status?: string;
  is_archived?: boolean;
  updated_at?: string;
};

export type SerialChapter = {
  id: string;
  series_id: string;
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
    show: (id: string) =>
      api.get<{ success: boolean; data: { series: SerialSeries & { chapters?: SerialChapter[] } } }>("/api/serial/series/" + id).then((r) => r.data.series),
    create: (body: { title: string; genre_key?: string; universe_id?: string }) =>
      api.post<{ success: boolean; data: { series: SerialSeries } }>("/api/serial/series", body).then((r) => r.data.series),
    genres: () =>
      api
        .get<{ success: boolean; data: { genres: string[]; emergent_description?: string } }>("/api/serial/genres")
        .then((r) => ({ genres: r.data?.genres ?? [], emergent_description: r.data?.emergent_description })),
    universes: () =>
      api
        .get<{ success: boolean; data: { universes: SerialUniverseOption[] } }>("/api/serial/universes")
        .then((r) => r.data?.universes ?? []),
    update: (id: string, body: Partial<SerialSeries>) =>
      api.patch("/api/serial/series/" + id, body),
    generateNextChapter: (id: string) =>
      api.post("/api/serial/series/" + id + "/generate-next-chapter"),
    generateOutline: (id: string) =>
      api.post("/api/serial/series/" + id + "/outline/generate"),
    arcs: (id: string) =>
      api.get<{ success: boolean; data?: { arcs: unknown[] } }>("/api/serial/series/" + id + "/arcs").then((r) => r.data?.arcs ?? []),
  },
  storyBible: {
    show: (seriesId: string) =>
      api.get("/api/serial/series/" + seriesId + "/story-bible"),
    update: (seriesId: string, body: unknown) =>
      api.put("/api/serial/series/" + seriesId + "/story-bible", body),
    generateFromPremise: (seriesId: string, body?: { premise?: string }) =>
      api.post("/api/serial/series/" + seriesId + "/story-bible/generate-from-premise", body ?? {}),
  },
};
