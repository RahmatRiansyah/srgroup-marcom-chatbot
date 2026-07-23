<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Services\ClaudeService;
use App\Services\GeminiService;
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
    public function send(Request $request, ClaudeService $claude, GeminiService $gemini, $sessionId = null)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $userMessage = $request->input('message');
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

        // 3. Panggil Claude dulu (engine utama). Kalau gagal (token/kredit habis,
        //    rate limit, atau Anthropic sedang down), otomatis fallback ke Gemini
        //    supaya chatbot tetap bisa menjawab.
        $engine = 'claude';
        $result = $claude->chat($history, $userMessage);

        if ($result['error'] ?? false) {
            Log::warning('ChatController: Claude gagal, fallback ke Gemini', [
                'session_id'    => $session->id,
                'claude_error'  => $result['reply'],
            ]);

            $engine = 'gemini';
            $result = $gemini->chat($history, $userMessage);
        }

        // Kalau Gemini (fallback) juga gagal, kasih pesan yang jujur ke user
        // daripada nampilin error mentah dari service.
        if ($result['error'] ?? false) {
            $result['reply'] = 'Maaf, chatbot sedang tidak bisa menjawab (Claude & Gemini sama-sama gagal dihubungi). Coba lagi beberapa saat lagi, atau cek log server untuk detail.';
        }

        // 4. Simpan pesan (hanya jawaban akhir yang disimpan; detail tool_calls/engine
        //    dikirim ke frontend untuk transparansi, tidak disimpan ke DB).
        ChatMessage::create([
            'user_id'         => $userId,
            'chat_session_id' => $session->id,
            'message'         => $userMessage,
            'response'        => $result['reply'],
        ]);

        return response()->json([
            'reply'      => $result['reply'],
            'session_id' => $session->id,
            'title'      => $session->title,
            'tool_calls' => $result['tool_calls'],
            'engine'     => $engine, // 'claude' atau 'gemini' (dipakai fallback)
        ]);
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