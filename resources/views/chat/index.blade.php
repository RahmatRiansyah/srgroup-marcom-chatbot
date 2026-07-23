<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Marcom AI Assistant - SR Group</title>
    <!-- CDN Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- CDN Marked.js untuk render Markdown -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex font-sans overflow-hidden">

    <!-- 1. Include Component Sidebar Navigasi -->
    @include('components.sidebar')

    <!-- 2. Area Utama Chat Application -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">

        <!-- Top Header Bar -->
        <header class="bg-slate-800 border-b border-slate-700 py-4 px-6 flex justify-between items-center shadow-lg shrink-0">
            <div class="flex items-center space-x-3">
                <div class="bg-indigo-600 text-white p-2 rounded-lg font-bold">AI</div>
                <div>
                    <h1 class="font-bold text-lg text-white">Marcom Strategy Assistant</h1>
                    <p class="text-xs text-slate-400">Monitoring Competitor & Trend Analytics</p>
                </div>
            </div>
        </header>

        <!-- Chat Box / Area Obrolan -->
        <main class="flex-1 overflow-y-auto p-4 md:p-6 space-y-4 max-w-4xl mx-auto w-full" id="chat-box">
            
            <!-- Pesan Selamat Datang dari AI (Tampil jika riwayat masih kosong) -->
            @if(!isset($messages) || $messages->isEmpty())
                <div class="flex items-start space-x-3">
                    <div class="bg-indigo-600 text-white p-2 rounded-full text-xs font-bold shrink-0">AI</div>
                    <div class="bg-slate-800 border border-slate-700 rounded-2xl rounded-tl-none p-4 max-w-2xl text-slate-200 text-sm shadow-sm">
                        Halo! Saya Asisten Strategi Marcom Anda. Ada yang bisa saya bantu terkait analisis tren atau strategi campaign kompetitor hari ini?
                    </div>
                </div>

                <!-- Quick Prompts / Contoh Pertanyaan -->
                <div class="pl-11 flex flex-wrap gap-2" id="quick-prompts">
                    @foreach ([
                        'Produk apa yang lagi naik daun minggu ini?',
                        'Ringkasan aktivitas kompetitor hari ini',
                        'Ada perubahan apa dari kompetitor utama kita?',
                    ] as $prompt)
                        <button
                            type="button"
                            class="quick-prompt-btn text-xs text-slate-300 bg-slate-800 hover:bg-slate-700 border border-slate-700 hover:border-indigo-500 px-3 py-2 rounded-xl transition text-left"
                        >{{ $prompt }}</button>
                    @endforeach
                </div>
            @else
                <!-- Render Riwayat Pesan dari Database -->
                @foreach($messages as $msg)
                    @if($msg->role === 'user' || isset($msg->message))
                        <!-- Bubble Pesan User -->
                        <div class="flex items-start space-x-3 justify-end">
                            <div class="bg-indigo-600 text-white rounded-2xl rounded-tr-none p-4 max-w-2xl text-sm shadow-sm whitespace-pre-line">
                                {{ $msg->message ?? $msg->content }}
                            </div>
                        </div>
                    @endif

                    @if($msg->role === 'assistant' || isset($msg->response))
                        <!-- Bubble Balasan AI -->
                        <div class="flex items-start space-x-3">
                            <div class="bg-indigo-600 text-white p-2 rounded-full text-xs font-bold shrink-0">AI</div>
                            <div class="bg-slate-800 border border-slate-700 rounded-2xl rounded-tl-none p-4 max-w-2xl text-slate-200 text-sm leading-relaxed space-y-2 prose prose-invert max-w-none">
                                {!! \Illuminate\Support\Str::markdown($msg->response ?? $msg->content) !!}
                            </div>
                        </div>
                    @endif
                @endforeach
            @endif

        </main>

        <!-- Footer Input Form -->
        <footer class="bg-slate-800 border-t border-slate-700 p-4 shadow-2xl shrink-0">
            <form id="chat-form" class="max-w-4xl mx-auto flex items-center gap-3">
                <input 
                    type="text" 
                    id="user-message" 
                    name="message"
                    placeholder="Ketik pesan atau pertanyaan strategi marcom..." 
                    required 
                    autocomplete="off"
                    class="flex-1 bg-slate-900 border border-slate-700 text-slate-100 text-sm rounded-xl px-4 py-3 focus:outline-none focus:border-indigo-500 transition"
                >
                <button 
                    type="submit" 
                    id="send-btn"
                    class="bg-indigo-600 hover:bg-indigo-500 text-white font-medium px-5 py-3 rounded-xl transition text-sm flex items-center gap-2 shadow-md hover:shadow-indigo-500/20 shrink-0"
                >
                    <span>Kirim</span>
                </button>
            </form>
        </footer>

    </div>

    <!-- JavaScript AJAX Handler (Diperbaiki) -->
    <script>
        const chatForm = document.getElementById('chat-form');
        const userMessageInput = document.getElementById('user-message');
        const chatBox = document.getElementById('chat-box');
        const sendBtn = document.getElementById('send-btn');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Auto-scroll ke paling bawah saat halaman selesai dimuat
        chatBox.scrollTop = chatBox.scrollHeight;

        // Quick Prompts: klik chip -> isi input & langsung kirim
        document.querySelectorAll('.quick-prompt-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                userMessageInput.value = btn.textContent.trim();
                const quickPrompts = document.getElementById('quick-prompts');
                if (quickPrompts) quickPrompts.remove();
                chatForm.requestSubmit();
            });
        });

        // Listener saat form dikirim
        chatForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const message = userMessageInput.value.trim();
            if (!message) return;

            // 1. Tampilkan Pesan User di UI
            appendMessage('user', message);
            userMessageInput.value = '';

            // 2. Tampilkan Indikator Loading (AI mengetik...)
            const loadingId = appendLoading();
            sendBtn.disabled = true;
            sendBtn.classList.add('opacity-50', 'cursor-not-allowed');

            // Penentuan URL Dynamic Sesi
            const sessionId = "{{ isset($activeSession) && $activeSession ? $activeSession->id : '' }}";
            const url = sessionId ? `/chat/${sessionId}/send` : '/chat/send';

            try {
                // 3. Kirim AJAX POST Request ke Route Chat
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ message: message })
                });

                const data = await response.json();

                // 4. Hapus Indikator Loading & Tampilkan Jawaban AI
                removeLoading(loadingId);
                
                if (data.reply) {
                    appendMessage('ai', data.reply, data.engine);

                    // Jika ini sesi baru, alihkan ke URL sesi spesifik
                    if (!sessionId && data.session_id) {
                        window.location.href = `/chat/${data.session_id}`;
                    }
                } else {
                    appendMessage('ai', 'Gagal menerima balasan dari server.');
                }

            } catch (error) {
                removeLoading(loadingId);
                appendMessage('ai', 'Terjadi kesalahan koneksi. Silakan coba lagi.');
            } finally {
                sendBtn.disabled = false;
                sendBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        });

        function appendMessage(sender, text, engine) {
            const wrapper = document.createElement('div');
            wrapper.className = `flex items-start space-x-3 ${sender === 'user' ? 'justify-end' : ''}`;

            if (sender === 'user') {
                wrapper.innerHTML = `
                    <div class="bg-indigo-600 text-white rounded-2xl rounded-tr-none p-4 max-w-2xl text-sm shadow-sm whitespace-pre-line">
                        ${escapeHtml(text)}
                    </div>
                `;
            } else {
                // Render teks AI menggunakan marked.js
                const parsedText = marked.parse(text);
                const fallbackBadge = engine === 'gemini'
                    ? `<div class="text-[10px] text-amber-400 mt-1.5 flex items-center gap-1">
                         <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                         Dijawab pakai Gemini (mode cadangan, Claude sedang tidak bisa diakses)
                       </div>`
                    : '';
                wrapper.innerHTML = `
                    <div class="bg-indigo-600 text-white p-2 rounded-full text-xs font-bold shrink-0">AI</div>
                    <div>
                        <div class="bg-slate-800 border border-slate-700 rounded-2xl rounded-tl-none p-4 max-w-2xl text-slate-200 text-sm leading-relaxed space-y-2 prose prose-invert max-w-none">
                            ${parsedText}
                        </div>
                        ${fallbackBadge}
                    </div>
                `;
            }

            chatBox.appendChild(wrapper);
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        function appendLoading() {
            const loadingId = 'loading-' + Date.now();
            const wrapper = document.createElement('div');
            wrapper.id = loadingId;
            wrapper.className = 'flex items-start space-x-3';
            wrapper.innerHTML = `
                <div class="bg-indigo-600 text-white p-2 rounded-full text-xs font-bold shrink-0">AI</div>
                <div class="bg-slate-800 border border-slate-700 rounded-2xl rounded-tl-none p-4 text-slate-400 text-sm animate-pulse">
                    Sedang merumuskan jawaban strategi...
                </div>
            `;
            chatBox.appendChild(wrapper);
            chatBox.scrollTop = chatBox.scrollHeight;
            return loadingId;
        }

        function removeLoading(id) {
            const el = document.getElementById(id);
            if (el) el.remove();
        }

        function escapeHtml(text) {
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    </script>
</body>
</html>