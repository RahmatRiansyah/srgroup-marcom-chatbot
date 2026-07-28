<?php

namespace App\Notifications;

use App\Models\ScrapeLog;
use App\Notifications\Channels\TelegramChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

/**
 * Dikirim ke semua admin ketika scraping GAGAL TOTAL (status 'failed')
 * sebanyak $consecutiveFailures kali BERTURUT-TURUT (lihat
 * ScrapeRunnerService::maybeNotifyConsecutiveFailures()).
 *
 * Sebelum ini, satu-satunya cara tahu scraping gagal berhari-hari adalah
 * buka halaman Log Scraping di admin panel secara manual.
 *
 * WAJIB implements ShouldQueue: notifikasi ini dikirim dari dalam
 * ScrapeRunnerService::run(), yang juga dipanggil dari scheduler
 * (php artisan schedule:work, proses tunggal yang jalan terus -- lihat
 * deploy/supervisor). Kalau notifikasi dikirim SINKRON dan SMTP/Telegram
 * kebetulan lambat/hang, seluruh scheduler ikut nyangkut dan jadwal LAIN
 * (mis. meta:sync tiap 30 menit) ikut berhenti jalan. Dengan ShouldQueue,
 * Notification::send() cuma insert baris ke tabel `jobs` (instan) --
 * pengiriman sebenarnya diproses terpisah oleh queue worker.
 */
class ScrapeFailedRepeatedly extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        protected ScrapeLog $log,
        protected int $consecutiveFailures,
    ) {
    }

    /**
     * 'telegram' otomatis di-skip oleh TelegramChannel sendiri kalau
     * TELEGRAM_BOT_TOKEN / TELEGRAM_ADMIN_CHAT_ID belum diisi di .env --
     * jadi aman dicantumkan terus di sini tanpa perlu dicek dulu di sini.
     */
    public function via(object $notifiable): array
    {
        return ['mail', TelegramChannel::class];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("⚠️ Scraping Gagal {$this->consecutiveFailures}x Berturut-turut - SR GROUP Marcom")
            ->greeting('Halo, ' . $notifiable->name . '!')
            ->line("Scraping data tren/kompetitor sudah GAGAL {$this->consecutiveFailures} kali berturut-turut.")
            ->line('Pesan kegagalan terakhir:')
            ->line($this->log->message ?? '(tidak ada pesan detail)')
            ->line('Waktu kegagalan terakhir: ' . $this->log->created_at->translatedFormat('d F Y, H:i') . ' WIB')
            ->line('Penyebab paling umum: mesin analisis Python (FastAPI) down, atau target scraping berubah struktur halamannya.')
            ->action('Buka Log Scraping', route('admin.scrapelog.index'))
            ->line('Notifikasi ini tidak akan dikirim ulang dalam 12 jam ke depan meski scraping masih terus gagal, supaya tidak membanjiri inbox.');
    }

    public function toTelegram(object $notifiable): string
    {
        $message = $this->log->message ?? '(tidak ada pesan detail)';
        $time    = $this->log->created_at->translatedFormat('d F Y, H:i');

        return "⚠️ <b>Scraping Gagal {$this->consecutiveFailures}x Berturut-turut</b>\n"
            . "SR GROUP Marcom\n\n"
            . "Pesan terakhir: {$message}\n"
            . "Waktu: {$time} WIB\n\n"
            . "Cek: " . route('admin.scrapelog.index');
    }
}
