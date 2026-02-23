"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { api } from "@/lib/api";
import { writerApi as writer } from "@/shared/api/writer";
import Link from "next/link";
import { useState } from "react";
import { useParams, useRouter } from "next/navigation";
import { ArrowLeft, GitMerge, Activity, Orbit, Zap, Trash2 } from "lucide-react";

interface UniverseSnippet {
    id: string;
    name: string;
    age: number;
}

interface WorldWithUniverses {
    id: string;
    name: string;
    status: string;
    origin_type: string;
    config?: Record<string, any>;
    gene_vector?: Record<string, any>;
    runtime_instances: UniverseSnippet[]; // Thuộc tính load từ API GET /api/worlds/{id} hiện thời
}

export default function WorldUniversesHubPage() {
    const params = useParams();
    const router = useRouter();
    const worldId = params.id as string;

    const queryClient = useQueryClient();

    const { data: world, isLoading, error } = useQuery<WorldWithUniverses>({
        queryKey: ["world", worldId],
        queryFn: async () => {
            const res = await api.get<WorldWithUniverses>(`/api/v4/tuzy/worlds/${worldId}`);
            return res.data;
        },
    });

    const deleteUniverseMutation = useMutation({
        mutationFn: (id: string) => writer.universes.delete(id),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ["world", worldId] });
        },
    });

    const handleDeleteUniverse = (e: React.MouseEvent, id: string) => {
        e.preventDefault();
        e.stopPropagation();
        if (confirm("Are you sure you want to delete this universe branch?")) {
            deleteUniverseMutation.mutate(id);
        }
    };

    const [isSpawning, setIsSpawning] = useState(false);

    const handleSpawnUniverse = async (e: React.MouseEvent) => {
        e.preventDefault();
        try {
            setIsSpawning(true);
            const saga = await writer.sagas.create({ name: `${world?.name || 'Unknown'} - Genesis Saga` });
            const universe = await writer.universes.create({
                name: `${world?.name || 'Unknown'} - Universe Alpha`,
                world_id: worldId,
                saga_id: saga.id,
            });

            setTimeout(() => {
                router.push(`/console/universes/${universe.id}`);
            }, 1500);
        } catch (err) {
            console.error(err);
            setIsSpawning(false);
            alert("Failed to spawn universe.");
        }
    };

    if (isSpawning) {
        return (
            <div className="fixed inset-0 z-50 flex flex-col items-center justify-center bg-black text-white px-4 overflow-hidden">
                <div className="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(59,130,246,0.15),transparent_70%)]" />
                <div className="relative z-10 flex flex-col items-center animate-pulse">
                    <div className="w-32 h-32 rounded-full border-t-2 border-blue-500 animate-spin flex items-center justify-center mb-8 relative">
                        <div className="absolute inset-2 rounded-full border-r-2 border-cyan-400 animate-spin-slow"></div>
                        <div className="absolute inset-4 rounded-full border-l-2 border-purple-500 animate-spin-reverse"></div>
                        <Zap className="w-8 h-8 text-blue-400 animate-bounce" />
                    </div>
                    <h2 className="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-cyan-300 font-vi tracking-widest uppercase">
                        Spawning First Universe...
                    </h2>
                    <p className="mt-4 text-slate-400 font-mono text-sm max-w-md text-center">
                        Synthesizing elements. Applying Gene Bounds. Unfurling state vectors.
                    </p>
                </div>
            </div>
        );
    }

    return (
        <div className="p-4 md:p-8 space-y-6">
            {/* Breadcrumb Navigation */}
            <header className="flex-none flex items-center gap-4 bg-black/40 backdrop-blur-xl border border-white/10 rounded-xl px-4 py-2 shadow-glow w-fit">
                <button
                    onClick={() => router.push('/console/worlds')}
                    className="p-2 -ml-2 rounded-lg text-muted-foreground hover:text-white hover:bg-white/10 transition-colors"
                >
                    <ArrowLeft className="w-5 h-5" />
                </button>
                <div className="w-px h-6 bg-white/10" />
                <div className="flex items-center gap-2">
                    <GlobeIcon status={world?.status} />
                    <h1 className="font-mono text-sm tracking-widest text-primary uppercase">
                        World Domain <span className="text-muted-foreground mr-1">/</span> {world?.name || worldId.split('-')[0]}
                    </h1>
                </div>
            </header>

            {/* Gene Vector / Preset Configuration */}
            {world?.gene_vector && Object.keys(world.gene_vector).length > 0 && (
                <div className="glass-card border border-white/5 rounded-xl p-5 mb-8">
                    <h3 className="text-white font-mono tracking-widest text-sm mb-4 flex items-center gap-2">
                        <Zap className="w-4 h-4 text-amber-400" />
                        INITIAL GENE VECTOR (PRESET: {world.config?.preset_key?.toUpperCase() || 'DEFAULT'})
                    </h3>
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                        {world.gene_vector.genre && (
                            <div className="bg-black/30 p-3 rounded-lg border border-white/5">
                                <span className="text-[10px] text-muted-foreground uppercase tracking-widest block mb-1">Genre Pattern</span>
                                <span className="text-emerald-400 font-mono text-sm">{world.gene_vector.genre}</span>
                            </div>
                        )}
                        {world.gene_vector.power_system && (
                            <div className="bg-black/30 p-3 rounded-lg border border-white/5">
                                <span className="text-[10px] text-muted-foreground uppercase tracking-widest block mb-1">Power System</span>
                                <span className="text-amber-400 font-mono text-sm">{world.gene_vector.power_system}</span>
                            </div>
                        )}
                        {world.gene_vector.tech_level && (
                            <div className="bg-black/30 p-3 rounded-lg border border-white/5">
                                <span className="text-[10px] text-muted-foreground uppercase tracking-widest block mb-1">Tech Ceiling</span>
                                <span className="text-cyan-400 font-mono text-sm">{world.gene_vector.tech_level}</span>
                            </div>
                        )}
                        {world.gene_vector.archetype && (
                            <div className="bg-black/30 p-3 rounded-lg border border-white/5">
                                <span className="text-[10px] text-muted-foreground uppercase tracking-widest block mb-1">Archetype Code</span>
                                <span className="text-purple-400 font-mono text-sm">{world.gene_vector.archetype}</span>
                            </div>
                        )}
                    </div>
                    {world.gene_vector.seed_vector && (
                        <div className="mt-4 pt-4 border-t border-white/5">
                            <span className="text-[10px] text-muted-foreground uppercase tracking-widest block mb-3">Ontological & Epistemic Seed Constraints</span>
                            <div className="flex flex-wrap gap-2">
                                {Object.entries(world.gene_vector.seed_vector).map(([cat, dims]: [string, any]) =>
                                    Object.entries(dims).map(([dim, range]: [string, any]) => (
                                        <div key={`${cat}-${dim}`} className="text-xs bg-accent/10 border border-accent/20 px-2.5 py-1.5 rounded text-white flex gap-2 items-center">
                                            <span className="text-accent/70 capitalize">{dim}</span>
                                            <span className="font-mono">
                                                {Array.isArray(range) ? `${range[0]} → ${range[1]}` : range}
                                            </span>
                                        </div>
                                    ))
                                )}
                            </div>
                        </div>
                    )}
                </div>
            )}

            {/* Main Grid */}
            <main>
                <div className="mb-6 flex items-center justify-between border-b border-white/10 pb-4">
                    <div>
                        <h2 className="text-2xl font-bold font-mono tracking-widest text-white flex items-center gap-3">
                            <GitMerge className="w-6 h-6 text-accent" />
                            Parallel Timelines (Universes)
                        </h2>
                        <p className="text-sm text-muted-foreground mt-1">Select a universe branch to enter core simulation.</p>
                    </div>

                    <button
                        onClick={handleSpawnUniverse}
                        className="glass-panel px-4 py-2 rounded-full text-accent hover:bg-accent/20 hover:text-white transition-all flex items-center gap-2 border border-accent/30 text-xs tracking-widest uppercase font-mono shadow-[0_0_15px_theme(colors.accent.DEFAULT/0.2)]"
                    >
                        <GitMerge className="w-4 h-4" />
                        Fork Branch
                    </button>
                </div>

                {isLoading ? (
                    <div className="flex items-center gap-3 text-accent font-mono text-sm tracking-widest">
                        <div className="w-4 h-4 rounded-full border-2 border-accent border-t-transparent animate-spin" />
                        SCANNING MULTIVERSE BRANCHES...
                    </div>
                ) : error ? (
                    <div className="text-destructive font-mono border border-destructive/30 bg-destructive/10 p-4 rounded-lg">
                        System Error: Failed to load timeline data.
                    </div>
                ) : !world?.runtime_instances || world.runtime_instances.length === 0 ? (
                    <div className="flex flex-col items-center justify-center py-20 border border-white/5 bg-white/5 rounded-xl border-dashed">
                        <p className="text-muted-foreground font-mono text-center mb-6">
                            NO UNIVERSES FOUND IN THIS WORLD DOMAIN.
                        </p>
                        <button
                            onClick={handleSpawnUniverse}
                            className="relative group overflow-hidden rounded-lg p-px"
                        >
                            <span className="absolute inset-0 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500 opacity-70 group-hover:opacity-100 transition-opacity duration-300"></span>
                            <div className="relative flex items-center justify-center gap-2 bg-slate-900 px-8 py-4 rounded-lg transition-all duration-300 group-hover:bg-slate-900/50">
                                <Zap className="w-5 h-5 text-amber-400" />
                                <span className="text-white font-bold tracking-widest uppercase text-sm font-vi">
                                    Ignite First Timeline
                                </span>
                            </div>
                        </button>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        {world.runtime_instances.map((universe) => (
                            <Link key={universe.id} href={`/console/universes/${universe.id}`} className="group block">
                                <div className="glass-card-accent relative h-full flex flex-col hover:border-accent/50 transition-colors overflow-hidden">
                                    {/* Status Indicator Bar */}
                                    <div className="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-accent to-accent/20" />
                                    {/* Header */}
                                    <div className="p-5 pl-6 flex justify-between items-start">
                                        <div>
                                            <h3 className="font-bold text-base text-foreground group-hover:text-accent transition-colors flex items-center gap-2">
                                                <Orbit className="w-4 h-4 text-accent/70" />
                                                {universe.name}
                                            </h3>
                                            <p className="text-[10px] text-muted-foreground font-mono uppercase tracking-wider mt-1">
                                                UID: {universe.id.split('-')[0]}
                                            </p>
                                        </div>
                                        <button
                                            onClick={(e) => handleDeleteUniverse(e, universe.id)}
                                            className="p-1.5 rounded-md hover:bg-destructive/20 text-muted-foreground hover:text-destructive transition-colors opacity-0 group-hover:opacity-100"
                                            title="Delete Universe"
                                        >
                                            <Trash2 className="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                    {/* Body Metrics */}
                                    <div className="p-4 pl-6 pt-2 mt-auto border-t border-white/5 bg-black/40">
                                        <div className="flex items-center justify-between">
                                            <div className="flex flex-col">
                                                <span className="text-[10px] text-muted-foreground font-mono uppercase tracking-widest block mb-1">Timeline Age</span>
                                                <span className="text-sm font-mono text-foreground flex items-center gap-1.5">
                                                    <Activity className="w-3 h-3 text-accent" />
                                                    {universe.age} Ticks
                                                </span>
                                            </div>
                                            <div className="w-6 h-6 rounded-full bg-accent/10 border border-accent/20 flex items-center justify-center group-hover:scale-110 group-hover:bg-accent/30 transition-all">
                                                <Zap className="w-3 h-3 text-accent" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </Link>
                        ))}
                    </div>
                )}
            </main>
        </div>
    );
}

// Giữ lại Component Icon status nội bộ
function GlobeIcon({ status }: { status?: string }) {
    if (status === 'running' || status === 'active') {
        return <div className="w-5 h-5 rounded-full border-2 border-primary border-t-transparent animate-spin" />;
    }
    return <div className="w-5 h-5 rounded-full border-2 border-muted-foreground" />;
}
