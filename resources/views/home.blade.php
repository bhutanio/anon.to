@extends('layouts.base')

@section('page_title')
@show

@section('content')
    <section class="container">
        <div class="block">
            <h1 class="page-title">Anonymous URL Shortener</h1>
            <p class="lead text-center">Create a secure anonymous short link from your url which also hides http referer!</p>
            {!! Form::open(['files'=>false, 'url'=>url('shorten'), 'id'=>'form_shortener', 'class' => '', 'role'=>'form']) !!}
            <div class="input-group" style="width: 100%;">
                {!! Form::text('url', null, ['class' => 'form-control input-lg', 'placeholder'=>'Paste a link to shorten it']) !!}
                <span class="input-group-btn">
                {!! Form::submit('Shorten', ['class'=>'btn btn-lg btn-primary']) !!}
                </span>
            </div>

            <div class="shorten-output">
                <div class="input-group short-url-group has-success hidden">
                    <span class="input-group-addon" id="sizing-addon1">Short URL: </span>
                    {!! Form::text('short_url', null, ['class' => 'form-control', 'readonly'=>'readonly']) !!}
                </div>
            </div>

            {!! Form::close() !!}
        </div>

        <div class="block">
            <h2 class="text-center">Anonymous Redirect</h2>
            <p class="lead">Do you want to link anonymously to other web sites without sending any referrer?</p>
            <p class="lead">Use <strong>{{ parse_url(env('APP_URL'), PHP_URL_HOST) }}</strong> to de-referer or null-referer your links.</p>
            <p class="lead">Just put <strong>{{ env('APP_URL') }}/?</strong> in front of your links. Eg:
                <strong>{{ env('APP_URL') }}/?http://www.google.com</strong></p>
        </div>

        <div class="row">
            <h2 class="text-center">Why use anon.to?</h2>
            <div class="col-sm-4">
                <div class="block block-box">
                    <i class="glyphicon glyphicon-lock"></i>
                    <p class="lead">We are SSL Secured.</p>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="block block-box">
                    <i class="glyphicon glyphicon-file"></i>
                    <p class="lead">We don't keep logs.</p>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="block block-box">
                    <i class="glyphicon glyphicon-link"></i>
                    <p class="lead">We hide your original referrer.</p>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('footer_js')
    <script>
        (function () {
            shortenUrl();
        })();
    </script>
@endsection