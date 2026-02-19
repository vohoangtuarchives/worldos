"use client";

import { useState, useMemo } from "react";
import { useQuery } from "@tanstack/react-query";
import { writerApi, MaterialTemplate, MaterialDetail, MutationPathway } from "./../../shared/api/writer";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
    BookOpen,
    Search,
    Filter,
    ArrowRight,
    Layers,
    Zap,
    Shield,
    Flame,
    AlertTriangle,
    ChevronRight,
    X,
    Activity,
    GitBranch,
    Users
} from "lucide-react";
import { cn } from "@/lib/utils";

/* ── Vietnamese labels ── */
const ONTOLOGY_LABELS: Record<string, { label: string; icon: typeof Layers; color: string }> = {
    symbolic: { label: "Biểu Tượng", icon: BookOpen, color: "text-purple-600 bg-purple-50 border-purple-200" },
    institutional: { label: "Thể Chế", icon: Shield, color: "text-blue-600 bg-blue-50 border-blue-200" },
    behavioral: { label: "Hành Vi", icon: Users, color: "text-green-600 bg-green-50 border-green-200" },
    structural: { label: "Cấu Trúc", icon: Layers, color: "text-amber-600 bg-amber-50 border-amber-200" },
};

const FUNCTION_LABELS: Record<string, { label: string; color: string }> = {
    legitimizing: { label: "Hợp Thức Hóa", color: "text-emerald-700 bg-emerald-50" },
    stabilizing: { label: "Ổn Định", color: "text-sky-700 bg-sky-50" },
    transformative: { label: "Biến Đổi", color: "text-violet-700 bg-violet-50" },
    destructive: { label: "Phá Hủy", color: "text-red-700 bg-red-50" },
    destabilizing: { label: "Gây Bất Ổn", color: "text-orange-700 bg-orange-50" },
};

const LIFECYCLE_LABELS: Record<string, string> = {
    dormant: "Ngủ Đông",
    active: "Hoạt Động",
    decaying: "Suy Thoái",
    legacy: "Di Sản",
};

export function MaterialWikiView() {
    const { data: catalogData, isLoading } = useQuery({
        queryKey: ["material-catalog"],
        queryFn: () => writerApi.materials.catalog(),
    });

    const [searchQuery, setSearchQuery] = useState("");
    const [ontologyFilter, setOntologyFilter] = useState<string | null>(null);
    const [functionFilter, setFunctionFilter] = useState<string | null>(null);
    const [selectedCode, setSelectedCode] = useState<string | null>(null);

    // Flatten catalog for filtering
    const allMaterials = useMemo(() => {
        if (!catalogData?.catalog) return [];
        const flat: MaterialTemplate[] = [];
        Object.values(catalogData.catalog).forEach(funcMap => {
            Object.values(funcMap).forEach(materials => {
                flat.push(...materials);
            });
        });
        return flat;
    }, [catalogData]);

    const filtered = useMemo(() => {
        return allMaterials.filter(m => {
            if (ontologyFilter && m.ontology !== ontologyFilter) return false;
            if (functionFilter && m.function !== functionFilter) return false;
            if (searchQuery) {
                const q = searchQuery.toLowerCase();
                return m.code.toLowerCase().includes(q);
            }
            return true;
        });
    }, [allMaterials, ontologyFilter, functionFilter, searchQuery]);

    if (isLoading) {
        return (
            <div className="flex items-center gap-2 text-muted-foreground animate-pulse p-8">
                <BookOpen className="h-5 w-5" />
                <span>Đang tải Bách Khoa Vật Liệu...</span>
            </div>
        );
    }

    return (
        <div className="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
            {/* Header */}
            <div className="glass-card p-6">
                <div className="flex items-center gap-4 mb-2">
                    <div className="h-12 w-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white shadow-lg">
                        <BookOpen className="h-6 w-6" />
                    </div>
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Bách Khoa Vật Liệu</h1>
                        <p className="text-sm text-muted-foreground">
                            Tra cứu {catalogData?.totals?.materials ?? 0} vật liệu lịch sử — phân loại theo bản thể và chức năng
                        </p>
                    </div>
                </div>
            </div>

            <div className="flex gap-6">
                {/* Left: Filter + List */}
                <div className="flex-1 min-w-0 space-y-4">
                    {/* Search & Filter Bar */}
                    <div className="glass-panel p-4 rounded-2xl space-y-4">
                        <div className="relative">
                            <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <input
                                type="text"
                                placeholder="Tìm material code..."
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-border/50 bg-white/50 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/30 transition-all"
                            />
                        </div>

                        {/* Ontology chips */}
                        <div className="space-y-2">
                            <span className="text-[10px] uppercase tracking-wider font-bold text-muted-foreground flex items-center gap-1.5">
                                <Filter className="h-3 w-3" /> Phân loại bản thể
                            </span>
                            <div className="flex flex-wrap gap-2">
                                {Object.entries(ONTOLOGY_LABELS).map(([key, { label, color }]) => (
                                    <button
                                        key={key}
                                        onClick={() => setOntologyFilter(ontologyFilter === key ? null : key)}
                                        className={cn(
                                            "px-3 py-1.5 rounded-lg text-xs font-bold border transition-all",
                                            ontologyFilter === key
                                                ? color + " shadow-sm"
                                                : "text-muted-foreground bg-white/30 border-border/30 hover:bg-white/60"
                                        )}
                                    >
                                        {label}
                                        {catalogData?.totals?.by_ontology?.[key] && (
                                            <span className="ml-1 opacity-60">({catalogData.totals.by_ontology[key]})</span>
                                        )}
                                    </button>
                                ))}
                            </div>
                        </div>

                        {/* Function chips */}
                        <div className="space-y-2">
                            <span className="text-[10px] uppercase tracking-wider font-bold text-muted-foreground">Chức năng</span>
                            <div className="flex flex-wrap gap-2">
                                {Object.entries(FUNCTION_LABELS).map(([key, { label, color }]) => (
                                    <button
                                        key={key}
                                        onClick={() => setFunctionFilter(functionFilter === key ? null : key)}
                                        className={cn(
                                            "px-3 py-1.5 rounded-lg text-xs font-bold border transition-all",
                                            functionFilter === key
                                                ? color + " border-current/20 shadow-sm"
                                                : "text-muted-foreground bg-white/30 border-border/30 hover:bg-white/60"
                                        )}
                                    >
                                        {label}
                                        {catalogData?.totals?.by_function?.[key] && (
                                            <span className="ml-1 opacity-60">({catalogData.totals.by_function[key]})</span>
                                        )}
                                    </button>
                                ))}
                            </div>
                        </div>

                        {(ontologyFilter || functionFilter) && (
                            <button
                                onClick={() => { setOntologyFilter(null); setFunctionFilter(null); }}
                                className="text-xs text-muted-foreground hover:text-foreground flex items-center gap-1"
                            >
                                <X className="h-3 w-3" /> Xóa bộ lọc
                            </button>
                        )}
                    </div>

                    {/* Material Grid */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                        {filtered.map((m) => {
                            const onto = ONTOLOGY_LABELS[m.ontology] ?? { label: m.ontology, color: "text-gray-600 bg-gray-50 border-gray-200" };
                            const func = FUNCTION_LABELS[m.function] ?? { label: m.function, color: "text-gray-700 bg-gray-50" };
                            const isSelected = selectedCode === m.code;

                            return (
                                <button
                                    key={m.id}
                                    onClick={() => setSelectedCode(isSelected ? null : m.code)}
                                    className={cn(
                                        "glass-card p-4 text-left transition-all group",
                                        isSelected
                                            ? "border-primary/40 shadow-md ring-1 ring-primary/20"
                                            : "hover:border-primary/20 hover:shadow-sm"
                                    )}
                                >
                                    <div className="flex items-start justify-between mb-2">
                                        <h3 className="font-bold text-sm tracking-wide">{m.code.replace(/_/g, " ")}</h3>
                                        <ChevronRight className={cn("h-4 w-4 text-muted-foreground transition-transform", isSelected && "rotate-90")} />
                                    </div>

                                    <div className="flex flex-wrap gap-1.5 mb-2">
                                        <Badge variant="outline" className={cn("text-[10px] font-bold", onto.color)}>{onto.label}</Badge>
                                        <Badge variant="outline" className={cn("text-[10px] font-bold", func.color)}>{func.label}</Badge>
                                        {m.default_lifecycle && (
                                            <Badge variant="secondary" className="text-[10px]">
                                                {LIFECYCLE_LABELS[m.default_lifecycle] ?? m.default_lifecycle}
                                            </Badge>
                                        )}
                                    </div>

                                    {(m.preconditions?.length ?? 0) > 0 && (
                                        <p className="text-[10px] text-muted-foreground font-mono truncate">
                                            Kích hoạt: {m.preconditions?.join(", ")}
                                        </p>
                                    )}
                                </button>
                            );
                        })}
                    </div>

                    {filtered.length === 0 && (
                        <div className="text-center py-12 text-muted-foreground text-sm">
                            Không tìm thấy material nào phù hợp.
                        </div>
                    )}
                </div>

                {/* Right: Detail Panel */}
                <div className="w-[400px] shrink-0">
                    {selectedCode ? (
                        <MaterialDetailPanel code={selectedCode} onClose={() => setSelectedCode(null)} />
                    ) : (
                        <div className="glass-panel p-6 rounded-2xl text-center text-muted-foreground">
                            <BookOpen className="h-8 w-8 mx-auto mb-3 opacity-40" />
                            <p className="text-sm font-medium">Chọn một vật liệu để xem chi tiết</p>
                            <p className="text-xs mt-1">Bao gồm: mutation pathways, áp lực, và archetype affinity</p>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}

/* ── Detail Panel ── */
function MaterialDetailPanel({ code, onClose }: { code: string; onClose: () => void }) {
    const { data, isLoading } = useQuery({
        queryKey: ["material-detail", code],
        queryFn: () => writerApi.materials.detail(code),
    });

    if (isLoading) {
        return <div className="glass-panel p-6 rounded-2xl animate-pulse text-muted-foreground text-sm">Đang tải...</div>;
    }

    const mat = data as MaterialDetail | undefined;
    if (!mat) return null;

    const onto = ONTOLOGY_LABELS[mat.ontology] ?? { label: mat.ontology, color: "text-gray-600 bg-gray-50 border-gray-200", icon: Layers };
    const func = FUNCTION_LABELS[mat.function] ?? { label: mat.function, color: "text-gray-700 bg-gray-50" };
    const OntoIcon = onto.icon;

    return (
        <div className="glass-panel p-6 rounded-2xl space-y-5 sticky top-4 animate-in fade-in slide-in-from-right-4 duration-300">
            {/* Header */}
            <div className="flex items-start justify-between">
                <div className="flex items-center gap-3">
                    <div className={cn("h-10 w-10 rounded-lg flex items-center justify-center", onto.color)}>
                        <OntoIcon className="h-5 w-5" />
                    </div>
                    <div>
                        <h3 className="font-bold text-base">{mat.code.replace(/_/g, " ")}</h3>
                        <div className="flex gap-1.5 mt-1">
                            <Badge variant="outline" className={cn("text-[9px] font-bold", onto.color)}>{onto.label}</Badge>
                            <Badge variant="outline" className={cn("text-[9px] font-bold", func.color)}>{func.label}</Badge>
                        </div>
                    </div>
                </div>
                <button onClick={onClose} className="text-muted-foreground hover:text-foreground p-1">
                    <X className="h-4 w-4" />
                </button>
            </div>

            {/* Usage */}
            {mat.usage && (
                <div className="grid grid-cols-2 gap-3">
                    <div className="p-3 rounded-lg bg-muted/30 border border-border/30">
                        <span className="text-[9px] uppercase tracking-wider font-bold text-muted-foreground">Tổng Instances</span>
                        <p className="text-lg font-bold">{mat.usage.total_instances}</p>
                    </div>
                    <div className="p-3 rounded-lg bg-muted/30 border border-border/30">
                        <span className="text-[9px] uppercase tracking-wider font-bold text-muted-foreground">Đang Hoạt Động</span>
                        <p className="text-lg font-bold text-success">{mat.usage.active_instances}</p>
                    </div>
                </div>
            )}

            {/* Preconditions */}
            {(mat.preconditions?.length ?? 0) > 0 && (
                <div className="space-y-2">
                    <h4 className="text-[10px] uppercase tracking-wider font-bold text-muted-foreground flex items-center gap-1.5">
                        <Zap className="h-3 w-3" /> Điều kiện kích hoạt
                    </h4>
                    <div className="space-y-1">
                        {mat.preconditions!.map((p, i) => (
                            <div key={i} className="px-3 py-1.5 rounded-lg bg-amber-50/50 border border-amber-200/30 text-xs font-mono text-amber-800">
                                {p}
                            </div>
                        ))}
                    </div>
                </div>
            )}

            {/* Pressure I/O */}
            {mat.pressure_outputs && Object.keys(mat.pressure_outputs).length > 0 && (
                <div className="space-y-2">
                    <h4 className="text-[10px] uppercase tracking-wider font-bold text-muted-foreground flex items-center gap-1.5">
                        <Activity className="h-3 w-3" /> Áp lực đầu ra
                    </h4>
                    <div className="space-y-1">
                        {Object.entries(mat.pressure_outputs).map(([key, val]) => (
                            <div key={key} className="flex items-center justify-between px-3 py-1.5 rounded-lg bg-muted/20 border border-border/20">
                                <span className="text-xs font-medium">{key}</span>
                                <span className={cn("text-xs font-mono font-bold", Number(val) > 0 ? "text-success" : "text-error")}>
                                    {Number(val) > 0 ? "+" : ""}{String(val)}
                                </span>
                            </div>
                        ))}
                    </div>
                </div>
            )}

            {/* Mutation Pathways */}
            {(mat.mutation_pathways?.length ?? 0) > 0 && (
                <div className="space-y-2">
                    <h4 className="text-[10px] uppercase tracking-wider font-bold text-muted-foreground flex items-center gap-1.5">
                        <GitBranch className="h-3 w-3" /> Đường biến thể
                    </h4>
                    <div className="space-y-2">
                        {mat.mutation_pathways!.map((p: MutationPathway, i: number) => (
                            <div key={i} className="p-3 rounded-lg bg-violet-50/30 border border-violet-200/30 space-y-1">
                                <div className="flex items-center gap-2 text-xs">
                                    <span className="font-bold">{mat.code}</span>
                                    <ArrowRight className="h-3 w-3 text-violet-500" />
                                    <span className="font-bold text-violet-700">{p.target_code}</span>
                                    <Badge variant="secondary" className="text-[9px] ml-auto">{(p.strength_transfer * 100).toFixed(0)}%</Badge>
                                </div>
                                <p className="text-[10px] text-muted-foreground">{p.description}</p>
                                <p className="text-[10px] font-mono text-violet-600">Khi: {p.trigger_condition}</p>
                            </div>
                        ))}
                    </div>
                </div>
            )}

            {/* Incompatible */}
            {(mat.incompatible_with?.length ?? 0) > 0 && (
                <div className="space-y-2">
                    <h4 className="text-[10px] uppercase tracking-wider font-bold text-muted-foreground flex items-center gap-1.5">
                        <AlertTriangle className="h-3 w-3" /> Không tương thích
                    </h4>
                    <div className="flex flex-wrap gap-1.5">
                        {mat.incompatible_with!.map((code, i) => (
                            <Badge key={i} variant="outline" className="text-[10px] text-red-600 border-red-200 bg-red-50/50">
                                {code.replace(/_/g, " ")}
                            </Badge>
                        ))}
                    </div>
                </div>
            )}

            {/* Affinity */}
            {mat.affinity?.archetypes && (
                <div className="space-y-2">
                    <h4 className="text-[10px] uppercase tracking-wider font-bold text-muted-foreground">Archetype Affinity</h4>
                    <div className="flex flex-wrap gap-1.5">
                        {mat.affinity.archetypes.map((a, i) => (
                            <Badge key={i} variant="secondary" className="text-[10px]">{a}</Badge>
                        ))}
                    </div>
                    {mat.affinity.activation_threshold != null && (
                        <p className="text-[10px] text-muted-foreground">
                            Ngưỡng kích hoạt: <span className="font-mono font-bold">{mat.affinity.activation_threshold}</span>
                            {mat.affinity.drift_modifier != null && (
                                <> · Drift: <span className="font-mono font-bold">{mat.affinity.drift_modifier}</span></>
                            )}
                        </p>
                    )}
                </div>
            )}
        </div>
    );
}
