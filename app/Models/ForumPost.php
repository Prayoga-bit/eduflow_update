<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForumPost extends Model
{
    /** @use HasFactory<\Database\Factories\ForumPostFactory> */
    use HasFactory;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'postid';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'group_id',
        'title',
        'content',
        'media_path',
        'media_type',
        'media_name',
        'views',
    ];

    /**
     * Get the user that owns the forum post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the group that owns the forum post.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(ForumGroup::class, 'group_id', 'id');
    }
    
    /**
     * Check if the post has media.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function media()
    {
        return $this->hasOne(ForumPost::class, 'postid', 'postid')
            ->whereNotNull('media_path');
    }

    /**
     * Get the replies for the forum post.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(ForumReply::class, 'postid', 'postid');
    }

    /**
     * Get the attachments for the forum post.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'postid', 'postid');
    }

    /**
     * Get all of the tags for the forum post.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    /**
     * Increment the view count for the forum post.
     *
     * @return void
     */
    public function incrementViewCount(): void
    {
        $this->increment('views');
    }
}