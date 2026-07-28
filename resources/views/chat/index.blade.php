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
    <!-- CDN DOMPurify -- WAJIB dipakai sebelum innerHTML, karena balasan AI bisa
         memuat teks hasil scraping web pihak ketiga yang tidak kita kontrol -->
    <script src="https://cdn.jsdelivr.net/npm/dompurify@3.1.6/dist/purify.min.js"></script>

    <!-- Custom CSS untuk Scrollbar & Styling Tabel AI -->
    <style>
        /* Smooth Scrollbar */
        #chat-box::-webkit-scrollbar {
            width: 6px;
        }
        #chat-box::-webkit-scrollbar-track {
            background: transparent;
        }
        #chat-box::-webkit-scrollbar-thumb {
            background: #e5e5e1;
            border-radius: 9999px;
        }
        #chat-box::-webkit-scrollbar-thumb:hover {
            background: #c8c6c5;
        }

        /* Styling Lengkap & Rapi untuk Tabel Hasil Output AI */
        .ai-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
            font-size: 0.875rem;
            text-align: left;
            border: 1px solid #dcdad5;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        .ai-content th {
            background-color: #f0eee9;
            color: #1b1c1c;
            font-weight: 700;
            padding: 0.625rem 0.875rem;
            border: 1px solid #dcdad5;
            white-space: nowrap;
        }
        .ai-content td {
            padding: 0.625rem 0.875rem;
            border: 1px solid #e5e5e1;
            vertical-align: top;
            color: #2c2a29;
        }
        .ai-content tr:nth-child(even) {
            background-color: #faf9f7;
        }
        .ai-content tr:hover {
            background-color: #f5f3f0;
        }
    </style>
</head>
<body class="bg-[#fbf9f8] text-[#1b1c1c] h-screen flex font-sans overflow-hidden antialiased">

    <!-- 1. Sidebar Navigasi -->
    @include('components.sidebar')

    <!-- 2. Area Utama Chat Application -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden relative">

        <!-- Top Header Bar -->
        <header class="bg-white/80 backdrop-blur-md border-b border-[#e5e5e1] py-3.5 px-4 sm:px-6 flex justify-between items-center z-10 shrink-0">
            <div class="flex items-center space-x-3">
                <button type="button" onclick="toggleSidebar()" class="md:hidden text-[#1b1c1c] hover:bg-[#f5f3f3] p-2 rounded-xl transition" aria-label="Buka menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                
                <div class="flex items-center gap-2.5">
                    <div class="bg-[#885215] text-white text-xs font-black tracking-wider px-2.5 py-1.5 rounded-lg shadow-xs">
                        SRGROUP
                    </div>
                    <div>
                        <h1 class="font-bold text-sm sm:text-base text-[#1b1c1c] leading-none">Marcom F&B Assistant</h1>
                        <p class="text-[11px] text-[#885215] font-medium mt-0.5">PT Sritama Boga Prima</p>
                    </div>
                </div>
            </div>

            <!-- Status Indicator -->
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-[11px] font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>AI Online</span>
                </span>
            </div>
        </header>

        <!-- Chat Box / Area Obrolan -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4 max-w-3xl mx-auto w-full" id="chat-box">
            
            <!-- Tampilan Awal / Empty State -->
            @if(!isset($messages) || $messages->isEmpty())
                <div class="flex flex-col items-center justify-center min-h-[65vh] text-center my-auto px-2">
                    <h2 class="text-xl sm:text-2xl font-bold text-[#1b1c1c] tracking-tight">
                        Halo Tim Marcom, ada yang bisa dibantu?
                    </h2>
                    <p class="text-xs sm:text-sm text-[#716e6b] max-w-md mt-1.5 mb-8 leading-relaxed">
                        Asisten cerdas pendukung keputusan strategi, tren kuliner, dan riset pasar PT Sritama Boga Prima.
                    </p>

                    <!-- Quick Prompts Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full max-w-xl text-left" id="quick-prompts">
                        @foreach ([
                            ['title' => 'Tren Kuliner', 'desc' => 'Apa tren menu makanan cepat saji terbaru?'],
                            ['title' => 'Strategi Promosi', 'desc' => 'Strategi promosi terbaik HOLLIDAY CATERING minggu ini'],
                            ['title' => 'Analisis Kompetitor', 'desc' => 'Bagaimana aktivitas pasar kompetitor DIM DIM SUM?'],
                            ['title' => 'Ide Content Plan', 'desc' => 'Buatkan ide konten Instagram engagement tinggi']
                        ] as $item)
                            <button
                                type="button"
                                class="quick-prompt-btn group p-3.5 bg-white hover:bg-[#f5f3f3] border border-[#e5e5e1] hover:border-[#885215]/40 rounded-2xl transition-all shadow-xs flex flex-col justify-between"
                            >
                                <span class="text-xs font-bold text-[#885215] group-hover:translate-x-0.5 transition-transform">{{ $item['title'] }}</span>
                                <span class="text-xs text-[#524439] mt-1 line-clamp-2">{{ $item['desc'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @else
                <!-- Render Riwayat Pesan -->
                @foreach($messages as $msg)
                    @if($msg->role === 'user' || isset($msg->message))
                        <!-- Box Pesan User (Rata Kanan & Fleksibel Mengikuti Kalimat) -->
                        <div class="flex justify-end my-2">
                            <div class="bg-[#f0eee9] border border-[#e2e0d9] text-[#1b1c1c] rounded-2xl px-4 py-2.5 max-w-[85%] sm:max-w-[75%] w-fit text-sm leading-relaxed font-medium shadow-xs">
                                {{ trim($msg->message ?? $msg->content) }}
                            </div>
                        </div>
                    @endif

                    @if($msg->role === 'assistant' || isset($msg->response))
                        <!-- Box Balasan AI (Full Width Sebelah Kiri) -->
                        <div class="w-full my-2">
                            <div class="bg-white border border-[#e5e5e1] rounded-2xl p-4 sm:p-5 text-[#1b1c1c] text-sm leading-relaxed prose prose-sm max-w-none shadow-xs overflow-x-auto ai-content">
                                {!! \Illuminate\Support\Str::markdown($msg->response ?? $msg->content) !!}
                            </div>
                        </div>
                    @endif
                @endforeach
            @endif

        </main>

        <!-- Footer Input Area -->
        <footer class="p-3 sm:p-4 shrink-0 bg-[#fbf9f8]/90 backdrop-blur-md border-t border-[#e5e5e1]">
            <div class="max-w-3xl mx-auto space-y-2">

                <!-- Model Selector Toolbar -->
                <div class="flex items-center justify-between px-1">
                    <div class="relative" id="model-selector-wrapper">
                        <button
                            type="button"
                            id="model-selector-btn"
                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#524439] bg-white border border-[#e5e5e1] hover:border-[#c8c6c5] px-3 py-1.5 rounded-full transition shadow-xs"
                        >
                            <svg class="w-3.5 h-3.5 text-[#885215]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span id="model-selector-label">Otomatis</span>
                            <svg class="w-3 h-3 text-[#716e6b]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <div
                            id="model-selector-menu"
                            class="hidden absolute bottom-full left-0 mb-2 w-72 bg-white border border-[#e5e5e1] rounded-2xl shadow-xl p-1.5 z-30"
                        ></div>
                    </div>
                </div>

                <!-- Input Box & Tombol Kirim -->
                <form id="chat-form" class="relative flex items-center bg-white border border-[#e5e5e1] focus-within:border-[#885215] focus-within:ring-2 focus-within:ring-[#885215]/20 rounded-2xl transition-all shadow-sm">
                    <input 
                        type="text" 
                        id="user-message" 
                        name="message"
                        placeholder="Ketik pesan atau pertanyaan strategi marcom..." 
                        required 
                        autocomplete="off"
                        class="w-full bg-transparent text-[#1b1c1c] text-sm py-3.5 pl-4 pr-12 focus:outline-none placeholder:text-[#a39a92]"
                    >
                    <button 
                        type="submit" 
                        id="send-btn"
                        class="absolute right-2 p-2 rounded-xl bg-[#885215] hover:bg-[#a3692a] text-white transition-all shadow-xs flex items-center justify-center shrink-0 disabled:opacity-40 disabled:cursor-not-allowed"
                        title="Kirim Pesan"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </form>

                <p class="text-[11px] text-center text-[#a39a92]">
                    SR GROUP AI dapat menyajikan data estimasi. Mohon verifikasi kembali info penting.
                </p>

            </div>
        </footer>

    </div>

    <!-- JavaScript Handler -->
    <script>
        const chatForm = document.getElementById('chat-form');
        const userMessageInput = document.getElementById('user-message');
        const chatBox = document.getElementById('chat-box');
        const sendBtn = document.getElementById('send-btn');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        chatBox.scrollTop = chatBox.scrollHeight;

        // MODEL SELECTOR CONFIG
        const ENGINE_META = {
            auto:   { label: 'Otomatis', description: 'Rantai cadangan: Claude → Groq → Gemini' },
            claude: { label: 'Claude',   description: 'Model utama, kualitas jawaban terbaik' },
            groq:   { label: 'Groq',     description: 'Cadangan pertama, respons cepat' },
            gemini: { label: 'Gemini',   description: 'Cadangan kedua' },
        };
        const ENGINE_STORAGE_KEY = 'srgroup_chat_engine';

        let selectedEngine = localStorage.getItem(ENGINE_STORAGE_KEY) || 'auto';
        if (!ENGINE_META[selectedEngine]) selectedEngine = 'auto';

        let engineStatusCache = { claude: { limited: false }, groq: { limited: false }, gemini: { limited: false } };

        const modelSelectorBtn   = document.getElementById('model-selector-btn');
        const modelSelectorLabel = document.getElementById('model-selector-label');
        const modelSelectorMenu  = document.getElementById('model-selector-menu');

        function selectEngine(key) {
            selectedEngine = key;
            localStorage.setItem(ENGINE_STORAGE_KEY, key);
            modelSelectorLabel.textContent = ENGINE_META[key].label;
            modelSelectorMenu.classList.add('hidden');
            renderModelMenu();
        }

        function renderModelMenu() {
            modelSelectorMenu.innerHTML = '';

            Object.keys(ENGINE_META).forEach(function (key) {
                const meta = ENGINE_META[key];
                const status = engineStatusCache[key];
                const isLimited = key !== 'auto' && status && status.limited;
                const isSelected = key === selectedEngine;

                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'w-full flex items-start gap-2 text-left px-3 py-2.5 rounded-xl transition ' +
                    (isLimited ? 'opacity-60 cursor-not-allowed' : 'hover:bg-[#f5f3f3] cursor-pointer');
                item.disabled = isLimited;

                item.innerHTML = `
                    <span class="mt-0.5 w-4 shrink-0 text-[#885215] text-sm font-bold">${isSelected ? '✓' : ''}</span>
                    <span class="flex-1">
                        <span class="flex items-center gap-2">
                            <span class="text-sm font-medium ${isLimited ? 'line-through text-[#a39a92]' : 'text-[#1b1c1c]'}">${meta.label}</span>
                            ${isLimited ? '<span class="text-[10px] font-semibold uppercase tracking-wide bg-[#f4e3d6] text-[#885215] px-1.5 py-0.5 rounded">Limit</span>' : ''}
                        </span>
                        <span class="block text-xs mt-0.5 ${isLimited ? 'text-[#a39a92]' : 'text-[#885215]'}">
                            ${isLimited ? 'Batas penggunaan tercapai, dinonaktifkan sementara' : meta.description}
                        </span>
                    </span>
                `;

                if (!isLimited) {
                    item.addEventListener('click', function () { selectEngine(key); });
                }

                modelSelectorMenu.appendChild(item);
            });
        }

        async function refreshEngineStatus() {
            try {
                const res = await fetch('{{ route("chat.engine-status") }}', {
                    headers: { 'Accept': 'application/json' },
                });
                if (!res.ok) return;

                engineStatusCache = await res.json();

                if (selectedEngine !== 'auto' && engineStatusCache[selectedEngine] && engineStatusCache[selectedEngine].limited) {
                    selectEngine('auto');
                }

                renderModelMenu();
            } catch (error) {}
        }

        modelSelectorBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            modelSelectorMenu.classList.toggle('hidden');
        });

        document.addEventListener('click', function (e) {
            if (!modelSelectorMenu.contains(e.target) && e.target !== modelSelectorBtn) {
                modelSelectorMenu.classList.add('hidden');
            }
        });

        modelSelectorLabel.textContent = ENGINE_META[selectedEngine].label;
        renderModelMenu();
        refreshEngineStatus();
        setInterval(refreshEngineStatus, 60000);

        // Quick Prompts Klik Handler
        document.querySelectorAll('.quick-prompt-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const promptDesc = btn.querySelector('.line-clamp-2') || btn;
                userMessageInput.value = promptDesc.textContent.trim();
                chatForm.requestSubmit();
            });
        });

        // Submit Form Handler
        chatForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const message = userMessageInput.value.trim();
            if (!message) return;

            appendMessage('user', message);
            userMessageInput.value = '';

            const loadingId = appendLoading();
            sendBtn.disabled = true;

            const sessionId = "{{ isset($activeSession) && $activeSession ? $activeSession->id : '' }}";
            const url = sessionId ? `/chat/${sessionId}/send` : '/chat/send';

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ message: message, engine: selectedEngine })
                });

                const data = await response.json();

                removeLoading(loadingId);

                if (response.status === 429) {
                    // Kena rate limit (throttle:20,1 di routes/web.php)
                    appendMessage('ai', 'Terlalu banyak pesan dalam waktu singkat. Tunggu sebentar lalu coba lagi.');
                } else if (data.reply) {
                    appendMessage('ai', data.reply, data.engine, data.engine_limited);

                    if (!sessionId && data.session_id) {
                        window.location.href = `/chat/${data.session_id}`;
                    }
                } else {
                    appendMessage('ai', 'Gagal menerima balasan dari server.');
                }

                refreshEngineStatus();

            } catch (error) {
                removeLoading(loadingId);
                appendMessage('ai', 'Terjadi kesalahan koneksi. Silakan coba lagi.');
            } finally {
                sendBtn.disabled = false;
            }
        });

        function appendMessage(sender, text, engine, engineLimited) {
            const wrapper = document.createElement('div');
            
            if (sender === 'user') {
                wrapper.className = 'flex justify-end my-2';
                wrapper.innerHTML = `
                    <div class="bg-[#f0eee9] border border-[#e2e0d9] text-[#1b1c1c] rounded-2xl px-4 py-2.5 max-w-[85%] sm:max-w-[75%] w-fit text-sm leading-relaxed font-medium shadow-xs">
                        ${escapeHtml(text.trim())}
                    </div>
                `;
            } else {
                wrapper.className = 'w-full my-2';
                // Balasan AI bisa memuat konten hasil scraping/tool-call yang tidak
                // kita kontrol -- selalu sanitasi HTML hasil marked.parse() sebelum
                // di-render, supaya tag <script>/onerror dsb tidak bisa dieksekusi.
                const parsedText = DOMPurify.sanitize(marked.parse(text));
                let fallbackBadge = '';
                
                if (engineLimited) {
                    fallbackBadge = `<div class="text-[10px] text-[#885215] mt-1.5 flex items-center gap-1 font-medium">
                         <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m0 3.75h.007v.008H12v-.008zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                         Model yang dipilih sedang limit -- pilih model lain atau mode Otomatis
                       </div>`;
                } else if (engine === 'groq') {
                    fallbackBadge = `<div class="text-[10px] text-[#885215] mt-1.5 flex items-center gap-1 font-medium">
                         <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                         Dijawab pakai Groq (mode cadangan)
                       </div>`;
                } else if (engine === 'gemini') {
                    fallbackBadge = `<div class="text-[10px] text-[#885215] mt-1.5 flex items-center gap-1 font-medium">
                         <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                         Dijawab pakai Gemini (mode cadangan)
                       </div>`;
                }

                wrapper.innerHTML = `
                    <div class="bg-white border border-[#e5e5e1] rounded-2xl p-4 sm:p-5 text-[#1b1c1c] text-sm leading-relaxed prose prose-sm max-w-none shadow-xs overflow-x-auto ai-content">
                        ${parsedText}
                    </div>
                    ${fallbackBadge}
                `;
            }

            chatBox.appendChild(wrapper);
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        function appendLoading() {
            const loadingId = 'loading-' + Date.now();
            const wrapper = document.createElement('div');
            wrapper.id = loadingId;
            wrapper.className = 'w-full my-2';
            wrapper.innerHTML = `
                <div class="bg-white border border-[#e5e5e1] rounded-2xl p-4 text-[#716e6b] text-sm animate-pulse shadow-xs flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#885215] animate-ping"></span>
                    <span>Sedang merumuskan jawaban...</span>
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