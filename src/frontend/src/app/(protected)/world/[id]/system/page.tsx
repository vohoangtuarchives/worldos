"use client";

import { useParams } from "next/navigation";
import { EventView } from "@/features/world/EventView";

export default function WorldSystemPage() {
  const params = useParams();
  const id = typeof params?.id === "string" ? params.id : "";
  if (!id) return <p className="p-6 text-destructive">Invalid world.</p>;
  return <EventView worldId={id} />;
}
