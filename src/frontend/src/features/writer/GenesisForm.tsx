"use client";

import { useState } from "react";
import { useGenesisPresets, useCreateWorld, useCreateUniverse } from "./useWriterApi";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Separator } from "@/components/ui/separator";
import { Sparkles, Globe, Zap, ArrowRight, CheckCircle2 } from "lucide-react";
import { useRouter } from "next/navigation";
import { cn } from "@/lib/utils";

export function GenesisForm() {
  const router = useRouter();

  // Steps: 1 = World (Container), 2 = Universe (Preset)
  const [step, setStep] = useState<1 | 2>(1);

  // Step 1 State
  const [worldName, setWorldName] = useState("");
  const [genre, setGenre] = useState("historical");
  const [originType, setOriginType] = useState("cosmic");

  // Step 2 State
  const { data: presets, isLoading: isLoadingPresets } = useGenesisPresets();
  const [selectedPresetKey, setSelectedPresetKey] = useState<string | null>(null);

  // Context passed from Step 1
  const [createdWorldId, setCreatedWorldId] = useState<string | null>(null);
  const [createdWorldName, setCreatedWorldName] = useState<string | null>(null);

  // API Hooks
  const createWorld = useCreateWorld();
  const createUniverse = useCreateUniverse();

  const handleCreateWorld = () => {
    if (!worldName) return;
    createWorld.mutate(
      { name: worldName, genre, origin_type: originType },
      {
        onSuccess: (data) => {
          setCreatedWorldId(data.world_id);
          setCreatedWorldName(data.name);
          setStep(2);
        },
      }
    );
  };

  const handleSpawnUniverse = () => {
    if (!createdWorldId || !selectedPresetKey) return;
    createUniverse.mutate(
      { world_id: createdWorldId, preset_key: selectedPresetKey },
      {
        onSuccess: (data) => {
          // Redirect to the World Hub/Dashboard
          router.push(`/writer/worlds/${createdWorldId}?spawned=${data.universe_id}`);
        },
      }
    );
  };

  // Helper to flatten presets
  type PresetItem = { key: string; name?: string; icon?: string; description?: string };
  type CategoryEntry = [string, { label?: string; presets?: PresetItem[] }];
  const categories = presets?.categories
    ? (Object.entries(presets.categories) as CategoryEntry[])
    : [];

  const CARD_GRADIENTS = [
    "from-violet-500/10 to-fuchsia-500/5",
    "from-emerald-500/10 to-teal-500/5",
    "from-amber-500/10 to-orange-500/5",
    "from-sky-500/10 to-indigo-500/5",
  ];

  return (
    <div className="flex flex-col gap-8 max-w-5xl mx-auto">
      {/* Progress Stepper */}
      <div className="flex items-center justify-center gap-4 text-sm font-medium text-muted-foreground">
        <div className={cn("flex items-center gap-2", step >= 1 && "text-primary")}>
          <div className={cn("w-8 h-8 rounded-full flex items-center justify-center border-2", step >= 1 ? "border-primary bg-primary/10" : "border-muted")}>1</div>
          <span>Khởi Tạo World</span>
        </div>
        <Separator className="w-12" />
        <div className={cn("flex items-center gap-2", step >= 2 && "text-primary")}>
          <div className={cn("w-8 h-8 rounded-full flex items-center justify-center border-2", step >= 2 ? "border-primary bg-primary/10" : "border-muted")}>2</div>
          <span>Gieo Hạt Universe</span>
        </div>
      </div>

      {step === 1 && (
        <Card className="glass-card border-primary/20 bg-background/40 backdrop-blur-xl">
          <CardHeader>
            <CardTitle className="flex items-center gap-2 text-2xl">
              <Globe className="w-6 h-6 text-indigo-400" />
              Khởi Tạo Thực Tại Gốc (Root Reality)
            </CardTitle>
            <CardDescription className="text-base">
              Establish the physical and metaphysical laws of the container. No timeline exists yet.
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-6">
            <div className="grid gap-4 md:grid-cols-2">
              <div className="space-y-2">
                <Label>Tên Thế Giới (World Name)</Label>
                <Input
                  placeholder="e.g. Terra Prime, The Ninth Realm..."
                  value={worldName}
                  onChange={(e) => setWorldName(e.target.value)}
                  className="bg-background/50 text-lg"
                />
              </div>
              <div className="space-y-2">
                <Label>Thể Loại Gốc (Genre Archetype)</Label>
                <select
                  className="w-full rounded-md border border-input bg-background/50 px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                  value={genre}
                  onChange={(e) => setGenre(e.target.value)}
                >
                  <option value="historical">Historical (Lịch sử)</option>
                  <option value="fantasy">Fantasy (Huyền huyễn)</option>
                  <option value="scifi">Sci-Fi (Khoa huyễn)</option>
                  <option value="xianxia">Xianxia (Tiên hiệp)</option>
                  <option value="wuxia">Wuxia (Kiếm hiệp)</option>
                </select>
              </div>
            </div>

            <div className="flex justify-end pt-4">
              <Button
                size="lg"
                onClick={handleCreateWorld}
                disabled={!worldName || createWorld.isPending}
                className="gap-2 bg-indigo-600 hover:bg-indigo-700"
              >
                {createWorld.isPending ? "Initialzing..." : "Thiết Lập World Container"}
                <ArrowRight className="w-4 h-4" />
              </Button>
            </div>
          </CardContent>
        </Card>
      )}

      {step === 2 && (
        <Card className="glass-card border-emerald-500/20 bg-background/40 backdrop-blur-xl">
          <CardHeader>
            <CardTitle className="flex items-center gap-2 text-2xl">
              <Zap className="w-6 h-6 text-emerald-400" />
              Gieo Hạt Vũ Trụ (Cosmic Seeding)
            </CardTitle>
            <CardDescription className="text-base">
              Choose a Preset to spawn the first Universe instance within <strong>{createdWorldName}</strong>.
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-6">
            {isLoadingPresets ? (
              <div className="py-12 text-center text-muted-foreground animate-pulse">Loading Celestial Database...</div>
            ) : (
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                {categories.map(([catKey, cat]) => (
                  cat.presets?.map((p, idx) => {
                    const isSelected = selectedPresetKey === p.key;
                    return (
                      <div
                        key={p.key}
                        onClick={() => setSelectedPresetKey(p.key)}
                        className={cn(
                          "cursor-pointer rounded-xl border p-4 transition-all duration-300 relative overflow-hidden group",
                          isSelected
                            ? "border-emerald-500 bg-emerald-500/10 shadow-[0_0_15px_rgba(16,185,129,0.3)]"
                            : "border-border/50 hover:border-emerald-500/50 hover:bg-white/5",
                          `bg-gradient-to-br ${CARD_GRADIENTS[idx % CARD_GRADIENTS.length]}`
                        )}
                      >
                        <div className="flex items-start justify-between mb-2">
                          <div className="text-2xl pt-1 pl-1">{p.icon || "🌌"}</div>
                          {isSelected && <CheckCircle2 className="w-5 h-5 text-emerald-500" />}
                        </div>
                        <h3 className={cn("font-bold text-lg mb-1 group-hover:text-emerald-400 transition-colors", isSelected && "text-emerald-400")}>
                          {p.name}
                        </h3>
                        <p className="text-xs text-muted-foreground leading-relaxed line-clamp-3">
                          {p.description}
                        </p>
                      </div>
                    )
                  })
                ))}
              </div>
            )}

            <div className="flex justify-between pt-4 border-t border-border/10">
              <div className="text-sm text-muted-foreground pt-3 italic">
                *Universe will inherit World's physics but evolve its own history.
              </div>
              <Button
                size="lg"
                onClick={handleSpawnUniverse}
                disabled={!selectedPresetKey || createUniverse.isPending}
                className="gap-2 bg-emerald-600 hover:bg-emerald-700"
              >
                {createUniverse.isPending ? "Spawning..." : "Khai Sinh Vũ Trụ (Spawn Universe)"}
                <Sparkles className="w-4 h-4" />
              </Button>
            </div>
          </CardContent>
        </Card>
      )}
    </div>
  );
}
