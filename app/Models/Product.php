<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'expiration_date', 'price', 'type', 'quantity'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function consumptions(): HasMany
    {
        return $this->hasMany(Consumption::class);
    }

    public function scopeValid(Builder $query): void
    {
        $query->where('expiration_date', '>', Carbon::now());
    }

    public function quantityConsumed(): float
    {
        $quantity = 0.0;
        foreach ($this->consumptions as $consumption) {
            $quantity += $consumption->quantity;
        }

        return $quantity;
    }

    public function getPercentageConsumed(): float
    {
        $initialQuantity = $this->quantity;
        $consumedQuantity = $this->quantityConsumed();

        $percentageConsumed = ($consumedQuantity * 100) / $initialQuantity;
        return $percentageConsumed;
    }

    public function getPercentageWasted(): float
    {
        return 1 - $this->getPercentageConsumed();
    }
}
