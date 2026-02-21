"use client";

import Link from "next/link";
import { Wand2 } from "lucide-react";
import { useAuth } from "@/lib/auth/AuthProvider";

export default function PublicPortal() {
    const { user, isLoading } = useAuth();

    return (
        <div className="flex min-h-screen flex-col items-center justify-center relative overflow-hidden bg-[#0c1017]">
            {/* Deep Space Background */}
            <div className="absolute inset-0 bg-[radial-gradient(circle_at_center,theme(colors.primary.DEFAULT/0.15)_0%,transparent_50%)] pointer-events-none mix-blend-screen" />
            <div className="absolute inset-0 bg-[url('/noise.png')] opacity-20 pointer-events-none mix-blend-overlay" style={{ backgroundSize: '150px' }} />

            <main className="z-10 flex flex-col items-center gap-8 text-center px-4">
                <div className="space-y-4 relative">
                    <div className="absolute -inset-8 bg-primary/20 blur-[100px] rounded-full" />
                    <h1 className="text-5xl md:text-7xl font-bold tracking-tighter text-transparent bg-clip-text bg-gradient-to-br from-white via-white/90 to-primary">
                        WorldOS <span className="text-primary tracking-widest font-mono text-3xl align-top">v4</span>
                    </h1>
                    <p className="text-muted-foreground font-mono text-lg md:text-xl tracking-wider uppercase drop-shadow-md">
                        Evolutionary Geopolitical Ecosystem
                    </p>
                </div>

                <div className="h-px w-24 bg-gradient-to-r from-transparent via-primary/50 to-transparent my-4" />

                {isLoading ? (
                    <div className="glass-panel px-8 py-4 rounded-full flex items-center gap-3 animate-pulse">
                        <div className="w-2 h-2 bg-primary rounded-full shadow-[0_0_10px_theme(colors.primary.DEFAULT)]" />
                        <span className="font-mono text-sm tracking-widest text-primary">INITIALIZING COMMLINK...</span>
                    </div>
                ) : (
                    <Link
                        href="/console"
                        className="group relative inline-flex items-center gap-3 px-8 py-4 bg-primary/10 hover:bg-primary/20 border border-primary/30 rounded-full transition-all duration-500 overflow-hidden"
                    >
                        <div className="absolute inset-0 bg-primary/20 translate-y-[100%] group-hover:translate-y-[0%] transition-transform duration-500 ease-out" />
                        <Wand2 className="w-5 h-5 text-primary group-hover:scale-110 transition-transform duration-500 z-10 drop-shadow-[0_0_8px_theme(colors.primary.DEFAULT)]" />
                        <span className="font-bold tracking-widest uppercase text-sm z-10 text-white group-hover:text-primary-foreground">
                            {user ? "Enter Nexus (Console)" : "Authenticate to Proceed"}
                        </span>
                    </Link>
                )}
            </main>
        </div>
    );
}
