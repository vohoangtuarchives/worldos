<?php

declare(strict_types=1);

namespace Tuzy\Domain\Cosmos\Service;

use Tuzy\Domain\Cosmos\ValueObject\FitnessVector;

/**
 * Service thực hiện chọn lọc Pareto.
 * Giữ lại các thực thể "không bị lấn át" (non-dominated) bởi bất kỳ thực thể nào khác.
 */
final class ParetoSelector
{
    /**
     * @param array $scoredItems Mảng các item dạng ['item' => $entity, 'vector' => FitnessVector]
     * @param int $targetCount Số lượng tối đa cần giữ lại
     * @return array Danh sách các item đã được lọc
     */
    public function select(array $scoredItems, int $targetCount): array
    {
        if (count($scoredItems) <= $targetCount) {
            return $scoredItems;
        }

        // 1. Phân tầng Pareto (Pareto Ranking)
        $fronts = $this->nonDominatedSort($scoredItems);

        $survivors = [];
        foreach ($fronts as $front) {
            if (count($survivors) + count($front) <= $targetCount) {
                // Thêm toàn bộ front này vào danh sách sống sót
                $survivors = array_merge($survivors, $front);
            } else {
                // Nếu thêm cả front này sẽ vượt quota, cần lọc bớt bằng Crowding Distance
                $remainingSlots = $targetCount - count($survivors);
                $refinedFront = $this->sortByCrowdingDistance($front, $remainingSlots);
                $survivors = array_merge($survivors, $refinedFront);
                break;
            }
        }

        return $survivors;
    }

    /**
     * Thuật toán Non-dominated Sorting.
     */
    private function nonDominatedSort(array $items): array
    {
        $fronts = [[]];
        $dominationCount = []; // n_p: số lượng item lấn át item p
        $dominatedSet = [];    // S_p: tập hợp các item bị item p lấn át

        foreach ($items as $i => $itemP) {
            $dominationCount[$i] = 0;
            $dominatedSet[$i] = [];

            foreach ($items as $j => $itemQ) {
                if ($i === $j) continue;

                if ($itemP['vector']->isDominatedBy($itemQ['vector'])) {
                    $dominationCount[$i]++;
                } elseif ($itemQ['vector']->isDominatedBy($itemP['vector'])) {
                    $dominatedSet[$i][] = $j;
                }
            }

            if ($dominationCount[$i] === 0) {
                $fronts[0][] = $itemP;
            }
        }

        // Xây dựng các front tiếp theo
        $i = 0;
        while (!empty($fronts[$i])) {
            $nextFront = [];
            foreach ($fronts[$i] as $itemP) {
                // Lấy index gốc của itemP (giả định p có ID hoặc index duy nhất)
                // Trong thực tế, chúng ta cần so sánh reference hoặc ID.
                // Ở đây ta dùng index trong vòng lặp ban đầu.
                $pIdx = array_search($itemP, $items, true);
                
                foreach ($dominatedSet[$pIdx] as $qIdx) {
                    $dominationCount[$qIdx]--;
                    if ($dominationCount[$qIdx] === 0) {
                        $nextFront[] = $items[$qIdx];
                    }
                }
            }
            $i++;
            if (empty($nextFront)) break;
            $fronts[$i] = $nextFront;
        }

        return $fronts;
    }

    /**
     * Lọc bớt các phần tử trong cùng một front bằng cơ chế Crowding Distance.
     * Ưu tiên các phần tử "độc bản" (cách xa các phần tử khác trong không gian fitness).
     */
    private function sortByCrowdingDistance(array $front, int $count): array
    {
        // Ở version đơn giản cho CLI, ta chỉ thực hiện sáo trộn ngẫu nhiên 
        // hoặc giữ lại những phần tử đầu tiên.
        // Một hệ thống Meta-simulation thực thụ sẽ tính khoảng cách đa chiều.
        
        usort($front, fn() => mt_rand(-1, 1));
        return array_slice($front, 0, $count);
    }
}
