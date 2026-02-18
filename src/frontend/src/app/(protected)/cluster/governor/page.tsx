import { GovernorDetail } from "@/features/cluster/GovernorDetail";

export default function GovernorPage() {
    return (
        <div className="space-y-6">
            <div className="flex flex-col gap-1">
                <h1 className="text-2xl font-black tracking-tighter text-slate-900 uppercase">
                    Nexus <span className="text-primary">Governor</span>
                </h1>
                <p className="text-sm text-slate-500 font-bold uppercase tracking-[0.2em]">Resource Sovereignty & Policy Control Layer</p>
            </div>
            <GovernorDetail />
        </div>
    );
}
