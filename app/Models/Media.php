<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'path',
        'name',
        'type',
        'size',
        'mime_type',
        'mediable_id',
        'mediable_type',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    /**
     * Get the parent mediable model (PostGroup, etc.).
     */
    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the URL to the media file.
     */
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }
}
