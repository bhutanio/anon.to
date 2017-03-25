<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="_token" content="{{ csrf_token() }}">
    <meta name="_base_url" content="{{ url('/') }}">
    <title>{{ meta()->metaTitle() }}</title>
    @if(meta()->description())
        <meta name="description" content="{{ meta()->description() }}">
    @endif
    <link rel="shortcut icon" href="{{ url('favicon.png') }}">
    <link href="{{ url(mix('css/app.css')) }}" rel="stylesheet">
    @yield('header_css')
</head>
<body>

<header>
    @include('layouts.blocks.nav')
</header>

<section class="container content" id="content-area">
    @include('layouts.blocks.notifications')
    @yield('content')
</section>

@include('layouts.blocks.footer')

<script src="{{ url(mix('js/app.js')) }}"></script>
@yield('footer_js')

<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
    ga('create', '{{ env('GOOGLE_ANALYTICS') }}', 'auto');
    ga('send', 'pageview');
</script>

</body>
</html>
