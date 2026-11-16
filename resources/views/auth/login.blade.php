<!DOCTYPE html>
<html lang="es-CO">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Ingresar · Panel Laura Rivera</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
</head>
<body class="admin login-page">
    <main class="login-card">
        <div class="login-head">
            <span class="brand-mark">LR</span>
            <div>
                <h1>Panel de administración</h1>
                <p>Laura Rivera</p>
            </div>
        </div>

        @if (session('status'))
            <div class="flash flash-ok" role="status">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}" class="form-stack" novalidate>
            @csrf
            <label class="field @error('email') has-error @enderror">
                <span>Correo</span>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
                @error('email')<span class="field-error">{{ $message }}</span>@enderror
            </label>
            <label class="field @error('password') has-error @enderror">
                <span>Contraseña</span>
                <input type="password" name="password" required autocomplete="current-password">
                @error('password')<span class="field-error">{{ $message }}</span>@enderror
            </label>
            <label class="check"><input type="checkbox" name="remember" value="1"> Mantener sesión iniciada</label>
            <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
        </form>
        <a href="{{ route('home') }}" class="login-back">← Volver al sitio</a>
    </main>
</body>
</html>
