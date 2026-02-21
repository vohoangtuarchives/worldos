<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorldPrimitive extends Model
{
    // Read-only model - no mass assignment allowed
    protected $fillable = [];
    protected $guarded = ['*'];

    protected $casts = [
        'domain' => \Tuzy\Domain\World\Enums\PrimitiveDomain::class,
        'constraints' => 'array',
        'tags' => 'array',
    ];

    /**
     * Check if primitive exists by domain and code
     */
    public static function exists(string $domain, string $code, string $version = '1.0.0'): bool
    {
        return static::where('domain', $domain)
            ->where('code', $code)
            ->where('version', $version)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Get primitive by code
     */
    public static function getByCode(string $code, string $version = '1.0.0'): ?self
    {
        return static::where('code', $code)
            ->where('version', $version)
            ->where('is_active', true)
            ->first();
    }
    /**
     * Get the bindings for this primitive
     */
    public function worldBindings()
    {
        return $this->hasMany(WorldPrimitiveBinding::class);
    }
}
