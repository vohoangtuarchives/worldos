"use client";

import { useQuery } from "@tanstack/react-query";
import { writerApi, MaterialInstanceItem, MaterialTimelineEvent } from "./../../shared/api/writer";
import { Badge } from "@/components/ui/badge";
import {
    Layers,
    Activity,
    Shield,
    BookOpen,
    Users,
    ArrowRight,
    PlayCircle,
    StopCircle,
    GitBranch
} from "lucide-react";
import { cn } from "@/lib/utils";

const ONTOLOGY_COLORS: Record<string, string> = {
    symbolic: "text-purple-600 bg-purple-50 border-purple-200",
    institutional: "text-blue-600 bg-blue-50 border-blue-200",
    behavioral: "text-green-600 bg-green-50 border-green-200",
    structural: "text-amber-600 bg-amber-50 border-amber-200",
};

const ONTOLOGY_LABELS: Record<string, string> = {
    symbolic: "Biểu Tượng", institutional: "Thể Chế", behavioral: "Hành Vi", structural: "Cấu Trúc",
};

const FUNCTION_LABELS: Record<string, string> = {
    legitimizing: "Hợp Thức Hóa", stabilizing: "Ổn Định", transformative: "Biến Đổi",
    destructive: "Phá Hủy", destabilizing: "Gây Bất Ổn",
};

export function WorldMaterialsView({ worldId }: { worldId: string }) {
    const { data, isLoading, error } = useQuery({
        queryKey: ["world-materials", worldId],
        queryFn: () => writerApi.materials.worldInstances(worldId),
        enabled: !!worldId,
    });

    const { data: timelineData } = useQuery({
        queryKey: ["world-materials-timeline", worldId],
        queryFn: () => writerApi.materials.timeline(worldId),
        enabled: !!worldId,
    });

    if (isLoading) {
        return (
            <div className="flex items-center gap-2 text-muted-foreground animate-pulse p-8">
                <Layers className="h-5 w-5" />
                <span>Đang tải thông tin Vật Liệu...</span>
            </div>
        );
    }

    if (error) {
        return (
            <div className="glass-card border-error/20 p-6 text-error">
                <h3 className="font-bold">Lỗi</h3>
                <p className="text-sm">Không thể tải thông tin material cho world này.</p>
            </div>
        );
    }

    const instances = data?.instances ?? [];
    const lifecycle = data?.lifecycle ?? { dormant: 0, active: 0, retired: 0 };
    const events = timelineData?.events ?? [];

    const activeInstances = instances.filter((i: MaterialInstanceItem) => i.is_active);
    const dormantInstances = instances.filter((i: MaterialInstanceItem) => !i.is_active && !i.is_retired);
    const retiredInstances = instances.filter((i: MaterialInstanceItem) => i.is_retired);

    return (
        <div className="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
            {/* Header */}
            <div className="glass-card p-6">
                <div className="flex items-center gap-4 mb-4">
                    <div className="h-12 w-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white shadow-lg">
                        <Layers className="h-6 w-6" />
                    </div>
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight">Vật Liệu Thế Giới</h2>
                        <p className="text-sm text-muted-foreground">
                            {data?.world_name ?? worldId} · {data?.total ?? 0} vật liệu
                        </p>
                    </div>
                </div>

                {/* Lifecycle Overview */}
                <div className="grid grid-cols-3 gap-4">
                    <div className="p-4 rounded-xl bg-muted/30 border border-border/50">
                        <span className="text-[10px] uppercase tracking-wider font-bold text-muted-foreground">Hoạt Động</span>
                        <p className="text-2xl font-bold text-success">{lifecycle.active}</p>
                    </div>
                    <div className="p-4 rounded-xl bg-muted/30 border border-border/50">
                        <span className="text-[10px] uppercase tracking-wider font-bold text-muted-foreground">Ngủ Đông</span>
                        <p className="text-2xl font-bold text-muted-foreground">{lifecycle.dormant}</p>
                    </div>
                    <div className="p-4 rounded-xl bg-muted/30 border border-border/50">
                        <span className="text-[10px] uppercase tracking-wider font-bold text-muted-foreground">Đã Ngừng</span>
                        <p className="text-2xl font-bold text-error">{lifecycle.retired}</p>
                    </div>
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* Active Materials */}
                <div className="lg:col-span-2 space-y-3">
                    <h3 className="text-sm font-bold uppercase tracking-wider flex items-center gap-2 px-1">
                        <Activity className="h-4 w-4 text-success" /> Vật Liệu Hoạt Động ({activeInstances.length})
                    </h3>

                    {activeInstances.length > 0 ? (
                        <div className="space-y-2">
                            {activeInstances.map((inst: MaterialInstanceItem) => (
                                <MaterialInstanceCard key={inst.id} instance={inst} />
                            ))}
                        </div>
                    ) : (
                        <div className="glass-card p-6 text-center text-muted-foreground text-sm">
                            Không có vật liệu hoạt động nào.
                        </div>
                    )}

                    {/* Dormant */}
                    {dormantInstances.length > 0 && (
                        <>
                            <h3 className="text-sm font-bold uppercase tracking-wider flex items-center gap-2 px-1 mt-6">
                                <Shield className="h-4 w-4 text-muted-foreground" /> Ngủ Đông ({dormantInstances.length})
                            </h3>
                            <div className="space-y-2 opacity-70">
                                {dormantInstances.map((inst: MaterialInstanceItem) => (
                                    <MaterialInstanceCard key={inst.id} instance={inst} />
                                ))}
                            </div>
                        </>
                    )}

                    {/* Retired */}
                    {retiredInstances.length > 0 && (
                        <>
                            <h3 className="text-sm font-bold uppercase tracking-wider flex items-center gap-2 px-1 mt-6">
                                <StopCircle className="h-4 w-4 text-error" /> Đã Ngừng ({retiredInstances.length})
                            </h3>
                            <div className="space-y-2 opacity-50">
                                {retiredInstances.map((inst: MaterialInstanceItem) => (
                                    <MaterialInstanceCard key={inst.id} instance={inst} />
                                ))}
                            </div>
                        </>
                    )}
                </div>

                {/* Timeline */}
                <div className="space-y-3">
                    <h3 className="text-sm font-bold uppercase tracking-wider flex items-center gap-2 px-1">
                        <GitBranch className="h-4 w-4 text-violet-500" /> Dòng Thời Gian
                    </h3>
                    <div className="glass-panel p-4 rounded-2xl max-h-[600px] overflow-y-auto space-y-3">
                        {events.length > 0 ? (
                            events.map((ev: MaterialTimelineEvent, i: number) => (
                                <TimelineItem key={i} event={ev} />
                            ))
                        ) : (
                            <p className="text-xs text-muted-foreground text-center py-4">Chưa có sự kiện.</p>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}

/* ── Material Instance Card ── */
function MaterialInstanceCard({ instance: inst }: { instance: MaterialInstanceItem }) {
    const ontoColor = ONTOLOGY_COLORS[inst.ontology ?? ""] ?? "text-gray-600 bg-gray-50 border-gray-200";
    const ontoLabel = ONTOLOGY_LABELS[inst.ontology ?? ""] ?? inst.ontology ?? "—";
    const funcLabel = FUNCTION_LABELS[inst.function ?? ""] ?? inst.function ?? "—";

    const strengthPercent = (inst.strength_level / 10) * 100;
    const degradPercent = ((inst.degradation_level ?? 0) / 100) * 100;

    return (
        <div className="glass-card p-4 group hover:border-primary/20 transition-all">
            <div className="flex items-center justify-between mb-2">
                <div className="flex items-center gap-2">
                    <span className="font-bold text-sm">{inst.material_code?.replace(/_/g, " ") ?? "—"}</span>
                    <Badge variant="outline" className={cn("text-[9px] font-bold", ontoColor)}>{ontoLabel}</Badge>
                </div>
                <Badge
                    variant={inst.is_active ? "default" : inst.is_retired ? "destructive" : "secondary"}
                    className="text-[9px]"
                >
                    {inst.is_active ? "Hoạt Động" : inst.is_retired ? "Đã Ngừng" : "Ngủ Đông"}
                </Badge>
            </div>

            <div className="text-[10px] text-muted-foreground mb-2">
                Chức năng: {funcLabel}
                {inst.activation_epoch != null && <> · Kích hoạt tại epoch {inst.activation_epoch}</>}
            </div>

            {/* Strength Bar */}
            <div className="space-y-1">
                <div className="flex justify-between text-[10px]">
                    <span className="text-muted-foreground">Sức mạnh</span>
                    <span className="font-mono font-bold">{inst.strength_level}/10</span>
                </div>
                <div className="h-1.5 rounded-full bg-muted/30 overflow-hidden">
                    <div
                        className="h-full rounded-full bg-gradient-to-r from-emerald-400 to-emerald-600 transition-all"
                        style={{ width: `${strengthPercent}%` }}
                    />
                </div>
            </div>

            {/* Degradation Bar */}
            {Number(inst.degradation_level ?? 0) > 0 && (
                <div className="space-y-1 mt-1">
                    <div className="flex justify-between text-[10px]">
                        <span className="text-muted-foreground">Suy thoái</span>
                        <span className="font-mono font-bold text-error">{Number(inst.degradation_level ?? 0)}%</span>
                    </div>
                    <div className="h-1.5 rounded-full bg-muted/30 overflow-hidden">
                        <div
                            className="h-full rounded-full bg-gradient-to-r from-red-400 to-red-600 transition-all"
                            style={{ width: `${degradPercent}%` }}
                        />
                    </div>
                </div>
            )}

            {/* Mutation */}
            {inst.mutation_state && Object.keys(inst.mutation_state).length > 0 && inst.mutation_state.mutated_from && (
                <div className="mt-2 px-2 py-1.5 rounded-lg bg-violet-50/50 border border-violet-200/30 text-[10px] text-violet-700 flex items-center gap-1.5">
                    <GitBranch className="h-3 w-3" />
                    <span>Biến thể từ: <strong>{String(inst.mutation_state.mutated_from)}</strong></span>
                </div>
            )}
        </div>
    );
}

/* ── Timeline Item ── */
function TimelineItem({ event: ev }: { event: MaterialTimelineEvent }) {
    const icons: Record<string, typeof PlayCircle> = {
        activation: PlayCircle,
        mutation: GitBranch,
        deactivation: StopCircle,
    };
    const colors: Record<string, string> = {
        activation: "text-success bg-success/10",
        mutation: "text-violet-600 bg-violet-50",
        deactivation: "text-error bg-error/10",
    };

    const Icon = icons[ev.type] ?? Activity;
    const color = colors[ev.type] ?? "text-muted-foreground bg-muted/20";

    return (
        <div className="flex gap-3">
            <div className={cn("h-7 w-7 rounded-lg flex items-center justify-center shrink-0", color)}>
                <Icon className="h-3.5 w-3.5" />
            </div>
            <div className="min-w-0">
                <p className="text-xs font-medium">{ev.description}</p>
                {ev.type === "mutation" && ev.from && ev.to && (
                    <div className="flex items-center gap-1.5 text-[10px] text-violet-600 mt-0.5">
                        <span>{ev.from}</span>
                        <ArrowRight className="h-2.5 w-2.5" />
                        <span className="font-bold">{ev.to}</span>
                    </div>
                )}
                <span className="text-[9px] text-muted-foreground font-mono">Epoch {ev.epoch}</span>
            </div>
        </div>
    );
}
