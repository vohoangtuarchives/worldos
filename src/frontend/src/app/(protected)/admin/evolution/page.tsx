import Link from "next/link";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

export default function EvolutionLabPage() {
  return (
    <div className="p-6">
      <div className="mb-4 flex items-center gap-4">
        <Button variant="outline" size="sm" asChild>
          <Link href="/admin">← Admin</Link>
        </Button>
        <h1 className="text-2xl font-semibold">Evolution Lab</h1>
      </div>
      <Card>
        <CardHeader>
          <CardTitle>Evolution Lab</CardTitle>
          <p className="text-sm text-muted-foreground">
            Dashboard: generations per hour, collapse rate, frontier size. Pareto frontier, AI toggle. Backend endpoints TBD.
          </p>
        </CardHeader>
        <CardContent>
          <p className="text-sm text-muted-foreground">
            When backend exposes evolution stats and AI toggle (e.g. POST /api/admin/evolution/ai-toggle), this page will show the dashboard and controls.
          </p>
        </CardContent>
      </Card>
    </div>
  );
}
