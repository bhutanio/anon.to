var fs = require('fs');
var gulp = require('gulp');
var elixir = require('laravel-elixir');

elixir(function (mix) {
    mix.sass([
        'app.scss'
    ], './public/css/style.css');

    mix.scripts([
        './bower_components/bootstrap-sass/assets/javascripts/bootstrap.min.js',
        './bower_components/sweetalert/dist/sweetalert.min.js',
        './resources/assets/js/**/*.js'
    ], './public/js/app.js');

    mix.scripts([
        './bower_components/html5shiv/dist/html5shiv.min.js',
        './bower_components/Respond/dest/respond.min.js'
    ], './public/js/html5shiv.respond.min.js');

    mix.copy('./bower_components/bootstrap-sass/assets/fonts/bootstrap', 'public/fonts/');

    mix.task('writeVersionFile');
});

gulp.task('writeVersionFile', function (cb) {
    var version = Math.floor(Date.now() / 1000);
    fs.writeFile('version.txt', version, cb);
});
