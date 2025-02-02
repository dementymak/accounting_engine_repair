<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScrapInventory extends Model
{
    use HasFactory;

    protected $table = 'scrap_inventory';

    protected $fillable = [
        'weight',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(ScrapTransaction::class, 'scrap_id');
    }
} 