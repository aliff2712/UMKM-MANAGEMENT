<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - TechneFest UMKM</title>
    
    <!-- Fonts: Plus Jakarta Sans for Premium & Modern Look -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons for clean aesthetic -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        :root {
            --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #311042 100%);
            --primary: #8b5cf6;
            --primary-hover: #7c3aed;
            --primary-glow: rgba(139, 92, 246, 0.45);
            --accent: #d946ef;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --glass-bg: rgba(15, 23, 42, 0.65);
            --glass-border: rgba(255, 255, 255, 0.08);
            --input-bg: rgba(15, 23, 42, 0.8);
            --input-border: rgba(255, 255, 255, 0.1);
            --error: #f43f5e;
            --success: #10b981;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        body {
            background: var(--bg-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            position: relative;
        }

        /* Ambient Glowing Background Elements */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
            z-index: 1;
            opacity: 0.5;
            animation: floatOrb 12s ease-in-out infinite alternate;
        }

        .orb-1 {
            width: 400px;
            height: 400px;
            background: var(--primary);
            top: -10%;
            left: -10%;
        }

        .orb-2 {
            width: 500px;
            height: 500px;
            background: var(--accent);
            bottom: -15%;
            right: -10%;
            animation-delay: -4s;
        }

        .orb-3 {
            width: 300px;
            height: 300px;
            background: #3b82f6;
            top: 40%;
            left: 70%;
            animation-delay: -8s;
        }

        @keyframes floatOrb {
            0% {
                transform: translate(0, 0) scale(1);
            }
            100% {
                transform: translate(40px, 40px) scale(1.15);
            }
        }

        /* Container Layout & Glassmorphism */
        .wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 460px;
            padding: 24px;
        }

        .login-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.4s;
            animation: cardAppear 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .login-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 30px 60px -10px rgba(0, 0, 0, 0.6), 0 0 40px rgba(139, 92, 246, 0.15);
        }

        @keyframes cardAppear {
            0% {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Branding */
        .brand {
            text-align: center;
            margin-bottom: 32px;
        }

        .brand-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            border-radius: 16px;
            color: #fff;
            margin-bottom: 16px;
            box-shadow: 0 8px 20px rgba(139, 92, 246, 0.3);
            position: relative;
            overflow: hidden;
        }

        .brand-logo::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transform: rotate(45deg);
            animation: shine 4s ease-in-out infinite;
        }

        @keyframes shine {
            0% { transform: translate(-100%, -100%) rotate(45deg); }
            20%, 100% { transform: translate(100%, 100%) rotate(45deg); }
        }

        .brand h1 {
            font-size: 26px;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.5px;
            margin-bottom: 6px;
        }

        .brand p {
            font-size: 14px;
            color: var(--text-muted);
            font-weight: 500;
        }

        /* Forms Elements */
        .form-group {
            margin-bottom: 22px;
            position: relative;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #cbd5e1;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-container i {
            position: absolute;
            left: 16px;
            color: var(--text-muted);
            font-size: 18px;
            transition: color 0.3s;
            pointer-events: none;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 12px;
            color: var(--text-main);
            font-size: 15px;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            outline: none;
        }

        .form-input::placeholder {
            color: #475569;
        }

        .form-input:focus {
            border-color: var(--primary);
            background: rgba(15, 23, 42, 0.95);
            box-shadow: 0 0 0 4px var(--primary-glow);
        }

        .form-input:focus + i {
            color: var(--primary);
        }

        /* Eye toggle for password visibility */
        .password-toggle {
            position: absolute;
            right: 16px;
            cursor: pointer;
            color: var(--text-muted);
            transition: color 0.3s;
            background: none;
            border: none;
            outline: none;
            display: flex;
            align-items: center;
        }

        .password-toggle:hover {
            color: var(--text-main);
        }

        /* Checkbox & Remember Me */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
            font-size: 14px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            cursor: pointer;
            color: #cbd5e1;
            font-weight: 500;
            user-select: none;
        }

        .remember-me input {
            display: none;
        }

        .checkbox-custom {
            width: 18px;
            height: 18px;
            border: 2px solid var(--input-border);
            border-radius: 5px;
            margin-right: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            background: var(--input-bg);
        }

        .remember-me input:checked + .checkbox-custom {
            background: var(--primary);
            border-color: var(--primary);
            box-shadow: 0 0 10px var(--primary-glow);
        }

        .remember-me input:checked + .checkbox-custom::after {
            content: '';
            width: 5px;
            height: 9px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
            margin-bottom: 2px;
        }

        /* Button Styling */
        .btn-submit {
            width: 100%;
            padding: 14px 24px;
            background: linear-gradient(90deg, var(--primary) 0%, #a855f7 100%);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(139, 92, 246, 0.45);
            background: linear-gradient(90deg, var(--primary-hover) 0%, #9333ea 100%);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* Error/Alert Notification System */
        .alert {
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid transparent;
            animation: alertSlide 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.1) forwards;
        }

        .alert-danger {
            background: rgba(244, 63, 94, 0.15);
            border-color: rgba(244, 63, 94, 0.25);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            border-color: rgba(16, 185, 129, 0.25);
            color: #a7f3d0;
        }

        @keyframes alertSlide {
            0% {
                opacity: 0;
                transform: translateY(-10px) scale(0.98);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Footer styling */
        .login-footer {
            margin-top: 32px;
            text-align: center;
            font-size: 13px;
            color: var(--text-muted);
        }

        .login-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .login-footer a:hover {
            color: var(--accent);
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <!-- Ambient glowing bg orbs -->
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="wrapper">
        <div class="login-card">
            
            <div class="brand">
                <div class="brand-logo">
                    <i data-lucide="store" style="width: 28px; height: 28px;"></i>
                </div>
                <h1>TechneFest</h1>
                <p>Sistem Manajemen UMKM Pintar</p>
            </div>

            <!-- Error Alerts -->
            @if ($errors->any())
                <div class="alert alert-danger">
                    <i data-lucide="alert-circle" style="width: 18px; height: 18px; flex-shrink: 0;"></i>
                    <div>
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Success Alerts (e.g. after logout) -->
            @if (session('success'))
                <div class="alert alert-success">
                    <i data-lucide="check-circle" style="width: 18px; height: 18px; flex-shrink: 0;"></i>
                    <div>
                        <p>{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            <form action="{{ url('/login') }}" method="POST">
                @csrf

                <!-- Email field -->
                <div class="form-group">
                    <label class="form-label" for="email">Alamat Email</label>
                    <div class="input-container">
                        <input class="form-input" type="email" name="email" id="email" placeholder="nama@toko.com" value="{{ old('email') }}" required autofocus>
                        <i data-lucide="mail"></i>
                    </div>
                </div>

                <!-- Password field -->
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-container">
                        <input class="form-input" type="password" name="password" id="password" placeholder="••••••••" required>
                        <i data-lucide="lock"></i>
                        <button type="button" class="password-toggle" id="password-toggle">
                            <i data-lucide="eye" id="eye-icon"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember me options -->
                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span class="checkbox-custom"></span>
                        Ingat Saya
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit">
                    <span>Masuk ke Dashboard</span>
                    <i data-lucide="arrow-right" style="width: 18px; height: 18px;"></i>
                </button>
            </form>

            <div class="login-footer">
                <p>&copy; {{ date('Y') }} TechneFest. All rights reserved.</p>
            </div>

        </div>
    </div>

    <!-- Initialize Lucide Icons and Password Eye Toggle logic -->
    <script>
        // Init Lucide
        lucide.createIcons();

        // Password View Toggle logic
        const passwordInput = document.getElementById('password');
        const passwordToggle = document.getElementById('password-toggle');
        const eyeIcon = document.getElementById('eye-icon');

        passwordToggle.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Swap icon name dynamically & update lucide
            if (type === 'text') {
                eyeIcon.setAttribute('data-lucide', 'eye-off');
            } else {
                eyeIcon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        });
    </script>
</body>
</html>
