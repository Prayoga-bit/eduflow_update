<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostGroup extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'post_group';

    protected $fillable = [
        'group_id',
        'user_id',
        'content',
        'media_path',
        'media_type',
        'media_name',
        'media_size',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the group that owns the post.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(ForumGroup::class, 'group_id');
    }
    
    /**
     * Alias for group() for backward compatibility.
     */
    public function forumGroup(): BelongsTo
    {
        return $this->belongsTo(ForumGroup::class, 'group_id');
    }

    /**
     * Get the user that created the post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the replies for the post.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(ReplyPostGroup::class, 'post_group_id')->latest();
    }

    /**
     * Get the number of replies for the post.
     */
    public function repliesCount(): int
    {
        return $this->replies()->count();
    }
}
