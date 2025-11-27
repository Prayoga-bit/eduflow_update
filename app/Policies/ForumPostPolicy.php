<?php

namespace App\Policies;

use App\Models\ForumPost;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ForumPostPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Anyone can view posts
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ForumPost $post): bool
    {
        // Anyone can view a post if it's in a public group
        if ($post->group->is_public) {
            return true;
        }
        
        // For private groups, only members can view posts
        return $post->group->isMember($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Any authenticated user can create a post
        return $user->exists;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ForumPost $post): bool
    {
        // Only the post author, group admins, or site admins can update the post
        return $user->id === $post->user_id || 
               $post->group->isAdmin($user) || 
               $user->is_admin;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ForumPost $post): bool
    {
        // Only the post author, group admins, or site admins can delete the post
        return $user->id === $post->user_id || 
               $post->group->isAdmin($user) || 
               $user->is_admin;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ForumPost $post): bool
    {
        // Only group admins or site admins can restore posts
        return $post->group->isAdmin($user) || $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ForumPost $post): bool
    {
        // Only site admins can force delete posts
        return $user->is_admin;
    }

    /**
     * Determine whether the user can like the post.
     */
    public function like(User $user, ForumPost $post): bool
    {
        // Can't like your own post
        if ($user->id === $post->user_id) {
            return false;
        }
        
        // Must be a member of the group to like posts
        return $post->group->isMember($user);
    }
}
