@extends('layouts.auth')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Logo dan Header -->
        <div class="text-center">
            <div class="mx-auto mb-6">
                <!-- Loading spinner dengan logo -->
                <div id="logoLoader" class="mx-auto h-96 w-96 flex flex-col items-center justify-center">
                    <div class="relative">
                        <img src="{{ asset('image/logologin.png') }}" 
                             alt="Optik Melati" 
                             class="h-32 w-32 object-contain mb-4"
                             onerror="this.style.display='none'; document.getElementById('loadingFallback').style.display='block';">
                        <div id="loadingFallback" class="h-32 w-32 bg-gradient-to-r from-orange-300 to-pink-300 rounded-full flex items-center justify-center mb-4" style="display: none;">
                            <svg class="h-16 w-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-500"></div>
                    <p class="text-sm text-gray-600 mt-2">Loading...</p>
                </div>
                
                <!-- Logo utama -->
                <img id="logoImage" 
                     src="{{ asset('image/logologin.png') }}" 
                     alt="Optik Melati" 
                     class="mx-auto h-96 w-96 object-contain shadow-2xl rounded-lg"
                     style="filter: drop-shadow(0 25px 50px rgba(0, 0, 0, 0.25)); display: none;"
                     onerror="tryAlternativeLogo(this)"
                     onload="showLogo(this)">
                
                <!-- Fallback logo jika semua gagal -->
                <div id="fallbackLogo" class="mx-auto h-96 w-96 bg-gradient-to-r from-orange-300 to-pink-300 rounded-full flex items-center justify-center shadow-2xl" style="display: none; filter: drop-shadow(0 25px 50px rgba(0, 0, 0, 0.25));">
                    <svg class="h-48 w-48 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <h2 class="text-3xl font-bold text-gray-900 mb-2"></h2>
            <p class="text-gray-600">Silahkan login untuk melanjutkan</p>
        </div>

        <!-- Form Login -->
        <form class="mt-8 space-y-6" action="{{ route('login') }}" method="POST">
            @csrf
            
            <!-- Email Field -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    Email Address
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                        </svg>
                    </div>
                    <input id="email" name="email" type="email" required 
                           class="appearance-none relative block w-full px-3 py-3 pl-10 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-orange-400 focus:z-10 sm:text-sm @error('email') border-red-500 @enderror"
                           placeholder="Masukkan email Anda"
                           value="{{ old('email') }}">
                </div>
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Field -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    Password
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <input id="password" name="password" type="password" required 
                           class="appearance-none relative block w-full px-3 py-3 pl-10 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-orange-400 focus:z-10 sm:text-sm @error('password') border-red-500 @enderror"
                           placeholder="Masukkan password Anda">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <button type="button" id="togglePassword" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="eyeIcon">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember_me" name="remember" type="checkbox" 
                           class="h-4 w-4 text-orange-500 focus:ring-orange-400 border-gray-300 rounded">
                    <label for="remember_me" class="ml-2 block text-sm text-gray-900">
                        Ingat saya
                    </label>
                </div>

                <div class="text-sm">
                    <a href="{{ route('password.request') }}" class="font-medium text-orange-600 hover:text-orange-500">
                        Lupa password?
                    </a>
                </div>
            </div>

            <!-- Login Button -->
            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-orange-300 to-pink-300 hover:from-orange-400 hover:to-pink-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-400 transition-all duration-200 transform hover:scale-105 shadow-lg">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-orange-200 group-hover:text-orange-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                    </span>
                    Masuk ke Aplikasi
                </button>
            </div>
        </form>
                           
        <!-- Footer -->
        <div class="text-center">
            <p class="text-sm text-gray-600">
                © {{ date('Y') }} Optik Melati. All rights reserved.
            </p>
        </div>
    </div>
</div>

<!-- JavaScript untuk toggle password dan force refresh -->
<script>
// Function untuk menampilkan logo setelah loading
function showLogo(img) {
    console.log('Logo berhasil dimuat dari:', img.src);
    const loader = document.getElementById('logoLoader');
    if (loader) {
        loader.style.display = 'none';
    }
    img.style.display = 'block';
    img.style.animation = 'fadeIn 0.5s ease-in';
}

// Function untuk mencoba logo alternatif
function tryAlternativeLogo(img) {
    const alternativePaths = [
        '/image/logologin.png',
        '/image/logoapp.png',
        '/image/optik-melati.png',
        '{{ asset("image/logologin.png") }}',
        '{{ asset("image/logoapp.png") }}',
        '{{ asset("image/optik-melati.png") }}'
    ];
    
    const currentSrc = img.src;
    const currentIndex = alternativePaths.indexOf(currentSrc);
    
    if (currentIndex < alternativePaths.length - 1) {
        // Coba path berikutnya
        img.src = alternativePaths[currentIndex + 1];
        console.log('Mencoba logo alternatif:', img.src);
    } else {
        // Semua path gagal, tampilkan fallback
        console.log('Semua logo gagal dimuat, menggunakan fallback');
        const loader = document.getElementById('logoLoader');
        if (loader) {
            loader.style.display = 'none';
        }
        img.style.display = 'none';
        const fallback = document.getElementById('fallbackLogo');
        if (fallback) {
            fallback.style.display = 'flex';
            fallback.style.animation = 'fadeIn 0.5s ease-in';
        }
    }
}

// Function untuk timeout loading
function handleLogoTimeout() {
    setTimeout(() => {
        const loader = document.getElementById('logoLoader');
        const logo = document.getElementById('logoImage');
        const fallback = document.getElementById('fallbackLogo');
        
        if (loader && loader.style.display !== 'none') {
            console.log('Logo loading timeout, menggunakan fallback');
            loader.style.display = 'none';
            if (logo) {
                logo.style.display = 'none';
            }
            if (fallback) {
                fallback.style.display = 'flex';
                fallback.style.animation = 'fadeIn 0.5s ease-in';
            }
        }
    }, 3000); // 3 detik timeout
}

// Function untuk inisialisasi logo
function initializeLogo() {
    const logoImage = document.getElementById('logoImage');
    const loader = document.getElementById('logoLoader');
    
    if (logoImage && loader) {
        // Cek apakah logo sudah dimuat
        if (logoImage.complete && logoImage.naturalHeight !== 0) {
            showLogo(logoImage);
        } else {
            // Mulai timeout
            handleLogoTimeout();
        }
    }
}

// Add CSS animation
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.9); }
        to { opacity: 1; transform: scale(1); }
    }
`;
document.head.appendChild(style);

// Initialize logo loading when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeLogo();
});

// Force refresh to prevent caching
(function() {
    // Force reload if coming from back/forward
    if (window.performance && window.performance.navigation.type === window.performance.navigation.TYPE_BACK_FORWARD) {
        window.location.reload(true);
    }
    
    // Add timestamp to prevent caching
    const timestamp = new Date().getTime();
    const currentUrl = window.location.href;
    if (currentUrl.indexOf('?') === -1) {
        window.history.replaceState(null, null, currentUrl + '?t=' + timestamp);
    }
})();

// Prevent form resubmission on refresh
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}

document.getElementById('togglePassword').addEventListener('click', function() {
    const password = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    
    if (password.type === 'password') {
        password.type = 'text';
        eyeIcon.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
        `;
    } else {
        password.type = 'password';
        eyeIcon.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
        `;
    }
});
</script>
@endsection