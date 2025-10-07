<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Project extends Model
{
    protected $fillable = [
        'client_id',
        'title',
        'description',
        'budget_min',
        'budget_max',
        'status', // npr: 'open','in_progress','closed'
        'tags',   // opcionalno JSON lista tagova
    ];

    protected $casts = [
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
        'tags'       => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    // Ako svaki projekat u datom trenutku ima 1 aktivno angažovanje
    public function engagement(): HasOne
    {
        return $this->hasOne(Engagement::class);
    }
}
