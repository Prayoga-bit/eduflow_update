<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class ForumGroup extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'banner_image',
        'is_private',
        'created_by',
    ];

    protected $casts = [
        'is_private' => 'boolean',
    ];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($group) {
            $group->slug = Str::slug($group->name);
        });

        static::updating(function ($group) {
            $group->slug = Str::slug($group->name);
        });
    }

    /**
     * Get the user that created the group.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'forum_group_members', 'group_id', 'user_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get the group posts.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(PostGroup::class, 'group_id');
    }
    
    /**
     * Alias for posts() for backward compatibility.
     * 
     * @deprecated Use posts() instead.
     */
    public function postGroups(): HasMany
    {
        return $this->posts()->latest();
    }

    /**
     * Check if a user is a member of this group
     *
     * @param User|int|null $user
     * @return bool
     */
    public function isMember($user = null): bool
    {
        if (!$user) {
            $user = auth()->user();
        }
        
        if (!$user) {
            return false;
        }
        
        $userId = $user instanceof User ? $user->id : $user;
        
        if ($this->relationLoaded('members')) {
            return $this->members->contains('id', $userId);
        }
        
        return $this->members()->where('user_id', $userId)->exists();
    }

    /**
     * Check if a user is an admin of this group
     *
     * @param User|int|null $user
     * @return bool
     */
    public function isAdmin($user = null): bool
    {
        if (!$user) {
            $user = auth()->user();
        }
        
        if (!$user) {
            return false;
        }
        
        $userId = $user instanceof User ? $user->id : $user;
        
        // Check if the user is the creator of the group
        if ($this->created_by === $userId) {
            return true;
        }
        
        // Check if the user has an admin role in the group members
        if ($this->relationLoaded('members')) {
            $member = $this->members->firstWhere('id', $userId);
            return $member ? $member->pivot->role === 'admin' : false;
        }
        
        // If members are not loaded, query the database
        $member = $this->members()
            ->where('user_id', $userId)
            ->first();
            
        return $member ? $member->pivot->role === 'admin' : false;
    }

}
