import { GenesisForm } from "@/features/writer/GenesisForm";
import { Sparkles } from "lucide-react";

export default function GenesisPage() {
  return (
    <div className="space-y-6">
      <div className="flex flex-col gap-1">
        <h1 className="text-2xl font-bold tracking-tight text-foreground flex items-center gap-2">
          <Sparkles className="h-6 w-6 text-primary" />
          Quá Trình Khởi Nguyên (Genesis)
        </h1>
        <p className="text-sm text-muted-foreground uppercase tracking-wider font-medium">
          Initiating World-Entity Seeding • Deep-Sim Protocol
        </p>
      </div>

      <div className="glass-card p-8">
        <GenesisForm />
      </div>
    </div>
  );
}
