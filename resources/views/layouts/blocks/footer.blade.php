<footer>
    <div class="footer-top">
        <div class="container footer-top">
            <div class="row">
                <div class="col-md-6 footer-col text-center">
                    <h4 class="text-danger">GitHub <a href="https://github.com/bhutanio/anon.to" title="Anon.to GitHub Repo" target="_blank">Repo</a></h4>
                    <p>Copyright &copy; {{ env('SITE_NAME') }}</p>
                    <ul class="list-inline">
                        <li><a href="{{ url('about') }}" title="About">About</a></li>
                        <li><a href="{{ url('about/terms') }}" title="Terms of Service">Terms</a></li>
                        <li><a href="{{ url('about/privacy-policy') }}" title="Privacy Policy">Privacy</a></li>
                    </ul>
                </div>
                <div class="col-md-6 footer-col text-center">
                    <h4 class="text-danger"><a href="{{ url('about') }}">About</a> {{ env('SITE_NAME') }}</h4>
                    <p>{{ env('SITE_NAME') }} is a free to use, open source, Anonymous URL Shortener and Redirect service, created by
                        <a href="http://bhutan.io" title="Bhutan.io">bhutan.io</a></p>
                </div>
            </div>
        </div>
    </div>
</footer>