"use client";

import { useState } from "react";
import { useAvailableUniverses, useCreateSeries, useSeriesGenres } from "./useSerialApi";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { useRouter } from "next/navigation";

export function CreateSeriesButton() {
  const router = useRouter();
  const [open, setOpen] = useState(false);
  const [title, setTitle] = useState("");
  const [genreKey, setGenreKey] = useState("");
  const [universeId, setUniverseId] = useState("");
  const create = useCreateSeries();
  const { data: genreCatalog } = useSeriesGenres();
  const { data: universes } = useAvailableUniverses();

  return (
    <>
      <Button size="sm" onClick={() => setOpen(true)}>Tạo series</Button>
      {open && (
        <Card className="fixed inset-4 z-10 m-auto max-w-md">
          <CardHeader className="flex flex-row items-center justify-between">
            <CardTitle>Series Factory · worldOS v3</CardTitle>
            <Button variant="ghost" size="sm" onClick={() => setOpen(false)}>Đóng</Button>
          </CardHeader>
          <CardContent>
            <form
              onSubmit={(e) => {
                e.preventDefault();
                if (!title.trim()) return;
                create.mutate(
                  {
                    title: title.trim(),
                    genre_key: genreKey || undefined,
                    universe_id: universeId || undefined,
                  },
                  {
                    onSuccess: (result: unknown) => {
                      const data = result as { data?: { series?: { id: string } } };
                      const id = data?.data?.series?.id;
                      if (id) router.push("/serial/series/" + id);
                      setOpen(false);
                      setTitle("");
                      setGenreKey("");
                      setUniverseId("");
                    },
                  }
                );
              }}
              className="flex flex-col gap-3"
            >
              <Input
                placeholder="Tên series"
                value={title}
                onChange={(e) => setTitle(e.target.value)}
              />

              <label className="text-sm text-muted-foreground">
                Genre preset
                <select
                  className="mt-1 w-full rounded-md border bg-background px-3 py-2 text-sm"
                  value={genreKey}
                  onChange={(e) => setGenreKey(e.target.value)}
                >
                  <option value="">Auto từ Universe (worldOS v3)</option>
                  {(genreCatalog?.genres ?? []).map((genre) => (
                    <option key={genre} value={genre}>{genre}</option>
                  ))}
                </select>
              </label>

              <label className="text-sm text-muted-foreground">
                Universe có sẵn
                <select
                  className="mt-1 w-full rounded-md border bg-background px-3 py-2 text-sm"
                  value={universeId}
                  onChange={(e) => setUniverseId(e.target.value)}
                >
                  <option value="">Không bind universe</option>
                  {(universes ?? []).map((u) => (
                    <option key={u.id} value={u.id}>
                      {(u.name && u.name.trim()) || u.id.slice(0, 8)} · {u.status ?? "unknown"} · {u.id.slice(0, 8)}
                    </option>
                  ))}
                </select>
              </label>

              {genreCatalog?.emergent_description && (
                <p className="text-xs text-muted-foreground">{genreCatalog.emergent_description}</p>
              )}

              <Button type="submit" disabled={create.isPending || !title.trim()}>
                {create.isPending ? "Đang khởi tạo…" : "Khởi tạo series"}
              </Button>
            </form>
          </CardContent>
        </Card>
      )}
    </>
  );
}
