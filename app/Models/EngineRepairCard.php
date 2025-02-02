<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EngineRepairCard extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'task_number',
        'repair_card_number',
        'model',
        'temperature_sensor',
        'crown_height',
        'connection_type',
        'connection_notes',
        'groove_distances',
        'wires_in_groove',
        'scrap_weight',
        'total_wire_weight',
        'winding_resistance',
        'mass_resistance',
        'notes',
        'completed_at',
        'wire_id'
    ];

    protected $casts = [
        'groove_distances' => 'array',
        'completed_at' => 'datetime',
        'crown_height' => 'float',
        'scrap_weight' => 'float',
        'total_wire_weight' => 'float'
    ];

    public function setGrooveDistancesAttribute($value)
    {
        if (is_string($value)) {
            $value = array_map('trim', explode('/', $value));
            $value = array_map('floatval', $value);
        }
        $this->attributes['groove_distances'] = json_encode($value);
    }

    public function wireUsages(): HasMany
    {
        return $this->hasMany(WireUsage::class, 'repair_card_id');
    }

    public function wireReservations(): HasMany
    {
        return $this->hasMany(WireReservation::class);
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

    public function getTotalWireWeightAttribute(): float
    {
        return $this->attributes['total_wire_weight'] ?? $this->calculateTotalUsedWeight();
    }

    public function getWireUsageSummaryAttribute(): array
    {
        $summary = [];
        foreach ($this->wireUsages as $usage) {
            $consumed = $usage->initial_weight - $usage->used_weight;
            $summary[] = [
                'diameter' => $usage->wireInventory->diameter,
                'initial_weight' => $usage->initial_weight,
                'residual_weight' => $usage->used_weight,
                'consumed_weight' => $consumed
            ];
        }
        return $summary;
    }
} 









