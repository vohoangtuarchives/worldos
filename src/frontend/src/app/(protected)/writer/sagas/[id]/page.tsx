import Link from "next/link";
import { SagaDetailView } from "@/features/writer/SagaDetailView";
import { SagaTreeView } from "@/features/writer/SagaTreeView";
import { RunSagaButton } from "@/features/writer/RunSagaButton";
import { Button } from "@/components/ui/button";

export default async function SagaDetailPage({
  params,
  searchParams,
}: {
  params: Promise<{ id: string }>;
  searchParams: Promise<{ created?: string }>;
}) {
  const { id } = await params;
  const { created } = await searchParams;
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
      <SagaDetailView sagaId={id} showCreatedMessage={created === "1"} />
      <div className="mt-6">
        <SagaTreeView sagaId={id} />
      </div>
    </div>
  );
}
