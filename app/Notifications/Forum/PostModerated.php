<?php

namespace App\Notifications\Forum;

use App\Models\ForumPost;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class PostModerated extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The post that was moderated.
     *
     * @var \App\Models\ForumPost
     */
    public $post;

    /**
     * The action that was taken (approved, rejected, deleted, etc.).
     *
     * @var string
     */
    public $action;

    /**
     * The reason for the moderation action.
     *
     * @var string|null
     */
    public $reason;

    /**
     * The moderator who performed the action.
     *
     * @var \App\Models\User
     */
    public $moderator;

    /**
     * Create a new notification instance.
     *
     * @param  \App\Models\ForumPost  $post
     * @param  string  $action
     * @param  string|null  $reason
     * @param  \App\Models\User  $moderator
     * @return void
     */
    public function __construct(ForumPost $post, string $action, ?string $reason, User $moderator)
    {
        $this->post = $post;
        $this->action = $action;
        $this->reason = $reason;
        $this->moderator = $moderator;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $actionText = ucfirst($this->action);
        $subject = "Your post has been {$this->action}";
        
        $message = (new MailMessage)
            ->subject($subject)
            ->line("Your post **{$this->post->title}** in **{$this->post->group->name}** has been {$this->action} by a moderator.");

        if ($this->reason) {
            $message->line('')
                ->line('**Reason:**')
                ->line(new HtmlString(nl2br(e($this->reason))));
        }

        if ($this->action !== 'deleted') {
            $message->action('View Post', $this->getPostUrl());
        }

        $message->line('')
            ->line('If you believe this was done in error, please contact the forum administrators.')
            ->line('')
            ->line('Thank you for your understanding.');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'type' => 'post_moderated',
            'post_id' => $this->post->id,
            'post_title' => $this->post->title,
            'group_id' => $this->post->group_id,
            'group_name' => $this->post->group->name,
            'action' => $this->action,
            'reason' => $this->reason,
            'moderator_id' => $this->moderator->id,
            'moderator_name' => $this->moderator->name,
            'url' => $this->action !== 'deleted' ? $this->getPostUrl() : null,
            'timestamp' => now(),
        ];
    }

    /**
     * Get the URL to the post.
     *
     * @return string
     */
    protected function getPostUrl(): string
    {
        return route('forum.posts.show', [
            'group' => $this->post->group->slug,
            'post' => $this->post->id,
            'slug' => $this->post->slug,
        ]);
    }

    /**
     * Get the notification's database type.
     *
     * @return string
     */
    public function databaseType(): string
    {
        return 'forum.post_moderated';
    }
}
