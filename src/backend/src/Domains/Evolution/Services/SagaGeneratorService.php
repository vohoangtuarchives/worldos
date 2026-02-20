<?php

namespace WorldOS\Domains\Evolution\Services;

use WorldOS\Domains\Evolution\ValueObjects\Universe;

class SagaGeneratorService
{
    private \App\Domains\Narrative\Services\NarrativeBridge $narrativeBridge;

    /** Nhiá»u biáº¿n thá»ƒ káº¿t thÃºc theo cause â€” tá»« ngá»¯ vÃ  vÄƒn phong Ä‘a dáº¡ng */
    private const ENDINGS = [
        'HEAT_DEATH' => [
            'Cuá»‘i cÃ¹ng, Ä‘á»‹nh luáº­t nhiá»‡t Ä‘á»™ng há»c khÃ´ng thá»ƒ chá»‘i bá». Entropy nuá»‘t chá»­ng má»i cáº¥u trÃºc, chá»‰ cÃ²n láº¡i khoáº£ng khÃ´ng láº¡nh láº½o vÃ  im láº·ng (Nhiá»‡t cháº¿t).',
            'Tráº­t tá»± nhÆ°á»ng chá»— cho há»—n mang. Tháº¿ giá»›i táº¯t dáº§n trong cÃ¡i láº¡nh vÃ´ táº­n cá»§a entropy tá»‘i Ä‘a (Heat Death).',
        ],
        'TIME_CRUNCH' => [
            'Báº£n thÃ¢n thá»i gian rÃ£ ra. VÅ© trá»¥ sá»¥p Ä‘á»• dÆ°á»›i sá»©c náº·ng lá»‹ch sá»­ cá»§a chÃ­nh nÃ³, káº¿t thÃºc trong má»™t ká»³ dá»‹ thuáº§n dá»¯ liá»‡u (Time Crunch).',
        ],
        'STAGNATION' => [
            'Sá»± hoÃ n háº£o trá»Ÿ thÃ nh nhÃ  tÃ¹. KhÃ´ng cÃ²n thay Ä‘á»•i, sá»± sá»‘ng máº¥t háº¿t Ã½ nghÄ©a; vÅ© trá»¥ hÃ³a thÃ nh má»™t tÆ°á»£ng Ä‘Ã i tÄ©nh táº¡i, vÄ©nh cá»­u (TrÃ¬ trá»‡).',
            'Tráº­t tá»± tuyá»‡t Ä‘á»‘i vÃ  entropy gáº§n nhÆ° báº±ng khÃ´ng khiáº¿n tháº¿ giá»›i Ä‘Ã³ng bÄƒng â€” khÃ´ng cÃ²n kháº£ nÄƒng thÃ­ch nghi hay tiáº¿n hÃ³a (Stagnation).',
        ],
        'STRUCTURAL_FRACTURE' => [
            'Äáº¡o cá»§a tháº¿ giá»›i ráº¡n ná»©t. MÃ¢u thuáº«n tÃ­ch tá»¥ vÆ°á»£t ngÆ°á»¡ng chá»‹u Ä‘á»±ng; cáº¥u trÃºc sá»¥p Ä‘á»• tá»« bÃªn trong (Structural Fracture).',
            'Sá»± ráº¡n ná»©t cáº¥u trÃºc khÃ´ng thá»ƒ hÃ n gáº¯n. Tháº¿ giá»›i tan vá»¡ dÆ°á»›i Ã¡p lá»±c cá»§a chÃ­nh nhá»¯ng mÃ¢u thuáº«n ná»™i táº¡i.',
        ],
        'CONVERGENCE' => [
            'DÃ²ng thá»i gian riÃªng cá»§a nÃ³ Ä‘Ã£ hÃ²a vÃ o má»™t Ä‘iá»ƒm Ä‘á»“ng bá»™ tuyá»‡t Ä‘á»‘i vá»›i má»™t thá»±c táº¡i khÃ¡c. Hai thÃ nh má»™t, vÆ°á»£t lÃªn (Convergence).',
        ],
        'BIFURCATION' => [
            'Äá»™ phá»©c táº¡p cá»§a nhá»¯ng lá»±a chá»n vÆ°á»£t quÃ¡ sá»©c chá»©a cá»§a má»™t dÃ²ng thá»i gian. NÃ³ Ä‘Ã£ tÃ¡ch thÃ nh nhá»¯ng nhÃ¡nh tá»“n táº¡i song song (Bifurcation).',
        ],
        'default' => [
            'NÃ³ káº¿t thÃºc Ä‘á»™t ngá»™t, sá»‘ pháº­n Ä‘Ã³ng áº¥n bá»Ÿi nhá»¯ng lá»±c lÆ°á»£ng vÅ© trá»¥ khÃ´ng rÃµ.',
            'Tháº¿ giá»›i cháº¥m dá»©t trong má»™t cÃ¡ch mÃ  biÃªn niÃªn sá»­ khÃ´ng ghi chÃ©p Ä‘áº§y Ä‘á»§.',
        ],
    ];

    public function __construct(\App\Domains\Narrative\Services\NarrativeBridge $narrativeBridge)
    {
        $this->narrativeBridge = $narrativeBridge;
    }

    public function generateSaga(Universe $universe, string $deathCause): string
    {
        $state = $universe->getState();
        $age = $universe->getAge();
        $id = substr($universe->getId(), 0, 8);
        $name = "Universe " . $id;

        $genre = $this->narrativeBridge->detectGenre($state);
        $primaryGenre = $genre->getPrimaryGenre();
        $traits = implode(', ', $genre->getTraits());

        $params = $universe->getParameters();
        $ancestors = $params['ancestors'] ?? [];
        if (!empty($ancestors)) {
            $shortAncestors = array_map(fn ($aid) => substr($aid, 0, 8), $ancestors);
            $originPrefix = "Sinh ra tá»« dÃ²ng dÃµi [" . implode(', ', $shortAncestors) . "], nÃ³ ";
        } else {
            $originPrefix = "Ná»•i lÃªn tá»« bá»t lÆ°á»£ng tá»­ nhÆ° ";
        }

        $intro = "{$originPrefix} má»™t thá»±c táº¡i **{$primaryGenre}** vá»›i cÃ¡c Ä‘áº·c trÆ°ng [{$traits}].";
        $mid = $this->getMiddle($universe);
        $end = $this->getEnding($deathCause);

        return "{$name}. {$intro} {$mid} {$end}";
    }

    private function getMiddle(Universe $u): string
    {
        $context = $this->narrativeBridge->generateContext($u->getState());
        $age = $u->getAge();
        $params = $u->getParameters();
        $milestoneStr = "";
        if (!empty($params['milestones'])) {
            $milestoneStr = "\n\n**DÃ²ng thá»i gian:**\n";
            foreach (array_slice($params['milestones'], -8) as $m) {
                $milestoneStr .= "- Chu ká»³ {$m['age']}: **{$m['event']}** â€” {$m['description']}\n";
            }
        }
        return "Trong {$age} chu ká»³ tá»“n táº¡i, mÃ´ phá»ng ghi nháº­n: \"{$context}\"{$milestoneStr}";
    }

    private function getEnding(string $cause): string
    {
        $pool = self::ENDINGS[$cause] ?? self::ENDINGS['default'];
        return $pool[array_rand($pool)];
    }
}



