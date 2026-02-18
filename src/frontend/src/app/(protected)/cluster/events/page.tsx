import { SystemEventsTerminal } from "@/features/cluster/SystemEventsTerminal";

export default function EventsPage() {
    return (
        <div className="space-y-6">
            <div className="flex flex-col gap-1">
                <h1 className="text-2xl font-black tracking-tighter text-slate-900 uppercase">
                    Neural <span className="text-primary">Logs</span>
                </h1>
                <p className="text-sm text-slate-500 font-bold uppercase tracking-[0.2em]">Real-time Cluster Event Synchronizer</p>
            </div>
            <SystemEventsTerminal />
        </div>
    );
}
