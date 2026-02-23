"use client";

import { useState } from "react";
import { useQuery, useMutation } from "@tanstack/react-query";
import { useRouter } from "next/navigation";
import { Sparkles, Globe, Loader2, ArrowRight, Zap, Target, BookOpen, Skull, Users, Activity } from "lucide-react";
import { writerApi } from "@/shared/api/writer";

// Map archetype icons for aesthetics
const ARCHETYPE_ICONS: Record<string, React.ReactNode> = {
    ascension_mysticism: <Sparkles className="w-5 h-5 text-amber-400" />,
    martial_honor_hierarchy: <Target className="w-5 h-5 text-red-500" />,
    tech_stratified_world: <Zap className="w-5 h-5 text-cyan-400" />,
    political_intrigue_mortality: <Users className="w-5 h-5 text-purple-400" />,
    entropic_collapse: <Skull className="w-5 h-5 text-emerald-500" />,
    hybrid_evolution: <Globe className="w-5 h-5 text-blue-400" />,
    cultural_slice_of_life: <BookOpen className="w-5 h-5 text-orange-400" />,
};

export default function GenesisPage() {
    const router = useRouter();
    const [selectedPreset, setSelectedPreset] = useState<string | null>(null);
    const [selectedPresetData, setSelectedPresetData] = useState<any>(null);
    const [name, setName] = useState("");
    const [isInitializing, setIsInitializing] = useState(false);

    // Fetch Presets
    const { data: categories, isLoading: isPresetsLoading } = useQuery({
        queryKey: ["genesis-presets"],
        queryFn: () => writerApi.genesis.presets(),
    });

    // Single Mutation for Genesis (World Creation Only)
    const genesisMutation = useMutation({
        mutationFn: async () => {
            if (!name.trim() || !selectedPreset) throw new Error("Missing inputs");
            setIsInitializing(true);

            // Create World based on selected Preset boundaries
            const world = await writerApi.worlds.create({
                name: name,
                preset: selectedPreset,
                origin_type: 'cosmic'
            });

            return world.id;
        },
        onSuccess: (worldId) => {
            // Small delay for cinematic effect
            setTimeout(() => {
                setIsInitializing(false);
                router.push(`/console/worlds/${worldId}`);
            }, 1500);
        },
        onError: (error) => {
            console.error("Genesis failed:", error);
            setIsInitializing(false);
            alert("Failed to initialize system. Check console for details.");
        }
    });

    const handleGenesis = () => {
        if (!name.trim() || !selectedPreset) return;
        genesisMutation.mutate();
    };

    const handleSelectPreset = (preset: any) => {
        setSelectedPreset(preset.key);
        setSelectedPresetData(preset);

        const genre = preset.genre?.toUpperCase() || 'UNKNOWN';
        const power = preset.power_system?.toUpperCase() || 'UNKNOWN';

        // Tạo chuỗi thời gian gọn YYYYMMDDHHMM
        const now = new Date();
        const timeStr = now.getFullYear().toString() +
            String(now.getMonth() + 1).padStart(2, '0') +
            String(now.getDate()).padStart(2, '0') +
            String(now.getHours()).padStart(2, '0') +
            String(now.getMinutes()).padStart(2, '0');

        setName(`W_${genre}_${power}_${timeStr}`);
    };

    if (isInitializing) {
        return (
            <div className="min-h-screen flex flex-col items-center justify-center bg-black text-white px-4 relative overflow-hidden">
                <div className="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(59,130,246,0.15),transparent_70%)]" />
                <div className="relative z-10 flex flex-col items-center animate-pulse">
                    <div className="w-32 h-32 rounded-full border-t-2 border-blue-500 animate-spin flex items-center justify-center mb-8 relative">
                        <div className="absolute inset-2 rounded-full border-r-2 border-cyan-400 animate-spin-slow"></div>
                        <div className="absolute inset-4 rounded-full border-l-2 border-purple-500 animate-spin-reverse"></div>
                        <Sparkles className="w-8 h-8 text-blue-400 animate-bounce" />
                    </div>
                    <h2 className="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-cyan-300 font-vi tracking-widest uppercase">
                        Initiating Genesis...
                    </h2>
                    <p className="mt-4 text-slate-400 font-mono text-sm max-w-md text-center">
                        Forging state vectors. Mapping ontological rules. Igniting the spark of time.
                    </p>
                </div>
            </div>
        );
    }

    return (
        <div className="max-w-6xl mx-auto space-y-8 pb-12">
            {/* Header */}
            <div className="relative rounded-2xl overflow-hidden glass-card p-10 border border-slate-800/50">
                <div className="absolute top-0 right-0 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2" />
                <div className="relative z-10 max-w-2xl">
                    <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-sm font-medium mb-6">
                        <Sparkles className="w-4 h-4" />
                        <span>God Mode Enabled</span>
                    </div>
                    <h1 className="text-4xl sm:text-5xl font-bold text-white mb-4 tracking-tight font-vi">
                        System Genesis
                    </h1>
                    <p className="text-slate-400 text-lg leading-relaxed">
                        Establish the foundational parameters of a new reality. This action will simultaneously generate a corresponding Saga, a physical World container, and ignite the first Universe at Tick 0.
                    </p>
                </div>
            </div>

            {/* Configuration Form */}
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {/* Left Column: Naming & Activation */}
                <div className="space-y-6">
                    <div className="glass-card p-6 rounded-xl border border-slate-800/50">
                        <h2 className="text-xl font-semibold text-white mb-6 flex items-center gap-2 font-vi">
                            <Globe className="w-5 h-5 text-indigo-400" />
                            1. Identifier
                        </h2>
                        <div className="space-y-4">
                            <div>
                                <label className="block text-sm font-medium text-slate-400 mb-2">Project Name (Saga/World Root)</label>
                                <input
                                    type="text"
                                    value={name}
                                    onChange={(e) => setName(e.target.value)}
                                    placeholder="e.g. Project Eden, The Last City..."
                                    className="w-full bg-slate-900/50 border border-slate-700/50 rounded-lg px-4 py-3 text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all font-mono"
                                />
                            </div>
                        </div>
                    </div>

                    {/* Hiển thị tham số cấu hình khi đã chọn Preset */}
                    {selectedPresetData && (
                        <div className="glass-card p-6 rounded-xl border border-slate-800/50">
                            <h2 className="text-xl font-semibold text-white mb-4 flex items-center gap-2 font-vi">
                                <Activity className="w-5 h-5 text-cyan-400" />
                                Seed Configuration Details
                            </h2>
                            <div className="space-y-4 text-sm">
                                <div className="grid grid-cols-2 gap-3">
                                    <div className="bg-slate-900/60 p-3 rounded-lg border border-slate-700/50">
                                        <span className="text-slate-500 block mb-1 uppercase tracking-wider text-[10px]">Genre</span>
                                        <span className="text-emerald-400 font-mono">{selectedPresetData.genre || 'N/A'}</span>
                                    </div>
                                    <div className="bg-slate-900/60 p-3 rounded-lg border border-slate-700/50">
                                        <span className="text-slate-500 block mb-1 uppercase tracking-wider text-[10px]">Power System</span>
                                        <span className="text-amber-400 font-mono">{selectedPresetData.power_system || 'N/A'}</span>
                                    </div>
                                </div>
                                {selectedPresetData.seed_vector && (
                                    <div className="bg-slate-900/60 p-4 rounded-lg border border-slate-700/50 w-full overflow-hidden">
                                        <span className="text-slate-500 block mb-3 uppercase tracking-wider text-xs border-b border-slate-800 pb-2">Seed Vector (Max-Min Bounds)</span>
                                        <div className="grid grid-cols-1 gap-y-2">
                                            {Object.entries(selectedPresetData.seed_vector).flatMap(([category, dims]: [string, any]) =>
                                                Object.entries(dims).map(([dim, range]: [string, any]) => (
                                                    <div key={dim} className="flex justify-between items-center bg-black/40 p-2 rounded border border-slate-800/50">
                                                        <span className="text-slate-400 capitalize text-xs truncate mr-2" title={dim}>{dim}</span>
                                                        <span className="text-cyan-400 font-mono text-[11px] whitespace-nowrap bg-blue-900/20 px-2 py-0.5 rounded">
                                                            {Array.isArray(range) ? `${range[0]} → ${range[1]}` : range}
                                                        </span>
                                                    </div>
                                                ))
                                            )}
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>
                    )}

                    <div className="glass-card p-6 rounded-xl border border-slate-800/50">
                        <h2 className="text-xl font-semibold text-white mb-4 flex items-center gap-2 font-vi">
                            <Zap className="w-5 h-5 text-amber-400" />
                            2. Ignition
                        </h2>
                        <p className="text-slate-400 text-sm mb-6">
                            Review your selections. Genesis is an irreversible systemic event.
                        </p>
                        <button
                            onClick={handleGenesis}
                            disabled={!name.trim() || !selectedPreset || genesisMutation.isPending}
                            className="w-full relative group overflow-hidden rounded-lg p-px"
                        >
                            <span className="absolute inset-0 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500 opacity-70 group-hover:opacity-100 transition-opacity duration-300"></span>
                            <div className="relative flex items-center justify-center gap-2 bg-slate-900 px-6 py-4 rounded-lg transition-all duration-300 group-hover:bg-slate-900/50">
                                <span className="text-white font-semibold tracking-wide uppercase text-sm font-vi">
                                    {genesisMutation.isPending ? "Igniting..." : "Let There Be Light"}
                                </span>
                                <ArrowRight className={`w-5 h-5 text-white transition-transform duration-300 ${name && selectedPreset ? 'group-hover:translate-x-1' : ''}`} />
                            </div>
                        </button>
                    </div>
                </div>

                {/* Right Column: Presets */}
                <div className="lg:col-span-2 space-y-6">
                    <div className="glass-card p-6 rounded-xl border border-slate-800/50 h-full">
                        <h2 className="text-xl font-semibold text-white mb-2 flex items-center gap-2 font-vi">
                            <BookOpen className="w-5 h-5 text-emerald-400" />
                            3. Seed Configuration (Presets)
                        </h2>
                        <p className="text-slate-400 text-sm mb-6">
                            Select the foundational archetype. This determines the initial state boundaries, physical laws, and evolutionary trajectory of the universe.
                        </p>

                        {isPresetsLoading ? (
                            <div className="flex items-center justify-center py-20">
                                <Loader2 className="w-8 h-8 text-blue-500 animate-spin" />
                            </div>
                        ) : (
                            <div className="space-y-8">
                                {categories && Object.entries(categories).map(([catKey, cat]: [string, any]) => (
                                    <div key={catKey}>
                                        <h3 className="text-lg font-medium text-slate-300 mb-4 font-vi tracking-wide border-b border-slate-800 pb-2">
                                            {cat.label}
                                        </h3>
                                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            {cat.presets.map((preset: any) => {
                                                const isSelected = selectedPreset === preset.key;
                                                const icon = ARCHETYPE_ICONS[preset.archetype] || <Globe className="w-5 h-5 text-slate-400" />;

                                                return (
                                                    <div
                                                        key={preset.key}
                                                        onClick={() => handleSelectPreset(preset)}
                                                        className={`
                              relative p-5 rounded-xl border transition-all duration-300 cursor-pointer overflow-hidden group
                              ${isSelected
                                                                ? 'bg-blue-500/10 border-blue-500/50 shadow-[0_0_15px_rgba(59,130,246,0.15)]'
                                                                : 'bg-slate-900/40 border-slate-800/60 hover:border-slate-600 hover:bg-slate-800/40'}
                            `}
                                                    >
                                                        <div className="flex items-start gap-4 relative z-10">
                                                            <div className={`
                                p-2.5 rounded-lg shrink-0 transition-colors
                                ${isSelected ? 'bg-blue-500/20' : 'bg-slate-800 group-hover:bg-slate-700'}
                              `}>
                                                                {icon}
                                                            </div>
                                                            <div className="flex-1 min-w-0">
                                                                <h4 className={`font-medium mb-1 truncate ${isSelected ? 'text-blue-400' : 'text-slate-200'}`}>
                                                                    {preset.icon} {preset.name}
                                                                </h4>
                                                                <p className="text-sm text-slate-500 line-clamp-2 leading-relaxed">
                                                                    {preset.description}
                                                                </p>
                                                            </div>
                                                        </div>

                                                        {/* Selected Indicator */}
                                                        {isSelected && (
                                                            <div className="absolute top-3 right-3">
                                                                <span className="flex h-3 w-3 relative">
                                                                    <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                                                    <span className="relative inline-flex rounded-full h-3 w-3 bg-blue-500"></span>
                                                                </span>
                                                            </div>
                                                        )}
                                                    </div>
                                                );
                                            })}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}
