<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Roadmap Minggu 2: "Setup scheduler (cron sederhana dulu) untuk narik data harian".
// Catatan: ini cuma jadwal di sisi Laravel. Supaya benar-benar jalan otomatis,
// server (VPS/Railway/Render) tetap butuh SATU baris cron sungguhan yang
// menjalankan Laravel scheduler tiap menit:
//   * * * * * cd /path-ke-project && php artisan schedule:run >> /dev/null 2>&1
Schedule::command('scrape:run')->dailyAt('06:00');

// Sync engagement Meta (akun sendiri) jauh lebih sering dari scraping
// kompetitor -- ini yang bikin dashboard/chatbot terasa "real-time".
// Meta sendiri tidak punya push live untuk like/komentar/reach, jadi
// polling tiap 30 menit ini praktiknya paling "real-time" yang wajar
// tanpa boros kuota rate limit Graph API. Ubah intervalnya kalau perlu
// lebih cepat/lambat (mis. ->everyFifteenMinutes()).
Schedule::command('meta:sync')->everyThirtyMinutes();