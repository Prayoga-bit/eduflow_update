<?php

namespace App\Notifications\Forum;

use App\Models\ForumReply;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class NewReplyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The reply instance.
     *
     * @var \App\Models\ForumReply
     */
    public $reply;

    /**
     * Create a new notification instance.
     *
     * @param  \App\Models\ForumReply  $reply
     * @return void
     */
    public function __construct(ForumReply $reply)
    {
        $this->reply = $reply;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $channels = ['database'];
        
        // Check if the user has email notifications enabled
        if ($notifiable->email_notifications) {
            $channels[] = 'mail';
        }
        
        return $channels;
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
        $replyAuthor = $this->reply->user;
        
        $message = (new MailMessage)
            ->subject("New reply to: {$post->title}")
            ->line("**{$replyAuthor->name}** has replied to the post **{$post->title}** in the **{$post->group->name}** group.")
            ->line(new HtmlString(nl2br(e($this->reply->content))))
            ->action('View Reply', route('forum.posts.show', [
                'group' => $post->group->slug,
                'post' => $post->id,
                'slug' => $post->slug,
                '#reply-' . $this->reply->id
            ]));
            
        // Add a line for parent reply if this is a nested reply
        if ($this->reply->parent_id) {
            $message->line('This is a reply to your comment.');
        }
        
        // Add unsubscribe link
        $message->line('')
            ->line('---')
            ->line('You are receiving this email because you are subscribed to this thread.')
            ->action('Unsubscribe from this thread', route('forum.posts.unsubscribe', [
                'post' => $post->id,
                'token' => $post->getUnsubscribeToken($notifiable)
            ]));
            
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
            'reply_id' => $this->reply->id,
            'post_id' => $this->reply->post_id,
            'post_title' => $this->reply->post->title,
            'group_id' => $this->reply->post->group_id,
            'group_name' => $this->reply->post->group->name,
            'author_id' => $this->reply->user_id,
            'author_name' => $this->reply->user->name,
            'is_reply_to_user' => $this->reply->post->user_id === $notifiable->id,
            'is_reply_to_comment' => $this->isReplyToUsersComment($notifiable),
            'excerpt' => str_limit(strip_tags($this->reply->content), 200),
        ];
    }
    
    /**
     * Check if this reply is in response to a comment by the notifiable user.
     *
     * @param  mixed  $notifiable
     * @return bool
     */
    protected function isReplyToUsersComment($notifiable): bool
    {
        if (!$this->reply->parent_id) {
            return false;
        }
        
        $parentReply = $this->reply->parent;
        return $parentReply && $parentReply->user_id === $notifiable->id;
    }
    
    /**
     * Get the notification's database type.
     *
     * @return string
     */
    public function databaseType(): string
    {
        return 'forum.new_reply';
    }
}
