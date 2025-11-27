<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ForumGroup;
use App\Models\ForumPost;
use App\Exceptions\ForumException;
use Symfony\Component\HttpFoundation\Response;

class ForumAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     *
     * @throws \App\Exceptions\ForumException
     */
    public function handle(Request $request, Closure $next, ?string $guard = null)
    {
        // Get the authenticated user
        $user = Auth::guard($guard)->user();
        
        // Check if the user is authenticated
        if (!$user) {
            if ($request->expectsJson()) {
                throw ForumException::unauthorized('You must be logged in to access this resource.');
            }
            
            return redirect()->route('login');
        }
        
        // Check if the user's email is verified
        if (config('forum.enable_email_verification') && !$user->hasVerifiedEmail()) {
            if ($request->expectsJson()) {
                throw ForumException::forbidden('You must verify your email address before accessing the forum.');
            }
            
            return redirect()->route('verification.notice')
                ->with('warning', 'You must verify your email address before accessing the forum.');
        }
        
        // Check if the user is banned from the forum
        if ($user->is_banned_from_forum) {
            $message = 'You have been banned from the forum';
            
            if ($user->forum_ban_reason) {
                $message .= ': ' . $user->forum_ban_reason;
            }
            
            if ($user->forum_ban_expires_at) {
                $message .= '. This ban will be lifted on ' . $user->forum_ban_expires_at->format('F j, Y');
            }
            
            if ($request->expectsJson()) {
                throw ForumException::forbidden($message);
            }
            
            return redirect()->route('home')
                ->with('error', $message);
        }
        
        return $next($request);
    }
    
    /**
     * Check if the user can access a specific forum group.
     *
     * @param  \App\Models\ForumGroup  $group
     * @param  bool  $requireMembership
     * @return void
     *
     * @throws \App\Exceptions\ForumException
     */
    public static function checkGroupAccess(ForumGroup $group, bool $requireMembership = false): void
    {
        $user = Auth::user();
        
        // Check if the group is private and the user is not a member
        if ($group->is_private && (!$user || !$group->isMember($user))) {
            throw ForumException::forbidden('This is a private group. You must be a member to view its contents.');
        }
        
        // If membership is required, check if the user is a member
        if ($requireMembership && (!$user || !$group->isMember($user))) {
            throw ForumException::forbidden('You must be a member of this group to perform this action.');
        }
    }
    
    /**
     * Check if the user can moderate a forum group.
     *
     * @param  \App\Models\ForumGroup  $group
     * @param  string  $minRole
     * @return void
     *
     * @throws \App\Exceptions\ForumException
     */
    public static function checkModerationRights(ForumGroup $group, string $minRole = 'moderator'): void
    {
        $user = Auth::user();
        
        // Admins can do anything
        if ($user && $user->is_admin) {
            return;
        }
        
        // Check if the user is a member of the group
        if (!$user || !$group->isMember($user)) {
            throw ForumException::forbidden('You must be a member of this group to perform this action.');
        }
        
        // Check if the user has the required role
        $member = $group->members()->where('user_id', $user->id)->first();
        
        if (!$member) {
            throw ForumException::forbidden('You do not have permission to perform this action.');
        }
        
        $roles = ['member' => 1, 'moderator' => 2, 'admin' => 3];
        $requiredRole = $roles[$minRole] ?? 1;
        $userRole = $roles[$member->pivot->role] ?? 0;
        
        if ($userRole < $requiredRole) {
            throw ForumException::forbidden('You do not have sufficient permissions to perform this action.');
        }
    }
    
    /**
     * Check if the user can edit or delete a post.
     *
     * @param  \App\Models\ForumPost  $post
     * @return void
     *
     * @throws \App\Exceptions\ForumException
     */
    public static function checkPostOwnership(ForumPost $post): void
    {
        $user = Auth::user();
        
        // Admins can do anything
        if ($user && $user->is_admin) {
            return;
        }
        
        // Check if the user is the author of the post
        if ($user && $user->id === $post->user_id) {
            return;
        }
        
        // Check if the user is a moderator or admin of the group
        if ($user && $post->group) {
            $member = $post->group->members()->where('user_id', $user->id)->first();
            
            if ($member && in_array($member->pivot->role, ['moderator', 'admin'])) {
                return;
            }
        }
        
        throw ForumException::forbidden('You do not have permission to modify this post.');
    }
}
