<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'CDRRMO DMS' }}</title>
    @vite(['resources/css/app.css'])
    @livewireStyles
</head>
<body class="bg-gray-50">
    {{ $slot }}
    @livewireScripts
</body>
</html>
