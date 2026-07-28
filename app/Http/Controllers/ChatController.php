<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Services\ClaudeService;
use App\Services\GroqService;
use App\Services\GeminiService;
use App\Services\EngineStatusService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    /**
     * Tampilkan antarmuka chat sesuai sesi aktif + daftar riwayat di sidebar.
     */
    public function index(Request $request, $sessionId = null)
    {
        $userId = Auth::id();

        // 1. Ambil daftar semua sesi percakapan milik user (dengan pencarian jika ada)
        $search = $request->query('search');
        $sessions = ChatSession::where('user_id', $userId)
            ->when($search, function ($query, $search) {
                return $query->where('title', 'like', "%{$search}%");
            })
            ->latest()
            ->get();

        // 2. Tentukan Sesi Chat Aktif
        $activeSession = null;
        if ($sessionId) {
            $activeSession = ChatSession::where('user_id', $userId)->find($sessionId);
        }

        // Jika tidak ada ID di URL, ambil sesi paling terakhir
        if (!$activeSession && !$search) {
            $activeSession = $sessions->first();
        }

        // 3. Ambil pesan-pesan dari sesi aktif tersebut
        $messages = $activeSession ? $activeSession->messages()->orderBy('created_at', 'asc')->get() : collect();

        return view('chat.index', compact('sessions', 'activeSession', 'messages', 'search'));
    }

    /**
     * Buat Sesi Percakapan Baru.
     */
    public function newSession()
    {
        $session = ChatSession::create([
            'user_id' => Auth::id(),
            'title'   => 'Percakapan Baru'
        ]);

        return redirect()->route('chat.show', $session->id);
    }

    /**
     * Kirim pesan user ke Claude dalam sesi aktif.
     *
     * Berbeda dari versi sebelumnya: di sini kita TIDAK menempel data
     * tren/kompetitor secara manual ke prompt. Claude yang memutuskan lewat
     * tool use (getTrend / getCompetitorPrice / getSummary) kapan perlu
     * mengambil data real-time — dan data itu selalu lewat AnalyticsApiService,
     * yang di baliknya memanggil mesin analisis Python. Ini yang dimaksud
     * "Integrasi LLM + Function Calling" di roadmap Minggu 5.
     */
    public function send(
        Request $request,
        ClaudeService $claude,
        GroqService $groq,
        GeminiService $gemini,
        EngineStatusService $engineStatus,
        $sessionId = null
    ) {
        $request->validate([
            'message' => 'required|string',
            // 'auto' (default) = rantai fallback otomatis Claude -> Groq -> Gemini.
            // Selain itu = user pilih sendiri lewat model-selector di UI.
            'engine'  => 'nullable|string|in:auto,claude,groq,gemini',
        ]);

        $userMessage = $request->input('message');
        $selectedEngine = $request->input('engine', 'auto');
        $userId = Auth::id();

        // 1. Pastikan Sesi Ada (Jika belum ada, buat otomatis)
        if ($sessionId) {
            $session = ChatSession::where('user_id', $userId)->findOrFail($sessionId);
        } else {
            $session = ChatSession::create([
                'user_id' => $userId,
                'title'   => Str::limit($userMessage, 30) // Set judul otomatis dari pesan pertama
            ]);
        }

        // Jika judul sesi masih default, ubah dengan potongan pesan pertama user
        if ($session->title === 'Percakapan Baru') {
            $session->update([
                'title' => Str::limit($userMessage, 30)
            ]);
        }

        // 2. Ambil 5 riwayat percakapan terakhir HANYA DARI SESI INI (Multi-turn),
        //    dikonversi ke format messages Claude (role: user/assistant).
        $chatHistory = $session->messages()->latest()->take(5)->get()->reverse();

        $history = [];
        foreach ($chatHistory as $turn) {
            $history[] = ['role' => 'user', 'content' => $turn->message];
            $history[] = ['role' => 'assistant', 'content' => $turn->response];
        }

        $engines = [
            'claude' => $claude,
            'groq'   => $groq,
            'gemini' => $gemini,
        ];

        if ($selectedEngine !== 'auto') {
            // 3a. User pilih model tertentu lewat model-selector di UI (bukan
            //     "Otomatis"). Hormati pilihannya: TIDAK diam-diam dialihkan ke
            //     engine lain kalau gagal, supaya badge "dijawab pakai X" di
            //     frontend selalu jujur sesuai yang dipilih user.
            if ($engineStatus->isLimited($selectedEngine)) {
                return response()->json([
                    'reply'          => 'Model ini sedang mencapai batas penggunaan (limit). Silakan pilih model lain atau gunakan mode Otomatis.',
                    'session_id'     => $session->id,
                    'title'          => $session->title,
                    'tool_calls'     => [],
                    'engine'         => $selectedEngine,
                    'engine_limited' => true,
                ]);
            }

            $engine = $selectedEngine;
            $result = $engines[$selectedEngine]->chat($history, $userMessage);

            if ($result['error'] ?? false) {
                Log::warning("ChatController: model pilihan user ({$selectedEngine}) gagal", [
                    'session_id' => $session->id,
                    'error'      => $result['reply'],
                ]);

                $result['reply'] = "Maaf, model yang dipilih ({$selectedEngine}) sedang tidak bisa menjawab. Coba model lain atau gunakan mode Otomatis.";
            }
        } else {
            // 3b. Mode Otomatis (default): rantai fallback berantai
            //     Claude -> Groq -> Gemini. Groq ditaruh sebelum Gemini karena kuota
            //     gratisnya jauh lebih lega (puluhan request/menit, ribuan/hari)
            //     dibanding kuota gratis Gemini yang cuma ~20 request/hari.
            //     Engine yang SUDAH DIKETAHUI sedang limit (lihat EngineStatusService)
            //     langsung dilewati tanpa dipanggil, supaya user tidak perlu
            //     menunggu request yang sudah pasti gagal.
            $chain  = ['claude', 'groq', 'gemini'];
            $engine = null;
            $result = ['error' => true, 'reply' => 'Semua model sedang tidak bisa diakses.', 'tool_calls' => []];

            foreach ($chain as $candidate) {
                if ($engineStatus->isLimited($candidate)) {
                    Log::info("ChatController: {$candidate} lagi limit, dilewati di mode Otomatis", [
                        'session_id' => $session->id,
                    ]);
                    continue;
                }

                $engine = $candidate;
                $result = $engines[$candidate]->chat($history, $userMessage);

                if (!($result['error'] ?? false)) {
                    break;
                }

                Log::warning("ChatController: {$candidate} gagal, coba engine berikutnya", [
                    'session_id' => $session->id,
                    'error'      => $result['reply'],
                ]);
            }

            // Kalau ketiga engine (Claude, Groq, Gemini) sama-sama gagal/limit,
            // kasih pesan yang jujur ke user daripada nampilin error mentah dari service.
            if ($result['error'] ?? false) {
                $result['reply'] = 'Maaf, chatbot sedang tidak bisa menjawab (Claude, Groq, & Gemini sama-sama gagal dihubungi atau lagi limit). Coba lagi beberapa saat lagi.';
            }
        }

        // 4. Simpan pesan (hanya jawaban akhir yang disimpan; detail tool_calls
        //    dikirim ke frontend untuk transparansi, tidak disimpan ke DB).
        //    'engine' DISIMPAN (item 9: tracking proporsi pemakaian Claude vs
        //    Groq vs Gemini) -- null kalau semua engine gagal/limit, supaya
        //    gampang di-query ("berapa % pesan yang gagal total") daripada
        //    dicampur sebagai string 'none'.
        ChatMessage::create([
            'user_id'         => $userId,
            'chat_session_id' => $session->id,
            'message'         => $userMessage,
            'response'        => $result['reply'],
            'engine'          => $engine,
        ]);

        return response()->json([
            'reply'      => $result['reply'],
            'session_id' => $session->id,
            'title'      => $session->title,
            'tool_calls' => $result['tool_calls'] ?? [],
            'engine'     => $engine ?? 'none', // engine yang benar-benar menjawab ('none' kalau semuanya gagal/limit)
        ]);
    }

    /**
     * Status limit tiap engine (Claude/Groq/Gemini), dipakai model-selector
     * di UI chat untuk mencoret & menonaktifkan sementara opsi yang sudah
     * kena limit. Lihat EngineStatusService untuk detail bagaimana status
     * ini ditandai (dipanggil dari dalam masing-masing *Service saat API
     * mengembalikan rate limit / kuota habis).
     */
    public function engineStatus(EngineStatusService $engineStatus)
    {
        return response()->json($engineStatus->status());
    }

    /**
     * Hapus Sesi Percakapan.
     */
    public function destroy($id)
    {
        $session = ChatSession::where('user_id', Auth::id())->findOrFail($id);
        $session->delete();

        return redirect()->route('chat.index');
    }
}