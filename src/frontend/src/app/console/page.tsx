"use client";

import Link from "next/link";
import { useAuth } from "@/lib/auth/AuthProvider";
import {
  Activity,
  Globe2,
  TerminalSquare,
  Wand2,
  Network,
  Cpu,
  Swords,
  BookOpenText,
  Clock
} from "lucide-react";

export default function GodConsole() {
  const { user, isLoading } = useAuth();

  return (
    <div className="min-h-screen p-4 md:p-8 flex flex-col gap-6 relative overflow-hidden">
      {/* Background Decorators */}
      <div className="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-primary/20 blur-[120px] rounded-full pointer-events-none" />
      <div className="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-accent/10 blur-[120px] rounded-full pointer-events-none" />

      {/* Header */}
      <header className="flex flex-col md:flex-row shadow-glow justify-between items-start md:items-end pb-6 border-b border-white/5">
        <div>
          <div className="flex items-center gap-3 mb-2">
            <Cpu className="w-8 h-8 text-primary animate-pulse" />
            <h1 className="text-4xl font-bold tracking-tight bg-gradient-to-r from-primary to-accent bg-clip-text text-transparent">
              WorldOS Nexus
            </h1>
          </div>
          <p className="text-muted-foreground font-mono text-sm uppercase tracking-widest pl-1">
            God-Level Simulation Terminal // v4.0.0
          </p>
        </div>

        <div className="mt-4 md:mt-0 glass-panel px-4 py-2 rounded-lg flex items-center gap-4">
          <div className="flex flex-col">
            <span className="metric-label">System Status</span>
            <span className="metric-value text-success flex items-center gap-2">
              <span className="w-2 h-2 rounded-full bg-success animate-pulse shadow-[0_0_8px_hsl(140_70%_45%_/_0.8)]" />
              ONLINE
            </span>
          </div>
          <div className="w-px h-8 bg-white/10" />
          <div className="flex flex-col items-end">
            <span className="metric-label">Operator</span>
            <span className="metric-value font-mono text-foreground">
              {isLoading ? "AUTHENTICATING..." : user ? user.name : "GUEST"}
            </span>
          </div>
        </div>
      </header>

      {/* Main Grid */}
      <main className="grid grid-cols-1 md:grid-cols-12 gap-6 flex-1">

        {/* Left Column: Core Domains */}
        <div className="md:col-span-8 grid grid-cols-1 sm:grid-cols-2 gap-6">

          <Link href="/console/worlds" className="group">
            <div className="glass-card h-full p-6 flex flex-col gap-4">
              <div className="flex items-center justify-between">
                <div className="p-3 bg-primary/10 rounded-lg group-hover:bg-primary/20 transition-colors">
                  <Globe2 className="w-6 h-6 text-primary" />
                </div>
                <div className="status-glow-running w-3 h-3" />
              </div>
              <div>
                <h3 className="text-xl font-bold text-foreground mb-1">Worlds & Universes</h3>
                <p className="text-sm text-muted-foreground">Manage active simulations, physics laws, and evolutionary cycles.</p>
              </div>
              <div className="mt-auto grid grid-cols-2 gap-4 pt-4 border-t border-white/5">
                <div>
                  <div className="metric-label">Active Maps</div>
                  <div className="metric-value text-xl">1</div>
                </div>
                <div>
                  <div className="metric-label">Total Entities</div>
                  <div className="metric-value text-xl">2.4M</div>
                </div>
              </div>
            </div>
          </Link>

          <Link href="/console/sagas" className="group">
            <div className="glass-card-accent h-full p-6 flex flex-col gap-4">
              <div className="flex items-center justify-between">
                <div className="p-3 bg-accent/10 rounded-lg group-hover:bg-accent/20 transition-colors">
                  <BookOpenText className="w-6 h-6 text-accent" />
                </div>
                <div className="status-glow-paused w-3 h-3" />
              </div>
              <div>
                <h3 className="text-xl font-bold text-foreground mb-1">Sagas & Epics</h3>
                <p className="text-sm text-muted-foreground">Trace historical branches, timelines and narrative extraction.</p>
              </div>
              <div className="mt-auto grid grid-cols-2 gap-4 pt-4 border-t border-white/5">
                <div>
                  <div className="metric-label">Forked Logs</div>
                  <div className="metric-value text-xl text-accent">142</div>
                </div>
                <div>
                  <div className="metric-label">Current Era</div>
                  <div className="metric-value text-xl text-accent">V</div>
                </div>
              </div>
            </div>
          </Link>

          <Link href="/console/vietnamese-heroes" className="group">
            <div className="glass-card h-full p-6 flex flex-col gap-4 border-red-500/20 hover:border-red-500/40">
              <div className="flex items-center justify-between">
                <div className="p-3 bg-red-500/10 rounded-lg group-hover:bg-red-500/20 transition-colors">
                  <Swords className="w-6 h-6 text-red-400" />
                </div>
                <Clock className="w-4 h-4 text-muted-foreground" />
              </div>
              <div>
                <h3 className="text-xl font-bold text-foreground mb-1">Heroes Engine</h3>
                <p className="text-sm text-muted-foreground">Spawn extreme outliers. Track mutations, legacies and realm-shattering power scales.</p>
              </div>
            </div>
          </Link>

          <Link href="/console/marketplace" className="group">
            <div className="glass-card h-full p-6 flex flex-col gap-4 border-purple-500/20 hover:border-purple-500/40">
              <div className="flex items-center justify-between">
                <div className="p-3 bg-purple-500/10 rounded-lg group-hover:bg-purple-500/20 transition-colors">
                  <Network className="w-6 h-6 text-purple-400" />
                </div>
              </div>
              <div>
                <h3 className="text-xl font-bold text-foreground mb-1">Marketplace & Economy</h3>
                <p className="text-sm text-muted-foreground">Cross-world asset exchange, resource trading, and civilization wealth indices.</p>
              </div>
            </div>
          </Link>

        </div>

        {/* Right Column: Terminal & Stats */}
        <div className="md:col-span-4 flex flex-col gap-6">
          <div className="glass-panel rounded-xl p-6 h-full flex flex-col">
            <div className="flex items-center gap-2 mb-6 pb-4 border-b border-white/5">
              <TerminalSquare className="w-5 h-5 text-muted-foreground" />
              <h3 className="font-mono text-sm tracking-widest uppercase">Live Chronicle</h3>
            </div>

            <div className="flex-1 overflow-hidden relative">
              <div className="absolute inset-0 bg-gradient-to-b from-transparent via-transparent to-background/80 z-10 pointer-events-none" />
              <div className="space-y-4 font-mono text-xs max-h-[300px] overflow-y-auto pb-8 custom-scrollbar">

                <div className="flex gap-3 text-muted-foreground">
                  <span className="text-primary/70 shrink-0">[14:02:44]</span>
                  <span className="text-warning">WARN: Multiplicative Escalation in Basin Alpha.</span>
                </div>
                <div className="flex gap-3 text-muted-foreground">
                  <span className="text-primary/70 shrink-0">[14:02:45]</span>
                  <span>Empire 'Aethelgard' reached tech level 9.</span>
                </div>
                <div className="flex gap-3 text-muted-foreground">
                  <span className="text-primary/70 shrink-0">[14:02:48]</span>
                  <span className="text-success">INFO: Structural stability intact.</span>
                </div>
                <div className="flex gap-3 text-foreground">
                  <span className="text-primary/70 shrink-0">[14:03:01]</span>
                  <span className="text-purple-400">HERO SPAWNED: 'Lycoris' [Class: S] in sector 7.</span>
                </div>
                <div className="flex gap-3 text-muted-foreground opacity-50">
                  <span className="text-primary/70 shrink-0">[14:03:02]</span>
                  <span>Awaiting next tick...</span>
                </div>
              </div>
            </div>

            <div className="mt-4 pt-4 border-t border-white/5">
              <div className="flex justify-between items-center text-xs font-mono">
                <span className="text-muted-foreground">TICK RATE</span>
                <span className="text-primary">60.0 ETA/s</span>
              </div>
              <div className="w-full bg-white/5 h-1 md:h-2 mt-2 rounded-full overflow-hidden">
                <div className="bg-primary h-full w-[85%] rounded-full shadow-[0_0_10px_hsl(185_70%_50%)]" />
              </div>
            </div>
          </div>
        </div>
      </main>

      <div className="text-center mt-auto pt-4">
        {!user && !isLoading && (
          <div className="animate-bounce">
            <Link href="/login" className="inline-flex items-center gap-2 px-6 py-3 bg-primary/10 text-primary border border-primary/30 rounded-full hover:bg-primary/20 transition-all shadow-[0_0_20px_hsl(185_70%_50%_/_0.2)]">
              <Wand2 className="w-4 h-4" />
              <span className="font-bold tracking-wider uppercase text-sm">Initialize Setup (Sign In)</span>
            </Link>
          </div>
        )}
      </div>
    </div>
  );
}
