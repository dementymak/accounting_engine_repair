<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EngineRepairCard extends Model
{
    protected $fillable = [
        'task_number',
        'repair_card_number',
        'completed_at',
        'crown_height',
        'connection_type',
        'connection_notes',
        'groove_distances',
        'wires_in_groove',
        'wire',
        'temperature_sensor',
        'scrap_weight',
        'total_wire_weight',
        'winding_resistance',
        'mass_resistance',
        'model',
        'notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'completed_at' => 'datetime',
        'crown_height' => 'double',
        'scrap_weight' => 'decimal:2',
        'total_wire_weight' => 'decimal:2',
        'groove_distances' => 'array',
    ];

    public function wireUsages(): HasMany
    {
        return $this->hasMany(WireUsage::class, 'repair_card_id');
    }

    public function originalWires(): HasMany
    {
        return $this->hasMany(OriginalWire::class, 'repair_card_id');
    }

    public function scrapTransactions(): HasMany
    {
        return $this->hasMany(ScrapTransaction::class, 'repair_card_id');
    }

    public function calculateTotalUsedWeight(): float
    {
        return $this->wireUsages->sum(function ($usage) {
            return $usage->initial_weight - $usage->used_weight;
        });
    }
} 







