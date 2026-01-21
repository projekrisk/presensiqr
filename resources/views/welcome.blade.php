<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SIPQR - Future of Attendance</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Outfit"', 'sans-serif'], // Font lebih geometris & modern
                    },
                    colors: {
                        dark: '#0B1121', // Deep Space Blue
                        glass: 'rgba(255, 255, 255, 0.05)',
                        primary: '#3B82F6',
                        accent: '#06B6D4',
                    },
                    animation: {
                        'blob': 'blob 7s infinite',
                        'scan': 'scan 3s linear infinite',
                    },
                    keyframes: {
                        blob: {
                            '0%': { transform: 'translate(0px, 0px) scale(1)' },
                            '33%': { transform: 'translate(30px, -50px) scale(1.1)' },
                            '66%': { transform: 'translate(-20px, 20px) scale(0.9)' },
                            '100%': { transform: 'translate(0px, 0px) scale(1)' },
                        },
                        scan: {
                            '0%': { top: '0%' },
                            '50%': { top: '100%' },
                            '100%': { top: '0%' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        /* Tech Grid Background */
        .bg-grid {
            background-size: 40px 40px;
            background-image: linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        }
    </style>
</head>
<body class="antialiased bg-dark text-white font-sans overflow-x-hidden selection:bg-cyan-500 selection:text-white">

    <!-- BACKGROUND ELEMENTS -->
    <div class="fixed inset-0 z-0 bg-grid"></div>
    <!-- Glowing Orbs -->
    <div class="fixed top-0 left-1/4 w-96 h-96 bg-blue-600 rounded-full mix-blend-multiply filter blur-[128px] opacity-20 animate-blob"></div>
    <div class="fixed top-0 right-1/4 w-96 h-96 bg-cyan-500 rounded-full mix-blend-multiply filter blur-[128px] opacity-20 animate-blob animation-delay-2000"></div>
    <div class="fixed -bottom-32 left-1/3 w-96 h-96 bg-purple-600 rounded-full mix-blend-multiply filter blur-[128px] opacity-20 animate-blob animation-delay-4000"></div>

    <!-- MAIN CONTAINER -->
    <div class="relative z-10 flex flex-col min-h-screen">
        
        <!-- NAVBAR (Floating Glass) -->
        <nav class="w-full pt-6 px-4 flex justify-center">
            <div class="bg-glass backdrop-blur-md border border-white/10 rounded-full px-6 py-3 flex items-center justify-between w-full max-w-5xl shadow-2xl">
                <!-- Logo -->
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-400 flex items-center justify-center text-white font-bold text-lg shadow-[0_0_15px_rgba(59,130,246,0.5)]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                    </div>
                    <span class="font-bold text-xl tracking-wide">SIP<span class="text-cyan-400">QR</span></span>
                </div>

                <!-- Simple Action -->
                <a href="/admin/login" class="text-sm font-semibold text-gray-300 hover:text-white transition">
                    Login Admin →
                </a>
            </div>
        </nav>

        <!-- HERO CONTENT -->
        <main class="flex-grow flex items-center justify-center px-4 py-10 relative">
            
            <div class="max-w-4xl mx-auto text-center relative">
                
                <!-- Decorative Scanner Effect Behind Title -->
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[120%] h-[150%] border-x border-white/5 mask-image-gradient pointer-events-none"></div>

                <!-- Badge -->
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/5 border border-white/10 text-cyan-400 text-xs font-bold uppercase tracking-[0.2em] mb-8 backdrop-blur-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse shadow-[0_0_10px_#06B6D4]"></span>
                    System Online v2.0
                </div>

                <!-- Main Title -->
                <h1 class="text-5xl md:text-8xl font-extrabold tracking-tight mb-6 leading-tight text-white drop-shadow-2xl">
                    PRESENSI <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-cyan-400 to-emerald-400">MASA DEPAN</span>
                </h1>

                <!-- Description -->
                <p class="text-lg md:text-xl text-gray-400 mb-12 max-w-2xl mx-auto font-light leading-relaxed">
                    Tinggalkan kertas dan manual. Beralih ke ekosistem presensi digital berbasis 
                    <span class="text-white font-semibold">AI & QR Code</span>. 
                    Real-time, transparan, dan terintegrasi penuh.
                </p>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-5 relative z-20">
                    
                    <!-- Login Button (Glowing) -->
                    <a href="/admin/login" class="group relative px-8 py-4 bg-blue-600 rounded-2xl font-bold text-white overflow-hidden transition-all hover:scale-105 hover:shadow-[0_0_40px_rgba(37,99,235,0.5)]">
                        <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
                        <span class="flex items-center gap-3">
                            Akses Dashboard
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </span>
                    </a>

                    <!-- Register Button (Glass) -->
                    <a href="https://wa.me/6281234567890" target="_blank" class="group px-8 py-4 bg-white/5 border border-white/10 rounded-2xl font-bold text-gray-300 hover:bg-white/10 hover:text-white hover:border-white/30 transition-all backdrop-blur-sm flex items-center gap-2">
                        <span>Daftarkan Sekolah</span>
                        <div class="w-2 h-2 rounded-full bg-green-500 group-hover:animate-ping"></div>
                    </a>
                </div>

                <!-- Stats / Trust Indicators -->
                <div class="mt-20 pt-10 border-t border-white/5 grid grid-cols-2 md:grid-cols-4 gap-8">
                    <div>
                        <h4 class="text-2xl font-bold text-white">100+</h4>
                        <p class="text-xs text-gray-500 uppercase tracking-wider mt-1">Sekolah</p>
                    </div>
                    <div>
                        <h4 class="text-2xl font-bold text-white">50k+</h4>
                        <p class="text-xs text-gray-500 uppercase tracking-wider mt-1">Siswa Aktif</p>
                    </div>
                    <div>
                        <h4 class="text-2xl font-bold text-white">0.2s</h4>
                        <p class="text-xs text-gray-500 uppercase tracking-wider mt-1">Kecepatan Scan</p>
                    </div>
                    <div>
                        <h4 class="text-2xl font-bold text-white">99.9%</h4>
                        <p class="text-xs text-gray-500 uppercase tracking-wider mt-1">Uptime</p>
                    </div>
                </div>

            </div>
        </main>

        <!-- FOOTER -->
        <footer class="py-6 text-center text-gray-600 text-xs font-medium relative z-10">
            <p>&copy; {{ date('Y') }} SIPQR SYSTEM. ENGINEERED FOR EDUCATION.</p>
        </footer>
    </div>

</body>
</html>