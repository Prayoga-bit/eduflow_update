<?php

namespace App\Policies;

use App\Models\ForumReply;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ForumReplyPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Anyone can view replies
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ForumReply $reply): bool
    {
        // Anyone can view a reply if it's in a public group
        if ($reply->post->group->is_public) {
            return true;
        }
        
        // For private groups, only members can view replies
        return $reply->post->group->isMember($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Any authenticated user can create a reply
        return $user->exists;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ForumReply $reply): bool
    {
        // Only the reply author, group admins, or site admins can update the reply
        return $user->id === $reply->user_id || 
               $reply->post->group->isAdmin($user) || 
               $user->is_admin;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ForumReply $reply): bool
    {
        // Only the reply author, group admins, or site admins can delete the reply
        return $user->id === $reply->user_id || 
               $reply->post->group->isAdmin($user) || 
               $user->is_admin;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ForumReply $reply): bool
    {
        // Only group admins or site admins can restore replies
        return $reply->post->group->isAdmin($user) || $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ForumReply $reply): bool
    {
        // Only site admins can force delete replies
        return $user->is_admin;
    }

    /**
     * Determine whether the user can like the reply.
     */
    public function like(User $user, ForumReply $reply): bool
    {
        // Can't like your own reply
        if ($user->id === $reply->user_id) {
            return false;
        }
        
        // Must be a member of the group to like replies
        return $reply->post->group->isMember($user);
    }


    /**
     * Determine whether the user can reply to the reply.
     */
    public function reply(User $user, ForumReply $reply): bool
    {
        // Must be a member of the group to reply
        return $reply->post->group->isMember($user);
    }
}
