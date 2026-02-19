import { SerialSeriesList } from "@/features/serial/SerialSeriesList";
import { CreateSeriesButton } from "@/features/serial/CreateSeriesButton";

export default function SerialPage() {
  return (
    <div className="p-6">
      <div className="mb-6 flex items-center justify-between">
        <h1 className="text-2xl font-semibold text-foreground">Truyện dài kỳ · worldOS v3</h1>
        <CreateSeriesButton />
      </div>
      <p className="mb-4 text-sm text-muted-foreground">
        Series Factory giờ hỗ trợ bind Universe ID và chọn preset genre phù hợp kiến trúc worldOS v3.
      </p>
      <SerialSeriesList />
    </div>
  );
}
