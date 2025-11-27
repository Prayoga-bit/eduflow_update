<?php

namespace App\Services;

use App\Models\ForumPost;
use App\Models\ForumReply;
use App\Models\ForumGroup;
use App\Models\User;
use App\Notifications\Forum\PostModerated;
use App\Notifications\Forum\ReplyModerated;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ForumModerationService
{
    /**
     * The moderator performing the action.
     *
     * @var \App\Models\User
     */
    protected $moderator;

    /**
     * The reason for the moderation action.
     *
     * @var string
     */
    protected $reason;

    /**
     * Create a new forum moderation service instance.
     *
     * @param  \App\Models\User  $moderator
     * @return void
     */
    public function __construct(User $moderator)
    {
        $this->moderator = $moderator;
    }

    /**
     * Set the reason for the moderation action.
     *
     * @param  string  $reason
     * @return $this
     */
    public function withReason(string $reason): self
    {
        $this->reason = $reason;
        return $this;
    }

    /**
     * Approve a pending post.
     *
     * @param  \App\Models\ForumPost  $post
     * @return bool
     */
    public function approvePost(ForumPost $post): bool
    {
        if ($post->is_approved) {
            return true;
        }

        return DB::transaction(function () use ($post) {
            $post->update([
                'is_approved' => true,
                'approved_by' => $this->moderator->id,
                'approved_at' => now(),
            ]);

            // Notify the post author
            if ($post->user) {
                $post->user->notify(new PostModerated(
                    $post,
                    'approved',
                    $this->reason,
                    $this->moderator
                ));
            }

            return true;
        });
    }

    /**
     * Reject a pending post.
     *
     * @param  \App\Models\ForumPost  $post
     * @return bool
     */
    public function rejectPost(ForumPost $post): bool
    {
        return DB::transaction(function () use ($post) {
            $post->update([
                'is_approved' => false,
                'approved_by' => $this->moderator->id,
                'approved_at' => now(),
                'rejected_at' => now(),
            ]);

            // Notify the post author
            if ($post->user) {
                $post->user->notify(new PostModerated(
                    $post,
                    'rejected',
                    $this->reason,
                    $this->moderator
                ));
            }


            return true;
        });
    }


    /**
     * Delete a post and its replies.
     *
     * @param  \App\Models\ForumPost  $post
     * @param  bool  $softDelete
     * @return bool
     */
    public function deletePost(ForumPost $post, bool $softDelete = true): bool
    {
        return DB::transaction(function () use ($post, $softDelete) {
            $user = $post->user;
            
            // Delete or soft delete the post
            if ($softDelete) {
                $post->delete();
            } else {
                $post->forceDelete();
            }

            // Notify the post author if this wasn't their action
            if ($user && $user->id !== $this->moderator->id) {
                $user->notify(new PostModerated(
                    $post,
                    'deleted',
                    $this->reason,
                    $this->moderator
                ));
            }

            return true;
        });
    }


    /**
     * Approve a pending reply.
     *
     * @param  \App\Models\ForumReply  $reply
     * @return bool
     */
    public function approveReply(ForumReply $reply): bool
    {
        if ($reply->is_approved) {
            return true;
        }

        return DB::transaction(function () use ($reply) {
            $reply->update([
                'is_approved' => true,
                'approved_by' => $this->moderator->id,
                'approved_at' => now(),
            ]);

            // Notify the reply author
            if ($reply->user) {
                $reply->user->notify(new ReplyModerated(
                    $reply,
                    'approved',
                    $this->reason,
                    $this->moderator
                ));
            }


            return true;
        });
    }


    /**
     * Reject a pending reply.
     *
     * @param  \App\Models\ForumReply  $reply
     * @return bool
     */
    public function rejectReply(ForumReply $reply): bool
    {
        return DB::transaction(function () use ($reply) {
            $reply->update([
                'is_approved' => false,
                'approved_by' => $this->moderator->id,
                'approved_at' => now(),
                'rejected_at' => now(),
            ]);

            // Notify the reply author
            if ($reply->user) {
                $reply->user->notify(new ReplyModerated(
                    $reply,
                    'rejected',
                    $this->reason,
                    $this->moderator
                ));
            }


            return true;
        });
    }


    /**
     * Delete a reply.
     *
     * @param  \App\Models\ForumReply  $reply
     * @param  bool  $softDelete
     * @return bool
     */
    public function deleteReply(ForumReply $reply, bool $softDelete = true): bool
    {
        return DB::transaction(function () use ($reply, $softDelete) {
            $user = $reply->user;
            
            // Delete or soft delete the reply
            if ($softDelete) {
                $reply->delete();
            } else {
                $reply->forceDelete();
            }

            // Notify the reply author if this wasn't their action
            if ($user && $user->id !== $this->moderator->id) {
                $user->notify(new ReplyModerated(
                    $reply,
                    'deleted',
                    $this->reason,
                    $this->moderator
                ));
            }

            return true;
        });
    }


    /**
     * Ban a user from the forum.
     *
     * @param  \App\Models\User  $user
     * @param  \DateTimeInterface|string  $expiresAt
     * @param  string  $reason
     * @return bool
     */
    public function banUser(User $user, $expiresAt = null, string $reason = null): bool
    {
        if ($user->is_admin || $user->is_moderator) {
            throw new \InvalidArgumentException('Cannot ban administrators or moderators');
        }

        $expiresAt = $expiresAt ? Carbon::parse($expiresAt) : null;

        return DB::transaction(function () use ($user, $expiresAt, $reason) {
            $user->update([
                'banned_at' => now(),
                'banned_until' => $expiresAt,
                'banned_reason' => $reason,
                'banned_by' => $this->moderator->id,
            ]);

            // Invalidate all user sessions
            $user->sessions()->delete();

            return true;
        });
    }

    /**
     * Unban a user from the forum.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function unbanUser(User $user): bool
    {
        if (!$user->isBanned()) {
            return true;
        }

        return DB::transaction(function () use ($user) {
            $user->update([
                'banned_at' => null,
                'banned_until' => null,
                'banned_reason' => null,
                'banned_by' => null,
            ]);

            return true;
        });
    }

    /**
     * Close a topic to prevent further replies.
     *
     * @param  \App\Models\ForumPost  $post
     * @return bool
     */
    public function closeTopic(ForumPost $post): bool
    {
        if ($post->is_locked) {
            return true;
        }

        return DB::transaction(function () use ($post) {
            $post->update([
                'is_locked' => true,
                'locked_by' => $this->moderator->id,
                'locked_at' => now(),
                'locked_reason' => $this->reason,
            ]);

            return true;
        });
    }

    /**
     * Reopen a closed topic.
     *
     * @param  \App\Models\ForumPost  $post
     * @return bool
     */
    public function reopenTopic(ForumPost $post): bool
    {
        if (!$post->is_locked) {
            return true;
        }

        return DB::transaction(function () use ($post) {
            $post->update([
                'is_locked' => false,
                'locked_by' => null,
                'locked_at' => null,
                'locked_reason' => null,
            ]);

            return true;
        });
    }

    /**
     * Pin a topic to the top of the forum.
     *
     * @param  \App\Models\ForumPost  $post
     * @return bool
     */
    public function pinTopic(ForumPost $post): bool
    {
        if ($post->is_pinned) {
            return true;
        }

        return DB::transaction(function () use ($post) {
            $post->update([
                'is_pinned' => true,
                'pinned_at' => now(),
                'pinned_by' => $this->moderator->id,
            ]);

            return true;
        });
    }

    /**
     * Unpin a topic.
     *
     * @param  \App\Models\ForumPost  $post
     * @return bool
     */
    public function unpinTopic(ForumPost $post): bool
    {
        if (!$post->is_pinned) {
            return true;
        }

        return DB::transaction(function () use ($post) {
            $post->update([
                'is_pinned' => false,
                'pinned_at' => null,
                'pinned_by' => null,
            ]);

            return true;
        });
    }

    /**
     * Move a topic to a different group.
     *
     * @param  \App\Models\ForumPost  $post
     * @param  \App\Models\ForumGroup  $targetGroup
     * @return bool
     */
    public function moveTopic(ForumPost $post, ForumGroup $targetGroup): bool
    {
        if ($post->group_id === $targetGroup->id) {
            return true;
        }

        $originalGroupId = $post->group_id;

        return DB::transaction(function () use ($post, $targetGroup, $originalGroupId) {
            // Update the post's group
            $post->update([
                'group_id' => $targetGroup->id,
                'moved_from_group_id' => $originalGroupId,
                'moved_by' => $this->moderator->id,
                'moved_at' => now(),
            ]);

            // Update all replies to point to the new group
            $post->replies()->update(['group_id' => $targetGroup->id]);

            return true;
        });
    }

    /**
     * Merge two topics together.
     *
     * @param  \App\Models\ForumPost  $sourcePost
     * @param  \App\Models\ForumPost  $targetPost
     * @return bool
     */
    public function mergeTopics(ForumPost $sourcePost, ForumPost $targetPost): bool
    {
        if ($sourcePost->id === $targetPost->id) {
            throw new \InvalidArgumentException('Cannot merge a topic with itself');
        }

        if ($sourcePost->group_id !== $targetPost->group_id) {
            throw new \InvalidArgumentException('Topics must be in the same group to merge');
        }

        return DB::transaction(function () use ($sourcePost, $targetPost) {
            // Update all replies from source to point to target
            $sourcePost->replies()->update(['forum_post_id' => $targetPost->id]);

            // Update any subscriptions
            $sourcePost->subscribers()->each(function ($subscriber) use ($targetPost) {
                // Only subscribe if not already subscribed
                if (!$targetPost->subscribers()->where('user_id', $subscriber->id)->exists()) {
                    $targetPost->subscribers()->attach($subscriber->id);
                }
            });

            // Soft delete the source post
            $sourcePost->update([
                'merged_into_post_id' => $targetPost->id,
                'merged_by' => $this->moderator->id,
                'merged_at' => now(),
            ]);

            $sourcePost->delete();

            return true;
        });
    }

    /**
     * Split replies from a topic into a new topic.
     *
     * @param  \App\Models\ForumPost  $post
     * @param  array  $replyIds
     * @param  string  $newTitle
     * @return \App\Models\ForumPost
     */
    public function splitReplies(ForumPost $post, array $replyIds, string $newTitle)
    {
        if (empty($replyIds)) {
            throw new \InvalidArgumentException('No reply IDs provided');
        }

        return DB::transaction(function () use ($post, $replyIds, $newTitle) {
            // Create a new post with the same group and original post's author
            $newPost = ForumPost::create([
                'user_id' => $post->user_id,
                'group_id' => $post->group_id,
                'title' => $newTitle,
                'content' => 'This topic was created by splitting from another topic.',
                'is_approved' => $post->is_approved,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Move the selected replies to the new post
            $replies = $post->replies()->whereIn('id', $replyIds)->get();
            
            foreach ($replies as $reply) {
                $reply->update([
                    'forum_post_id' => $newPost->id,
                    'group_id' => $newPost->group_id,
                    'split_from_reply_id' => $reply->id,
                    'split_at' => now(),
                    'split_by' => $this->moderator->id,
                ]);
            }

            // Update the new post's timestamps based on the first reply
            if ($firstReply = $newPost->replies()->orderBy('created_at')->first()) {
                $newPost->update([
                    'created_at' => $firstReply->created_at,
                    'updated_at' => $newPost->replies()->max('created_at'),
                ]);
            }

            return $newPost;
        });
    }
}
