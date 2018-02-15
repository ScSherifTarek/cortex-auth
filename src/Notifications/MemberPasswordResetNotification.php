<?php

declare(strict_types=1);

namespace Cortex\Fort\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class MemberPasswordResetNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The password reset token.
     *
     * @var string
     */
    public $token;

    /**
     * The password reset expiration date.
     *
     * @var int
     */
    public $expiration;

    /**
     * Create a notification instance.
     *
     * @param string $token
     * @param string $expiration
     */
    public function __construct($token, $expiration)
    {
        $this->token = $token;
        $this->expiration = $expiration;
    }

    /**
     * Get the notification's channels.
     *
     * @param mixed $notifiable
     *
     * @return array|string
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $email = $notifiable->getEmailForPasswordReset();
        $link = route('frontarea.passwordreset.reset')."?email={$email}&expiration={$this->expiration}&token={$this->token}";

        return (new MailMessage())
            ->subject(trans('cortex/fort::emails.passwordreset.request.subject'))
            ->line(trans('cortex/fort::emails.passwordreset.request.intro', ['expire' => now()->createFromTimestamp($this->expiration)->diffForHumans()]))
            ->action(trans('cortex/fort::emails.passwordreset.request.action'), $link)
            ->line(trans('cortex/fort::emails.passwordreset.request.outro'));
    }
}
