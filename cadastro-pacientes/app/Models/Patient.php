<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Patient extends Model
{
    protected $fillable = ['name', 'cpf', 'birth_date', 'phone', 'health_unit_id'];

    protected $casts = [
        'birth_date' => 'date:Y-m-d',
    ];

    public function healthUnit(): BelongsTo
    {
        return $this->belongsTo(HealthUnit::class);
    }
}
