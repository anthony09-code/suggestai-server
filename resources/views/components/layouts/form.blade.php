<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Feedback' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --office-color: {{ $office->color }};
            --office-color-10: {{ $office->color }}1a;
            --office-color-30: {{ $office->color }}4d;
        }
    </style>
</head>
<body
    class="min-h-screen p-4 text-text"
    style="background-color: var(--office-color-10);"
>
    {{ $slot }}
</body>
</html>
