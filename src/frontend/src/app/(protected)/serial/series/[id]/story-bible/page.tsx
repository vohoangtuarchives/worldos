import Link from "next/link";
import { Button } from "@/components/ui/button";
import { StoryBibleView } from "@/features/serial/StoryBibleView";

export default async function StoryBiblePage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;

  return (
    <div className="p-6">
      <div className="mb-4 flex items-center gap-4">
        <Button variant="outline" size="sm" asChild>
          <Link href={"/serial/series/" + id}>← Series</Link>
        </Button>
        <h1 className="text-2xl font-semibold">Story Bible</h1>
      </div>
      <StoryBibleView seriesId={id} />
    </div>
  );
}
