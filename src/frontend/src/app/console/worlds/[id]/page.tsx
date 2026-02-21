"use client";

import { useQuery } from "@tanstack/react-query";
import { api } from "@/lib/api";
import Link from "next/link";
import { useParams, useRouter } from "next/navigation";
import { ArrowLeft, GitMerge, Activity, Orbit, Zap } from "lucide-react";

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
    runtime_instances: UniverseSnippet[]; // Thuộc tính load từ API GET /api/worlds/{id} hiện thời
}

export default function WorldUniversesHubPage() {
    const params = useParams();
    const router = useRouter();
    const worldId = params.id as string;

    const { data: world, isLoading, error } = useQuery<WorldWithUniverses>({
        queryKey: ["world", worldId],
        queryFn: async () => {
            // Tận dụng API GET /api/worlds/{id} của backend đã có sẵn runtime_instances
            const res = await api.get<{ data: WorldWithUniverses }>(`/api/worlds/${worldId}`);
            return res.data.data;
        },
    });

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

                    <button className="glass-panel px-4 py-2 rounded-full text-accent hover:bg-accent/20 hover:text-white transition-all flex items-center gap-2 border border-accent/30 text-xs tracking-widest uppercase font-mono shadow-[0_0_15px_theme(colors.accent.DEFAULT/0.2)]">
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
                    <div className="text-muted-foreground font-mono text-center py-20 border border-white/5 bg-white/5 rounded-xl border-dashed">
                        NO UNIVERSES FOUND IN THIS WORLD DOMAIN.
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
