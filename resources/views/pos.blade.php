<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cafeteria Joana POS</title>
    @vite(['resources/css/app.css', 'resources/js/pos/main.js'])
</head>

<body class="antialiased">
    <div id="app" data-user-name="{{ auth()->user()->name }}" data-csrf-token="{{ csrf_token() }}"></div>
</body>

</html>
