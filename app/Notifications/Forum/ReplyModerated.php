<?php

namespace App\Notifications\Forum;

use App\Models\ForumReply;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class ReplyModerated extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The reply that was moderated.
     *
     * @var \App\Models\ForumReply
     */
    public $reply;

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
     * @param  \App\Models\ForumReply  $reply
     * @param  string  $action
     * @param  string|null  $reason
     * @param  \App\Models\User  $moderator
     * @return void
     */
    public function __construct(ForumReply $reply, string $action, ?string $reason, User $moderator)
    {
        $this->reply = $reply->withoutRelations(); // Prevent serializing the entire model
        $this->action = $action;
        $this->reason = $reason;
        $this->moderator = $moderator->withoutRelations();
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
        $post = $this->reply->post;
        $actionText = ucfirst($this->action);
        $subject = "Your reply has been {$this->action}";
        
        $message = (new MailMessage)
            ->subject($subject)
            ->line("Your reply in **{$post->title}** in **{$post->group->name}** has been {$this->action} by a moderator.");

        if ($this->reason) {
            $message->line('')
                ->line('**Reason:**')
                ->line(new HtmlString(nl2br(e($this->reason))));
        }

        if ($this->action !== 'deleted') {
            $message->action('View Reply', $this->getReplyUrl());
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
        $post = $this->reply->post;
        
        return [
            'type' => 'reply_moderated',
            'reply_id' => $this->reply->id,
            'post_id' => $post->id,
            'post_title' => $post->title,
            'group_id' => $post->group_id,
            'group_name' => $post->group->name,
            'action' => $this->action,
            'reason' => $this->reason,
            'moderator_id' => $this->moderator->id,
            'moderator_name' => $this->moderator->name,
            'url' => $this->action !== 'deleted' ? $this->getReplyUrl() : null,
            'timestamp' => now()->toDateTimeString(),
        ];
    }

    /**
     * Get the URL to the reply.
     *
     * @return string
     */
    protected function getReplyUrl(): string
    {
        $post = $this->reply->post;
        
        return route('forum.posts.show', [
            'group' => $post->group->slug,
            'post' => $post->id,
            'slug' => $post->slug,
        ]) . '#reply-' . $this->reply->id;
    }

    /**
     * Get the notification's database type.
     *
     * @return string
     */
    public function databaseType(): string
    {
        return 'forum.reply_moderated';
    }

    /**
     * Prepare the notification for serialization.
     *
     * @return array
     */
    public function __serialize(): array
    {
        return [
            'reply' => $this->reply->id,
            'action' => $this->action,
            'reason' => $this->reason,
            'moderator' => $this->moderator->id,
        ];
    }

    /**
     * Prepare the notification after deserialization.
     *
     * @param  array  $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->reply = ForumReply::findOrFail($data['reply']);
        $this->action = $data['action'];
        $this->reason = $data['reason'];
        $this->moderator = User::findOrFail($data['moderator']);
    }
}