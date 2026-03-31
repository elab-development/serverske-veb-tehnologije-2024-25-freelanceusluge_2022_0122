<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Engagement extends Model
{
    use HasFactory;
    protected $fillable = [
        'project_id',
        'bid_id',
        'provider_id',
        'client_id',
        'agreed_amount',
        'started_at',
        'ended_at',
        'state', // 'active','completed','cancelled'
    ];

    protected $casts = [
        'agreed_amount' => 'decimal:2',
        'started_at'    => 'datetime',
        'ended_at'      => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function bid(): BelongsTo
    {
        return $this->belongsTo(Bid::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
