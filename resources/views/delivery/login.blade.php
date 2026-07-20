<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Login - Paint Store</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>
<div class="login-page">
    <div class="login-hero" style="background:linear-gradient(135deg,#1e3a5f,#1d4ed8);">
        <h1>Delivery<br>Partner Portal</h1>
        <p>Pick up packed orders and deliver to customers within 30 minutes.</p>
        <ul class="features">
            <li>View assigned deliveries</li>
            <li>Live delivery countdown timer</li>
            <li>Mark delivered & collect COD</li>
        </ul>
    </div>
    <div class="login-form-side">
        <div class="login-box">
            <h2>Delivery Sign In</h2>
            <p class="subtitle">Use credentials provided by admin</p>
            @if(isset($errors) && $errors->any())
                <div class="error">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="/delivery/login">
                @csrf
                <div class="form-group">
                    <label for="email">Email address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Sign In</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
