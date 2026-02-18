import Link from "next/link";
import { SagaTreeView } from "@/features/writer/SagaTreeView";
import { RunSagaButton } from "@/features/writer/RunSagaButton";
import { Button } from "@/components/ui/button";

export default async function SagaDetailPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  if (!id) return <p>Invalid saga.</p>;
  return (
    <div className="p-6">
      <div className="mb-4 flex items-center gap-4">
        <Button variant="outline" size="sm" asChild>
          <Link href="/writer">← Writer</Link>
        </Button>
        <h1 className="text-2xl font-semibold">Saga</h1>
        <RunSagaButton sagaId={id} />
      </div>
      <SagaTreeView sagaId={id} />
    </div>
  );
}
