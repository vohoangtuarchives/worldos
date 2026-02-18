import Link from "next/link";
import { Button } from "@/components/ui/button";
import { GenesisForm } from "@/features/writer/GenesisForm";

export default function GenesisPage() {
  return (
    <div className="p-6">
      <div className="mb-4 flex items-center gap-4">
        <Button variant="outline" size="sm" asChild>
          <Link href="/writer">← Writer</Link>
        </Button>
        <h1 className="text-2xl font-semibold">Genesis</h1>
      </div>
      <GenesisForm />
    </div>
  );
}
