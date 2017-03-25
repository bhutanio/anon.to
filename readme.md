# anon.to
[anon.to](https://anon.to) is an anonymous URL redirector and shortener built using [Laravel](https://laravel.com/).

### Requirement
- [**PHP**](https://php.net) 5.5.9+ or [HHVM](http://hhvm.com) 3.3+
- PHP Extensions: openssl, mcrypt and mbstring
- Database server: [MySQL](https://www.mysql.com) or [**MariaDB**](https://mariadb.org)
- [Redis](http://redis.io) Server
- [Composer](https://getcomposer.org)
- [Node.js](https://nodejs.org/) with npm

### Installation
* clone the repository: `git clone https://github.com/bhutanio/anon.to.git anon.to`
* create a database
* create configuration env file `.env` refer to `.env.example`
* install: `composer install --no-dev`
* setup database tables: `php artisan migrate`

### License
anon.to is open source software licensed under the [MIT license](http://opensource.org/licenses/MIT).
