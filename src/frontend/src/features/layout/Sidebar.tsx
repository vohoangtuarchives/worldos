"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { cn } from "@/lib/utils";
import {
    LayoutDashboard,
    Activity,
    Globe,
    Orbit,
    Sparkles,
    Settings,
    LogOut,
    ChevronRight,
    ShieldCheck,
    BookOpen,
    Zap,
    Bot
} from "lucide-react";
import { useAuth, type User } from "@/lib/auth/AuthProvider";
import { Button } from "@/components/ui/button";

interface SidebarProps {
    user: User;
}

const navGroups = [
    {
        label: "Hệ Thống (Cluster)",
        items: [
            { name: "Tổng Quan", href: "/cluster", icon: LayoutDashboard },
            { name: "Điều Hành (Governor)", href: "/cluster/governor", icon: ShieldCheck },
            { name: "Hệ Thống AI", href: "/cluster/ai", icon: Bot },
            { name: "Sự Kiện", href: "/cluster/events", icon: Activity },
        ]
    },
    {
        label: "Dòng Đời (Universe)",
        items: [
            { name: "Tổng Quan (Admin)", href: "/admin", icon: Orbit },
            { name: "Bách Khoa (Wiki)", href: "/wiki", icon: BookOpen },
        ]
    },
    {
        label: "Sáng Tác (Writer)",
        items: [
            { name: "Sagas", href: "/writer", icon: Globe },
            { name: "Serial", href: "/serial", icon: Globe },
        ]
    },
    {
        label: "Khởi Nguyên (Genesis)",
        items: [
            { name: "Tạo Thế Giới", href: "/writer/genesis", icon: Sparkles },
        ]
    }
];

export function Sidebar({ user }: SidebarProps) {
    const pathname = usePathname();
    const { logout } = useAuth();

    return (
        <aside className="flex h-screen w-64 flex-col border-r border-border/50 bg-card/50 backdrop-blur-xl">
            <div className="flex h-14 items-center border-b border-border/50 px-6">
                <Link href="/cluster" className="flex items-center gap-2 font-bold text-primary">
                    <Orbit className="h-6 w-6" />
                    <span className="tracking-tight text-xl">WorldOS</span>
                </Link>
            </div>

            <div className="flex-1 overflow-y-auto py-4">
                {navGroups.map((group, i) => (
                    <div key={i} className="mb-6 px-4">
                        <h3 className="mb-2 px-2 text-[10px] uppercase font-bold tracking-widest text-muted-foreground/70">
                            {group.label}
                        </h3>
                        <div className="space-y-1">
                            {group.items.map((item) => (
                                <Link
                                    key={item.href}
                                    href={item.href}
                                    className={cn(
                                        "group flex items-center justify-between rounded-md px-2 py-1.5 text-sm font-medium transition-colors",
                                        pathname === item.href
                                            ? "bg-primary/10 text-primary"
                                            : "text-muted-foreground hover:bg-muted hover:text-foreground"
                                    )}
                                >
                                    <div className="flex items-center gap-2.5">
                                        <item.icon className={cn(
                                            "h-4 w-4",
                                            pathname === item.href ? "text-primary" : "text-muted-foreground group-hover:text-foreground"
                                        )} />
                                        {item.name}
                                    </div>
                                    {pathname === item.href && <ChevronRight className="h-3 w-3" />}
                                </Link>
                            ))}
                        </div>
                    </div>
                ))}
            </div>

            <div className="border-t border-border/50 p-4">
                <div className="glass-card-accent mb-4 p-3">
                    <div className="flex items-center justify-between mb-2">
                        <span className="metric-label">System Pressure</span>
                        <span className="text-[10px] font-mono font-bold text-success">LỰC 0.12</span>
                    </div>
                    <div className="h-1 w-full rounded-full bg-muted overflow-hidden">
                        <div className="h-full w-12 bg-primary status-glow-running" />
                    </div>
                </div>

                <div className="flex items-center gap-3 px-2 mb-4">
                    <div className="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xs">
                        {user.email?.[0].toUpperCase()}
                    </div>
                    <div className="flex-1 overflow-hidden">
                        <p className="text-xs font-bold text-foreground truncate">{user.email}</p>
                        <p className="text-[10px] text-muted-foreground uppercase tracking-widest">Administrator</p>
                    </div>
                </div>

                <Button
                    variant="ghost"
                    size="sm"
                    className="w-full justify-start gap-2 text-muted-foreground hover:text-destructive"
                    onClick={() => void logout()}
                >
                    <LogOut className="h-4 w-4" />
                    Logout
                </Button>
            </div>
        </aside>
    );
}
