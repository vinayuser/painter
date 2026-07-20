<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Login - Paint Store</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>
<div class="login-page">
    <div class="login-hero" style="background:linear-gradient(135deg,#14532d,#166534);">
        <h1>Vendor<br>Portal</h1>
        <p>Manage your products and fulfill customer orders on the Paint Store marketplace.</p>
        <ul class="features">
            <li>Add products to platform categories</li>
            <li>Pack orders within 30 minutes</li>
            <li>Track active and past orders</li>
        </ul>
    </div>
    <div class="login-form-side">
        <div class="login-box">
            <h2>Vendor Sign In</h2>
            <p class="subtitle">Use credentials provided by admin</p>
            @if(isset($errors) && $errors->any())
                <div class="error">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="/vendor/login">
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
