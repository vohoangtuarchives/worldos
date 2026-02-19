"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { cn } from "@/lib/utils";
import {
    LayoutDashboard,
    Orbit,
    Sparkles,
    Settings,
    LogOut,
    ChevronRight,
    ShieldCheck,
    BookOpen,
    Bot,
    PenTool,
    Library,
    Cpu,
    Activity
} from "lucide-react";
import { useAuth, type User } from "@/lib/auth/AuthProvider";
import { Button } from "@/components/ui/button";

interface SidebarProps {
    user: User;
}

const navModules = [
    {
        key: "system",
        label: "System",
        items: [
            { name: "Dashboard", href: "/cluster", icon: LayoutDashboard },
            { name: "Governor", href: "/cluster/governor", icon: ShieldCheck },
            { name: "AI Cluster", href: "/cluster/ai", icon: Cpu },
        ]
    },
    {
        key: "writer",
        label: "Writer (Saga)",
        items: [
            { name: "Orchestrator", href: "/writer", icon: GlobeIcon },
            { name: "Genesis", href: "/writer/genesis", icon: Sparkles },
        ]
    },
    {
        key: "serial",
        label: "Serial (Story)",
        items: [
            { name: "Series List", href: "/serial", icon: Library },
            // { name: "Factory", href: "/serial/factory", icon: PenTool }, // Merged into List Page action
        ]
    },
    {
        key: "admin",
        label: "Admin & Evolution",
        items: [
            { name: "Evolution", href: "/admin", icon: Orbit },
            { name: "Wiki", href: "/wiki", icon: BookOpen },
            { name: "Settings", href: "/admin/settings", icon: Settings },
        ]
    }
];

function GlobeIcon(props: any) {
    return <Orbit {...props} />; // Placeholder
}

export function Sidebar({ user }: SidebarProps) {
    const pathname = usePathname();
    const { logout } = useAuth();

    return (
        <aside className="flex h-screen w-64 flex-col border-r border-border/50 bg-card/50 backdrop-blur-xl transition-all duration-300">
            <div className="flex h-14 items-center border-b border-border/50 px-6">
                <Link href="/cluster" className="flex items-center gap-2 font-bold text-primary hover:opacity-80 transition-opacity">
                    <div className="h-8 w-8 rounded-lg bg-primary/10 flex items-center justify-center">
                        <Orbit className="h-5 w-5" />
                    </div>
                    <span className="tracking-tight text-lg">WorldOS</span>
                </Link>
            </div>

            <div className="flex-1 overflow-y-auto py-6 px-3 space-y-6">
                {navModules.map((module) => (
                    <div key={module.key}>
                        <h3 className="mb-2 px-3 text-[10px] uppercase font-bold tracking-widest text-muted-foreground/60">
                            {module.label}
                        </h3>
                        <div className="space-y-0.5">
                            {module.items.map((item) => {
                                const isActive = pathname === item.href || pathname?.startsWith(item.href + "/");
                                return (
                                    <Link
                                        key={item.href}
                                        href={item.href}
                                        className={cn(
                                            "group flex items-center justify-between rounded-md px-3 py-2 text-sm font-medium transition-all duration-200",
                                            isActive
                                                ? "bg-primary/10 text-primary shadow-sm"
                                                : "text-muted-foreground hover:bg-muted hover:text-foreground"
                                        )}
                                    >
                                        <div className="flex items-center gap-3">
                                            <item.icon className={cn(
                                                "h-4 w-4 transition-colors",
                                                isActive ? "text-primary" : "text-muted-foreground group-hover:text-foreground"
                                            )} />
                                            {item.name}
                                        </div>
                                        {isActive && <div className="h-1.5 w-1.5 rounded-full bg-primary animate-pulse" />}
                                    </Link>
                                );
                            })}
                        </div>
                    </div>
                ))}
            </div>

            <div className="border-t border-border/50 p-4 bg-muted/20">
                <div className="flex items-center gap-3 mb-4">
                    <div className="h-9 w-9 rounded-full bg-gradient-to-br from-primary/20 to-secondary/20 flex items-center justify-center text-primary font-bold text-xs shadow-inner">
                        {user.email?.[0].toUpperCase()}
                    </div>
                    <div className="flex-1 overflow-hidden">
                        <p className="text-xs font-bold text-foreground truncate">{user.name || 'Admin'}</p>
                        <p className="text-[10px] text-muted-foreground truncate">{user.email}</p>
                    </div>
                </div>

                <Button
                    variant="outline"
                    size="sm"
                    className="w-full justify-start gap-2 h-8 text-xs font-normal"
                    onClick={() => void logout()}
                >
                    <LogOut className="h-3.5 w-3.5" />
                    Sign Out
                </Button>
            </div>
        </aside>
    );
}
