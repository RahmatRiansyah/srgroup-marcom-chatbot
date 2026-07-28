<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Channel notifikasi kustom lewat Telegram Bot API, dipakai berdampingan
 * dengan channel 'mail' bawaan Laravel -- sengaja tidak pakai package
 * laravel-notification-channels/telegram supaya tidak nambah dependency
 * composer cuma untuk 1 pesan simpel (cukup HTTP POST biasa ke
 * https://api.telegram.org/bot<TOKEN>/sendMessage).
 *
 * Notifikasi yang mau dikirim lewat channel ini WAJIB punya method
 * toTelegram(object $notifiable): string di class Notification-nya
 * (lihat App\Notifications\ScrapeFailedRepeatedly).
 *
 * Setup token & chat_id: lihat komentar config/services.php -> 'telegram'.
 */
class TelegramChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        $token  = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');

        if (empty($token) || empty($chatId) || !method_exists($notification, 'toTelegram')) {
            return;
        }

        $text = $notification->toTelegram($notifiable);

        try {
            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id'    => $chatId,
                'text'       => $text,
                'parse_mode' => 'HTML',
            ]);

            if (!$response->successful()) {
                Log::warning('TelegramChannel: gagal kirim notifikasi', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            // Kegagalan kirim notifikasi TIDAK boleh sampai bikin scraping/job
            // gagal -- ini cuma channel notifikasi tambahan, bukan fitur inti.
            Log::warning('TelegramChannel: exception saat kirim notifikasi', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}
