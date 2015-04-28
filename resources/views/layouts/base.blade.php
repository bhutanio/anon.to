<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Anon.to - Anonymous URL Shortener</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat:400,700">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic">
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
                    <a href="#redirect">Redirect</a>
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
                    <h1 class="name">Anonymous URL Shortener</h1>
                    <hr class="star-light">
                    <span class="skills">Paste a link to shorten it</span>

                    @if(session('hash'))
                    <div class="alert alert-warning">
                        <p>Here is you short URL: <a href="{{ route('home') }}/{{ session('hash') }}">{{ route('home') }}/{{ session('hash') }}</a></p>
                    </div>
                    @endif
                    {!! $errors->first('url', '<div class="alert alert-danger">:message</div>') !!}
                    <form action="{{ route('shorten') }}" method="POST" class="form-inline">
                        <div class="form-group">
                            <label class="sr-only" for="exampleInputEmail3">URL</label>
                            <input type="url" name="url" class="form-control" id="url" placeholder="Enter URL">
                        </div>
                        <button type="submit" class="btn btn-info">Shorten</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</header>

<section id="redirect">
    <div class="container">
        <div class="row">
            <div class="col-sm-12 text-center">
                <h2>Anonymous Redirect</h2>
                <hr class="star-primary">
            </div>
            <div class="col-sm-12">
                <p>Do you want to link anonymously to other web sites without sending any referrer?</p>
                <p>Then use <strong>anon.to</strong></p>
                <p>Just put <strong>https://anon.to/?</strong> in front of your links.</p>
                <p>Example link: <strong>https://anon.to/?http://www.google.com</strong></p>
            </div>
        </div>
    </div>
</section>

<footer class="text-center">
    <div class="footer-above">
        <div class="container">
            <div class="row">
                <div class="footer-col col-md-6">
                    <h3>Contribute</h3>
                    <ul class="list-inline">
                        <li>
                            <a href="https://github.com/bhutanio/anon.to" class="btn-social btn-outline"><i class="fa fa-fw fa-github"></i></a>
                        </li>
                    </ul>
                </div>
                <div class="footer-col col-md-6">
                    <h3>About Anon.to</h3>
                    <p>Anon.to is a free to use, open source URL Shortener and Redirector, created by <a href="http://bhutan.io">bhutan.io</a></p>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-below">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    Copyright &copy; Anon.to
                </div>
            </div>
        </div>
    </div>
</footer>

<script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>