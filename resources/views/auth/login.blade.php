<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login POS — Dona Joana</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #1a100c;
            --card: rgba(255, 252, 248, 0.92);
            --ink: #1c1410;
            --muted: #6b5a4e;
            --accent: #3d2317;
            --accent-hover: #2a1710;
            --line: rgba(61, 35, 23, 0.18);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Outfit', sans-serif;
            color: var(--ink);
            background: var(--bg);
            overflow-x: hidden;
        }

        .scene {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            isolation: isolate;
        }

        /* Camada 1: imagem com fundo (cafe.png) — suave e ampla */
        .bg-photo {
            position: absolute;
            inset: 0;
            background:
                url('{{ asset('images/auth/cafe.png') }}') center center / cover no-repeat;
            opacity: 0.28;
            filter: saturate(1.05) contrast(1.05);
            z-index: 0;
            pointer-events: none;
        }

        /* Camada 2: xícara sem fundo — destaque transparente */
        .bg-cup {
            position: absolute;
            right: -4%;
            bottom: -8%;
            width: min(62vw, 720px);
            height: min(62vw, 720px);
            background:
                url('{{ asset('images/auth/cafe_sem_fundo.png') }}') center bottom / contain no-repeat;
            opacity: 0.42;
            z-index: 1;
            pointer-events: none;
            animation: floatCup 8s ease-in-out infinite;
        }

        .bg-cup--left {
            right: auto;
            left: -10%;
            bottom: auto;
            top: -6%;
            width: min(42vw, 420px);
            height: min(42vw, 420px);
            opacity: 0.22;
            transform: scaleX(-1);
            animation-delay: -3s;
        }

        /* Overlay para legibilidade */
        .bg-veil {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 70% at 50% 45%, rgba(26, 16, 12, 0.25) 0%, rgba(26, 16, 12, 0.72) 100%),
                linear-gradient(160deg, rgba(61, 35, 23, 0.35), rgba(26, 16, 12, 0.55));
            z-index: 2;
            pointer-events: none;
        }

        @keyframes floatCup {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-12px);
            }
        }

        .bg-cup--left.float-alt {
            animation-name: floatCupAlt;
        }

        @keyframes floatCupAlt {

            0%,
            100% {
                transform: scaleX(-1) translateY(0);
            }

            50% {
                transform: scaleX(-1) translateY(10px);
            }
        }

        .card {
            position: relative;
            z-index: 3;
            width: 100%;
            max-width: 400px;
            padding: 32px 28px 28px;
            border-radius: 20px;
            background: var(--card);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.55);
            box-shadow:
                0 24px 60px rgba(0, 0, 0, 0.35),
                0 2px 0 rgba(255, 255, 255, 0.4) inset;
        }

        .brand {
            margin: 0 0 6px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--muted);
        }

        h1 {
            margin: 0 0 8px;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--ink);
        }

        .subtitle {
            margin: 0 0 24px;
            font-size: 14px;
            color: var(--muted);
            line-height: 1.4;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--ink);
        }

        input {
            width: 100%;
            margin-top: 6px;
            margin-bottom: 14px;
            padding: 12px 14px;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.85);
            font: inherit;
            color: var(--ink);
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(61, 35, 23, 0.15);
        }

        button {
            width: 100%;
            margin-top: 8px;
            padding: 13px 16px;
            border: 0;
            border-radius: 12px;
            background: var(--accent);
            color: #fff;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.15s ease, transform 0.1s ease;
        }

        button:hover {
            background: var(--accent-hover);
        }

        button:active {
            transform: scale(0.99);
        }

        .error {
            margin-bottom: 14px;
            padding: 10px 12px;
            border-radius: 10px;
            background: rgba(185, 28, 28, 0.08);
            color: #b91c1c;
            font-size: 14px;
        }

        @media (max-width: 720px) {
            .bg-cup {
                width: 90vw;
                height: 90vw;
                opacity: 0.28;
                right: -20%;
            }

            .bg-cup--left {
                display: none;
            }

            .bg-photo {
                opacity: 0.22;
            }
        }
    </style>
</head>

<body>
    <div class="scene">
        <div class="bg-photo" aria-hidden="true"></div>
        <div class="bg-cup bg-cup--left float-alt" aria-hidden="true"></div>
        <div class="bg-cup" aria-hidden="true"></div>
        <div class="bg-veil" aria-hidden="true"></div>

        <div class="card">
            <p class="brand">Cafeteria Joana POS</p>
            <h1>LOGIN</h1>
            <p class="subtitle">O café nosso de cada dia!</p>

            @if ($errors->any())
                <div class="error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}">
                @csrf
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                    autocomplete="username">

                <label for="password">Senha</label>
                <input id="password" type="password" name="password" required autocomplete="current-password">

                <button type="submit">Entrar</button>
            </form>
        </div>
    </div>
</body>

</html>
