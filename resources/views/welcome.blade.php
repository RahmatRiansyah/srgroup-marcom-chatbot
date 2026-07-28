<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Holliday Group - Portal Internal SR GROUP</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/srgroup-logo-black.svg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="bg-[#F8F7F4] text-[#18181B] antialiased min-h-screen selection:bg-[#B86B1B] selection:text-white">

    <!-- Wrapper Utama: Di Desktop Pas 1 Layar (h-screen & overflow-hidden) -->
    <div class="min-h-screen lg:h-screen lg:overflow-hidden flex flex-col justify-between max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 lg:py-6">
        
        <!-- 1. Header Topbar -->
        <header class="flex items-center justify-between shrink-0 mb-6 lg:mb-0">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/srgroup-logo-black.svg') }}" 
                     alt="Holliday Group" 
                     class="h-10 sm:h-12 w-auto object-contain"
                     onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\'font-extrabold text-lg tracking-wider text-[#18181B]\'>HOLLIDAY GROUP</span>';">
            </div>

            <div>
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#18181B] text-white text-xs font-semibold hover:bg-[#27272A] transition-colors shadow-xs">
                            <span>Buka Dashboard</span>
                            <svg class="w-3.5 h-3.5 text-[#B86B1B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#18181B] text-white text-xs font-semibold hover:bg-[#27272A] transition-colors shadow-xs">
                            <span>Masuk Sistem</span>
                            <svg class="w-3.5 h-3.5 text-[#B86B1B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @endauth
                @endif
            </div>
        </header>

        <!-- 2. Main Content (2 Kolom di Desktop) -->
        <main class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-center my-auto py-2">
            
            <!-- Kolom Kiri: Branding & Informasi -->
            <div class="lg:col-span-6 space-y-5 text-center lg:text-left">
                
                <!-- Badge Kategori -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#B86B1B]/10 border border-[#B86B1B]/20 text-[#B86B1B] text-xs font-bold tracking-wide">
                    <span class="w-2 h-2 rounded-full bg-[#B86B1B] animate-pulse"></span>
                    <span>Marcom & Corporate Portal</span>
                </div>

                <!-- Judul Utama -->
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-[#18181B] tracking-tight leading-[1.15]">
                    Selamat Datang di <br class="hidden sm:inline">
                    <span class="text-[#B86B1B]">Holliday Group</span>
                </h1>

                <!-- Deskripsi Subtitle -->
                <p class="text-xs sm:text-sm text-[#71717A] max-w-xl mx-auto lg:mx-0 leading-relaxed">
                    Sistem Layanan Terpadu Pemasaran & Komunikasi <strong class="text-[#18181B]">SR GROUP / PT Sritama Boga Prima</strong>. Akses terpusat pengelolaan media, komunikasi, dan layanan katering terintegrasi.
                </p>

                <!-- Tombol Aksi Utama -->
                <div class="pt-2 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 py-3.5 px-7 rounded-xl bg-[#B86B1B] hover:bg-[#965410] text-white text-sm font-bold transition-all shadow-md">
                            <span>Masuk ke Dashboard</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 py-3.5 px-7 rounded-xl bg-[#B86B1B] hover:bg-[#965410] text-white text-sm font-bold transition-all shadow-md">
                            <span>Masuk ke Sistem</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @endauth
                </div>

                <!-- Info Ringkas Divisi -->
                <div class="pt-4 grid grid-cols-3 gap-3 border-t border-[#EBEAE5] text-left max-w-lg mx-auto lg:mx-0">
                    <div>
                        <p class="text-[10px] text-[#71717A] uppercase font-bold tracking-wider">Divisi</p>
                        <p class="text-xs font-extrabold text-[#18181B] mt-0.5">Marcom & Media</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-[#71717A] uppercase font-bold tracking-wider">Layanan</p>
                        <p class="text-xs font-extrabold text-[#18181B] mt-0.5">Food & Catering</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-[#71717A] uppercase font-bold tracking-wider">Holding</p>
                        <p class="text-xs font-extrabold text-[#18181B] mt-0.5">SR GROUP</p>
                    </div>
                </div>

            </div>

            <!-- Kolom Kanan: CAROUSEL SHOWCASE (SLIDESHOW GAMBAR KULINER BERGANTIAN) -->
            <div class="lg:col-span-6 relative">
                <div class="relative rounded-3xl overflow-hidden border border-[#EBEAE5] shadow-xl bg-[#18181B]">
                    
                    <!-- Container Slideshow -->
                    <div id="welcome-carousel" class="relative h-72 sm:h-88 lg:h-[400px] w-full overflow-hidden">
                        
                        <!-- SLIDE 1: MAIN SHOWCASE (Grand Event & Catering) -->
                        <div class="carousel-item absolute inset-0 opacity-100 transition-opacity duration-1000 ease-in-out pointer-events-auto">
                            <img src="https://images.unsplash.com/photo-1511795409834-ef04bbd61622?q=80&w=1200&auto=format&fit=crop" 
                                 alt="Holliday Group Catering Showcase" 
                                 class="w-full h-full object-cover object-center">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/30 to-transparent"></div>
                            <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                                <span class="inline-block px-2.5 py-1 rounded-md bg-[#B86B1B] text-[10px] font-extrabold uppercase tracking-widest text-white mb-2">
                                    SR GROUP / PT Sritama Boga Prima
                                </span>
                                <h3 class="text-lg sm:text-xl font-bold tracking-tight text-white">
                                    Your Food & Catering Partner
                                </h3>
                                <p class="text-xs text-zinc-300 mt-1">
                                    Penyedia katering & layanan kuliner terpercaya berstandar tinggi di Indonesia.
                                </p>
                            </div>
                        </div>

                        <!-- SLIDE 2: GAMBAR MAKANAN 1 (Buffet & Event Catering) -->
                        <div class="carousel-item absolute inset-0 opacity-0 transition-opacity duration-1000 ease-in-out pointer-events-none">
                            <img src="https://images.unsplash.com/photo-1555244162-803834f70033?q=80&w=1200&auto=format&fit=crop" 
                                 alt="Katering Prasmanan Holliday Group" 
                                 class="w-full h-full object-cover object-center">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/30 to-transparent"></div>
                            <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                                <span class="inline-block px-2.5 py-1 rounded-md bg-[#B86B1B] text-[10px] font-extrabold uppercase tracking-widest text-white mb-2">
                                    Buffet & Event Catering
                                </span>
                                <h3 class="text-lg sm:text-xl font-bold tracking-tight text-white">
                                    Layanan Katering Pesta & Acara Resmi
                                </h3>
                                <p class="text-xs text-zinc-300 mt-1">
                                    Penyajian buffet profesional dengan dekorasi & cita rasa premium.
                                </p>
                            </div>
                        </div>

                        <!-- SLIDE 3: GAMBAR MAKANAN 2 (Culinary Plating & Fine Dining) -->
                        <div class="carousel-item absolute inset-0 opacity-0 transition-opacity duration-1000 ease-in-out pointer-events-none">
                            <img src="https://images.unsplash.com/photo-1544025162-d76694265947?q=80&w=1200&auto=format&fit=crop" 
                                 alt="Kuliner Khas & Plating" 
                                 class="w-full h-full object-cover object-center">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/30 to-transparent"></div>
                            <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                                <span class="inline-block px-2.5 py-1 rounded-md bg-[#B86B1B] text-[10px] font-extrabold uppercase tracking-widest text-white mb-2">
                                    Signature Dishes
                                </span>
                                <h3 class="text-lg sm:text-xl font-bold tracking-tight text-white">
                                    Keberagaman Menu & Cita Rasa Otentik
                                </h3>
                                <p class="text-xs text-zinc-300 mt-1">
                                    Diolah khusus oleh tim koki berpengalaman Holliday Group.
                                </p>
                            </div>
                        </div>

                        <!-- SLIDE 4: GAMBAR MAKANAN 3 (Hospitality & Banquet Setup) -->
                        <div class="carousel-item absolute inset-0 opacity-0 transition-opacity duration-1000 ease-in-out pointer-events-none">
                            <img src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?q=80&w=1200&auto=format&fit=crop" 
                                 alt="Restaurant & Catering Service" 
                                 class="w-full h-full object-cover object-center">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/30 to-transparent"></div>
                            <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                                <span class="inline-block px-2.5 py-1 rounded-md bg-[#B86B1B] text-[10px] font-extrabold uppercase tracking-widest text-white mb-2">
                                    Hospitality Excellence
                                </span>
                                <h3 class="text-lg sm:text-xl font-bold tracking-tight text-white">
                                    Standar Pelayanan Katering Terbaik
                                </h3>
                                <p class="text-xs text-zinc-300 mt-1">
                                    Mendukung berbagai kebutuhan korporat dan perayaan keluarga.
                                </p>
                            </div>
                        </div>

                    </div>

                    <!-- Dot Indicators Carousel (Indikator Titik Di Bawah) -->
                    <div class="absolute top-4 right-4 z-20 flex items-center gap-1.5 bg-black/40 backdrop-blur-md px-3 py-1.5 rounded-full border border-white/10">
                        <button onclick="setSlide(0)" class="carousel-dot w-2 h-2 rounded-full bg-white transition-all duration-300" aria-label="Slide 1"></button>
                        <button onclick="setSlide(1)" class="carousel-dot w-2 h-2 rounded-full bg-white/40 hover:bg-white/70 transition-all duration-300" aria-label="Slide 2"></button>
                        <button onclick="setSlide(2)" class="carousel-dot w-2 h-2 rounded-full bg-white/40 hover:bg-white/70 transition-all duration-300" aria-label="Slide 3"></button>
                        <button onclick="setSlide(3)" class="carousel-dot w-2 h-2 rounded-full bg-white/40 hover:bg-white/70 transition-all duration-300" aria-label="Slide 4"></button>
                    </div>

                </div>
            </div>

        </main>

        <!-- 3. Footer -->
        <footer class="py-3 text-center border-t border-[#EBEAE5] shrink-0 mt-6 lg:mt-0">
            <p class="text-xs text-[#71717A]">
                © {{ date('Y') }} <strong class="text-[#18181B]">Holliday Group / SR GROUP</strong>. All rights reserved.
            </p>
        </footer>

    </div>

    <!-- Script Transisi Carousel Otomatis -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let currentSlide = 0;
            const slides = document.querySelectorAll('.carousel-item');
            const dots = document.querySelectorAll('.carousel-dot');
            const totalSlides = slides.length;
            const intervalTime = 4000; // Berganti setiap 4 detik

            function updateCarousel() {
                slides.forEach((slide, index) => {
                    if (index === currentSlide) {
                        slide.classList.remove('opacity-0', 'pointer-events-none');
                        slide.classList.add('opacity-100', 'pointer-events-auto');
                    } else {
                        slide.classList.remove('opacity-100', 'pointer-events-auto');
                        slide.classList.add('opacity-0', 'pointer-events-none');
                    }
                });

                dots.forEach((dot, index) => {
                    if (index === currentSlide) {
                        dot.classList.remove('bg-white/40');
                        dot.classList.add('bg-[#B86B1B]', 'w-5'); // Titik aktif memanjang warna emas
                    } else {
                        dot.classList.remove('bg-[#B86B1B]', 'w-5');
                        dot.classList.add('bg-white/40', 'w-2');
                    }
                });
            }

            function nextSlide() {
                currentSlide = (currentSlide + 1) % totalSlides;
                updateCarousel();
            }

            window.setSlide = function(index) {
                currentSlide = index;
                updateCarousel();
                resetTimer();
            };

            let timer = setInterval(nextSlide, intervalTime);

            function resetTimer() {
                clearInterval(timer);
                timer = setInterval(nextSlide, intervalTime);
            }

            // Inisialisasi awal
            updateCarousel();
        });
    </script>

</body>
</html>