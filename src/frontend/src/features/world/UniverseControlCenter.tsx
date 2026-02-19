"use client";

import { useState } from "react";
import {
    useUniverseSnapshots,
    useUniverseFork,
    useUniverseEvaluate,
    useUniverseApplyPressure,
    useUniverseMetrics,
    useUniverseStyle
} from "@/features/writer/useWriterApi";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Slider } from "@/components/ui/slider";
import {
    GitFork,
    BrainCircuit,
    Zap,
    History as LucideHistory,
    TrendingUp,
    ShieldAlert,
    ChevronRight,
    Sparkles,
    Settings,
    Palette
} from "lucide-react";
import { cn } from "@/lib/utils";
import type { UniverseSnapshotItem } from "@/shared/api/writer";

interface UniverseControlCenterProps {
    worldId: string;
    universeId: string;
    sagaId?: string;
}

export function UniverseControlCenter({ worldId, universeId, sagaId }: UniverseControlCenterProps) {
    const { data: snapshots = [], isLoading: snapLoading } = useUniverseSnapshots(universeId);
    const { data: metrics, isLoading: metricsLoading } = useUniverseMetrics(universeId, { refetchInterval: 5000 });

    const fork = useUniverseFork();
    const evaluate = useUniverseEvaluate();
    const applyPressure = useUniverseApplyPressure();

    const [selectedTick, setSelectedTick] = useState<number | null>(null);
    const [pressureType, setPressureType] = useState<string>("military");
    const [pressureIntensity, setPressureIntensity] = useState<number>(0.5);
    const [evalResult, setEvalResult] = useState<any>(null);

    const handleFork = () => {
        if (selectedTick === null) return;
        fork.mutate({ universeId, tick: selectedTick, sagaId });
    };

    const handleEvaluate = () => {
        evaluate.mutate(universeId, {
            onSuccess: (res) => setEvalResult(res.data)
        });
    };

    const { data: styleData } = useUniverseStyle(universeId);

    const handleApplyPressure = () => {
        applyPressure.mutate({ universeId, type: pressureType, intensity: pressureIntensity });
    };

    return (
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 animate-in fade-in slide-in-from-bottom-4 duration-500">

            {/* Left Column: Metrics & Evaluation */}
            <div className="space-y-6">
                <Card className="glass-card border-primary/20 bg-primary/5">
                    <CardHeader className="pb-2">
                        <CardTitle className="text-sm font-bold uppercase tracking-wider flex items-center gap-2">
                            <Sparkles className="h-4 w-4 text-primary" />
                            IP Evaluation
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-medium text-muted-foreground uppercase">IP Potential Score</span>
                            <span className={cn(
                                "text-2xl font-bold font-mono",
                                evalResult ? (evalResult.ip_score > 7 ? "text-success" : "text-primary") : "text-muted-foreground"
                            )}>
                                {evalResult ? evalResult.ip_score.toFixed(1) : "?.?"}
                            </span>
                        </div>

                        {evalResult ? (
                            <div className="p-3 rounded-lg bg-white/40 border border-primary/10">
                                <p className="text-[10px] font-bold uppercase text-muted-foreground mb-1">AI Recommendation</p>
                                <div className="flex items-center gap-2 bg-primary/10 p-2 rounded border border-primary/20">
                                    <BrainCircuit className="h-4 w-4 text-primary" />
                                    <span className="text-xs font-bold uppercase tracking-tight">{evalResult.recommendation}</span>
                                </div>
                                {evalResult.suggestion && (
                                    <p className="text-[10px] mt-2 text-muted-foreground italic">
                                        Suggestion: Apply {evalResult.suggestion.type} pressure ({evalResult.suggestion.intensity.toFixed(2)})
                                    </p>
                                )}
                            </div>
                        ) : (
                            <p className="text-xs text-muted-foreground italic text-center py-4">Run evaluation to see IP potential.</p>
                        )}

                        <Button
                            className="w-full gap-2 font-bold"
                            onClick={handleEvaluate}
                            disabled={evaluate.isPending}
                        >
                            <BrainCircuit className="h-4 w-4" />
                            {evaluate.isPending ? "Analyzing..." : "Analyze Universe"}
                        </Button>
                    </CardContent>
                </Card>

                <Card className="glass-card">
                    <CardHeader className="pb-2">
                        <CardTitle className="text-sm font-bold uppercase tracking-wider flex items-center gap-2">
                            <Settings className="h-4 w-4 text-primary" />
                            Kernel Intervention
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-6">
                        <div className="space-y-3">
                            <label className="text-[10px] font-bold uppercase text-muted-foreground">Pressure Vector</label>
                            <div className="grid grid-cols-2 gap-2">
                                {["military", "resource", "ideology", "tech"].map((type) => (
                                    <Button
                                        key={type}
                                        variant={pressureType === type ? "default" : "outline"}
                                        size="sm"
                                        className="text-[10px] font-bold uppercase h-8"
                                        onClick={() => setPressureType(type)}
                                    >
                                        {type}
                                    </Button>
                                ))}
                            </div>
                        </div>

                        <div className="space-y-4">
                            <div className="flex justify-between items-center">
                                <label className="text-[10px] font-bold uppercase text-muted-foreground">Intensity</label>
                                <span className="text-xs font-mono font-bold text-primary">{(pressureIntensity * 100).toFixed(0)}%</span>
                            </div>
                            <Slider
                                value={[pressureIntensity]}
                                min={0}
                                max={1}
                                step={0.01}
                                onValueChange={(v) => setPressureIntensity(v[0])}
                            />
                        </div>

                        <Button
                            variant="secondary"
                            className="w-full gap-2 font-bold"
                            onClick={handleApplyPressure}
                            disabled={applyPressure.isPending}
                        >
                            <Zap className="h-4 w-4" /> Apply Selection Pressure
                        </Button>
                    </CardContent>
                </Card>

                {/* Style Visualization Card */}
                <Card className="glass-card border-primary/20">
                    <CardHeader className="pb-2">
                        <CardTitle className="text-sm font-bold uppercase tracking-wider flex items-center gap-2">
                            <Palette className="h-4 w-4 text-primary" />
                            Universe Style
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        {styleData ? (
                            <div className="space-y-3">
                                <div className="flex justify-between items-center bg-muted/30 p-2 rounded border border-border/50">
                                    <span className="text-xs font-bold uppercase">{styleData.name}</span>
                                    <Badge variant="outline" className="text-[10px]">v{styleData.version}</Badge>
                                </div>
                                <div className="space-y-2">
                                    {Object.entries(styleData.style_vector).map(([key, val]) => (
                                        <div key={key} className="space-y-1">
                                            <div className="flex justify-between text-[9px] uppercase font-bold text-muted-foreground">
                                                <span>{key}</span>
                                                <span>{((val as number) * 100).toFixed(0)}%</span>
                                            </div>
                                            <div className="h-1 bg-muted rounded-full overflow-hidden">
                                                <div
                                                    className="h-full bg-primary"
                                                    style={{ width: `${(val as number) * 100}%` }}
                                                />
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        ) : (
                            <p className="text-xs text-muted-foreground italic text-center py-4">Loading universe style...</p>
                        )}
                    </CardContent>
                </Card>
            </div>

            {/* Right Column: Timeline & Forking */}
            <div className="lg:col-span-2 space-y-6">
                <Card className="glass-card overflow-hidden">
                    <CardHeader className="border-b bg-muted/20">
                        <div className="flex items-center justify-between">
                            <CardTitle className="text-sm font-bold uppercase tracking-wider flex items-center gap-2">
                                <LucideHistory className="h-4 w-4 text-primary" />
                                Universe Timeline (Snapshots)
                            </CardTitle>
                            <Badge variant="outline" className="font-mono text-[10px]">{snapshots.length} Snapshots</Badge>
                        </div>
                    </CardHeader>
                    <CardContent className="p-0">
                        <div className="h-[400px] overflow-y-auto scrollbar-hide">
                            <table className="w-full text-sm">
                                <thead className="sticky top-0 bg-background/80 backdrop-blur-sm shadow-sm z-10">
                                    <tr className="border-b border-border text-[10px] uppercase font-bold text-muted-foreground">
                                        <th className="px-4 py-3 text-left">Tick</th>
                                        <th className="px-4 py-3 text-left">Entropy</th>
                                        <th className="px-4 py-3 text-left">Stability</th>
                                        <th className="px-4 py-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {snapLoading ? (
                                        <tr>
                                            <td colSpan={4} className="p-8 text-center text-muted-foreground animate-pulse">Loading temporal data...</td>
                                        </tr>
                                    ) : snapshots.length === 0 ? (
                                        <tr>
                                            <td colSpan={4} className="p-8 text-center text-muted-foreground italic">No snapshots available for this timeline.</td>
                                        </tr>
                                    ) : (
                                        snapshots.map((s: UniverseSnapshotItem) => (
                                            <tr
                                                key={s.id}
                                                className={cn(
                                                    "border-b border-border/50 hover:bg-muted/30 transition-colors cursor-pointer group",
                                                    selectedTick === s.tick ? "bg-primary/5 border-primary/20" : ""
                                                )}
                                                onClick={() => setSelectedTick(s.tick)}
                                            >
                                                <td className="px-4 py-3 font-mono font-bold text-xs">{s.tick}</td>
                                                <td className="px-4 py-3 font-mono text-xs">{s.entropy?.toFixed(3) ?? "—"}</td>
                                                <td className="px-4 py-3">
                                                    <div className="flex items-center gap-2">
                                                        <div className="h-1.5 w-12 bg-muted rounded-full overflow-hidden">
                                                            <div
                                                                className="h-full bg-success"
                                                                style={{ width: `${(s.stability_index ?? 0) * 100}%` }}
                                                            />
                                                        </div>
                                                        <span className="font-mono text-[10px]">{(s.stability_index ?? 0).toFixed(3)}</span>
                                                    </div>
                                                </td>
                                                <td className="px-4 py-3 text-right">
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        className={cn(
                                                            "h-7 px-2 text-[10px] font-bold uppercase gap-1",
                                                            selectedTick === s.tick ? "bg-primary text-white" : "opacity-0 group-hover:opacity-100"
                                                        )}
                                                    >
                                                        Selected <ChevronRight className="h-3 w-3" />
                                                    </Button>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                    <div className="p-4 bg-muted/30 border-t flex items-center justify-between">
                        <div className="text-xs">
                            <span className="text-muted-foreground uppercase font-bold block mb-0.5 text-[9px]">Fork Point</span>
                            <span className="font-mono font-bold">{selectedTick !== null ? `Tick ${selectedTick}` : "Not Selected"}</span>
                        </div>
                        <Button
                            className="gap-2 font-bold px-8 h-10 shadow-lg"
                            disabled={selectedTick === null || fork.isPending}
                            onClick={handleFork}
                        >
                            <GitFork className="h-4 w-4" />
                            {fork.isPending ? "Bifurcating..." : "Bifurcate Timeline"}
                        </Button>
                    </div>
                </Card>
            </div>

        </div>
    );
}
