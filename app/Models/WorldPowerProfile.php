??php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class WorldPowerProfile extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'parameters' ='> 'array',
        'material_affinities' =''array',
        'progression_state' ='array',
        'collision_traits' ='array',
    ];

    public function world()
    {
        return $this-belongsTo(World::class);
    }
}