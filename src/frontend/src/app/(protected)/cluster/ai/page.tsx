import { AgentDashboard } from "@/features/writer/AgentDashboard";

export default function AIMissionControlPage() {
    return (
        <div className="animate-in fade-in slide-in-from-bottom-4 duration-1000">
            <div className="flex flex-col gap-2 mb-8">
                <h1 className="text-4xl font-extrabold tracking-tighter text-slate-900">
                    AI MISSION <span className="text-primary">CONTROL</span>
                </h1>
                <p className="text-slate-500 font-medium">
                    Orchestration plane for autonomous narrative agents and LLM throughput monitoring.
                </p>
            </div>

            <AgentDashboard />
        </div>
    );
}
