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
        /* Hide scrollbar for modal */
        .no-scroll {
            overflow: hidden;
        }
    </style>
</head>
<body class="antialiased bg-dark text-white font-sans overflow-x-hidden selection:bg-cyan-500 selection:text-white">

    <!-- ALERTS (Feedback User) -->
    @if(session('success'))
    <div class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 w-full max-w-lg px-4">
        <div class="bg-green-500/10 border border-green-500/50 text-green-400 px-4 py-3 rounded-xl shadow-2xl backdrop-blur-md flex items-center justify-between">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-green-400 hover:text-white">✕</button>
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 w-full max-w-lg px-4">
        <div class="bg-red-500/10 border border-red-500/50 text-red-400 px-4 py-3 rounded-xl shadow-2xl backdrop-blur-md">
            <div class="flex items-center mb-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-bold">Terjadi Kesalahan:</span>
            </div>
            <ul class="list-disc list-inside text-sm pl-8">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button onclick="this.parentElement.remove()" class="absolute top-3 right-3 text-red-400 hover:text-white">✕</button>
        </div>
    </div>
    @endif

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
                    <a href="#pricing" class="group px-8 py-4 bg-white/5 border border-white/10 rounded-2xl font-bold text-gray-300 hover:bg-white/10 hover:text-white hover:border-white/30 transition-all backdrop-blur-sm flex items-center gap-2">
                        <span>Lihat Paket</span>
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

        <!-- PRICING SECTION -->
        <section class="py-20 relative" id="pricing">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-5xl font-bold text-white mb-4">Pilih Paket Langganan</h2>
                    <p class="text-gray-400 max-w-2xl mx-auto">Mulai digitalisasi sekolah Anda dengan biaya terjangkau. Transparan, tanpa biaya tersembunyi.</p>
                </div>

                <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                    <!-- Free Tier (Trial) -->
                    <div class="p-8 rounded-3xl bg-white/5 border border-white/10 hover:border-cyan-500/50 transition-all duration-300 relative group">
                        <div class="absolute inset-0 bg-gradient-to-b from-cyan-500/5 to-transparent rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <h3 class="text-xl font-bold text-white mb-2">Paket Trial</h3>
                        <div class="flex items-baseline gap-1 mb-6">
                            <span class="text-4xl font-bold text-cyan-400">Rp 0</span>
                            <span class="text-gray-400">/ 4 Bulan</span>
                        </div>
                        <p class="text-gray-400 mb-8 text-sm leading-relaxed">Cocok untuk sekolah yang ingin mencoba sistem presensi digital tanpa risiko finansial.</p>
                        <ul class="space-y-4 mb-8 text-sm text-gray-300">
                            <li class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Akses Penuh Dashboard Admin
                            </li>
                            <li class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Aplikasi Android Guru & Kiosk
                            </li>
                            <li class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Unlimited Siswa & Guru
                            </li>
                            <li class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Laporan Presensi Real-time
                            </li>
                        </ul>
                        <button onclick="toggleModal('registerModal')" class="w-full py-4 rounded-xl border border-cyan-500/30 text-cyan-400 font-bold hover:bg-cyan-500 hover:text-white transition-all shadow-[0_0_20px_rgba(6,182,212,0.1)] hover:shadow-[0_0_30px_rgba(6,182,212,0.4)]">
                            Mulai Trial Sekarang
                        </button>
                    </div>

                    <!-- Annual Tier (Pro) -->
                    <div class="p-8 rounded-3xl bg-gradient-to-b from-blue-900/40 to-white/5 border border-blue-500/30 relative overflow-hidden transform hover:scale-105 transition-transform duration-300">
                        <div class="absolute top-0 right-0 bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-bl-xl shadow-lg">POPULAR</div>
                        <h3 class="text-xl font-bold text-white mb-2">Paket Tahunan</h3>
                        <div class="flex items-baseline gap-1 mb-6">
                            <span class="text-4xl font-bold text-white">Rp 1.5jt</span>
                            <span class="text-gray-400">/ Tahun</span>
                        </div>
                        <p class="text-gray-400 mb-8 text-sm leading-relaxed">Solusi jangka panjang dengan dukungan prioritas dan fitur kustomisasi laporan.</p>
                        <ul class="space-y-4 mb-8 text-sm text-gray-300">
                            <li class="flex items-center gap-3">
                                <div class="p-1 rounded-full bg-blue-500/20"><svg class="w-3 h-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div>
                                <strong>Semua Fitur Paket Trial</strong>
                            </li>
                            <li class="flex items-center gap-3">
                                <div class="p-1 rounded-full bg-blue-500/20"><svg class="w-3 h-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div>
                                Prioritas Support 24/7 via WA
                            </li>
                            <li class="flex items-center gap-3">
                                <div class="p-1 rounded-full bg-blue-500/20"><svg class="w-3 h-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div>
                                Backup Data Harian Otomatis
                            </li>
                            <li class="flex items-center gap-3">
                                <div class="p-1 rounded-full bg-blue-500/20"><svg class="w-3 h-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div>
                                Export Laporan Custom (Excel/PDF)
                            </li>
                        </ul>
                        <a href="https://wa.me/6281234567890?text=Halo%20Admin%20SIPQR,%20saya%20tertarik%20dengan%20Paket%20Tahunan" target="_blank" class="block text-center w-full py-4 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-500 transition-all shadow-lg shadow-blue-600/30">
                            Pilih Paket Tahunan
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- FOOTER -->
        <footer class="py-6 text-center text-gray-600 text-xs font-medium relative z-10 border-t border-white/5">
            <p>&copy; {{ date('Y') }} SIPQR SYSTEM. ENGINEERED FOR EDUCATION.</p>
        </footer>
    </div>

    <!-- REGISTRATION MODAL -->
    <div id="registerModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity opacity-0" id="modalBackdrop"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                
                <!-- Modal Panel -->
                <div class="relative transform overflow-hidden rounded-2xl bg-dark border border-white/10 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg scale-95 opacity-0" id="modalPanel">
                    
                    <!-- Glow Effect -->
                    <div class="absolute top-0 right-0 -mr-10 -mt-10 w-40 h-40 bg-blue-600 rounded-full mix-blend-screen filter blur-[60px] opacity-20 pointer-events-none"></div>
                    
                    <div class="bg-white/5 px-4 pb-4 pt-5 sm:p-6 sm:pb-4 relative z-10">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-900/30 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-xl font-semibold leading-6 text-white" id="modal-title">Registrasi Sekolah Baru</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-400">Isi data di bawah ini untuk memulai digitalisasi sekolah Anda.</p>
                                </div>

                                <!-- Form -->
                                <form action="{{ route('register.school') }}" method="POST" class="mt-6 space-y-4">
                                    @csrf
                                    <div>
                                        <label for="school_name" class="block text-sm font-medium text-gray-400 mb-1">Nama Sekolah</label>
                                        <input type="text" name="school_name" id="school_name" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-gray-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition" placeholder="Contoh: SMA Negeri 1 Jakarta" required value="{{ old('school_name') }}">
                                    </div>

                                    <div>
                                        <label for="npsn" class="block text-sm font-medium text-gray-400 mb-1">NPSN (Nomor Pokok Sekolah Nasional)</label>
                                        <input type="number" name="npsn" id="npsn" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-gray-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition" placeholder="8 digit angka" required value="{{ old('npsn') }}">
                                    </div>
                                    
                                    <div>
                                        <label for="admin_name" class="block text-sm font-medium text-gray-400 mb-1">Nama Admin / Kepala Sekolah</label>
                                        <input type="text" name="admin_name" id="admin_name" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-gray-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition" placeholder="Nama lengkap" required value="{{ old('admin_name') }}">
                                    </div>

                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-400 mb-1">Email Login Admin</label>
                                        <input type="email" name="email" id="email" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-gray-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition" placeholder="admin@sekolah.sch.id" required value="{{ old('email') }}">
                                    </div>

                                    <div>
                                        <label for="password" class="block text-sm font-medium text-gray-400 mb-1">Password Login</label>
                                        <input type="password" name="password" id="password" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-gray-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition" placeholder="Minimal 8 karakter" required>
                                    </div>

                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-gray-400 mb-1">Nomor WhatsApp (Untuk Notifikasi)</label>
                                        <input type="tel" name="phone" id="phone" class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-gray-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition" placeholder="0812..." required value="{{ old('phone') }}">
                                    </div>

                                    <div class="pt-4 flex flex-row-reverse gap-2">
                                        <button type="submit" class="w-full inline-flex justify-center rounded-lg bg-blue-600 px-3 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto transition">Daftar Sekarang</button>
                                        <button type="button" onclick="toggleModal('registerModal')" class="mt-3 inline-flex w-full justify-center rounded-lg bg-white/5 border border-white/10 px-3 py-2.5 text-sm font-semibold text-gray-300 shadow-sm hover:bg-white/10 sm:mt-0 sm:w-auto transition">Batal</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleModal(modalID){
            const modal = document.getElementById(modalID);
            const backdrop = document.getElementById('modalBackdrop');
            const panel = document.getElementById('modalPanel');
            const body = document.body;

            if(modal.classList.contains('hidden')){
                // Open
                modal.classList.remove('hidden');
                body.classList.add('no-scroll'); // Prevent body scroll
                
                // Animation In
                setTimeout(() => {
                    backdrop.classList.remove('opacity-0');
                    panel.classList.remove('scale-95', 'opacity-0');
                    panel.classList.add('scale-100', 'opacity-100');
                }, 10);
            } else {
                // Close Animation
                backdrop.classList.add('opacity-0');
                panel.classList.remove('scale-100', 'opacity-100');
                panel.classList.add('scale-95', 'opacity-0');

                setTimeout(() => {
                    modal.classList.add('hidden');
                    body.classList.remove('no-scroll');
                }, 300); // Wait for transition
            }
        }
    </script>

</body>
</html>