<nav class="navbar navbar-default navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="{{ url('/') }}" title="{{ env('SITE_NAME') }}">{{ env('SITE_NAME') }}</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li class="active" title="{{ env('SITE_NAME') }}"><a href="{{ url('/') }}">Home</a></li>
            </ul>

            <ul class="nav navbar-nav pull-right">
                <li class=""><a href="{{ url('report') }}">Report</a></li>
                <li class=""><a href="{{ url('about') }}">About</a></li>
            </ul>
        </div>
    </div>
</nav>