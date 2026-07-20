<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <title>Admin Login — Paint Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin-login.css') }}">
</head>
<body class="admin-login-body">

<div class="admin-login-shell">

    {{-- Brand / info panel --}}
    <aside class="admin-login-brand" aria-hidden="false">
        <div class="paint-blob paint-blob-1"></div>
        <div class="paint-blob paint-blob-2"></div>
        <div class="paint-blob paint-blob-3"></div>

        <div class="brand-content">
            <div class="brand-logo">
                <div class="brand-logo-icon"><i class="fas fa-palette"></i></div>
                <div class="brand-logo-text">
                    Paint Store
                    <span>Admin Control Panel</span>
                </div>
            </div>

            <h1 class="brand-headline">
                Manage your entire<br><em>paint marketplace</em>
            </h1>
            <p class="brand-desc">
                One dashboard for products, multi-vendor orders, delivery tracking,
                painter bookings, and payment reports — all in real time.
            </p>

            <div class="feature-grid">
                <div class="feature-card">
                    <i class="fas fa-box"></i>
                    <strong>Orders & Inventory</strong>
                    <span>Track stock & packing status</span>
                </div>
                <div class="feature-card">
                    <i class="fas fa-truck"></i>
                    <strong>Delivery & Vendors</strong>
                    <span>Multi-vendor fulfilment</span>
                </div>
                <div class="feature-card">
                    <i class="fas fa-paint-roller"></i>
                    <strong>Painter Bookings</strong>
                    <span>Assign & monitor jobs</span>
                </div>
                <div class="feature-card">
                    <i class="fas fa-chart-line"></i>
                    <strong>Revenue Analytics</strong>
                    <span>COD & online payments</span>
                </div>
            </div>

            <div class="brand-stats">
                <div class="brand-stat">
                    <strong>12+</strong>
                    <span>Admin modules</span>
                </div>
                <div class="brand-stat">
                    <strong>24/7</strong>
                    <span>Order monitoring</span>
                </div>
                <div class="brand-stat">
                    <strong>100%</strong>
                    <span>Secure access</span>
                </div>
            </div>
        </div>
    </aside>

    {{-- Login form --}}
    <main class="admin-login-form-wrap">
        <div class="login-card">
            <div class="login-card-mobile-logo">
                <div class="brand-logo-icon" style="width:44px;height:44px;font-size:1.1rem;border-radius:12px;">
                    <i class="fas fa-palette"></i>
                </div>
                <div>
                    <strong style="font-size:1.1rem;color:#0f172a;">Paint Store</strong>
                    <div style="font-size:0.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.06em;">Admin Panel</div>
                </div>
            </div>

            <div class="login-welcome">
                <h2>Welcome back</h2>
                <p>Sign in with your administrator credentials</p>
            </div>

            @if(session('status'))
                <div class="login-alert" role="alert" style="background:#ecfdf5;border-color:#a7f3d0;color:#065f46;">
                    <i class="fas fa-info-circle"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if(isset($errors) && $errors->any())
                <div class="login-alert" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login') }}" novalidate id="admin-login-form">
                @csrf

                <div class="login-field">
                    <label for="email">Email address</label>
                    <div class="login-input-wrap">
                        <i class="fas fa-envelope field-icon"></i>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="admin@paintstore.com"
                            required
                            autofocus
                            autocomplete="email"
                        >
                    </div>
                </div>

                <div class="login-field">
                    <label for="password">Password</label>
                    <div class="login-input-wrap">
                        <i class="fas fa-lock field-icon"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            required
                            autocomplete="current-password"
                        >
                        <button type="button" class="toggle-password" id="togglePassword" aria-label="Show password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="login-options">
                    <label class="login-remember">
                        <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                        Remember me
                    </label>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign in to Dashboard
                </button>
            </form>

            <p class="login-footer-note">
                <i class="fas fa-shield-alt"></i>
                Restricted to administrators — customers use the mobile app
            </p>
        </div>
    </main>

</div>

<script>
(function () {
    // Chrome/Firefox back-forward cache can restore a login page with an expired CSRF token.
    window.addEventListener('pageshow', function (event) {
        if (event.persisted) {
            window.location.reload();
        }
    });

    const toggle = document.getElementById('togglePassword');
    const input = document.getElementById('password');
    if (!toggle || !input) return;

    toggle.addEventListener('click', function () {
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        toggle.innerHTML = isPassword
            ? '<i class="fas fa-eye-slash"></i>'
            : '<i class="fas fa-eye"></i>';
        toggle.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
    });
})();
</script>
</body>
</html>
