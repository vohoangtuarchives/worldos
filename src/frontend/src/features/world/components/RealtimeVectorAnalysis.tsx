"use client";

import React from 'react';
import { useSimulationStore } from '../stores/useSimulationStore';

export function RealtimeVectorAnalysis() {
    const currentIdeology = useSimulationStore((state) => state.currentIdeology);
    const currentCulture = useSimulationStore((state) => state.currentCulture);

    const renderBars = (title: string, data: Record<string, number>, colorClass: string) => {
        return (
            <div className="flex-1 min-w-[200px] border border-slate-800 rounded-xl p-4 bg-slate-900/30">
                <h4 className="text-xs font-bold uppercase tracking-widest text-muted-foreground mb-4">{title}</h4>
                <div className="space-y-3">
                    {Object.entries(data).map(([key, val]) => (
                        <div key={key}>
                            <div className="flex items-center justify-between text-[10px] uppercase font-mono font-bold mb-1">
                                <span>{key.replace('_', ' ')}</span>
                                <span className={val >= 0.8 ? "text-rose-400" : ""}>{(val * 100).toFixed(1)}%</span>
                            </div>
                            <div className="h-1.5 w-full bg-slate-800 rounded-full overflow-hidden">
                                <div
                                    className={`h-full ${val >= 0.8 ? 'bg-rose-500' : colorClass} transition-all duration-75`}
                                    style={{ width: `${Math.max(0, Math.min(100, val * 100))}%` }}
                                />
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        );
    };

    return (
        <div className="flex flex-col gap-4">
            <div className="flex items-center gap-2 mb-2">
                <div className="h-2 w-2 rounded-full bg-sky-500 animate-pulse" />
                <span className="text-[10px] font-bold text-sky-400 uppercase tracking-widest">Real-time Ontology State</span>
            </div>
            <div className="flex flex-col md:flex-row gap-4">
                {renderBars("Ideology System", currentIdeology, "bg-sky-500")}
                {renderBars("Cultural Footprint", currentCulture, "bg-emerald-500")}
            </div>
        </div>
    );
}
