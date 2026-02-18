import Link from "next/link";
import { SerialSeriesList } from "@/features/serial/SerialSeriesList";
import { CreateSeriesButton } from "@/features/serial/CreateSeriesButton";

export default function SerialPage() {
  return (
    <div className="p-6">
      <div className="mb-6 flex items-center justify-between">
        <h1 className="text-2xl font-semibold text-foreground">Serial</h1>
        <CreateSeriesButton />
      </div>
      <SerialSeriesList />
    </div>
  );
}
