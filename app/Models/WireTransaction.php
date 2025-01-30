<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\WireInventory;
use App\Models\EngineRepairCard;

class WireTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wire_id',
        'repair_card_id',
        'type',
        'amount',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the wire that owns the transaction.
     */
    public function wire(): BelongsTo
    {
        return $this->belongsTo(WireInventory::class, 'wire_id');
    }

    /**
     * Get the repair card associated with the transaction.
     */
    public function repair_card(): BelongsTo
    {
        return $this->belongsTo(EngineRepairCard::class, 'repair_card_id');
    }
}
