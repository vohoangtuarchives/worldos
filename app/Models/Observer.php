<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Observer extends Model
{
    protected $fillable = ['name', 'role'];

    public function versions(): HasMany
    {
        return $this->hasMany(ObserverVersion::class);
    }
}
