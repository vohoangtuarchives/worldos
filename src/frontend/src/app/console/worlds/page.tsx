"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { api } from "@/lib/api";
import { writerApi as writer } from "@/shared/api/writer";
import Link from "next/link";
import { Globe2, Plus, Zap, Activity, Trash2 } from "lucide-react";

interface World {
    id: string;
    name: string;
    status: string;
    health_status: string;
    current_tick: number;
    origin_type: string;
    preset: string;
}

export default function WorldsHubPage() {
    const queryClient = useQueryClient();

    const { data: worlds, isLoading, error } = useQuery<World[]>({
        queryKey: ["worlds"],
        queryFn: async () => {
            const res = await api.get<World[]>("/api/v4/tuzy/worlds");
            return res.data;
        },
    });

    const deleteMutation = useMutation({
        mutationFn: (id: string) => writer.worlds.delete(id),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ["worlds"] });
        },
    });

    const handleDelete = (e: React.MouseEvent, id: string) => {
        e.preventDefault();
        e.stopPropagation();
        if (confirm("Are you sure you want to delete this world? This action cannot be undone.")) {
            deleteMutation.mutate(id);
        }
    };

    return (
        <div className="p-4 md:p-8 space-y-6">
            {/* Header */}
            <header className="flex flex-col md:flex-row shadow-glow justify-between items-start md:items-end pb-6 border-b border-primary/20">
                <div>
                    <div className="flex items-center gap-3 mb-2">
                        <Globe2 className="w-8 h-8 text-primary shadow-glow" />
                        <h1 className="text-3xl md:text-4xl font-bold tracking-tight bg-gradient-to-r from-primary to-accent bg-clip-text text-transparent uppercase">
                            Worlds Hub
                        </h1>
                    </div>
                    <p className="text-muted-foreground font-mono text-sm uppercase tracking-widest pl-1">
                        Active Simulation Matrices
                    </p>
                </div>

                <div className="mt-4 md:mt-0 flex gap-4">
                    <button className="glass-panel px-6 py-2 rounded-full text-primary hover:bg-primary/20 hover:text-white transition-all flex items-center gap-2 border border-primary/30 text-sm tracking-widest uppercase font-mono shadow-[0_0_15px_theme(colors.primary.DEFAULT/0.2)]">
                        <Plus className="w-4 h-4" />
                        Spawn Genesis
                    </button>
                </div>
            </header>

            {/* Main Grid */}
            <main>
                {isLoading ? (
                    <div className="flex items-center gap-3 text-primary font-mono text-sm tracking-widest">
                        <div className="w-4 h-4 rounded-full border-2 border-primary border-t-transparent animate-spin" />
                        INITIALIZING MATRIX...
                    </div>
                ) : error ? (
                    <div className="text-destructive font-mono border border-destructive/30 bg-destructive/10 p-4 rounded-lg">
                        System Error: Failed to load worlds payload.
                    </div>
                ) : worlds?.length === 0 ? (
                    <div className="text-muted-foreground font-mono text-center py-20 border border-white/5 bg-white/5 rounded-xl border-dashed">
                        NO ACTIVE WORLDS DETECTED IN THE MULTIVERSE.
                    </div>
                ) : (
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {worlds?.map((world) => (
                            <Link key={world.id} href={`/console/worlds/${world.id}`} className="group block">
                                <div className="glass-card relative h-full flex flex-col hover:border-primary/50 transition-colors overflow-hidden">

                                    {/* Decorative Glow */}
                                    {world.status === "running" || world.status === "active" ? (
                                        <div className="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-primary to-transparent" />
                                    ) : null}

                                    {/* Header */}
                                    <div className="p-6 pb-4 flex justify-between items-start">
                                        <div className="flex items-center gap-3">
                                            <div className="p-2.5 rounded-lg bg-black/40 border border-white/5 group-hover:border-primary/30 transition-colors">
                                                <Globe2 className="w-5 h-5 text-primary" />
                                            </div>
                                            <div>
                                                <h3 className="font-bold text-lg text-foreground group-hover:text-primary transition-colors">{world.name}</h3>
                                                <p className="text-xs text-muted-foreground font-mono uppercase tracking-wider">
                                                    ID: {world.id.split('-')[0]}
                                                </p>
                                            </div>
                                        </div>

                                        {/* Status & Actions */}
                                        <div className="flex flex-col items-end gap-3">
                                            <div className="h-2 w-2 relative">
                                                <div className={`absolute inset-0 rounded-full animate-pulse ${(world.status === 'running' || world.status === 'active') ? 'bg-primary shadow-[0_0_10px_theme(colors.primary.DEFAULT)]' : 'bg-muted-foreground'
                                                    }`} />
                                            </div>
                                            <button
                                                onClick={(e) => handleDelete(e, world.id)}
                                                className="p-1.5 rounded-md hover:bg-destructive/20 text-muted-foreground hover:text-destructive transition-colors opacity-0 group-hover:opacity-100"
                                                title="Delete World"
                                            >
                                                <Trash2 className="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>

                                    {/* Body Metrics */}
                                    <div className="p-6 pt-2 mt-auto border-t border-white/5 bg-black/20">
                                        <div className="grid grid-cols-2 gap-4">
                                            <div>
                                                <span className="text-[10px] text-muted-foreground font-mono uppercase tracking-widest block mb-1">Origin</span>
                                                <span className="text-sm text-foreground capitalize flex items-center gap-1.5">
                                                    <Zap className="w-3 h-3 text-accent" />
                                                    {world.origin_type || "Cosmic"}
                                                </span>
                                            </div>
                                            <div>
                                                <span className="text-[10px] text-muted-foreground font-mono uppercase tracking-widest block mb-1">Time Tick</span>
                                                <span className="text-sm font-mono text-foreground flex items-center gap-1.5">
                                                    <Activity className="w-3 h-3 text-success" />
                                                    {world.current_tick || 0}
                                                </span>
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
