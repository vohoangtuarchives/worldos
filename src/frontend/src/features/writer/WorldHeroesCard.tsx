"use client";

import { useWorldHeroes } from "./useWriterApi";
import { Badge } from "@/components/ui/badge";
import { Swords, Crown, BookOpen, Scale, MessageSquare, Zap } from "lucide-react";
import { cn } from "@/lib/utils";

const ARCHETYPE_ICONS: Record<string, any> = {
    'LEGENDARY_GENERAL': Swords,
    'FOUNDING_KING': Crown,
    'CULTURAL_HERO': BookOpen,
    'PHILOSOPHER_KING': Scale,
    'REBEL_LEADER': Zap,
    'WISE_QUEEN': MessageSquare,
};

export function WorldHeroesCard({ worldId }: { worldId: string }) {
    const { data: heroes, isLoading } = useWorldHeroes(worldId);

    if (isLoading) return <div className="animate-pulse h-20 bg-muted/20 rounded-xl" />;
    if (!heroes || heroes.length === 0) return null;

    return (
        <div className="space-y-4">
            <div className="flex items-center justify-between px-2">
                <h3 className="font-bold text-sm uppercase tracking-wider flex items-center gap-2">
                    <Crown className="h-4 w-4 text-purple-500" />
                    Active Heroes
                </h3>
                <span className="text-[10px] font-bold text-muted-foreground uppercase">{heroes.length} HEROES</span>
            </div>

            <ScrollArea className="w-full whitespace-nowrap rounded-xl border border-border/40 bg-background/40 backdrop-blur">
                <div className="flex w-max space-x-4 p-4">
                    {heroes.map((hero) => {
                        const Icon = ARCHETYPE_ICONS[hero.archetype] || Crown;
                        return (
                            <div key={hero.id} className="glass-card w-[280px] p-4 flex flex-col gap-3 hover:border-purple-500/30 transition-all group">
                                <div className="flex items-start justify-between">
                                    <div className="flex items-center gap-3">
                                        <div className="h-10 w-10 rounded-lg bg-purple-500/10 flex items-center justify-center text-purple-500 group-hover:bg-purple-500 group-hover:text-white transition-colors">
                                            <Icon className="h-5 w-5" />
                                        </div>
                                        <div>
                                            <h4 className="font-bold text-sm truncate w-[140px]">{hero.name}</h4>
                                            <p className="text-[10px] text-muted-foreground uppercase tracking-wider">{hero.archetype.replace('_', ' ')}</p>
                                        </div>
                                    </div>
                                    <Badge variant="outline" className={cn(
                                        "text-[9px] h-5",
                                        hero.is_generated ? "text-blue-400 border-blue-400/20" : "text-amber-400 border-amber-400/20"
                                    )}>
                                        {hero.is_generated ? "GEN" : "HIST"}
                                    </Badge>
                                </div>

                                {/* Stats */}
                                <div className="space-y-1.5 pt-2 border-t border-border/30">
                                    <div className="flex justify-between text-[10px] uppercase font-bold text-muted-foreground">
                                        <span>Impact</span>
                                        <span>{(hero.impact_score * 100).toFixed(0)}</span>
                                    </div>
                                    <div className="h-1.5 w-full bg-muted/30 rounded-full overflow-hidden">
                                        <div
                                            className="h-full bg-gradient-to-r from-purple-500 to-blue-500"
                                            style={{ width: `${Math.min(100, hero.impact_score * 100)}%` }}
                                        />
                                    </div>
                                </div>

                                {/* Dimensions Mini-Bars */}
                                {hero.dimensions && (
                                    <div className="grid grid-cols-2 gap-x-4 gap-y-1 mt-1">
                                        {Object.entries(hero.dimensions).slice(0, 4).map(([key, val]) => (
                                            <div key={key} className="flex items-center gap-2">
                                                <span className="text-[9px] text-muted-foreground uppercase w-8 truncate">{key.substring(0, 3)}</span>
                                                <div className="h-1 w-12 bg-muted/30 rounded-full overflow-hidden">
                                                    <div className="h-full bg-foreground/50" style={{ width: `${Number(val) * 100}%` }} />
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                )}

                                <div className="text-[10px] text-muted-foreground italic truncate mt-1">
                                    "{hero.biography?.substring(0, 40)}..."
                                </div>
                            </div>
                        );
                    })}
                </div>
                <ScrollBar orientation="horizontal" />
            </ScrollArea>
        </div>
    );
}
