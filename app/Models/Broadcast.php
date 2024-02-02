<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Broadcast extends Model
{
    use HasFactory;
    public function scopeDay(Builder $query): void
    {
        $query->where('created_at', '>', now()->subDay());
    }
    public function scopeWeek(Builder $query): void
    {
        $query->where('created_at', '>', now()->subWeek());
    }
    public function scopeMonth(Builder $query): void
    {
        $query->where('created_at', '>', now()->subMonth());
    }
    public function scopeYear(Builder $query): void
    {
        $query->where('created_at', '>', now()->subYear());
    }
}
