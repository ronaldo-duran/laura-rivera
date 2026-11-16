<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitEvent extends Model
{
    public const UPDATED_AT = null;

    public const TYPES = ['click', 'outbound', 'whatsapp', 'tel', 'mailto', 'download', 'scroll', 'engagement'];

    protected $guarded = ['id'];

    protected $casts = ['created_at' => 'datetime'];
}
