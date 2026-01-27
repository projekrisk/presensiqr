<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Skanara</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Outfit"', 'sans-serif'],
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
        .bg-grid {
            background-size: 40px 40px;
            background-image: linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        }
        .no-scroll { overflow: hidden; }
    </style>
</head>
<body class="antialiased bg-dark text-white font-sans overflow-x-hidden selection:bg-cyan-500 selection:text-white">

    @if(session('success'))
    <div class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 w-full max-w-lg px-4">
        <div class="bg-green-500/10 border border-green-500/50 text-green-400 px-4 py-3 rounded-xl shadow-2xl backdrop-blur-md flex items-center justify-between">
            <div class="flex items-center">
                <svg class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-green-400 hover:text-white">✕</button>
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 w-full max-w-lg px-4">
        <div class="bg-red-500/10 border border-red-500/50 text-red-400 px-4 py-3 rounded-xl shadow-2xl backdrop-blur-md">
            <div class="flex items-center mb-1">
                <svg class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-bold">Terjadi Kesalahan:</span>
            </div>
            <ul class="list-disc list-inside text-sm pl-8">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button onclick="this.parentElement.parentElement.remove()" class="absolute top-3 right-3 text-red-400 hover:text-white">✕</button>
        </div>
    </div>
    @endif

    <div class="fixed inset-0 z-0 bg-grid"></div>
    <div class="fixed top-0 left-1/4 w-96 h-96 bg-blue-600 rounded-full mix-blend-multiply filter blur-[128px] opacity-20 animate-blob"></div>
    <div class="fixed top-0 right-1/4 w-96 h-96 bg-cyan-500 rounded-full mix-blend-multiply filter blur-[128px] opacity-20 animate-blob animation-delay-2000"></div>
    <div class="fixed -bottom-32 left-1/3 w-96 h-96 bg-purple-600 rounded-full mix-blend-multiply filter blur-[128px] opacity-20 animate-blob animation-delay-4000"></div>

    <div class="relative z-10 flex flex-col min-h-screen">
        
        <nav class="w-full pt-6 px-4 flex justify-center">
            <div class="bg-glass backdrop-blur-md border border-white/10 rounded-full px-6 py-3 flex items-center justify-between w-full max-w-5xl shadow-2xl">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-400 flex items-center justify-center text-white font-bold text-lg shadow-[0_0_15px_rgba(59,130,246,0.5)]">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                    </div>
                    <span class="font-bold text-xl tracking-wide">Skan<span class="text-cyan-400">ara</span></span>
                </div>
                <a href="/admin/login" class="text-sm font-semibold text-gray-300 hover:text-white transition">
                    Login →
                </a>
            </div>
        </nav>

        <main class="flex-grow flex items-center justify-center px-4 py-10 relative">
            <div class="max-w-4xl mx-auto text-center relative">
                
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[120%] h-[150%] border-x border-white/5 mask-image-gradient pointer-events-none"></div>

                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/5 border border-white/10 text-cyan-400 text-xs font-bold uppercase tracking-[0.2em] mb-8 backdrop-blur-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse shadow-[0_0_10px_#06B6D4]"></span>
                    System Online v2.0
                </div>

                <h1 class="text-5xl md:text-8xl font-extrabold tracking-tight mb-6 leading-tight text-white drop-shadow-2xl">
                    PRESENSI <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-cyan-400 to-emerald-400">MASA DEPAN</span>
                </h1>

                <p class="text-lg md:text-xl text-gray-400 mb-12 max-w-2xl mx-auto font-light leading-relaxed">
                    Beralih ke ekosistem presensi digital berbasis 
                    <span class="text-white font-semibold">Digital</span>. 
                    Real-time, transparan, dan terintegrasi penuh.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-5 relative z-20">
                    <a href="/admin/login" class="group relative px-8 py-4 bg-blue-600 rounded-2xl font-bold text-white overflow-hidden transition-all hover:scale-105 hover:shadow-[0_0_40px_rgba(37,99,235,0.5)]">
                        <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
                        <span class="flex items-center gap-3">
                            Dashboard
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </span>
                    </a>

                    <button onclick="toggleModal('registerModal')" class="group px-8 py-4 bg-white/5 border border-white/10 rounded-2xl font-bold text-gray-300 hover:bg-white/10 hover:text-white hover:border-white/30 transition-all backdrop-blur-sm flex items-center gap-2">
                        <span>Daftar</span>
                        <div class="w-2 h-2 rounded-full bg-green-500 group-hover:animate-ping"></div>
                    </button>
                </div>

                <div class="mt-20 pt-10 border-t border-white/5 grid grid-cols-2 md:grid-cols-4 gap-8">
                    <div><h4 class="text-2xl font-bold text-white">100+</h4><p class="text-xs text-gray-500 uppercase tracking-wider mt-1">Sekolah</p></div>
                    <div><h4 class="text-2xl font-bold text-white">50k+</h4><p class="text-xs text-gray-500 uppercase tracking-wider mt-1">Siswa Aktif</p></div>
                    <div><h4 class="text-2xl font-bold text-white">0.2s</h4><p class="text-xs text-gray-500 uppercase tracking-wider mt-1">Kecepatan Scan</p></div>
                    <div><h4 class="text-2xl font-bold text-white">99.9%</h4><p class="text-xs text-gray-500 uppercase tracking-wider mt-1">Uptime</p></div>
                </div>

            </div>
        </main>

        <footer class="py-6 text-center text-gray-600 text-xs font-medium relative z-10 border-t border-white/5">
            <p>&copy; {{ date('Y') }} SKANARA SYSTEM. ENGINEERED FOR EDUCATION.</p>
        </footer>
    </div>

    <!-- REGISTRATION MODAL -->
    <div id="registerModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/90 backdrop-blur-sm transition-opacity opacity-0" id="modalBackdrop"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-slate-900 border border-white/10 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl scale-95 opacity-0" id="modalPanel">
                    <div class="bg-slate-800/50 px-4 pb-4 pt-5 sm:p-6 sm:pb-4 relative z-10">
                        <div class="w-full">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-xl font-bold leading-6 text-white" id="modal-title">Registrasi Sekolah Baru</h3>
                                <button type="button" onclick="toggleModal('registerModal')" class="text-gray-400 hover:text-white">✕</button>
                            </div>

                            <!-- TAB NAVIGATION -->
                            <div class="flex border-b border-gray-700 mb-6">
                                <button type="button" id="btn-tab-sekolah" onclick="switchTab('sekolah')" class="w-1/2 py-3 text-sm font-medium text-blue-500 border-b-2 border-blue-500 focus:outline-none transition-colors">
                                    1. Data Sekolah
                                </button>
                                <button type="button" id="btn-tab-admin" onclick="switchTab('admin')" class="w-1/2 py-3 text-sm font-medium text-gray-400 border-b-2 border-transparent hover:text-gray-200 focus:outline-none transition-colors">
                                    2. Akun Admin
                                </button>
                            </div>

                            <!-- FORM -->
                            <form action="{{ route('register.school') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                @csrf
                                
                                <!-- SECTION 1: DATA SEKOLAH -->
                                <div id="tab-sekolah" class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-1">Nama Sekolah</label>
                                        <input type="text" name="school_name" class="w-full bg-black/30 border border-gray-600 rounded-lg px-4 py-2.5 text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition" placeholder="Contoh: SMA Negeri 1 Jakarta" required value="{{ old('school_name') }}">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-1">NPSN</label>
                                        <input type="number" name="npsn" class="w-full bg-black/30 border border-gray-600 rounded-lg px-4 py-2.5 text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="8 digit angka" required value="{{ old('npsn') }}">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-1">Logo Sekolah (Wajib)</label>
                                        <input type="file" name="logo" accept="image/*" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700" required>
                                        <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG. Max: 2MB.</p>
                                    </div>
                                    <div class="pt-4 flex justify-end">
                                        <button type="button" onclick="switchTab('admin')" class="px-6 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-bold transition">Lanjut &rarr;</button>
                                    </div>
                                </div>

                                <!-- SECTION 2: DATA ADMIN -->
                                <div id="tab-admin" class="space-y-4 hidden">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-1">Nama Lengkap Admin</label>
                                        <input type="text" name="admin_name" class="w-full bg-black/30 border border-gray-600 rounded-lg px-4 py-2.5 text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="Nama Penanggung Jawab" required value="{{ old('admin_name') }}">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-1">Email Login</label>
                                        <input type="email" name="email" class="w-full bg-black/30 border border-gray-600 rounded-lg px-4 py-2.5 text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="admin@sekolah.sch.id" required value="{{ old('email') }}">
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300 mb-1">Password</label>
                                            <input type="password" name="password" class="w-full bg-black/30 border border-gray-600 rounded-lg px-4 py-2.5 text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="Min. 8 karakter" required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300 mb-1">No. WhatsApp</label>
                                            <input type="tel" name="phone" class="w-full bg-black/30 border border-gray-600 rounded-lg px-4 py-2.5 text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="0812..." required value="{{ old('phone') }}">
                                        </div>
                                    </div>
                                    <div class="pt-4 flex justify-between">
                                        <button type="button" onclick="switchTab('sekolah')" class="text-gray-400 hover:text-white font-medium">&larr; Kembali</button>
                                        <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg font-bold transition shadow-lg shadow-green-500/20">Daftar Sekarang</button>
                                    </div>
                                </div>
                            </form>
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
                modal.classList.remove('hidden');
                body.classList.add('no-scroll');
                setTimeout(() => {
                    backdrop.classList.remove('opacity-0');
                    panel.classList.remove('scale-95', 'opacity-0');
                    panel.classList.add('scale-100', 'opacity-100');
                }, 10);
            } else {
                backdrop.classList.add('opacity-0');
                panel.classList.remove('scale-100', 'opacity-100');
                panel.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    modal.classList.add('hidden');
                    body.classList.remove('no-scroll');
                }, 300);
            }
        }

        function switchTab(tabName) {
            const tabSekolah = document.getElementById('tab-sekolah');
            const tabAdmin = document.getElementById('tab-admin');
            const btnSekolah = document.getElementById('btn-tab-sekolah');
            const btnAdmin = document.getElementById('btn-tab-admin');

            if (tabName === 'sekolah') {
                tabSekolah.classList.remove('hidden');
                tabAdmin.classList.add('hidden');
                btnSekolah.classList.add('text-blue-500', 'border-blue-500');
                btnSekolah.classList.remove('text-gray-400', 'border-transparent');
                btnAdmin.classList.add('text-gray-400', 'border-transparent');
                btnAdmin.classList.remove('text-blue-500', 'border-blue-500');
            } else {
                tabSekolah.classList.add('hidden');
                tabAdmin.classList.remove('hidden');
                btnAdmin.classList.add('text-blue-500', 'border-blue-500');
                btnAdmin.classList.remove('text-gray-400', 'border-transparent');
                btnSekolah.classList.add('text-gray-400', 'border-transparent');
                btnSekolah.classList.remove('text-blue-500', 'border-blue-500');
            }
        }
    </script>
</body>
</html>