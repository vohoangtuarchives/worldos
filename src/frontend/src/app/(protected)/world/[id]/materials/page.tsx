"use client";

import { useParams } from "next/navigation";
import { WorldMaterialsView } from "@/features/writer/WorldMaterialsView";

export default function WorldMaterialsPage() {
    const params = useParams();
    const id = typeof params?.id === "string" ? params.id : "";

    if (!id) return <p className="p-6 text-destructive">Invalid world.</p>;

    return (
        <div className="p-6">
            <WorldMaterialsView worldId={id} />
        </div>
    );
}
