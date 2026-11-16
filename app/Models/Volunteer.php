<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Volunteer extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_type', 'document_number', 'first_names', 'last_names', 'preferred_name',
        'treatment', 'email', 'phone', 'neighborhood', 'help_type',
        'terms_accepted_at', 'terms_version', 'ip_address', 'user_agent', 'source',
    ];

    protected $casts = [
        'terms_accepted_at' => 'datetime',
    ];

    public function getFullNameAttribute(): string
    {
        return trim($this->first_names.' '.$this->last_names);
    }

    /** Cómo saludar a la persona: "Señora Ana", "Señor Luis" o solo "Ana". */
    public function getGreetingAttribute(): string
    {
        $name = $this->preferred_name ?: strtok($this->first_names, ' ');
        $prefix = match ($this->treatment) {
            'senora' => 'Señora ',
            'senor' => 'Señor ',
            default => '',
        };

        return $prefix.$name;
    }

    public function getDocumentTypeLabelAttribute(): string
    {
        return config('laura.tipos_documento')[$this->document_type] ?? $this->document_type;
    }

    public function getTreatmentLabelAttribute(): string
    {
        return config('laura.tratamientos')[$this->treatment] ?? $this->treatment;
    }

    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('first_names', 'like', "%{$term}%")
                ->orWhere('last_names', 'like', "%{$term}%")
                ->orWhere('preferred_name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('document_number', 'like', "%{$term}%");
        });
    }
}
