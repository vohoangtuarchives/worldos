"use client";

import { useParams } from "next/navigation";
import { EvolutionView } from "@/features/world/EvolutionView";

export default function WorldEvolutionPage() {
  const params = useParams();
  const id = typeof params?.id === "string" ? params.id : "";
  if (!id) return <p className="p-6 text-destructive">Invalid world.</p>;
  return <EvolutionView worldId={id} />;
}
