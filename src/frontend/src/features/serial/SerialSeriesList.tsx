"use client";

import Link from "next/link";
import { useSeriesList } from "./useSerialApi";
import { Badge } from "@/components/ui/badge";
import { BookOpen, Sparkles, Atom, Activity, ChevronRight } from "lucide-react";
import { cn } from "@/lib/utils";

export function SerialSeriesList() {
  const { data: series, isLoading, error } = useSeriesList();

  if (isLoading) return (
    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3 animate-pulse">
      {[1, 2, 3].map(i => <div key={i} className="h-40 rounded-xl bg-muted/20" />)}
    </div>
  );

  if (error) return (
    <div className="p-6 rounded-xl border border-destructive/20 bg-destructive/5 text-destructive">
      Failed to load series.
    </div>
  );

  if (!series?.length) return (
    <div className="flex flex-col items-center justify-center py-20 border border-dashed border-border/50 rounded-xl bg-muted/5 text-muted-foreground gap-3">
      <BookOpen className="h-10 w-10 opacity-20" />
      <p>Chưa có series nào. Hãy khởi tạo bằng Series Factory.</p>
    </div>
  );

  return (
    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3 animate-in fade-in slide-in-from-bottom-4 duration-700">
      {series.map((s) => (
        <Link key={s.id} href={"/serial/series/" + s.id} className="group block h-full">
          <div className="glass-card p-5 h-full relative overflow-hidden transition-all duration-300 hover:border-primary/40 hover:shadow-lg hover:shadow-primary/5">
            <div className="absolute top-0 right-0 p-4 opacity-0 group-hover:opacity-100 transition-opacity">
              <ChevronRight className="h-5 w-5 text-muted-foreground" />
            </div>

            <div className="flex flex-col h-full justify-between gap-4">
              <div>
                <div className="flex items-center justify-between mb-2">
                  <Badge variant="outline" className="text-[10px] uppercase tracking-wider h-5 bg-background/50 backdrop-blur-sm">
                    {s.genre_key ?? "Emergent"}
                  </Badge>
                  {s.universe && (
                    <Badge variant="secondary" className={cn(
                      "text-[10px] uppercase tracking-wider h-5 gap-1",
                      s.universe.status === 'active' ? "bg-green-500/10 text-green-600 border-green-500/20" : "bg-muted text-muted-foreground"
                    )}>
                      <Atom className="h-3 w-3" />
                      {s.universe.status}
                    </Badge>
                  )}
                </div>

                <h3 className="font-bold text-lg text-foreground group-hover:text-primary transition-colors line-clamp-2">
                  {s.title}
                </h3>

                {s.universe ? (
                  <div className="mt-2 flex items-center gap-2 text-xs text-muted-foreground">
                    <span className="font-medium text-foreground/80">{s.universe.name}</span>
                    <span>·</span>
                    <span className="flex items-center gap-1">
                      <Activity className="h-3 w-3" />
                      Entropy: {s.universe.entropy?.toFixed(2) ?? '0.00'}
                    </span>
                  </div>
                ) : (
                  <div className="mt-2 flex items-center gap-2 text-xs text-muted-foreground italic">
                    <span className="flex items-center gap-1">
                      <Atom className="h-3 w-3" />
                      Unbound Universe
                    </span>
                  </div>
                )}
              </div>

              <div className="pt-4 border-t border-border/50 grid grid-cols-2 gap-4">
                <div className="space-y-0.5">
                  <p className="text-[10px] uppercase tracking-widest text-muted-foreground">Progress</p>
                  <p className="font-mono text-sm font-bold flex items-center gap-1.5">
                    <Sparkles className="h-3.5 w-3.5 text-primary" />
                    {s.total_chapters_generated} <span className="text-[10px] font-normal text-muted-foreground">Chapters</span>
                  </p>
                </div>
                <div className="space-y-0.5">
                  <p className="text-[10px] uppercase tracking-widest text-muted-foreground">Volume</p>
                  <p className="font-mono text-sm font-bold">
                    Book {s.current_book_index + 1}
                  </p>
                </div>
              </div>
            </div>

            {/* Decorative gradient */}
            <div className="absolute -bottom-10 -right-10 w-32 h-32 bg-primary/5 rounded-full blur-3xl group-hover:bg-primary/10 transition-colors pointer-events-none" />
          </div>
        </Link>
      ))}
    </div>
  );
}
