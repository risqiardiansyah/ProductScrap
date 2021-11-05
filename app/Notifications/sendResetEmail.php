<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Lang;

class sendResetEmail extends ResetPassword
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public $token;
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->token);
        }

        $resetPasswordUrl = env('FRONT_URL') . 'reset_password/' . $this->token . '/' . $notifiable->email;
        return (new MailMessage)
            ->greeting(Lang::get('mails.greeting_reset', ['player' => $notifiable->name]))
            ->subject(Lang::get('mails.subject_reset'))
            ->line(Lang::get('mails.line1_reset'))
            ->action(Lang::get('mails.button_text_reset'), $resetPasswordUrl)
            ->line(Lang::get('mails.line2_reset', ['count' => config('auth.passwords.' . config('auth.defaults.passwords') . '.expire'), 'minutes' => 'minutes']));
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
            //
        ];
    }
}
