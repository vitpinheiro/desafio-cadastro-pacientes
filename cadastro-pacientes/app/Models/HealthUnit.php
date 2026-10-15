<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class HealthUnit extends Model
{
    protected $fillable = ['name', 'city'];

    protected static function booted(): void
    {
        static::creating(function (HealthUnit $healthUnit) {
            $healthUnit->uuid = $healthUnit->uuid ?? (string) Str::uuid();
        });
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }
}
