@extends('layouts.app')

@section('content')
    <div class="block">
        <div class="jumbotron shadowed">
            <div class="container">
                <h1 class="text-center">
                    @section('error_title')
                        {{ meta()->pageTitle() }}: Page not found!
                    @show
                </h1>
                <div class="separator"></div>
                @section('error_message')
                    <p>The requested URL was not found on this server. Make sure that the Web site address displayed in the address bar of your browser is spelled and formatted correctly.</p>
                @show
                <p class="text-center">
                    @section('button_back')
                        <a href="javascript:history.back()" class="btn btn-lg btn-info" title="Back to where ever you came from"><i class="glyphicon glyphicon-chevron-left"></i> Go Back</a>
                    @show
                    @section('button_home')
                        <a href="{{ url('/') }}" class="btn btn-lg btn-primary" title="Go to Home Page"><i class="glyphicon glyphicon-home"></i> Go to Home Page</a>
                    @show
                </p>
            </div>
        </div>
    </div>
@endsection