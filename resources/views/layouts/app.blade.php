<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>SR GROUP - Marcom Analytics</title>

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('images/srgroup-logo-white.svg') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- CDN SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-[#fbf9f8] text-[#1b1c1c] min-h-screen">
        <div class="min-h-screen bg-[#fbf9f8]">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-[#ffffff] shadow border-b border-[#e5e5e1]">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        <!-- Global SweetAlert2 Pop-up Script (SR GROUP Theme) -->
        <script>
            function konfirmasiNonaktifkan(namaAkun, formIdOrUrl) {
                // Filter otomatis agar tidak terjadi typo "akun akun contoh"
                let targetNama = namaAkun.trim();
                if (targetNama.toLowerCase().startsWith('akun ')) {
                    targetNama = targetNama.substring(5).trim();
                }

                Swal.fire({
                    title: 'Konfirmasi Nonaktifkan',
                    html: `Apakah Anda yakin ingin menonaktifkan akun <strong>"${targetNama}"</strong>?`,
                    icon: 'warning',
                    iconColor: '#885215',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#f5f3f3',
                    confirmButtonText: 'Ya, Nonaktifkan',
                    cancelButtonText: 'Batal',
                    background: '#ffffff',
                    color: '#1b1c1c',
                    customClass: {
                        popup: 'rounded-2xl border border-[#e5e5e1] shadow-xl p-6',
                        title: 'text-lg font-bold text-[#1b1c1c]',
                        htmlContainer: 'text-sm text-[#5f5e5e] mt-2',
                        confirmButton: 'px-4 py-2 text-xs font-semibold rounded-xl text-white transition shadow-sm',
                        cancelButton: 'px-4 py-2 text-xs font-semibold rounded-xl text-[#1b1c1c] border border-[#e5e5e1] transition hover:bg-[#efeded]'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        if (typeof formIdOrUrl === 'string' && document.getElementById(formIdOrUrl)) {
                            document.getElementById(formIdOrUrl).submit();
                        } else if (typeof formIdOrUrl === 'function') {
                            formIdOrUrl();
                        }
                    }
                });
            }
        </script>
    </body>
</html>