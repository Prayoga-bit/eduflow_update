<?php

namespace App\Services;

use App\Models\ForumPost;
use App\Models\ForumReply;
use App\Models\User;
use App\Notifications\Forum\NewReplyNotification;
use Illuminate\Support\Collection;

class ForumSubscriptionService
{
    /**
     * The post instance.
     *
     * @var \App\Models\ForumPost
     */
    protected $post;

    /**
     * The reply instance (if any).
     *
     * @var \App\Models\ForumReply|null
     */
    protected $reply;

    /**
     * The user who performed the action.
     *
     * @var \App\Models\User
     */
    protected $actor;

    /**
     * Create a new subscription service instance.
     *
     * @param  \App\Models\ForumPost  $post
     * @param  \App\Models\User  $actor
     * @param  \App\Models\ForumReply|null  $reply
     * @return void
     */
    public function __construct(ForumPost $post, User $actor, ?ForumReply $reply = null)
    {
        $this->post = $post;
        $this->actor = $actor;
        $this->reply = $reply;
    }

    /**
     * Subscribe a user to the post.
     *
     * @param  \App\Models\User|null  $user
     * @return void
     */
    public function subscribe(?User $user = null): void
    {
        $user = $user ?: $this->actor;
        
        if ($user->id !== $this->actor->id && !$user->can('manage_subscriptions', $this->post->group)) {
            return;
        }

        if (!$this->isSubscribed($user)) {
            $this->post->subscribers()->attach($user->id, [
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Unsubscribe a user from the post.
     *
     * @param  \App\Models\User|null  $user
     * @return void
     */
    public function unsubscribe(?User $user = null): void
    {
        $user = $user ?: $this->actor;
        
        if ($user->id !== $this->actor->id && !$user->can('manage_subscriptions', $this->post->group)) {
            return;
        }

        $this->post->subscribers()->detach($user->id);
    }

    /**
     * Toggle subscription status for a user.
     *
     * @param  \App\Models\User|null  $user
     * @return bool  Returns the new subscription status
     */
    public function toggleSubscription(?User $user = null): bool
    {
        $user = $user ?: $this->actor;
        
        if ($this->isSubscribed($user)) {
            $this->unsubscribe($user);
            return false;
        }
        
        $this->subscribe($user);
        return true;
    }

    /**
     * Check if a user is subscribed to the post.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function isSubscribed(User $user): bool
    {
        if ($user->id === $this->post->user_id) {
            return true; // Original poster is always considered subscribed
        }
        
        return $this->post->subscribers()->where('user_id', $user->id)->exists();
    }

    /**
     * Notify subscribers about a new reply.
     *
     * @param  \App\Models\ForumReply  $reply
     * @return void
     */
    public function notifySubscribers(ForumReply $reply): void
    {
        $this->reply = $reply;
        
        // Don't notify if the reply is not yet approved (if moderation is enabled)
        if (config('forum.moderation.enabled') && !$reply->is_approved) {
            return;
        }
        
        $subscribers = $this->getSubscribersToNotify();
        
        foreach ($subscribers as $subscriber) {
            // Don't notify the user who created the reply
            if ($subscriber->id === $this->actor->id) {
                continue;
            }
            
            // Don't notify users who have been mentioned (they'll get a separate mention notification)
            if ($this->reply->mentions->contains($subscriber)) {
                continue;
            }
            
            $subscriber->notify(new NewReplyNotification($reply));
        }
    }

    /**
     * Get all subscribers who should be notified about a new reply.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getSubscribersToNotify()
    {
        // Start with all subscribers
        $query = $this->post->subscribers()
            ->where('users.id', '!=', $this->actor->id);
        
        // If this is a reply to another reply, include the parent reply's author
        if ($this->reply->parent_id) {
            $query->orWhere('users.id', $this->reply->parent->user_id);
        }
        
        // Always include the original poster if they're not the one replying
        if ($this->post->user_id !== $this->actor->id) {
            $query->orWhere('users.id', $this->post->user_id);
        }
        
        // Apply notification preferences
        $query->where(function ($q) {
            $q->where('notification_preferences->email_replies', true)
              ->orWhere('notification_preferences->web_replies', true);
        });
        
        return $query->get();
    }

    /**
     * Get all users mentioned in the reply.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMentionedUsers()
    {
        if (!$this->reply) {
            return collect();
        }
        
        return $this->reply->mentionedUsers();
    }

    /**
     * Get the subscription status for multiple posts at once.
     *
     * @param  \Illuminate\Support\Collection  $posts
     * @param  \App\Models\User  $user
     * @return array
     */
    public static function getBulkSubscriptionStatus(Collection $posts, User $user): array
    {
        if ($posts->isEmpty()) {
            return [];
        }
        
        $subscribedPostIds = $user->subscribedPosts()
            ->whereIn('forum_posts.id', $posts->pluck('id'))
            ->pluck('forum_posts.id')
            ->toArray();
        
        $result = [];
        foreach ($posts as $post) {
            $result[$post->id] = in_array($post->id, $subscribedPostIds);
        }
        
        return $result;
    }

    /**
     * Get the number of subscribers for a post.
     *
     * @return int
     */
    public function getSubscriberCount(): int
    {
        return $this->post->subscribers()->count();
    }

    /**
     * Get the subscribers for the post.
     *
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSubscribers(int $limit = 10)
    {
        return $this->post->subscribers()
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    /**
     * Get posts that a user is subscribed to.
     *
     * @param  \App\Models\User  $user
     * @param  int  $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getUserSubscriptions(User $user, int $perPage = 15)
    {
        return $user->subscribedPosts()
            ->with(['user', 'group', 'lastReply.user'])
            ->withCount(['replies'])
            ->orderBy('forum_post_subscriptions.updated_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get the subscription settings for a user in a group.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ForumGroup  $group
     * @return array
     */
    public static function getUserGroupSubscriptionSettings(User $user, $group): array
    {
        $settings = [
            'auto_subscribe' => false,
            'notification_preferences' => [
                'email' => true,
                'web' => true,
            ],
        ];
        
        $subscription = $user->groupSubscriptions()
            ->where('group_id', $group->id)
            ->first();
            
        if ($subscription) {
            $settings['auto_subscribe'] = (bool) $subscription->auto_subscribe;
            $settings['notification_preferences'] = array_merge(
                $settings['notification_preferences'],
                $subscription->notification_preferences ?? []
            );
        }
        
        return $settings;
    }

    /**
     * Update the subscription settings for a user in a group.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ForumGroup  $group
     * @param  array  $settings
     * @return void
     */
    public static function updateUserGroupSubscriptionSettings(
        User $user,
        $group,
        array $settings
    ): void {
        $user->groupSubscriptions()->updateOrCreate(
            ['group_id' => $group->id],
            [
                'auto_subscribe' => $settings['auto_subscribe'] ?? false,
                'notification_preferences' => $settings['notification_preferences'] ?? [
                    'email' => true,
                    'web' => true,
                ],
                'updated_at' => now(),
            ]
        );
    }
}
