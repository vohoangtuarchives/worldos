<?php

namespace App\Console\Commands;

use Tuzy\Domain\Vietnamese\Models\VietnameseHero;
use Illuminate\Console\Command;

class FixHeroErasCommand extends Command
{
    protected $signature = 'world:fix-hero-eras';
    protected $description = 'Recalculate hero eras based heavily on birth year (50 years per era)';

    public function handle(): int
    {
        $heroes = VietnameseHero::whereNotNull('birth_year')->get();
        
        $this->info("Fixing eras for {$heroes->count()} heroes...");
        
        foreach ($heroes as $hero) {
            $oldEra = $hero->era;
            $newEra = (int) floor($hero->birth_year / 50);
            
            if ($oldEra !== $newEra) {
                $hero->era = $newEra;
                $hero->save();
                $this->line("Updated {$hero->name}: Era {$oldEra} -> {$newEra} (Year {$hero->birth_year})");
            }
        }
        
        $this->info("✅ Eras updated successfully.");
        return Command::SUCCESS;
    }
}
