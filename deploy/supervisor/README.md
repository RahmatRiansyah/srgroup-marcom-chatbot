# Supervisor configs — srgroup-marcom-chatbot

Dua proses background yang perlu selalu hidup di server produksi:

| File | Menjalankan | Kenapa |
|---|---|---|
| `srgroup-marcom-queue-worker.conf` | `php artisan queue:work` | Memproses `RunScrapeJob` & `RunMetaSyncJob` (tombol "Jalankan Sekarang" / "Sync Sekarang" di admin) |
| `srgroup-marcom-scheduler.conf` | `php artisan schedule:work` | Menjalankan jadwal di `routes/console.php` (`scrape:run` tiap 06:00, `meta:sync` tiap 30 menit) tanpa perlu crontab asli |

## Instalasi (Ubuntu/Debian)

```bash
sudo apt install supervisor          # kalau belum ada

# 1. Edit dulu `directory` & `user` di kedua file .conf supaya sesuai server kamu
# 2. Copy ke folder config supervisor
sudo cp deploy/supervisor/*.conf /etc/supervisor/conf.d/

# 3. Muat & jalankan
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl status
```

Kalau statusnya `RUNNING` untuk keduanya, berarti beres — queue worker & scheduler akan otomatis nyala lagi sendiri kalau proses crash atau server reboot (asal service `supervisor`-nya di-enable saat boot, biasanya sudah default lewat systemd).

## Setelah deploy kode baru

Queue worker **tidak otomatis pakai kode terbaru** sampai proses-nya direstart (PHP sudah "load" kode versi lama ke memory). Tambahkan baris ini ke akhir script deploy kamu:

```bash
php artisan queue:restart
```

(Aman dipanggil kapan saja — job yang sedang berjalan akan diselesaikan dulu, baru workernya berhenti & di-restart otomatis oleh Supervisor.)

`schedule:work` (scheduler) punya masalah yang sama — dia juga proses panjang yang "pegang" kode lama di memory. Kalau kamu mengubah jadwal di `routes/console.php` (nambah command baru, ubah jam/frekuensi, dst), `queue:restart` di atas **tidak** ikut merestart proses ini. Perlu dipanggil terpisah:

```bash
sudo supervisorctl restart srgroup-marcom-scheduler
```

Tambahkan juga baris ini ke script deploy kalau perubahan yang di-deploy menyentuh `routes/console.php`.

## Cek log kalau ada masalah

```bash
tail -f storage/logs/queue-worker.log
tail -f storage/logs/scheduler.log
sudo supervisorctl tail -f srgroup-marcom-queue-worker
```

## Catatan

- Kalau server ini juga punya crontab asli yang manggil `php artisan schedule:run`, **hapus** — jalan bersamaan dengan `schedule:work` bisa bikin `scrape:run`/`meta:sync` kepanggil 2x.
- Mesin analisis Python (FastAPI, folder `srgroup-marcom-analytics-service`) berjalan terpisah (biasanya lewat `uvicorn`) dan **tidak** dicakup oleh config ini — kalau service itu juga perlu auto-restart di server yang sama, bikin `.conf` serupa yang menjalankan perintah `uvicorn`-nya.
