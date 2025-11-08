<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirect Warning - anon.to</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gradient-to-br from-indigo-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-900 dark:to-indigo-950">

    <div class="min-h-screen flex items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
        <div class="max-w-2xl w-full">
            @include('partials.redirect-warning', [
                'destinationUrl' => $destinationUrl,
                'parsed' => $parsed,
                'link' => $link
            ])
        </div>
    </div>
    @fluxScripts
</body>
</html>
