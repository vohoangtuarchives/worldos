import Link from "next/link";
import { NarrativeOrchestrator } from "@/features/writer/NarrativeOrchestrator";

export default function WriterPage() {
  return (
    <div className="animate-in fade-in slide-in-from-bottom-4 duration-1000">
      <div className="flex flex-col gap-2 mb-8">
        <h1 className="text-4xl font-extrabold tracking-tighter text-slate-900 uppercase">
          Narrative <span className="text-primary">Orchestrator</span>
        </h1>
        <p className="text-slate-500 font-medium">
          Strategic gateway for managing autonomous sagas and parallel universe simulations.
        </p>
      </div>

      <NarrativeOrchestrator />
    </div>
  );
}
