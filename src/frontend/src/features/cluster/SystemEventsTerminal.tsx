"use client";

import React from "react";
import { Terminal as TerminalIcon, Search, ShieldAlert, Cpu, Globe, Zap, History, Database, ArrowRight } from "lucide-react";
import { useClusterSnapshot } from "./useClusterApi";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { cn } from "@/lib/utils";

export function SystemEventsTerminal() {
    const { data: snapshot } = useClusterSnapshot();
    const { worlds = [] } = snapshot ?? {};

    const [filter, setFilter] = React.useState("");

    // Generate some mock events based on world data for flavor
    const events = React.useMemo(() => {
        const baseEvents = worlds.flatMap(w => [
            {
                time: new Date(Date.now() - Math.random() * 100000).toLocaleTimeString([], { hour12: false, fractionalSecondDigits: 2 }),
                type: 'WORLD',
                source: w.name,
                msg: `Tick complete. Stable convergence at cycle ${w.current_tick}. Energy: ${w.entropy?.toFixed(4)}.`,
                level: 'INFO'
            },
            {
                time: new Date(Date.now() - Math.random() * 200000).toLocaleTimeString([], { hour12: false, fractionalSecondDigits: 2 }),
                type: 'PHYSICS',
                source: w.name,
                msg: `Fine-tuning constants: Planck length mutation detected in sector 4G.`,
                level: 'WARN'
            }
        ]);

        const sysEvents = [
            { time: "22:15:32.42", type: "KERNEL", source: "Governor", msg: "Pressure threshold reached (0.85). Initiation resource rebalancing.", level: "IMPORTANT" },
            { time: "22:15:01.12", type: "AUTH", source: "Master", msg: "Session validated. High-level clearance granted.", level: "INFO" },
            { time: "22:14:45.89", type: "DB", source: "Ledger", msg: "State snapshot committed for 12 active universes.", level: "INFO" },
            { time: "22:14:20.15", type: "NETWORK", source: "Bridge", msg: "Latency spike detected in regional cluster JP-1. Auto-routing active.", level: "WARN" },
        ];

        return [...baseEvents, ...sysEvents].sort((a, b) => b.time.localeCompare(a.time));
    }, [worlds]);

    const filteredEvents = events.filter(e =>
        e.msg.toLowerCase().includes(filter.toLowerCase()) ||
        e.source.toLowerCase().includes(filter.toLowerCase()) ||
        e.type.toLowerCase().includes(filter.toLowerCase())
    );

    return (
        <div className="flex flex-col h-[calc(100vh-180px)] animate-in fade-in duration-1000">
            {/* Terminal Header */}
            <div className="bg-slate-900 border-b border-primary/20 p-4 flex items-center justify-between rounded-t-xl shadow-2xl">
                <div className="flex items-center gap-4">
                    <div className="flex gap-1.5 px-2">
                        <div className="h-3 w-3 rounded-full bg-error/50" />
                        <div className="h-3 w-3 rounded-full bg-warning/50" />
                        <div className="h-3 w-3 rounded-full bg-success/50" />
                    </div>
                    <div className="h-4 w-px bg-slate-700" />
                    <div className="flex items-center gap-2 text-primary font-mono text-xs font-black uppercase tracking-[0.2em]">
                        <TerminalIcon className="h-4 w-4" />
                        Cluster Event Stream v4.2
                    </div>
                </div>
                <div className="flex items-center gap-4">
                    <div className="relative h-9 w-64 group">
                        <Search className="absolute left-3 top-2.5 h-4 w-4 text-primary/40 group-focus-within:text-primary transition-colors" />
                        <input
                            type="text"
                            placeholder="grep cluster logs..."
                            className="w-full h-full bg-slate-800 border border-slate-700 rounded-md pl-10 pr-4 text-xs font-mono text-primary outline-none focus:border-primary/50 transition-all shadow-inner"
                            value={filter}
                            onChange={(e) => setFilter(e.target.value)}
                        />
                    </div>
                    <Button variant="ghost" size="sm" className="text-white hover:bg-white/10 h-9 font-black uppercase tracking-widest text-[10px]">Clear Buffer</Button>
                </div>
            </div>

            {/* Terminal Body */}
            <div className="flex-1 bg-slate-950 p-6 font-mono text-[11px] overflow-y-auto custom-scrollbar relative border-x border-slate-900 border-b rounded-b-xl shadow-inner group">
                {/* Background scanning line effect */}
                <div className="absolute inset-0 pointer-events-none opacity-5 bg-[linear-gradient(rgba(18,16,16,0)_50%,rgba(0,0,0,0.25)_50%),linear-gradient(90deg,rgba(255,0,0,0.06),rgba(0,255,0,0.02),rgba(0,0,255,0.06))] bg-[length:100%_2px,3px_100%]" />

                <div className="space-y-1.5 relative z-10">
                    <div className="text-primary/50 mb-4 flex flex-col gap-1">
                        <p>[SYSTEM] Establishing secure neural uplink to cluster control plane...</p>
                        <p>[SYSTEM] Handshake complete. Buffer size: 2048 records.</p>
                        <p>[SYSTEM] Listening for events on stream/v4/cluster/global</p>
                        <p className="border-b border-primary/20 pb-2 mb-2 w-fit">--------------------------------------------------------------</p>
                    </div>

                    {filteredEvents.map((event, idx) => (
                        <div key={idx} className="flex gap-4 group hover:bg-white/5 py-0.5 px-2 -mx-2 rounded transition-colors items-start">
                            <span className="text-slate-500 font-bold shrink-0">{event.time}</span>
                            <span className={cn(
                                "font-black shrink-0 px-1.5 py-0 rounded-[2px] uppercase text-[9px]",
                                event.level === 'INFO' && "bg-blue-900/40 text-blue-400 border border-blue-400/30",
                                event.level === 'WARN' && "bg-warning/20 text-warning border border-warning/30",
                                event.level === 'IMPORTANT' && "bg-error/20 text-error border border-error/40 animate-pulse"
                            )}>
                                {event.level}
                            </span>
                            <span className="text-primary font-bold shrink-0">[{event.type}:{event.source}]</span>
                            <span className="text-slate-200 leading-relaxed font-medium">{event.msg}</span>
                        </div>
                    ))}

                    {filteredEvents.length === 0 && (
                        <div className="text-center py-20 text-slate-500 italic">
                            No logs found matching filter: "{filter}"
                        </div>
                    )}

                    <div className="flex gap-3 mt-4 text-primary animate-pulse">
                        <span className="shrink-0 font-bold">{new Date().toLocaleTimeString([], { hour12: false, fractionalSecondDigits: 2 })}</span>
                        <span className="font-black shrink-0">[READY]</span>
                        <span>_</span>
                    </div>
                </div>
            </div>

            {/* Quick Filter Bar */}
            <div className="mt-4 grid grid-cols-6 gap-3">
                {[
                    { label: 'Criticals', color: 'bg-error', icon: ShieldAlert },
                    { label: 'Neural', color: 'bg-primary', icon: Cpu },
                    { label: 'Sagas', color: 'bg-success', icon: History },
                    { label: 'Physics', color: 'bg-warning', icon: Globe },
                    { label: 'Governance', color: 'bg-slate-700', icon: Database },
                    { label: 'Security', color: 'bg-slate-900', icon: Zap },
                ].map((tag, i) => (
                    <button
                        key={i}
                        className="glass-card hover:bg-white/20 transition-all p-3 flex flex-col items-center gap-2 group border border-white/40"
                        onClick={() => setFilter(tag.label)}
                    >
                        <tag.icon className={cn("h-4 w-4", tag.color.replace('bg-', 'text-'))} />
                        <span className="text-[9px] font-black uppercase tracking-[0.1em] text-muted-foreground group-hover:text-primary">{tag.label}</span>
                    </button>
                ))}
            </div>
        </div>
    );
}
