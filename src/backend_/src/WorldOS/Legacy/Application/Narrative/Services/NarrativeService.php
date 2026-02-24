<?php

namespace WorldOS\Legacy\Application\Narrative\Services;

use App\Models\World;
use App\Models\WorldEvent;
use App\Models\WorldMyth;
use App\Models\WorldScar;
use WorldOS\Saga\Domain\Narrative\ValueObject\StorySlice;

class NarrativeService
{
    /**
     * Project a narrative slice from the world based on Observer Version rules.
     */
    public function project(
        World $world,
        \App\Models\ObserverVersion $observerVersion,
        int $fromTick,
        int $limit = 20
    ): StorySlice {
        $rules = $observerVersion->rules ?? [];

        // 1. Lấy events theo thời gian
        $events = WorldEvent::query()
            ->where('world_id', $world->id)
            ->where('tick', '>', $fromTick)
            ->orderBy('tick')
            ->limit($limit)
            ->get();

        if ($events->isEmpty()) {
            return new StorySlice(
                paragraphs: ['Thế giới trôi qua trong im lặng.'],
                nextCursor: $fromTick
            );
        }

        // 2. Lấy scars liên quan
        $scarMap = WorldScar::query()
            ->whereIn('source_event_id', $events->pluck('id'))
            ->get()
            ->keyBy('source_event_id');

        // 3. Lấy myths đang hoạt động
        $activeMyths = WorldMyth::query()
            ->where('world_id', $world->id)
            ->where('status', 'active')
            ->pluck('name');

        // 4. Diễn giải
        $paragraphs = [];

        foreach ($events as $event) {
            // FILTER: Check if event should be ignored
            if ($this->shouldIgnoreEvent($event, $rules)) {
                continue;
            }

            $text = $this->interpretEvent(
                $event,
                $scarMap->get($event->id),
                $activeMyths
            );

            if ($text !== null) {
                // TONE: Apply observer tone
                $text = $this->applyTone($text, $rules['tone'] ?? 'neutral');
                $paragraphs[] = $text;
            }
        }

        $lastTick = $events->last()->tick;

        return new StorySlice(
            paragraphs: $paragraphs,
            nextCursor: $lastTick
        );
    }

    protected function shouldIgnoreEvent(WorldEvent $event, array $rules): bool
    {
        $ignoreList = $rules['ignore'] ?? []; // e.g., ['ritual.performed']
        return in_array($event->type, $ignoreList);
    }

    protected function applyTone(string $text, string $tone): string
    {
        return match ($tone) {
            'tragic' => 'Người đời sau kể lại trong nước mắt rằng ' . lcfirst($text),
            'skeptic' => 'Có ghi chép vụn vặt cho rằng ' . lcfirst($text),
            'mythic' => 'Truyền thuyết ca ngợi rằng ' . lcfirst($text),
            default => ucfirst($text),
        };
    }

    protected function interpretEvent(
        WorldEvent $event,
        ?WorldScar $scar,
        $activeMyths
    ): ?string {
        // Xử lý theo type
        return match ($event->type) {
            'city.founded' => sprintf(
                'tại thời điểm %d, một thành phố được dựng lên với tên gọi %s.',
                $event->tick,
                $event->payload['name'] ?? 'vô danh'
            ),

            'drought.started' => $this->interpretDrought($event, $scar, $activeMyths),

            'time.passed' => null, // Im lặng

            'ritual.performed' => sprintf(
                'tại thời điểm %d, người ta thực hiện nghi lễ: %s.',
                $event->tick,
                $event->payload['belief'] ?? 'không rõ'
            ),

            'rumor.spread' => sprintf(
                'lời đồn lan rộng: "%s".',
                $event->payload['content'] ?? ''
            ),

            default => sprintf(
                'một sự kiện không rõ đã xảy ra tại thời điểm %d.',
                $event->tick
            ),
        };
    }

    protected function interpretDrought(
        WorldEvent $event,
        ?WorldScar $scar,
        $activeMyths
    ): string {
        // Nếu có myth "Hạn hán là hình phạt...", diễn giải sẽ thay đổi
        if ($activeMyths->contains('Hạn hán là hình phạt cho những kẻ bỏ quên tổ tiên')) {
            return sprintf(
                'từ thời điểm %d, hạn hán kéo đến như lời sấm truyền về sự trừng phạt.',
                $event->tick
            );
        }

        if ($scar) {
            return sprintf(
                'từ thời điểm %d, hạn hán bắt đầu gieo rắc nỗi sợ hãi kéo dài.',
                $event->tick
            );
        }

        return sprintf(
            'một đợt hạn hán ngắn ngủi được ghi nhận tại thời điểm %d.',
            $event->tick
        );
    }
}
