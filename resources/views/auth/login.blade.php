<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login POS</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; }
        .container { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { background: #fff; padding: 24px; border-radius: 8px; width: 100%; max-width: 360px; box-shadow: 0 8px 24px rgba(0,0,0,.08); }
        h1 { margin: 0 0 16px; font-size: 20px; }
        input { width: 100%; padding: 10px; margin-top: 8px; margin-bottom: 12px; border: 1px solid #d1d5db; border-radius: 6px; }
        button { width: 100%; padding: 10px; border: 0; border-radius: 6px; background: #111827; color: #fff; cursor: pointer; }
        .error { color: #b91c1c; font-size: 14px; margin-bottom: 8px; }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <h1>Acesso ao POS</h1>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}">
            @csrf
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required>

            <label for="password">Senha</label>
            <input id="password" type="password" name="password" required>

            <button type="submit">Entrar</button>
        </form>
    </div>
</div>
</body>
</html>
