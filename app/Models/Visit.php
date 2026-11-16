<?php

namespace App\Models;

use App\Support\Analytics\SourceClassifier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    protected $casts = [
        'is_entry' => 'boolean',
        'is_bot' => 'boolean',
        'is_ai_bot' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function scopeHumans($query)
    {
        return $query->where('is_bot', false);
    }

    public function getChannelLabelAttribute(): string
    {
        return SourceClassifier::LABELS[$this->channel] ?? $this->channel;
    }
}
