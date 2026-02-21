"use client";

import React, { useEffect, useRef } from "react";
import { useSimulationStore } from "../stores/useSimulationStore";
import { Flame, Info, AlertTriangle, Skull, ShieldAlert } from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { cn } from "@/lib/utils";

const SEVERITY_CONFIG = {
    LOW: { icon: Info, color: "text-blue-400", bg: "bg-blue-400/10", border: "border-blue-400/20" },
    MEDIUM: { icon: Flame, color: "text-yellow-400", bg: "bg-yellow-400/10", border: "border-yellow-400/20" },
    HIGH: { icon: AlertTriangle, color: "text-orange-500", bg: "bg-orange-500/10", border: "border-orange-500/30" },
    CRITICAL: { icon: Skull, color: "text-red-500", bg: "bg-red-500/20", border: "border-red-500/50" },
};

export function LiveChronicleNode() {
    const events = useSimulationStore((state) => state.chronicleEvents);
    const containerRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        // Auto-scroll to top when new events arrive
        if (containerRef.current) {
            containerRef.current.scrollTop = 0;
        }
    }, [events]);

    return (
        <div className="flex flex-col h-full bg-slate-900/40 rounded-xl overflow-hidden border border-slate-800/60 shadow-inner relative">
            <div className="sticky top-0 z-10 px-4 py-3 bg-slate-900/80 backdrop-blur-md border-b border-slate-800 flex items-center justify-between">
                <h3 className="font-bold text-xs uppercase tracking-widest flex items-center gap-2 text-slate-200">
                    <ShieldAlert className="h-4 w-4 text-rose-500" />
                    Live Chronicle Feed
                </h3>
                <Badge variant="secondary" className="px-2 py-0 text-[9px] bg-rose-500/10 text-rose-400 border border-rose-500/20">
                    ● REC
                </Badge>
            </div>

            <div
                ref={containerRef}
                className="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar"
            >
                {events.length === 0 ? (
                    <div className="h-full flex flex-col items-center justify-center text-slate-500 opacity-50 space-y-2">
                        <Info className="h-8 w-8" />
                        <p className="text-[10px] uppercase tracking-widest font-bold">Awaiting historical events...</p>
                    </div>
                ) : (
                    events.map((evt, idx) => {
                        const config = SEVERITY_CONFIG[evt.severity] || SEVERITY_CONFIG.LOW;
                        const Icon = config.icon;
                        const isLatest = idx === 0;

                        return (
                            <div
                                key={`${evt.year}-${evt.type}-${idx}`}
                                className={cn(
                                    "p-3 rounded-lg border text-sm transition-all duration-500 group relative overflow-hidden",
                                    config.bg, config.border,
                                    isLatest ? "animate-in slide-in-from-left-4 fade-in-50" : "opacity-80"
                                )}
                            >
                                {isLatest && <div className={cn("absolute left-0 top-0 bottom-0 w-1", config.color.replace('text', 'bg'))} />}

                                <div className="flex items-start gap-3">
                                    <div className={cn("mt-0.5 shrink-0", config.color)}>
                                        <Icon className="h-4 w-4" />
                                    </div>
                                    <div className="flex-1 min-w-0">
                                        <div className="flex items-center justify-between gap-2 mb-1">
                                            <span className="font-bold text-slate-200 truncate pr-2 text-xs">
                                                {evt.title}
                                            </span>
                                            <span className="text-[9px] font-mono text-slate-400 shrink-0 uppercase tracking-wider">
                                                Year {evt.year}
                                            </span>
                                        </div>
                                        <p className="text-[11px] text-slate-400 leading-relaxed line-clamp-2 group-hover:line-clamp-none transition-all">
                                            {evt.description}
                                        </p>
                                        {evt.metadata && Object.keys(evt.metadata).length > 0 && (
                                            <div className="mt-2 flex flex-wrap gap-1">
                                                {Object.entries(evt.metadata).slice(0, 3).map(([k, v]) => (
                                                    <span key={k} className="text-[8px] bg-slate-950/50 px-1.5 py-0.5 rounded text-slate-400 border border-slate-800">
                                                        {k}: {String(v)}
                                                    </span>
                                                ))}
                                            </div>
                                        )}
                                    </div>
                                </div>
                            </div>
                        );
                    })
                )}
            </div>
        </div>
    );
}
