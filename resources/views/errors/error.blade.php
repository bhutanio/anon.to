@extends('layouts.base')

@section('content')
    <section class="container">
        <div class="jumbotron">
            <h1 class="mt-5 text-center">
                @section('error_title')
                    {{ meta()->pageTitle() }}: Page not found!
                @show
            </h1>
            <hr>
            @section('message')
            <p>The requested URL was not found on this server. Make sure that the Web site address displayed in the address bar of your browser is spelled and formatted correctly.</p>
            @show
            <p class="text-center">
                <a href="javascript:history.back()" class="btn btn-lg btn-info" title="Back to where ever you came from"><i class="glyphicon glyphicon-backward"></i> Go Back</a>
                <a href="{{ url('/') }}" class="btn btn-lg btn-primary" title="Go to Home Page"><i class="glyphicon glyphicon-home"></i> Go to Home Page</a>
            </p>
        </div>
    </section>
@endsection