<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SR GROUP - Asisten AI Marcom</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/srgroup-logo-white.svg') }}">
    <!-- CDN Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- CDN Marked.js untuk render Markdown -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
</head>
<body class="bg-[#fbf9f8] text-[#1b1c1c] min-h-screen flex font-sans overflow-hidden">

    <!-- 1. Include Component Sidebar Navigasi -->
    @include('components.sidebar')

    <!-- 2. Area Utama Chat Application -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">

        <!-- Top Header Bar -->
        <header class="bg-[#ffffff] border-b border-[#e5e5e1] py-4 px-6 flex justify-between items-center shadow-sm shadow-[#0000000d] shrink-0">
            <div class="flex items-center space-x-3">
                <button type="button" onclick="toggleSidebar()" class="md:hidden shrink-0 text-[#1b1c1c] hover:text-[#1b1c1c] bg-[#f5f3f3] hover:bg-[#efeded] p-2 rounded-lg transition" aria-label="Buka menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="bg-[#885215] text-[#ffffff] p-2 rounded-lg font-bold">SRGROUP</div>
                <div>
                    <h1 class="font-bold text-lg text-[#1b1c1c]">Marcom F&B Assistant</h1>
                    <p class="text-xs text-[#885215]">AI insight untuk PT Sritama Boga Prima dan lima brand F&B</p>
                </div>
            </div>
        </header>

        <!-- Chat Box / Area Obrolan -->
        <main class="flex-1 overflow-y-auto p-4 md:p-6 space-y-4 max-w-4xl mx-auto w-full" id="chat-box">
            
            <!-- Brand Highlight -->
            <div class="bg-[#ffffff] border border-[#e5e5e1] rounded-3xl p-5 shadow-sm shadow-[#0000000d] text-[#1b1c1c]">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.2em] text-[#885215] font-semibold">PT Sritama Boga Prima</p>
                        <h2 class="text-2xl font-bold text-[#1b1c1c] leading-tight max-w-3xl">HOLLIDAY RESTAURANT · HOLLIDAY CATERING · DIM DIM SUM · DIM DIM SUM KITCHEN · THE SURI</h2>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach(['HOLLIDAY','CATERING','DIM DIM SUM','KITCHEN','THE SURI'] as $brand)
                            <span class="text-[11px] uppercase tracking-[0.14em] font-semibold bg-[#f5f3f3] border border-[#e5e5e1] text-[#1b1c1c] px-3 py-1 rounded-full">{{ $brand }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Pesan Selamat Datang dari AI (Tampil jika riwayat masih kosong) -->
            @if(!isset($messages) || $messages->isEmpty())
                <div class="flex items-start space-x-3">
                    <div class="bg-[#885215] text-[#ffffff] p-2 rounded-full text-xs font-bold shrink-0">AI</div>
                    <div class="bg-[#ffffff] border border-[#e5e5e1] rounded-3xl p-5 max-w-2xl text-[#1b1c1c] text-sm shadow-sm shadow-[#0000000d]">
                        Halo Tim Marcom PT Sritama Boga Prima! Saya siap bantu insight F&B untuk HOLLIDAY RESTAURANT, HOLLIDAY CATERING, DIM DIM SUM, DIM DIM SUM KITCHEN, dan THE SURI.
                        <div class="mt-3 text-[#885215] text-xs">Coba tanya tentang tren menu, kompetitor, campaign, atau rencana promosi.</div>
                    </div>
                </div>

                <!-- Quick Prompts / Contoh Pertanyaan -->
                <div class="pl-11 flex flex-wrap gap-2" id="quick-prompts">
                    @foreach ([
                        'Apa tren menu makanan cepat saji terbaru untuk brand saya?',
                        'Strategi promosi terbaik untuk HOLLIDAY CATERING minggu ini',
                        'Bagaimana aktivitas kompetitor DIM DIM SUM?',
                    ] as $prompt)
                        <button
                            type="button"
                            class="quick-prompt-btn text-xs text-[#1b1c1c] bg-[#f5f3f3] hover:bg-[#efeded] border border-[#e5e5e1] hover:border-[#c8c6c5] px-3 py-2 rounded-full transition text-left"
                        >{{ $prompt }}</button>
                    @endforeach
                </div>
            @else
                <!-- Render Riwayat Pesan dari Database -->
                @foreach($messages as $msg)
                    @if($msg->role === 'user' || isset($msg->message))
                        <!-- Bubble Pesan User -->
                        <div class="flex items-start space-x-3 justify-end">
                            <div class="bg-[#1b1c1c] border border-[#1b1c1c] text-[#ffffff] rounded-3xl rounded-tr-none p-4 max-w-2xl text-sm shadow-sm shadow-[#0000000d] whitespace-pre-line">
                                {{ $msg->message ?? $msg->content }}
                            </div>
                        </div>
                    @endif

                    @if($msg->role === 'assistant' || isset($msg->response))
                        <!-- Bubble Balasan AI -->
                        <div class="flex items-start space-x-3">
                            <div class="bg-[#885215] text-[#ffffff] p-2 rounded-full text-xs font-bold shrink-0">AI</div>
                            <div class="bg-[#ffffff] border border-[#e5e5e1] rounded-3xl rounded-tl-none p-4 max-w-2xl text-[#1b1c1c] text-sm leading-relaxed space-y-2 prose max-w-none shadow-sm shadow-[#0000000d]">
                                {!! \Illuminate\Support\Str::markdown($msg->response ?? $msg->content) !!}
                            </div>
                        </div>
                    @endif
                @endforeach
            @endif

        </main>

        <!-- Footer Input Form -->
        <footer class="bg-[#fbf9f8] border-t border-[#e5e5e1] p-4 shrink-0">
            <form id="chat-form" class="max-w-4xl mx-auto flex flex-col md:flex-row items-center gap-3">
                <input 
                    type="text" 
                    id="user-message" 
                    name="message"
                    placeholder="Ketik pesan atau pertanyaan strategi marcom..." 
                    required 
                    autocomplete="off"
                    class="flex-1 bg-[#ffffff] border border-[#e5e5e1] text-[#1b1c1c] text-sm rounded-2xl px-4 py-3 focus:outline-none focus:border-[#885215] focus:ring-2 focus:ring-[#885215]/20 transition"
                >
                <button 
                    type="submit" 
                    id="send-btn"
                    class="bg-[#885215] hover:bg-[#a3692a] text-[#ffffff] font-semibold px-5 py-3 rounded-2xl transition text-sm flex items-center justify-center gap-2 shadow-sm shadow-[#0000000d] w-full md:w-auto"
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
                    <div class="bg-[#1b1c1c] border border-[#1b1c1c] text-[#ffffff] rounded-2xl rounded-tr-none p-4 max-w-2xl text-sm shadow-sm shadow-[#0000000d] whitespace-pre-line">
                        ${escapeHtml(text)}
                    </div>
                `;
            } else {
                // Render teks AI menggunakan marked.js
                const parsedText = marked.parse(text);
                let fallbackBadge = '';
                if (engine === 'groq') {
                    fallbackBadge = `<div class="text-[10px] text-[#885215] mt-1.5 flex items-center gap-1">
                         <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                         Dijawab pakai Groq (mode cadangan, Claude sedang tidak bisa diakses)
                       </div>`;
                } else if (engine === 'gemini') {
                    fallbackBadge = `<div class="text-[10px] text-[#885215] mt-1.5 flex items-center gap-1">
                         <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                         Dijawab pakai Gemini (mode cadangan, Claude & Groq sedang tidak bisa diakses)
                       </div>`;
                }
                wrapper.innerHTML = `
                    <div class="bg-[#885215] text-[#ffffff] p-2 rounded-full text-xs font-bold shrink-0">AI</div>
                    <div>
                        <div class="bg-[#ffffff] border border-[#e5e5e1] rounded-2xl rounded-tl-none p-4 max-w-2xl text-[#1b1c1c] text-sm leading-relaxed space-y-2 prose max-w-none shadow-sm shadow-[#0000000d]">
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
                <div class="bg-[#885215] text-[#ffffff] p-2 rounded-full text-xs font-bold shrink-0">AI</div>
                <div class="bg-[#ffffff] border border-[#e5e5e1] rounded-2xl rounded-tl-none p-4 text-[#1b1c1c] text-sm animate-pulse">
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