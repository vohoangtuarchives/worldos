"use client";

import { useParams, useRouter } from "next/navigation";
import { ArrowLeft, CircleDashed } from "lucide-react";
import Link from "next/link";
import { EvolutionView } from "@/features/world/EvolutionView";
import { useQuery } from "@tanstack/react-query";
import { writerApi } from "@/shared/api/writer";

// Fetch nhỏ lẻ để get World ID nếu người dùng muốn Back ra đúng World chứa Universe này
export default function UniverseCoreConsolePage() {
    const params = useParams();
    const router = useRouter();
    const universeId = params.id as string;

    // Gọi api GET /api/universes/{id} để lấy parent world_id (giả lập / hoặc bỏ qua nếu làm state)
    // Tạm build trước UI Header Core view
    return (
        <div className="h-[calc(100vh-4rem)] flex flex-col p-4 md:p-6 gap-4 relative overflow-hidden">

            {/* Background Decorators */}
            <div className="absolute top-0 right-0 w-[50%] h-[50%] bg-accent/10 blur-[150px] rounded-full pointer-events-none mix-blend-screen" />

            {/* Breadcrumb Navigation - Layer 3 */}
            <header className="flex-none flex items-center justify-between gap-4 bg-black/40 backdrop-blur-xl border border-white/10 rounded-xl px-4 py-2 shadow-glow">
                <div className="flex items-center gap-4">
                    <button
                        onClick={() => router.back()}
                        className="p-2 -ml-2 rounded-lg text-muted-foreground hover:text-white hover:bg-white/10 transition-colors"
                    >
                        <ArrowLeft className="w-5 h-5" />
                    </button>
                    <div className="w-px h-6 bg-white/10" />
                    <div className="flex items-center gap-2">
                        <CircleDashed className="w-4 h-4 text-accent animate-[spin_4s_linear_infinite]" />
                        <h1 className="font-mono text-sm tracking-widest text-accent uppercase">
                            Core Simulation <span className="text-muted-foreground mx-1">/</span> {universeId.split('-')[0]}
                        </h1>
                    </div>
                </div>
            </header>

            {/* Main Simulation View Area */}
            <div className="flex-1 glass-card overflow-hidden">
                <EvolutionView worldId="" forceUniverseId={universeId} />
            </div>

        </div>
    );
}
