<?php

namespace App\Policies;

use App\Models\ForumGroup;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ForumGroupPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Anyone can view groups
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, ForumGroup $forumGroup): bool
    {
        // Public groups can be viewed by anyone
        if (!$forumGroup->is_private) {
            return true;
        }
        
        // Private groups can only be viewed by members or admins
        return $user && ($forumGroup->isMember($user) || $user->is_admin);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Any authenticated user can create a group
        return $user->exists;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ForumGroup $forumGroup): bool
    {
        // Only the group creator or an admin can update the group
        return $user->id === $forumGroup->user_id || $user->is_admin;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ForumGroup $forumGroup): bool
    {
        // Only the group creator or an admin can delete the group
        return $user->id === $forumGroup->user_id || $user->is_admin;
    }

    /**
     * Determine whether the user can join the group.
     */
    public function join(User $user, ForumGroup $forumGroup): bool
    {
        // Can't join if already a member
        if ($forumGroup->isMember($user)) {
            return false;
        }
        
        // Can't join private groups without an invitation
        if ($forumGroup->is_private) {
            return false;
        }
        
        return true;
    }

    /**
     * Determine whether the user can leave the group.
     */
    public function leave(User $user, ForumGroup $forumGroup): bool
    {
        // Must be a member to leave
        if (!$forumGroup->isMember($user)) {
            return false;
        }
        
        // Group creator can't leave (must delete the group instead)
        if ($user->id === $forumGroup->created_by) {
            return false;
        }
        
        return true;
    }

    /**
     * Determine whether the user can invite members to the group.
     */
    public function invite(User $user, ForumGroup $forumGroup): bool
    {
        // Only group admins can invite members
        return $forumGroup->members()
            ->where('user_id', $user->id)
            ->whereIn('role', ['admin', 'moderator'])
            ->exists();
    }

    /**
     * Determine whether the user can manage the group.
     */
    public function manage(User $user, ForumGroup $forumGroup): bool
    {
        // Only the group creator or an admin can manage the group
        return $user->id === $forumGroup->created_by || $user->is_admin;
    }
}
