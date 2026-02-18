"use client";

import { useRunSaga } from "./useWriterApi";
import { Button } from "@/components/ui/button";

export function RunSagaButton({ sagaId }: { sagaId: string }) {
  const run = useRunSaga();
  return (
    <Button
      size="sm"
      disabled={run.isPending}
      onClick={() => run.mutate(sagaId)}
    >
      {run.isPending ? "Running…" : "Run saga"}
    </Button>
  );
}
