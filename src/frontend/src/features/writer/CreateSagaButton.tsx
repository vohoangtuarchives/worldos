"use client";

import { useCreateSagaFromActive } from "./useWriterApi";
import { Button } from "@/components/ui/button";
import { useRouter } from "next/navigation";

export function CreateSagaButton() {
  const router = useRouter();
  const create = useCreateSagaFromActive();
  return (
    <Button
      size="sm"
      disabled={create.isPending}
      onClick={() =>
        create.mutate(undefined, {
          onSuccess: (data) => data?.id && router.push(`/writer/sagas/${data.id}`),
          onError: () => {},
        })
      }
    >
      {create.isPending ? "Creating…" : "Create saga from active"}
    </Button>
  );
}
