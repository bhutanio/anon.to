<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Anon.to</title>
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Montserrat:400,700">
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}"/>
</head>
<body id="page-top">

<nav class="navbar navbar-default navbar-fixed-top">
    <div class="container">
        <div class="navbar-header page-scroll">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#page-top">{{ config('settings.site_name') }}</a>
        </div>

        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav navbar-right">
                <li class="hidden">
                    <a href="#page-top"></a>
                </li>
                <li class="page-scroll">
                    <a href="#about">About</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<header>
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="intro-text">
                    <span class="name">Anonymous URL Shortener</span>
                    <hr class="star-light">
                    <span class="skills">Paste a link to shorten it</span>

                    <form method="post" class="form-inline">
                        <div class="form-group">
                            <label class="sr-only" for="exampleInputEmail3">URL</label>
                            <input type="url" name="url" class="form-control col-sm-6" id="url" placeholder="Enter URL">
                        </div>
                        <button type="submit" class="btn btn-default">Shorten</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</header>

<!-- Contact Section -->
<section id="about">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                @yield('content')
            </div>
        </div>
    </div>
</section>

<script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>