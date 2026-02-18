"use client";

import Link from "next/link";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { useSagas, useWorlds } from "./useWriterApi";
import type { Saga, World } from "@/shared/api/writer";

function KpiCard({
  title,
  value,
  sub,
  href,
}: {
  title: string;
  value: number | string;
  sub?: string;
  href?: string;
}) {
  const content = (
    <>
      <p className="text-2xl font-semibold tabular-nums">{value}</p>
      {sub && <p className="text-xs text-muted-foreground">{sub}</p>}
    </>
  );
  return (
    <Card>
      <CardHeader className="pb-2">
        <CardTitle className="text-base">{title}</CardTitle>
      </CardHeader>
      <CardContent>
        {href ? (
          <Link href={href} className="block text-primary hover:underline">
            {content}
          </Link>
        ) : (
          content
        )}
      </CardContent>
    </Card>
  );
}

function SagaMetricsTable({ sagas }: { sagas: Saga[] }) {
  return (
    <div className="overflow-x-auto">
      <table className="w-full text-sm">
        <thead>
          <tr className="border-b border-border">
            <th className="px-3 py-2 text-left font-medium">Saga</th>
            <th className="px-3 py-2 text-left font-medium">Status</th>
            <th className="px-3 py-2 text-right font-medium">Worlds</th>
          </tr>
        </thead>
        <tbody>
          {sagas.slice(0, 10).map((s) => (
            <tr key={s.id} className="border-b border-border/50 hover:bg-muted/50">
              <td className="px-3 py-2">
                <Link href={`/writer/sagas/${s.id}`} className="font-medium text-primary hover:underline">
                  {s.name}
                </Link>
              </td>
              <td className="px-3 py-2">
                <span className={s.status === "running" ? "text-green-600 dark:text-green-400" : "text-muted-foreground"}>
                  {s.status ?? "—"}
                </span>
              </td>
              <td className="px-3 py-2 text-right tabular-nums">{s.saga_worlds_count ?? s.world_count ?? "—"}</td>
            </tr>
          ))}
        </tbody>
      </table>
      {sagas.length > 10 && (
        <p className="mt-2 text-xs text-muted-foreground">+ {sagas.length - 10} more — see Sagas card below</p>
      )}
    </div>
  );
}

function WorldMetricsTable({ worlds }: { worlds: World[] }) {
  const withTick = worlds.filter((w) => w.current_tick != null && w.current_tick > 0).length;
  return (
    <div className="overflow-x-auto">
      <table className="w-full text-sm">
        <thead>
          <tr className="border-b border-border">
            <th className="px-3 py-2 text-left font-medium">World</th>
            <th className="px-3 py-2 text-left font-medium">Status</th>
            <th className="px-3 py-2 text-right font-medium">Tick</th>
          </tr>
        </thead>
        <tbody>
          {worlds.slice(0, 10).map((w) => (
            <tr key={w.id} className="border-b border-border/50 hover:bg-muted/50">
              <td className="px-3 py-2">
                <Link href={`/writer/worlds/${w.id}`} className="font-medium text-primary hover:underline">
                  {w.name}
                </Link>
              </td>
              <td className="px-3 py-2">
                <span className={w.status === "running" ? "text-green-600 dark:text-green-400" : "text-muted-foreground"}>
                  {w.status ?? w.health_status ?? "—"}
                </span>
              </td>
              <td className="px-3 py-2 text-right tabular-nums">{w.current_tick ?? "—"}</td>
            </tr>
          ))}
        </tbody>
      </table>
      {worlds.length > 10 && (
        <p className="mt-2 text-xs text-muted-foreground">+ {worlds.length - 10} more — see Worlds card below</p>
      )}
    </div>
  );
}

export function WriterMetricsDashboard() {
  const { data: sagas = [], isLoading: sagasLoading, error: sagasError } = useSagas();
  const { data: worlds = [], isLoading: worldsLoading, error: worldsError } = useWorlds();

  const totalSagas = sagas.length;
  const runningSagas = sagas.filter((s) => s.status === "running").length;
  const totalWorlds = worlds.length;
  const runningWorlds = worlds.filter((w) => w.status === "running").length;

  const isLoading = sagasLoading || worldsLoading;
  const hasError = sagasError || worldsError;

  if (isLoading) {
    return (
      <div className="flex items-center gap-2 rounded-lg border border-border bg-muted/30 px-4 py-6 text-muted-foreground">
        <div className="h-5 w-5 animate-spin rounded-full border-2 border-muted border-t-primary" />
        <span>Đang tải chỉ số…</span>
      </div>
    );
  }

  if (hasError) {
    return (
      <Card className="border-destructive/50">
        <CardContent className="p-4">
          <p className="text-sm text-destructive">
            Không tải được chỉ số: {(sagasError || worldsError) instanceof Error ? (sagasError || worldsError)?.message : "Unknown error"}
          </p>
        </CardContent>
      </Card>
    );
  }

  return (
    <div className="space-y-6">
      <div>
        <h2 className="mb-3 text-lg font-medium text-foreground">Chỉ số tổng quan</h2>
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <KpiCard title="Tổng Sagas" value={totalSagas} sub="saga" href="/writer#sagas" />
          <KpiCard title="Sagas đang chạy" value={runningSagas} sub="running" href="/writer#sagas" />
          <KpiCard title="Tổng Worlds" value={totalWorlds} sub="world" href="/writer#worlds" />
          <KpiCard title="Worlds đang chạy" value={runningWorlds} sub="running" href="/writer#worlds" />
        </div>
      </div>

      <div className="grid gap-6 md:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle className="text-base">Sagas — theo dõi nhanh</CardTitle>
            <p className="text-sm text-muted-foreground">Status và số worlds theo từng saga.</p>
          </CardHeader>
          <CardContent>
            {sagas.length === 0 ? (
              <p className="text-sm text-muted-foreground">Chưa có saga.</p>
            ) : (
              <SagaMetricsTable sagas={sagas} />
            )}
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle className="text-base">Worlds — theo dõi nhanh</CardTitle>
            <p className="text-sm text-muted-foreground">Status và tick theo từng world.</p>
          </CardHeader>
          <CardContent>
            {worlds.length === 0 ? (
              <p className="text-sm text-muted-foreground">Chưa có world.</p>
            ) : (
              <WorldMetricsTable worlds={worlds} />
            )}
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
