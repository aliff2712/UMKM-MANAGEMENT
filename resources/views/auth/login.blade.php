<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Modern - Login</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body {
            background-color: #FAFAFA;
        }
        @keyframes flashSuccess {
            0%, 100% { background-color: #FFFFFF; }
            50% { background-color: #EFF6FF; }
        }
        /* Animasi goyang untuk menarik perhatian saat ada error */
        @keyframes shakeError {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-6px); }
            40%, 80% { transform: translateX(6px); }
        }
        .animate-flash {
            animation: flashSuccess 0.4s ease-in-out;
        }
        .animate-shake {
            animation: shakeError 0.4s ease-in-out;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4 select-none">

    <div class="w-full max-w-md text-center" x-data="loginForm()" x-init="$nextTick(() => isLoaded = true)">
        
        <!-- Header -->
        <div class="mb-8 flex flex-col items-center"
             x-show="isLoaded"
             x-transition:enter="transition ease-out duration-700"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0">
            <div class="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-200 mb-4 transition-all duration-300 transform hover:scale-110 hover:rotate-3 cursor-pointer group">
                <i class="fa-solid fa-cart-shopping text-white text-2xl group-hover:animate-bounce"></i>
            </div>
            <h1 class="text-3xl font-bold text-blue-900 tracking-tight">POS Modern</h1>
            <p class="text-gray-500 text-sm mt-1">Sistem Kasir Premium</p>
        </div>

        <!-- Card Login -->
        <div class="bg-white rounded-3xl p-8 shadow-xl shadow-gray-100 border border-gray-50 text-left relative overflow-hidden"
             x-show="isLoaded"
             x-transition:enter="transition ease-out duration-700 delay-100"
             x-transition:enter-start="opacity-0 translate-y-6"
             x-transition:enter-end="opacity-100 translate-y-0">
            
            <!-- Tampilan Pesan Error yang Dipercantik -->
            @if ($errors->any())
                <div class="mb-5 p-4 bg-red-50/80 border border-red-100 text-red-700 rounded-2xl text-sm flex items-start gap-3 animate-shake shadow-sm"
                     x-data="{ show: true }"
                     x-show="show"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95">
                    <div class="flex-shrink-0 w-8 h-8 bg-red-100 rounded-xl flex items-center justify-center text-red-600 mt-0.5">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div class="flex-1 leading-relaxed">
                        <span class="font-semibold block text-red-900 mb-0.5">Gagal Masuk</span>
                        {{ $errors->first() }}
                    </div>
                    <button type="button" @click="show = false" class="text-red-400 hover:text-red-600 p-1 rounded-lg transition-colors cursor-pointer">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" @submit="handleSubmit($event)">
                @csrf
                
                <!-- Email Field -->
                <div class="mb-5">
                    <label class="block text-gray-700 text-sm font-medium mb-2">Email</label>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 group-focus-within:text-blue-500 transition-colors duration-300">
                            <i class="fa-regular fa-envelope"></i>
                        </span>
                        <input 
                            type="email" 
                            name="email" 
                            x-model="email"
                            :class="{'animate-flash': isFlashing}"
                            placeholder="nama@email.com" 
                            class="w-full pl-11 pr-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-300 text-gray-700"
                            required
                        >
                    </div>
                </div>

                <!-- Password Field -->
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-medium mb-2">Password</label>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 group-focus-within:text-blue-500 transition-colors duration-300">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input 
                            :type="showPassword ? 'text' : 'password'" 
                            name="password" 
                            x-model="password"
                            :class="{'animate-flash': isFlashing}"
                            placeholder="••••••••" 
                            class="w-full pl-11 pr-12 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-300 text-gray-700"
                            required
                        >
                        <button 
                            type="button" 
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 hover:text-blue-600 transition-colors duration-200"
                        >
                            <i class="fa-regular transition-transform duration-200" :class="showPassword ? 'fa-eye-slash scale-105 text-blue-500' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>

                <!-- Button Login -->
                <button 
                    type="submit" 
                    :disabled="isLoading || isEmpty"
                    :class="isEmpty ? 'opacity-60 cursor-not-allowed bg-gray-400 shadow-none' : 'bg-blue-600 hover:bg-blue-700 shadow-lg shadow-blue-200 hover:shadow-blue-300 active:scale-[0.98]'"
                    class="w-full text-white font-medium py-3 rounded-xl transition-all duration-300 flex items-center justify-center gap-2 cursor-pointer"
                >
                    <template x-if="isLoading">
                        <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <span x-text="isLoading ? 'Menghubungkan...' : 'Login'"></span>
                </button>
            </form>

            <!-- Quick Login Section -->
            <div class="mt-8 text-center"
                 x-show="isLoaded"
                 x-transition:enter="transition ease-out duration-700 delay-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100">
                <p class="text-xs text-gray-400 font-medium tracking-wide mb-3">Demo Login Cepat:</p>
                <div class="flex justify-center gap-2">
                    <button 
                        @click="quickLogin('kasir@toko.com', 'password')"
                        class="px-5 py-2 bg-blue-50 hover:bg-blue-100 text-blue-600 text-xs font-semibold rounded-lg transition-all duration-200 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer"
                    >
                        Kasir
                    </button>
                    <button 
                        @click="quickLogin('admin@toko.com', 'password')"
                        class="px-5 py-2 bg-purple-50 hover:bg-purple-100 text-purple-600 text-xs font-semibold rounded-lg transition-all duration-200 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer"
                    >
                        Admin
                    </button>
                    <button 
                        @click="quickLogin('owner@toko.com', 'password')"
                        class="px-5 py-2 bg-amber-50 hover:bg-amber-100 text-amber-600 text-xs font-semibold rounded-lg transition-all duration-200 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer"
                    >
                        Owner
                    </button>
                </div>
            </div>

        </div>
    </div>

    <script>
        function loginForm() {
            return {
                isLoaded: false,
                isLoading: false,
                isFlashing: false,
                email: '',
                password: '',
                showPassword: false,
                
                get isEmpty() {
                    return this.email.trim() === '' || this.password.trim() === '';
                },

                quickLogin(demoEmail, demoPassword) {
                    this.email = '';
                    this.password = '';
                    this.isFlashing = true;
                    
                    let i = 0;
                    const type = () => {
                        if (i < demoEmail.length) {
                            this.email += demoEmail.charAt(i);
                            i++;
                            setTimeout(type, 15);
                        } else {
                            this.password = demoPassword;
                            setTimeout(() => this.isFlashing = false, 400);
                        }
                    };
                    type();
                },

                handleSubmit(e) {
                    e.preventDefault();
                    if (this.isEmpty) return;

                    this.isLoading = true;

                    setTimeout(() => {
                        e.target.submit();
                    }, 1000);
                }
            }
        }
    </script>
</body>
</html>