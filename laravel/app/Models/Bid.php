<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Bid extends Model
{
    use HasFactory;
    protected $fillable = [
        'project_id',
        'provider_id',
        'amount',
        'message',
        'status', // 'pending','accepted','rejected','withdrawn'
        'days_to_complete',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'days_to_complete' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function engagement(): HasOne
    {
        return $this->hasOne(Engagement::class);
    }
}
