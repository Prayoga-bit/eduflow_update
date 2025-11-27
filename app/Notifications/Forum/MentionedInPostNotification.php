<?php

namespace App\Notifications\Forum;

use App\Models\ForumPost;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class MentionedInPostNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The post where the mention occurred.
     *
     * @var \App\Models\ForumPost
     */
    public $post;

    /**
     * The user who mentioned the recipient.
     *
     * @var \App\Models\User
     */
    public $mentioner;

    /**
     * The content where the mention occurred.
     *
     * @var string
     */
    public $content;

    /**
     * The type of mention (post or reply).
     *
     * @var string
     */
    public $type;

    /**
     * Create a new notification instance.
     *
     * @param  \App\Models\ForumPost  $post
     * @param  \App\Models\User  $mentioner
     * @param  string  $content
     * @param  string  $type  Either 'post' or 'reply'
     * @return void
     */
    public function __construct(ForumPost $post, User $mentioner, string $content, string $type = 'post')
    {
        $this->post = $post;
        $this->mentioner = $mentioner;
        $this->content = $content;
        $this->type = $type;
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
        
        // Check if the user has email notifications enabled for mentions
        if ($notifiable->email_notifications && $notifiable->email_mentions) {
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
        $subject = "You were mentioned in a {$this->type}";
        $introLine = "**{$this->mentioner->name}** mentioned you in a {$this->type} in the **{$this->post->group->name}** group.";
        
        $message = (new MailMessage)
            ->subject($subject)
            ->line($introLine)
            ->line(new HtmlString(nl2br(e($this->content))))
            ->action('View ' . ucfirst($this->type), $this->getUrl());
            
        // Add unsubscribe link
        $message->line('')
            ->line('---')
            ->line('You are receiving this email because you were mentioned in this ' . $this->type . '.')
            ->line('You can manage your notification preferences in your account settings.');
            
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
            'post_id' => $this->post->id,
            'post_title' => $this->post->title,
            'group_id' => $this->post->group_id,
            'group_name' => $this->post->group->name,
            'mentioner_id' => $this->mentioner->id,
            'mentioner_name' => $this->mentioner->name,
            'type' => $this->type,
            'excerpt' => str_limit(strip_tags($this->content), 200),
            'url' => $this->getUrl(),
        ];
    }
    
    /**
     * Get the URL for the mentioned post or reply.
     *
     * @return string
     */
    protected function getUrl(): string
    {
        $url = route('forum.posts.show', [
            'group' => $this->post->group->slug,
            'post' => $this->post->id,
            'slug' => $this->post->slug,
        ]);
        
        if ($this->type === 'reply') {
            $url .= '#reply-' . $this->content->id;
        }
        
        return $url;
    }
    
    /**
     * Get the notification's database type.
     *
     * @return string
     */
    public function databaseType(): string
    {
        return 'forum.mentioned';
    }
}
