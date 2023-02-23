<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'expiration_date', 'price', 'type', 'quantity'];
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    public function scopeValid(Builder $query): void
    {
        $query->where('expiration_date', '>', Carbon::now());
    }
}
