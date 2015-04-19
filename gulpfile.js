var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Less
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function (mix) {
    mix.sass([
        'bootstrap.scss'
    ], 'resources/assets/vendor/css');

    mix.copy('bower_components/fontawesome/css/font-awesome.css', 'resources/assets/vendor/css/font-awesome.css');
    mix.copy('bower_components/jquery/dist/jquery.js', 'resources/assets/vendor/js/jquery.js');
    mix.copy('bower_components/bootstrap-sass/assets/javascripts/bootstrap.js', 'resources/assets/vendor/js/bootstrap.js');

    mix.styles([
        'assets/vendor/css/bootstrap.css',
        'assets/vendor/css/font-awesome.css',
        'assets/css/**/*.css'
    ], 'public/assets/css/style.css','resources');

    mix.scripts([
        'assets/vendor/js/jquery.js',
        'assets/vendor/js/bootstrap.js',
        'assets/js/**/*.js'
    ], 'public/assets/js/app.js','resources');

    mix.copy('bower_components/bootstrap-sass/assets/fonts/bootstrap', 'public/assets/fonts');
    mix.copy('bower_components/fontawesome/fonts/', 'public/assets/fonts');
});
