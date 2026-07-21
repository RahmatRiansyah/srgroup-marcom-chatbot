<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TrendSource;
use App\Models\TrendPost;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
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
     * Kirim pesan user ke AI Gemini dalam sesi aktif.
     */
    public function send(Request $request, $sessionId = null)
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

        // 2. Ambil data target & kompetitor dari database
        $sources = TrendSource::all();
        $latestPosts = TrendPost::with('trendSource')->latest()->take(5)->get();

        $contextText = "Daftar Target & Kompetitor Marcom Aktif saat ini:\n";
        foreach ($sources as $source) {
            $contextText .= "- {$source->name} (Platform: {$source->platform})\n";
        }

        $contextText .= "\nKonten Postingan & Tren Terbaru Kompetitor:\n";
        foreach ($latestPosts as $post) {
            $targetName = $post->trendSource->name ?? 'Kompetitor';
            $contextText .= "- [Target: {$targetName}] Judul: {$post->title} | Konten: \"{$post->content}\"\n";
        }

        $systemInstruction = "Kamu adalah Asisten Strategi Marcom.\n\n" . $contextText;

        // 3. Ambil 5 riwayat percakapan terakhir HANYA DARI SESI INI (Multi-turn)
        $chatHistory = $session->messages()->latest()->take(5)->get()->reverse();

        $contents = [];
        if ($chatHistory->isNotEmpty()) {
            $isFirstTurn = true;
            foreach ($chatHistory as $history) {
                if ($isFirstTurn) {
                    $contents[] = [
                        'role' => 'user',
                        'parts' => [['text' => "[KONTEKS SISTEM]\n" . $systemInstruction . "\n\n[PESAN USER]\n" . $history->message]]
                    ];
                    $isFirstTurn = false;
                } else {
                    $contents[] = ['role' => 'user', 'parts' => [['text' => $history->message]]];
                }
                $contents[] = ['role' => 'model', 'parts' => [['text' => $history->response]]];
            }
            $contents[] = ['role' => 'user', 'parts' => [['text' => $userMessage]]];
        } else {
            $contents[] = [
                'role' => 'user',
                'parts' => [['text' => $systemInstruction . "\n\nPertanyaan User: " . $userMessage]]
            ];
        }

        // 4. Panggil API Gemini
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            return response()->json(['reply' => 'Error: GEMINI_API_KEY belum dikonfigurasi']);
        }

        $endpoints = [
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent',
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent',
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-lite-latest:generateContent',
        ];
        $lastError = '';
        foreach ($endpoints as $endpoint) {
            try {
                $response = Http::withoutVerifying()->post("{$endpoint}?key={$apiKey}", [
                    'contents' => $contents
                ]);

                if ($response->successful()) {
                    $responseData = $response->json();
                    $aiReply = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? null;

                    if ($aiReply) {
                        // Simpan Pesan dengan chat_session_id
                        ChatMessage::create([
                            'user_id'         => $userId,
                            'chat_session_id' => $session->id,
                            'message'         => $userMessage,
                            'response'        => $aiReply,
                        ]);

                        return response()->json([
                            'reply'      => $aiReply,
                            'session_id' => $session->id,
                            'title'      => $session->title
                        ]);
                    }
                }
                $lastError = $response->body();
            } catch (\Exception $e) {
                $lastError = $e->getMessage();
            }
        }

        return response()->json(['reply' => 'Gagal menghubungkan ke AI Gemini: ' . $lastError]);
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