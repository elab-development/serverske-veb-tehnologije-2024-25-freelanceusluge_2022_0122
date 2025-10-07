<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage; // za url()

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'headline',
        'bio',
        'github_url',
        'portfolio_url',
        'avatar_path',   // <— npr. 'profiles/123/avatar.jpg'
        'banner_path',   // (opciono)
    ];

    protected $appends = ['avatar_url', 'banner_url']; // auto se dodaju u JSON

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function skills() { return $this->belongsToMany(Skill::class); }


    // Izračunata polja za javni URL
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar_path ? Storage::url($this->avatar_path) : null;
    }

    public function getBannerUrlAttribute(): ?string
    {
        return $this->banner_path ? Storage::url($this->banner_path) : null;
    }
}
