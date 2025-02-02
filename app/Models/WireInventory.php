<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WireInventory extends Model
{
    use HasFactory;

    protected $table = 'wire_inventory';

    protected $fillable = [
        'diameter',
        'weight',
    ];

    protected $casts = [
        'diameter' => 'decimal:2',
        'weight' => 'decimal:2',
    ];

    public function wireUsages(): HasMany
    {
        return $this->hasMany(WireUsage::class);
    }

    public function repair_cards()
    {
        return $this->hasMany(RepairCard::class, 'wire_id');
    }

    /**
     * Get the transactions for the wire.
     */
    public function transactions()
    {
        return $this->hasMany(WireTransaction::class, 'wire_id');
    }

    /**
     * Get the reservations for the wire.
     */
    public function reservations()
    {
        return $this->hasMany(WireReservation::class);
    }

    /**
     * Get the available weight (excluding reservations)
     */
    public function getAvailableWeightAttribute()
    {
        return $this->weight - $this->reservations()->sum('reserved_weight');
    }
} 





