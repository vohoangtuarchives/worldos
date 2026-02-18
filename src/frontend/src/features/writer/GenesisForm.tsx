"use client";

import { useState } from "react";
import { useGenesisPresets, useCreateGenesis } from "./useWriterApi";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { useRouter } from "next/navigation";

export function GenesisForm() {
  const router = useRouter();
  const { data: presets, isLoading } = useGenesisPresets();
  const create = useCreateGenesis();
  const [presetId, setPresetId] = useState("");
  const [name, setName] = useState("");

  type PresetItem = { key: string; name?: string; icon?: string; description?: string };
  type CategoryEntry = [string, { label?: string; presets?: PresetItem[] }];
  const categories = presets?.categories
    ? (Object.entries(presets.categories) as CategoryEntry[])
    : [];
  const hasPresets = categories.some(([, cat]) => (cat.presets?.length ?? 0) > 0);

  // Subtle gradient variants per card (premium feel)
  const CARD_GRADIENTS = [
    "from-violet-500/15 via-background to-fuchsia-500/10",
    "from-emerald-500/15 via-background to-teal-500/10",
    "from-amber-500/15 via-background to-orange-500/10",
    "from-sky-500/15 via-background to-indigo-500/10",
    "from-rose-500/15 via-background to-pink-500/10",
    "from-slate-400/20 via-background to-zinc-400/15",
  ];

  return (
    <Card>
      <CardHeader>
        <CardTitle>Create world from preset</CardTitle>
      </CardHeader>
      <CardContent>
        {isLoading && <p className="text-sm text-muted-foreground">Loading presets…</p>}
        {!isLoading && !hasPresets && <p className="text-sm text-muted-foreground">No presets.</p>}
        {!isLoading && hasPresets && (
          <form
            className="flex flex-col gap-6"
            onSubmit={(e) => {
              e.preventDefault();
              if (!presetId) return;
              create.mutate(
                { name: name || "New World", preset_key: presetId || undefined },
                { onSuccess: () => router.push("/writer") }
              );
            }}
          >
            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
              {(() => {
                let cardIndex = 0;
                return categories.map(([slug, cat]) =>
                  (cat.presets ?? []).map((p) => {
                    const idx = cardIndex++;
                    const gradient = CARD_GRADIENTS[idx % CARD_GRADIENTS.length];
                    const selected = presetId === p.key;
                    return (
                      <Card
                        key={p.key}
                        role="button"
                        tabIndex={0}
                        className={`cursor-pointer bg-gradient-to-br transition-all duration-300 hover:shadow-md hover:brightness-100 ${
                          selected
                            ? "ring-2 ring-primary bg-gradient-to-br from-primary/20 via-primary/10 to-background shadow-sm"
                            : `hover:opacity-95 ${gradient}`
                        }`}
                      onClick={() => {
                        setPresetId(p.key);
                        setName(p.name ?? p.key);
                      }}
                      onKeyDown={(e) => {
                        if (e.key === "Enter" || e.key === " ") {
                          e.preventDefault();
                          setPresetId(p.key);
                          setName(p.name ?? p.key);
                        }
                      }}
                    >
                      <CardHeader className="flex flex-row items-center gap-2 pb-2">
                        {p.icon && <span className="text-xl">{p.icon}</span>}
                        <CardTitle className="text-sm font-medium">{p.name ?? p.key}</CardTitle>
                      </CardHeader>
                      {p.description && (
                        <CardContent className="pt-0 text-xs text-muted-foreground line-clamp-2">
                          {p.description}
                        </CardContent>
                      )}
                    </Card>
                    );
                  })
                );
              })()}
            </div>
            <div className="flex flex-col gap-4 border-t pt-4">
              <div>
                <label className="text-sm font-medium">Tên thế giới (tự điền theo preset, có thể sửa)</label>
                <input
                  type="text"
                  className="mt-1 w-full rounded-md border border-border bg-background px-3 py-2"
                  value={name}
                  onChange={(e) => setName(e.target.value)}
                />
              </div>
              <Button type="submit" disabled={!presetId || create.isPending}>
                {create.isPending ? "Đang tạo…" : "Tạo thế giới"}
              </Button>
            </div>
          </form>
        )}
      </CardContent>
    </Card>
  );
}
