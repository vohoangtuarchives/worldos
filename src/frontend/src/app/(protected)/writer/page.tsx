import Link from "next/link";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { SagaList } from "@/features/writer/SagaList";
import { WorldList } from "@/features/writer/WorldList";
import { CreateSagaButton } from "@/features/writer/CreateSagaButton";
import { WriterMetricsDashboard } from "@/features/writer/WriterMetricsDashboard";

export default function WriterPage() {
  return (
    <div className="p-6">
      <div className="mb-6 flex items-center justify-between">
        <h1 className="text-2xl font-semibold text-foreground">Writer</h1>
        <div className="flex gap-2">
          <CreateSagaButton />
          <Button asChild variant="outline" size="sm">
            <Link href="/writer/genesis">Genesis</Link>
          </Button>
        </div>
      </div>

      <section className="mb-8">
        <WriterMetricsDashboard />
      </section>

      <div className="grid gap-6 md:grid-cols-2">
        <Card id="sagas">
          <CardHeader>
            <CardTitle>Sagas</CardTitle>
            <CardDescription>Yggdrasil sagas — tree view and run</CardDescription>
          </CardHeader>
          <CardContent>
            <SagaList />
          </CardContent>
        </Card>
        <Card id="worlds">
          <CardHeader>
            <CardTitle>Worlds</CardTitle>
            <CardDescription>Worlds and instances — Hub controls</CardDescription>
          </CardHeader>
          <CardContent>
            <WorldList />
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
