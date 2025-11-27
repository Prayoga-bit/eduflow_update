<?php

namespace App\Traits;

use App\Models\User;
use App\Notifications\Forum\MentionedInPostNotification;
use Illuminate\Support\Str;

/**
 * Trait for handling user mentions in content.
 */
trait HandlesMentions
{
    /**
     * The regular expression pattern for matching mentions.
     *
     * @var string
     */
    protected $mentionPattern = '/@([\w\-\.]+(?:\s+[\w\-\.]+)*)/';

    /**
     * Parse the content for mentions and notify mentioned users.
     *
     * @param  string  $content
     * @param  \App\Models\ForumPost  $post
     * @param  string  $type  Either 'post' or 'reply'
     * @return string
     */
    protected function parseMentions(string $content, $post, string $type = 'post'): string
    {
        $mentions = $this->extractMentions($content);
        
        if (empty($mentions)) {
            return $content;
        }

        $users = User::whereIn('username', $mentions)
            ->where('id', '!=', auth()->id()) // Don't notify the author
            ->get();

        // Notify each mentioned user
        foreach ($users as $user) {
            // Check if the user has access to the post's group
            if ($user->can('view', $post)) {
                $user->notify(new MentionedInPostNotification(
                    $post,
                    auth()->user(),
                    $content,
                    $type
                ));
            }
        }

        // Replace usernames with links to user profiles
        foreach ($mentions as $username) {
            $content = preg_replace(
                '/@' . preg_quote($username, '/') . '\b/',
                "<a href=\"" . route('users.show', $username) . "\" class=\"mention\">@$username</a>",
                $content
            );
        }

        return $content;
    }

    /**
     * Extract usernames from content that are mentioned with @.
     *
     * @param  string  $content
     * @return array
     */
    protected function extractMentions(string $content): array
    {
        if (!preg_match_all($this->mentionPattern, $content, $matches)) {
            return [];
        }

        // Get unique usernames without the @ symbol
        $mentions = array_unique($matches[1]);
        
        // Filter out any empty or invalid usernames
        return array_filter($mentions, function ($username) {
            return !empty(trim($username)) && 
                   strlen($username) <= 25 && 
                   preg_match('/^[a-zA-Z0-9\-\._]+$/', $username);
        });
    }

    /**
     * Get a list of users mentioned in the content.
     *
     * @param  string  $content
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getMentionedUsers(string $content)
    {
        $mentions = $this->extractMentions($content);
        
        if (empty($mentions)) {
            return collect();
        }

        return User::whereIn('username', $mentions)->get();
    }

    /**
     * Check if a specific user is mentioned in the content.
     *
     * @param  string  $content
     * @param  \App\Models\User  $user
     * @return bool
     */
    protected function isUserMentioned(string $content, User $user): bool
    {
        $mentions = $this->extractMentions($content);
        return in_array($user->username, $mentions);
    }

    /**
     * Remove mentions from the content.
     *
     * @param  string  $content
     * @return string
     */
    protected function removeMentions(string $content): string
    {
        return preg_replace($this->mentionPattern, '', $content);
    }

    /**
     * Get a preview of the content with mentions highlighted.
     *
     * @param  string  $content
     * @param  int  $length
     * @return string
     */
    protected function getMentionPreview(string $content, int $length = 100): string
    {
        $preview = Str::limit(strip_tags($content), $length);
        
        // Highlight mentions in the preview
        $preview = preg_replace(
            $this->mentionPattern,
            '<span class="mention-preview">$0</span>',
            $preview
        );
        
        return $preview;
    }
}
