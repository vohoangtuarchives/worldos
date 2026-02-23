"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { Cpu, Globe2, BookOpenText, Target, Beaker, LogOut, Sparkles } from "lucide-react";
import { useAuth } from "@/lib/auth/AuthProvider";

const MENUS = [
    { name: "Nexus", path: "/console", icon: <Cpu className="w-4 h-4" /> },
    { name: "Genesis", path: "/console/genesis", icon: <Sparkles className="w-4 h-4" /> },
    { name: "Worlds", path: "/console/worlds", icon: <Globe2 className="w-4 h-4" /> },
    { name: "Sagas", path: "/console/sagas", icon: <BookOpenText className="w-4 h-4" /> },
    { name: "Heroes", path: "/console/heroes", icon: <Target className="w-4 h-4" /> },
    { name: "Market", path: "/console/marketplace", icon: <Beaker className="w-4 h-4" /> },
];

export default function ConsoleLayout({ children }: { children: React.ReactNode }) {
    const pathname = usePathname();
    const { user, logout } = useAuth();

    return (
        <div className="min-h-screen bg-cosmic-grid flex flex-col pt-16">
            {/* Top Fixed Navigation Bar */}
            <nav className="fixed top-0 inset-x-0 h-16 bg-background/80 backdrop-blur-2xl border-b border-primary/20 z-50 flex items-center shadow-[0_4px_30px_rgba(0,0,0,0.5)]">
                <div className="w-full px-4 md:px-8 flex items-center justify-between">

                    <div className="flex items-center gap-6">
                        <Link href="/" className="flex items-center gap-2 group mr-4">
                            <Cpu className="w-6 h-6 text-primary group-hover:rotate-180 transition-transform duration-700" />
                            <span className="font-bold text-lg tracking-widest bg-gradient-to-r from-primary to-accent bg-clip-text text-transparent hidden sm:block">
                                WORLDOS
                            </span>
                        </Link>

                        {/* Nav Links */}
                        <div className="hidden md:flex items-center gap-1 bg-white/5 p-1 rounded-full border border-white/5">
                            {MENUS.map((menu) => {
                                const isActive = pathname === menu.path;
                                return (
                                    <Link
                                        key={menu.name}
                                        href={menu.path}
                                        className={`flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium transition-all duration-300 ${isActive
                                            ? "bg-primary/20 text-primary shadow-[0_0_15px_theme(colors.primary.DEFAULT/0.3)] border border-primary/30"
                                            : "text-muted-foreground hover:text-foreground hover:bg-white/5"
                                            }`}
                                    >
                                        {menu.icon}
                                        {menu.name}
                                    </Link>
                                );
                            })}
                        </div>
                    </div>

                    <div className="flex items-center gap-4">
                        <div className="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full bg-success/10 border border-success/20">
                            <div className="w-2 h-2 rounded-full bg-success animate-pulse shadow-[0_0_8px_theme(colors.success.DEFAULT/0.8)]" />
                            <span className="text-xs font-mono text-success uppercase tracking-widest">Sys Online</span>
                        </div>

                        {user && (
                            <button
                                onClick={logout}
                                className="text-muted-foreground hover:text-destructive flex items-center gap-2 text-sm ml-4 transition-colors p-2 rounded-full hover:bg-destructive/10"
                                title="Disconnect from Nexus"
                            >
                                <LogOut className="w-4 h-4" />
                                <span className="sr-only">Logout</span>
                            </button>
                        )}
                    </div>
                </div>
            </nav>

            {/* Page Content area */}
            <div className="flex-1 w-full max-w-7xl mx-auto">
                {children}
            </div>
        </div>
    );
}
