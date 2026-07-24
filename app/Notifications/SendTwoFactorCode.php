<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendTwoFactorCode extends Notification
{
    use Queueable;

    public string $code;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $code)
    {
        $this->code = $code;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Kode Verifikasi 2FA (OTP) - SR GROUP Marcom')
            ->greeting('Halo, ' . $notifiable->name . '!')
            ->line('Kode verifikasi 2-Langkah (OTP) Anda untuk masuk ke sistem SR GROUP Marcom adalah:')
            ->line('**' . $this->code . '**')
            ->line('Kode ini berlaku selama 10 menit. Jangan berikan kode ini kepada siapapun.')
            ->line('Jika Anda tidak mencoba login, abaikan pesan ini.');
    }
}
