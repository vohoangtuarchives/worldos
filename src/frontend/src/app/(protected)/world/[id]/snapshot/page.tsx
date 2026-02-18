"use client";

import { useParams } from "next/navigation";
import { SnapshotView } from "@/features/world/SnapshotView";

export default function WorldSnapshotPage() {
  const params = useParams();
  const id = typeof params?.id === "string" ? params.id : "";
  if (!id) return <p className="p-6 text-destructive">Invalid world.</p>;
  return <SnapshotView worldId={id} />;
}
