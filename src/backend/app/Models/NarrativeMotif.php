<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NarrativeMotif extends Model
{
    protected $table = 'narrative_motif_registry';

    protected $fillable = ['story_ref', 'symbol', 'visual_anchor', 'wound_theme'];
}
