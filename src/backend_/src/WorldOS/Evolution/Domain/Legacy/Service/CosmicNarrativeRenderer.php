<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Service;
use WorldOS\Evolution\Domain\Legacy\ValueObject\WorldSnapshot;

use WorldOS\Evolution\Domain\Legacy\ValueObject\CosmicState;
use WorldOS\Evolution\Domain\Legacy\ValueObject\CivilizationSnapshot;

/**
 * CosmologyNarrativeRenderer
 *
 * "Narrative as Renderer" â€” interprets the simulation state into text.
 * Reads ONLY. Never modifies state. Zero coupling to simulation logic.
 *
 * This renderer is separate from the physics engine by design:
 * the simulation produces numbers, the renderer produces prose.
 */
class CosmicNarrativeRenderer
{
    /**
     * Render a narrative description of the current cosmic state.
     *
     * @param WorldSnapshot $snapshot Current world state
     * @param array $events Events from the last step (if any)
     * @return array{cosmic: string, environment: string, civilization: string, tension: string, events: array<string>}
     */
    public function render(WorldSnapshot $snapshot, array $events = []): array
    {
        return [
            'cosmic' => $this->renderCosmic($snapshot),
            'environment' => $this->renderEnvironment($snapshot),
            'civilization' => $this->renderCivilization($snapshot),
            'tension' => $this->renderTension($snapshot),
            'events' => $this->renderEvents($events),
        ];
    }

    private function renderCosmic(WorldSnapshot $snapshot): string
    {
        $cosmic = $snapshot->cosmic;
        $energy = $cosmic->energy;
        $entropy = $cosmic->entropy;
        $attractor = $cosmic->currentAttractor;

        $energyDesc = match (true) {
            $energy > 0.8 => 'ThiÃªn khÃ­ trÃ o dÃ¢ng mÃ£nh liá»‡t, nÄƒng lÆ°á»£ng vÅ© trá»¥ á»Ÿ Ä‘á»‰nh cao',
            $energy > 0.6 => 'Linh khÃ­ á»•n Ä‘á»‹nh, dÃ²ng cháº£y vÅ© trá»¥ hÃ i hÃ²a',
            $energy > 0.4 => 'ThiÃªn khÃ­ suy yáº¿u, nÄƒng lÆ°á»£ng vÅ© trá»¥ Ä‘ang thoÃ¡i trÃ o',
            default       => 'ThiÃªn khÃ­ kiá»‡t quá»‡, hÆ° khÃ´ng bao trÃ¹m váº¡n váº­t',
        };

        $entropyDesc = match (true) {
            $entropy > 0.7 => 'Há»—n mang lan trÃ n, tráº­t tá»± vÅ© trá»¥ Ä‘ang tan rÃ£',
            $entropy > 0.4 => 'Nhiá»…u loáº¡n gia tÄƒng, ranh giá»›i giá»¯a tráº­t tá»± vÃ  há»—n mang má» nháº¡t',
            $entropy > 0.2 => 'VÅ© trá»¥ váº­n hÃ nh theo quy luáº­t, tráº­t tá»± Ä‘Æ°á»£c duy trÃ¬',
            default        => 'Tráº­t tá»± tuyá»‡t Ä‘á»‘i, vÅ© trá»¥ tÄ©nh láº·ng nhÆ° máº·t nÆ°á»›c há»“ thu',
        };

        $attractorName = match ($attractor) {
            'EQUILIBRIUM'        => 'ThiÃªn HÃ²a',
            'HIGH_CHAOS'         => 'ThiÃªn Loáº¡n',
            'RESONANCE_DOMINANT' => 'ThiÃªn Minh',
            'VOID_COLLAPSE'      => 'ThiÃªn Diá»‡t',
            default              => str_contains($attractor, 'EMERGENT') ? 'ThiÃªn Biáº¿n (Cháº¿ Ä‘á»™ má»›i)' : $attractor,
        };

        return "{$energyDesc}. {$entropyDesc}. VÅ© trá»¥ Ä‘ang trong cháº¿ Ä‘á»™ [{$attractorName}].";
    }

    private function renderEnvironment(WorldSnapshot $snapshot): string
    {
        $env = $snapshot->environment;

        $terrain = match (true) {
            $env->terrainStability > 0.8 => 'Äá»‹a máº¡ch vá»¯ng cháº¯c, Ä‘áº¥t Ä‘ai phÃ¬ nhiÃªu',
            $env->terrainStability > 0.5 => 'Äá»‹a máº¡ch dao Ä‘á»™ng, thá»‰nh thoáº£ng cÃ³ cháº¥n Ä‘á»™ng nháº¹',
            $env->terrainStability > 0.3 => 'Äá»‹a máº¡ch báº¥t á»•n, Ä‘á»™ng Ä‘áº¥t vÃ  sá»¥p lá»Ÿ thÆ°á»ng xuyÃªn',
            default                      => 'Äá»‹a máº¡ch gÃ£y vá»¡, Ä‘áº¡i Ä‘á»‹a cháº¥n cÃ³ thá»ƒ xáº£y ra báº¥t cá»© lÃºc nÃ o',
        };

        $anomaly = match (true) {
            $env->anomalyDensity > 0.5 => 'Dá»‹ tÆ°á»£ng xuáº¥t hiá»‡n kháº¯p nÆ¡i â€” váº¿t ráº¡n thá»±c táº¡i, xoÃ¡y khÃ´ng-thá»i gian',
            $env->anomalyDensity > 0.2 => 'Má»™t sá»‘ dá»‹ tÆ°á»£ng Ä‘Ã£ Ä‘Æ°á»£c ghi nháº­n: Ã¡nh sÃ¡ng báº¥t thÆ°á»ng, vÃ¹ng nÄƒng lÆ°á»£ng dá»‹ biáº¿n',
            default                    => 'KhÃ´ng gian yÃªn bÃ¬nh, Ã­t dá»‹ tÆ°á»£ng',
        };

        return "{$terrain}. {$anomaly}.";
    }

    private function renderCivilization(WorldSnapshot $snapshot): string
    {
        $civ = $snapshot->civilization;

        $knowledge = match (true) {
            $civ->collectiveKnowledge > 1.5 => 'VÄƒn minh Ä‘áº¡t Ä‘á»‰nh cao tri thá»©c, hiá»ƒu biáº¿t sÃ¢u sáº¯c vá» quy luáº­t vÅ© trá»¥',
            $civ->collectiveKnowledge > 0.8 => 'Tri thá»©c phÃ¡t triá»ƒn, cÃ¡c phÃ¡i tu luyá»‡n vÃ  há»c giáº£ nghiÃªn cá»©u thiÃªn Ä‘áº¡o',
            $civ->collectiveKnowledge > 0.3 => 'Tri thá»©c cÃ²n sÆ¡ khai, dÃ¢n chÃºng báº¯t Ä‘áº§u tÃ¬m hiá»ƒu tháº¿ giá»›i',
            default                         => 'DÃ¢n chÃºng mÃ´ng muá»™i, sá»‘ng theo báº£n nÄƒng',
        };

        $ritual = match (true) {
            $civ->ritualCoherence > 0.7 => 'Nghi lá»… Ä‘á»“ng bá»™ cao â€” toÃ n dÃ¢n cá»™ng hÆ°á»Ÿng vá»›i thiÃªn Ä‘áº¡o',
            $civ->ritualCoherence > 0.4 => 'Nghi lá»… cÃ³ tá»• chá»©c, nhÆ°ng chÆ°a Ä‘áº¡t má»©c cá»™ng hÆ°á»Ÿng',
            default                     => 'Nghi lá»… rá»i ráº¡c, má»—i nÆ¡i má»—i kiá»ƒu',
        };

        $stability = match (true) {
            $civ->factionStability > 0.7 => 'CÃ¡c tháº¿ lá»±c hÃ²a bÃ¬nh, xÃ£ há»™i á»•n Ä‘á»‹nh',
            $civ->factionStability > 0.4 => 'CÄƒng tháº³ng giá»¯a cÃ¡c phe phÃ¡i, nhÆ°ng chÆ°a bÃ¹ng ná»•',
            default                      => 'Xung Ä‘á»™t lan trÃ n, xÃ£ há»™i bÃªn bá» vá»±c sá»¥p Ä‘á»•',
        };

        return "{$knowledge}. {$ritual}. {$stability}.";
    }

    private function renderTension(WorldSnapshot $snapshot): string
    {
        $tension = $snapshot->compositeTension();

        return match (true) {
            $tension > 0.7 => 'âš ï¸ Cáº¢NH BÃO: Ãp lá»±c vÅ© trá»¥ cá»±c ká»³ cao â€” ThiÃªn Kiáº¿p cÃ³ thá»ƒ xáº£y ra',
            $tension > 0.5 => 'ðŸ”¶ Ãp lá»±c gia tÄƒng â€” cÃ¡c lá»±c lÆ°á»£ng vÅ© trá»¥ Ä‘ang há»™i tá»¥',
            $tension > 0.3 => 'ðŸ”µ Ãp lá»±c vá»«a pháº£i â€” vÅ© trá»¥ váº­n hÃ nh bÃ¬nh thÆ°á»ng',
            default        => 'ðŸŸ¢ BÃ¬nh yÃªn â€” váº¡n váº­t hÃ i hÃ²a',
        };
    }

    private function renderEvents(array $events): array
    {
        $rendered = [];

        foreach ($events as $event) {
            $type = $event['type'] ?? 'UNKNOWN';
            $year = $event['year'] ?? '?';
            $from = $event['from'] ?? '?';
            $to = $event['to'] ?? '?';

            $rendered[] = match ($type) {
                'MINOR_BIFURCATION' => "ðŸŒ€ NÄƒm {$year}: ThiÃªn Äáº¡o chuyá»ƒn biáº¿n â€” Cháº¿ Ä‘á»™ [{$from}] â†’ [{$to}]. Váº¡n váº­t cáº£m á»©ng, thá»i Ä‘áº¡i má»›i báº¯t Ä‘áº§u.",
                'MAJOR_BIFURCATION' => "ðŸ’¥ NÄƒm {$year}: Äáº I THIÃŠN BIáº¾N! Má»™t cháº¿ Ä‘á»™ vÅ© trá»¥ chÆ°a tá»«ng tá»“n táº¡i Ä‘Ã£ xuáº¥t hiá»‡n: [{$to}]. ÄÃ¢y lÃ  khoáº£nh kháº¯c mÃ  lá»‹ch sá»­ chia thÃ nh TRÆ¯á»šC vÃ  SAU.",
                default => "ðŸ“Œ NÄƒm {$year}: Sá»± kiá»‡n vÅ© trá»¥ [{$type}].",
            };
        }

        return $rendered;
    }
}




