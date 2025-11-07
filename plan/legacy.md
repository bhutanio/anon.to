# Legacy Code Analysis: anon.to (Laravel 5.4)

**Branch:** `origin/laravel-5.4`
**Laravel Version:** 5.4
**PHP Version:** ≥5.6.4 (7.0 preferred)
**Last Updated:** ce62e6a (asset update)

This document provides a comprehensive analysis of the original anon.to implementation, serving as a reference for understanding the legacy codebase and informing the Laravel 12 modernization.

---

## Table of Contents

1. [Project Overview](#project-overview)
2. [Architecture & Design Philosophy](#architecture--design-philosophy)
3. [Core Features](#core-features)
4. [Database Schema](#database-schema)
5. [Request Flow Analysis](#request-flow-analysis)
6. [Controllers & Services](#controllers--services)
7. [Frontend Implementation](#frontend-implementation)
8. [Security & Validation](#security--validation)
9. [Caching Strategy](#caching-strategy)
10. [Configuration](#configuration)
11. [Deployment & Operations](#deployment--operations)
12. [Key Files Reference](#key-files-reference)
13. [Migration Considerations](#migration-considerations)

---

## Project Overview

**anon.to** is an anonymous URL shortener and de-referrer service built on Laravel 5.4. It provides two primary services:

1. **URL Shortening**: Convert long URLs into 6-character short codes
2. **Anonymous Redirect**: Strip HTTP referrer headers using intermediate redirect page

The service emphasizes:
- Privacy (no logs, SSL secured)
- Performance (Redis caching, CDN-ready)
- Simplicity (minimal UI, fast redirects)

---

## Architecture & Design Philosophy

### Design Decisions

1. **URL Component Storage**: URLs are stored as parsed components (scheme, host, port, path, query, fragment) rather than complete strings
   - **Why**: Enables efficient duplicate detection across all URL parts
   - **Trade-off**: More complex queries but better data integrity

2. **6-Character Hash**: Fixed-length alphanumeric hashes with word exclusion
   - **Why**: Predictable URLs, prevents offensive words
   - **Excluded Words**: ~600 common English words (see `excluded_words()` in helpers.php)

3. **Intermediate Redirect Page**: Shows preview before final redirect (1 second delay)
   - **Why**: Strips referrer, provides user awareness, allows analytics
   - **Implementation**: Meta refresh + JavaScript for fragment handling

4. **Anonymous First**: Default user (ID=1) for unauthenticated links
   - **Why**: No registration required, privacy-focused
   - **Admin User**: Hard-coded as ID=2

5. **Redis-Heavy Caching**: 24-hour cache for link lookups
   - **Why**: Reduces database load on popular links
   - **Implementation**: Cache key = hash, value = reconstructed URL

### Tech Stack

**Backend**:
- Laravel 5.4 (PHP framework)
- MySQL/MariaDB (persistent storage)
- Redis (cache, sessions, queues)
- GeoIP2 2.7 (geolocation)
- Google reCAPTCHA 1.1 (bot protection)
- Guzzle 6.3 (HTTP client)

**Frontend**:
- Bootstrap 3 (CSS framework)
- jQuery (AJAX, DOM manipulation)
- Laravel Collective (Form/HTML helpers)
- SweetAlert2 (modals)
- Webpack Mix (asset compilation)

**Infrastructure**:
- Cloudflare-ready (CF-Connecting-IP support)
- Supervisor (queue workers)
- Cron jobs (cleanup tasks)

---

## Core Features

### 1. URL Shortening

**Endpoint**: `POST /shorten`
**Middleware**: `ajax`, `throttle:20,1`
**Rate Limit**: 20 requests per minute per IP

**Flow**:
1. Validate URL (required, valid URL format)
2. Parse URL into components
3. Check if URL matches APP_URL → return as-is if self-referential
4. Check database for existing URL by all components
5. If exists, return existing hash
6. If new:
   - Generate 6-char random hash
   - Check hash uniqueness (retry if collision)
   - Store in database
   - Cache for 24 hours
7. Return short URL: `{APP_URL}/{hash}`

**Key Code**:
```php
// LinksController::urlExists()
// Checks ALL URL components for exact match:
$link = Links::where('url_scheme', $url['scheme'])
    ->where('url_host', $url['host'])
    ->whereNull('url_port')  // or ->where('url_port', $port)
    ->whereNull('url_path')  // or ->where('url_path', $path)
    ->whereNull('url_query') // or ->where('url_query', $query)
    ->whereNull('url_fragment') // or ->where('url_fragment', $fragment)
    ->first();
```

### 2. Anonymous Redirect

**Two Methods**:

#### Method A: Short URL Redirect
**Endpoint**: `GET /{hash}` (6-char alphanumeric)
**Route File**: `routes/static.php`
**Pattern**: `[A-Za-z0-9]{6}`

**Flow**:
1. Check Redis cache for hash
2. If cache miss:
   - Query `links` table by hash
   - If found, cache for 24 hours
   - If not found, return 404
3. Reconstruct URL from components
4. Display `anonymous.blade.php` with:
   - Meta refresh: 1 second delay
   - noreferrer/nofollow links
   - Google Analytics tracking
   - JavaScript fragment handler

#### Method B: Direct Redirect
**Endpoint**: `GET /?{url}`
**Example**: `https://anon.to/?http://example.com`

**Flow**:
1. HomeController checks `QUERY_STRING` for valid URL
2. If valid URL, calls `anonymousRedirect()` directly
3. If not URL, shows homepage

**Key Code**:
```php
// HomeController::index()
$url = $this->request->server('QUERY_STRING');
if (is_valid_url($url)) {
    return app(RedirectController::class)->anonymousRedirect($url);
}
return view('home');
```

### 3. User Management

**Authentication**: Laravel built-in Auth with email activation

**Features**:
- Registration with email verification
- Password reset
- View personal links (`/my`)
- Search links by hash, domain, or path

**Admin Features** (User ID = 2):
- View all links (`/admin/links`)
- Delete any link
- View reported links (`/admin/reports`)

### 4. Link Reporting

**Endpoint**: `GET/POST /report`

**Flow**:
1. User submits URL, email, comment
2. Google reCAPTCHA validation
3. Check if URL exists in system (via hash or full URL)
4. Prevent duplicate reports per link
5. Store report with IP address
6. Email notification to admin
7. Admin reviews at `/admin/reports`

### 5. Static Pages CMS

**Endpoint**: `/about`, `/about/terms`, `/about/privacy-policy`

**Implementation**:
- Content stored in `contents` table
- Seeded with default legal pages
- Editable via database
- Uses `title_slug` for routing

---

## Database Schema

### `links` Table
Primary storage for shortened URLs.

```sql
CREATE TABLE `links` (
  `id` bigint UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `hash` varchar(191) UNIQUE NOT NULL,
  `url_scheme` varchar(191) NOT NULL,
  `url_host` text NOT NULL,
  `url_port` text NULL,
  `url_path` text NULL,
  `url_query` text NULL,
  `url_fragment` text NULL,
  `created_by` int UNSIGNED NULL,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  INDEX `links_created_by_index` (`created_by`)
);
```

**Key Points**:
- `hash`: 6-character unique identifier
- URL split into components for precise duplicate detection
- `created_by`: 1 for anonymous, actual user_id for authenticated
- No expiration/TTL - links are permanent

### `link_reports` Table
Stores user-submitted reports of problematic links.

```sql
CREATE TABLE `link_reports` (
  `id` bigint UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `link_id` bigint UNSIGNED NOT NULL,
  `url` text NOT NULL,
  `email` varchar(191) NOT NULL,
  `comment` text NOT NULL,
  `ip_address` varchar(191) NULL,
  `created_by` int UNSIGNED NOT NULL,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  INDEX `link_reports_link_id_index` (`link_id`),
  INDEX `link_reports_created_by_index` (`created_by`)
);
```

**Key Points**:
- One report per link (enforced in controller)
- Stores reporter's email and IP
- `url` denormalized for quick admin review

### `contents` Table
Simple CMS for static pages.

```sql
CREATE TABLE `contents` (
  `id` int UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `title` varchar(191) NOT NULL,
  `title_slug` varchar(191) UNIQUE NOT NULL,
  `content` mediumtext NOT NULL,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL
);
```

**Seeded Pages**:
- `about`: About page
- `terms`: Terms of Service
- `privacy-policy`: Privacy Policy

### `users` Table
Standard Laravel authentication.

```sql
-- Laravel 5.4 default users table
-- Notable: User ID 1 = anonymous, User ID 2 = admin
```

### `user_activations` Table
Email activation tokens.

```sql
CREATE TABLE `user_activations` (
  `user_id` int UNSIGNED PRIMARY KEY,
  `token` varchar(191) NOT NULL,
  `created_at` timestamp NULL
);
```

---

## Request Flow Analysis

### URL Shortening Flow

```
User submits form
    ↓
[AJAX] POST /shorten
    ↓
Middleware: ajax, throttle:20,1
    ↓
ShortenLinkController::shorten()
    ↓
Validate URL format
    ↓
UrlServices::parseUrl()
    ↓
Check if self-referential → return original
    ↓
LinksController::urlExists()
    - Query by all URL components
    ↓
If exists: return existing hash
    ↓
If new:
    ↓
LinksController::generateHash()
    - Str::random(6)
    - Check excluded_words()
    - Check uniqueness (retry if collision)
    ↓
Links::create()
    ↓
Cache::put($hash, $url, 60*24)
    ↓
Return JSON: {url: "https://anon.to/{hash}"}
    ↓
[JavaScript] Display short URL in form
```

### Redirect Flow (Short URL)

```
User visits /{hash}
    ↓
Route: routes/static.php
    Pattern: [A-Za-z0-9]{6}
    ↓
RedirectController::redirect($key)
    ↓
Cache::get($key)
    ↓
If cache hit:
    - Return anonymousRedirect($url)
    ↓
If cache miss:
    - LinksController::hashExists($key)
    - Query links table
    ↓
If found:
    - Cache for 24 hours
    - UrlServices::unParseUrlFromDb()
    - Return anonymousRedirect($url)
    ↓
If not found:
    - abort(404, 'Link not found!')
    ↓
anonymousRedirect($url):
    - Return view('anonymous', compact('url'))
    - Headers: Cache-Control: 3600s
    - Expires: +1 hour
    ↓
[Browser]
    - Meta refresh: 1 second
    - JavaScript: Handle URL fragments
    - nofollow/noreferrer links
    - Google Analytics tracking
    ↓
Final redirect to destination
```

### Redirect Flow (Direct URL)

```
User visits /?http://example.com
    ↓
Route: routes/web.php → HomeController
    ↓
HomeController::index()
    ↓
$url = request()->server('QUERY_STRING')
    ↓
is_valid_url($url) → true
    ↓
app(RedirectController::class)->anonymousRedirect($url)
    ↓
[Same as above: anonymous.blade.php]
```

---

## Controllers & Services

### LinksController (Base)
**Path**: `app/Http/Controllers/LinksController.php`
**Type**: Abstract base controller
**Purpose**: Shared link management logic

**Key Methods**:
- `hashExists($hash)`: Query by hash
- `urlExists(array $url)`: Check all URL components for duplicates
- `createUrlHash($parsed_url)`: Generate unique hash and store
- `cacheLink($link)`: Cache link for 24 hours
- `generateHash()`: Create 6-char hash, avoid excluded words

**Design Pattern**: Template method - child controllers extend for specific features

### ShortenLinkController
**Path**: `app/Http/Controllers/ShortenLinkController.php`
**Extends**: LinksController
**Route**: `POST /shorten`

**Responsibilities**:
- Validate URL input
- Parse URL components
- Check for self-referential URLs
- Coordinate with parent class for duplicate check/creation
- Return JSON response

**Validation Rules**:
```php
[
    'url' => 'required|url',
]
```

**Custom Messages**:
```php
[
    'url.required' => 'Please paste a link to shorten',
    'url.url' => 'Link must be a valid url starting with http:// or https://',
]
```

### RedirectController
**Path**: `app/Http/Controllers/RedirectController.php`
**Extends**: LinksController
**Route**: `GET /{key}`

**Key Methods**:
- `redirect($key)`: Main redirect logic with caching
- `anonymousRedirect($url)`: Display intermediate page

**Caching Strategy**:
- Check cache first (60*24 minutes = 24 hours)
- Populate cache on miss
- Cache value: reconstructed URL string

**HTTP Headers**:
```php
->setExpires(Carbon::now()->addHours(1))
->header('Cache-Control', 'public,max-age=3600,s-maxage=3600')
```

### HomeController
**Path**: `app/Http/Controllers/HomeController.php`
**Routes**: `GET /` (both web.php and static.php)

**Dual Purpose**:
1. Direct URL redirect: `/?{url}` → anonymous redirect
2. Homepage: Show shortener form

**Logic**:
```php
$url = $this->request->server('QUERY_STRING');
if (is_valid_url($url)) {
    return app(RedirectController::class)->anonymousRedirect($url);
}
return view('home');
```

### ReportLinkController
**Path**: `app/Http/Controllers/ReportLinkController.php`
**Routes**: `GET /report`, `POST /report`

**Custom Validation**:
- `anon_url`: Checks if URL exists in system
- `recaptcha`: Validates Google reCAPTCHA response

**Flow**:
1. Parse submitted URL
2. Find link (by hash or full URL)
3. Check for duplicate report
4. Store report with IP
5. Email admin
6. Redirect with flash message

### MyLinksController
**Path**: `app/Http/Controllers/My/MyLinksController.php`
**Routes**: `GET /my`, `GET /admin/links`, `DELETE /delete/link`

**Features**:
- Pagination (50 per page)
- Search by: hash, domain, path
- User-specific links OR all links (admin)
- Delete functionality (admin only)

**Admin Check**:
```php
if (Auth::id() == 2 && $this->request->is('admin/*')) {
    // Show all links
} else {
    // Show user's links only
}
```

### StaticPagesController
**Path**: `app/Http/Controllers/StaticPagesController.php`

**CMS Integration**:
- Loads content from `contents` table
- Uses `title_slug` for lookup
- Falls back to empty content if not found

### UrlServices
**Path**: `app/Services/UrlServices.php`
**Type**: Service class (injected)

**Key Methods**:

1. **parseUrl($url)**
   ```php
   // Returns array with defaults for all components
   [
       "scheme" => "https",
       "host" => "example.com",
       "port" => null,
       "path" => "/path",
       "query" => "foo=bar",
       "fragment" => "section"
   ]
   // Normalizes: "/" path becomes null
   ```

2. **unParseUrl(array $parsed)**
   ```php
   // Reconstructs URL from components
   // Ensures path is "/" if empty
   // Adds port, query, fragment if present
   ```

3. **unParseUrlFromDb(Links $link)**
   ```php
   // Converts Link model to URL string
   // Wraps unParseUrl() with model attributes
   ```

### MetaDataService
**Path**: `app/Services/MetaDataService.php`
**Type**: Singleton service

**Purpose**: Centralized SEO meta tag management

**Features**:
- Page title
- Meta title (browser tab)
- Meta description
- Canonical URL
- Favicon
- Pagination-aware (appends "Page X")

**Usage**:
```php
meta()->setMeta('My Links');
meta()->metaTitle(); // Returns: "My Links - Anon.to"
```

---

## Frontend Implementation

### JavaScript Architecture

**File**: `resources/assets/js/app.js`
**Build**: Webpack Mix

**Key Functions**:

1. **shortenUrl()**
   - Attaches to `#form_shortener`
   - AJAX form submission
   - Error handling with Bootstrap styles
   - Loading spinner during request
   - Displays short URL on success

2. **deleteLink()**
   - SweetAlert2 confirmation modal
   - AJAX DELETE request
   - Admin-only functionality

**AJAX Pattern**:
```javascript
// Fetch fresh CSRF token before each request
$.ajax({
    url: BASEURL + '/csrf',
    type: 'GET'
}).done(function (data) {
    $('input[name="_token"]').val(data);
});
```

### Views Structure

**Layout**: `resources/views/layouts/app.blade.php`

**Key Features**:
- Meta tags (CSRF, base URL, SEO)
- Google Analytics integration
- Bootstrap 3 + custom styles
- Content area with min-height calculation

**Homepage**: `resources/views/home.blade.php`

**Components**:
1. URL shortener form (AJAX-powered)
2. Anonymous redirect explanation
3. Feature highlights (SSL, No logs, Referrer hiding)

**Form Implementation**:
```blade
{!! Form::open(['url' => url('shorten'), 'id' => 'form_shortener']) !!}
    {!! Form::text('url', null, ['placeholder' => 'Paste a link to shorten it']) !!}
    {!! Form::submit('Shorten', ['class' => 'btn btn-primary']) !!}
{!! Form::close() !!}
```

**Anonymous Page**: `resources/views/anonymous.blade.php`

**Critical Features**:
```html
<!-- Meta refresh with 1-second delay -->
<meta http-equiv="refresh" content="1; url={{ $url }}" id="url">

<!-- Stripped referrer -->
<a href="{{ $url }}" rel="noreferrer nofollow">{{ str_limit($url, 32) }}</a>

<!-- JavaScript fragment handling -->
<script>
if(window.location.hash) {
    document.getElementById('url')
        .setAttribute('content', '0; url={{ $url }}' + window.location.hash);
}
</script>

<!-- Google Analytics -->
<script>
ga('create', '{{ env('GOOGLE_ANALYTICS') }}', 'auto');
ga('send', 'pageview');
</script>
```

**My Links Page**: `resources/views/my/links.blade.php`

**Features**:
- Search form (hash, domain, path)
- Paginated table (50 per page)
- Human-readable timestamps (`diffForHumans()`)
- Admin: Delete buttons with AJAX

---

## Security & Validation

### Input Validation

1. **URL Shortening**:
   ```php
   // Required, must be valid URL
   $this->validate($request, [
       'url' => 'required|url',
   ]);
   ```

2. **Link Reporting**:
   ```php
   // Custom validator + reCAPTCHA
   $this->validate($request, [
       'url' => 'required|url|anon_url',
       'email' => 'required|email',
       'comment' => 'required',
       'g-recaptcha-response' => 'required|recaptcha',
   ]);
   ```

3. **Custom Validators** (AppServiceProvider):
   ```php
   // Password policy: 8+ chars, mix of case/digits/special
   Validator::extend('password_policy', ...);

   // reCAPTCHA verification
   Validator::extend('recaptcha', function ($attribute, $value) {
       $recaptcha = new \ReCaptcha\ReCaptcha(env('API_GOOGLE_RECAPTCHA'));
       $resp = $recaptcha->verify($value, get_ip());
       return $resp->isSuccess();
   });

   // Check if URL exists in system
   Validator::extend('anon_url', ...);
   ```

### Middleware

1. **AjaxMiddleware** (`ajax`):
   ```php
   // Blocks non-AJAX requests (except in local env)
   if (app()->environment() != 'local' && !$request->ajax()) {
       return response('Not Allowed.', 405);
   }
   ```

2. **AdminMiddleware** (`admin`):
   ```php
   // Hard-coded admin check
   if (Auth::id() == 2) {
       return $next($request);
   }
   return abort(403, 'Access Denied.');
   ```

3. **Throttle Middleware**:
   ```php
   // Shorten endpoint: 20 requests per minute
   Route::post('shorten', ...)->middleware(['ajax', 'throttle:20,1']);
   ```

### CSRF Protection

- Laravel's built-in `VerifyCsrfToken` middleware
- Fresh token fetched via AJAX before each request:
  ```javascript
  $.ajax({ url: BASEURL + '/csrf', type: 'GET' })
  ```

### SQL Injection Protection

- All queries use Eloquent ORM or Query Builder
- Parameter binding via `where()` clauses
- No raw SQL queries with user input

### XSS Protection

- Blade `{{ }}` escapes output by default
- `{!! !!}` used only for Form builder (trusted)
- No direct `innerHTML` manipulation in JavaScript

### Hash Security

1. **Collision Avoidance**:
   ```php
   $hash = Str::random(6);
   while (Links::where('hash', $hash)->first()) {
       $hash = Str::random(6); // Retry until unique
   }
   ```

2. **Word Exclusion**:
   - ~600 common English words blacklisted
   - Prevents offensive/confusing hashes
   - Function: `excluded_words()` in helpers.php

### IP Address Handling

**Helper Function**: `get_ip()`
```php
// Checks Cloudflare header first
if (getenv('HTTP_CF_CONNECTING_IP') && is_valid_ip(getenv('HTTP_CF_CONNECTING_IP'))) {
    return getenv('HTTP_CF_CONNECTING_IP');
}
return request()->getClientIp();
```

**Validation**:
- `is_valid_ip($ip, $which = 'ipv4')`: Validates IP format
- `is_public_ip($ip)`: Excludes private/reserved ranges
- Used for: Report IP tracking, GeoIP lookups

---

## Caching Strategy

### Redis Configuration

**ENV Variables**:
```
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
```

### Link Caching

**TTL**: 24 hours (60 minutes × 24)

**Cache Operations**:

1. **Write** (after creation):
   ```php
   Cache::put($link->hash, $reconstructed_url, 60 * 24);
   ```

2. **Read** (on redirect):
   ```php
   if ($url = Cache::get($key)) {
       return $this->anonymousRedirect($url);
   }
   ```

3. **Invalidation** (on delete):
   ```php
   Cache::forget($link->hash);
   $link->delete();
   ```

**Benefits**:
- Reduces database load for popular links
- Sub-millisecond redirect times for cached links
- Transparent cache-miss handling

### HTTP Caching

**Anonymous Redirect Page**:
```php
return response()
    ->view('anonymous', compact('url'))
    ->setExpires(Carbon::now()->addHours(1))
    ->header('Cache-Control', 'public,max-age=3600,s-maxage=3600');
```

**CDN Strategy**:
- Public caching for 1 hour
- Suitable for Cloudflare caching
- Reduces origin server load

---

## Configuration

### Environment Variables

**Critical Variables**:

```bash
# Application
APP_URL='https://anon.to'
SITE_NAME='Anon.to'
SITE_META_TITLE='Anonymous URL Shortener and Redirect Service'

# Google Services
API_GOOGLE_RECAPTCHA='secret_key'
API_GOOGLE_RECAPTCHA_CLIENT='site_key'
GOOGLE_ANALYTICS='UA-XXXXXXXX-X'

# Cloudflare (optional)
CLOUDFLARE_USERNAME=
CLOUDFLARE_API_KEY=
CLOUDFLARE_ZONE_ID=
```

### Service Providers

**AppServiceProvider** (`app/Providers/AppServiceProvider.php`):

**Registered Services**:
1. MetaDataService (singleton)
2. GeoIP2 Reader (singleton)

**Custom Validators**:
- `password_policy`: Strong password check
- `recaptcha`: Google reCAPTCHA verification

**Schema Fix**:
```php
Schema::defaultStringLength(191); // MySQL 5.6 compatibility
```

### Route Configuration

**Route Files**:
1. `routes/web.php`: Auth, user links, admin, shortening API
2. `routes/static.php`: Redirect endpoint, static pages
3. `routes/api.php`: Empty (unused)
4. `routes/console.php`: Artisan commands

**Middleware Groups**:
- `web`: Standard Laravel web middleware
- `static`: Empty (no middleware for redirects)
- `api`: Throttle 60/minute + bindings

### HTTP Kernel

**Custom Middleware**:
```php
protected $routeMiddleware = [
    'ajax' => \App\Http\Middleware\AjaxMiddleware::class,
    'admin' => \App\Http\Middleware\AdminMiddleware::class,
    // ... standard Laravel middleware
];
```

---

## Deployment & Operations

### Server Requirements

- PHP 5.6.4+ (7.0+ recommended)
- PHP Extensions: openssl, mcrypt, mbstring, phpredis
- MySQL 5.6+ or MariaDB
- Redis 3.0+
- Composer
- Node.js with npm

### Installation Steps

```bash
# 1. Clone repository
git clone https://github.com/bhutanio/anon.to.git anon.to
cd anon.to

# 2. Install dependencies
composer install --no-dev
npm install
npm run production

# 3. Configure environment
cp .env.example .env
# Edit .env with database, redis, API keys

# 4. Generate app key
php artisan key:generate

# 5. Run migrations
php artisan migrate

# 6. Configure admin user
php artisan tinker
>>> DB::table('users')->where('id', 2)->update(['email'=>'admin@example.com']);
>>> exit

# 7. Reset admin password via web interface
# Visit /password/reset
```

### Cron Jobs

```bash
# Laravel scheduler (runs every minute)
* * * * * php /path/to/anon.to/artisan schedule:run >/dev/null 2>&1

# Clear expired password reset tokens (every 5 minutes)
*/5 * * * * php /path/to/anon.to/artisan auth:clear-resets >/dev/null 2>&1
```

### Supervisor Configuration

**Queue Worker** (`/etc/supervisor/conf.d/anon.conf`):
```ini
[program:anon-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/anon.to/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/anon-queue.log
```

**Start Supervisor**:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start anon-queue:*
```

### GeoIP Database

**Automatic Download** (via Composer):
```json
"post-install-cmd": [
    "wget -qO- http://geolite.maxmind.com/download/geoip/database/GeoLite2-Country.mmdb.gz | gunzip > storage/geoip/geolite2-country.mmdb"
]
```

**Manual Update**:
```bash
cd storage/geoip
wget http://geolite.maxmind.com/download/geoip/database/GeoLite2-Country.mmdb.gz
gunzip -f GeoLite2-Country.mmdb.gz
```

### Google reCAPTCHA Setup

1. Visit: https://www.google.com/recaptcha/admin
2. Register domain
3. Get Site Key and Secret Key
4. Add to `.env`:
   ```
   API_GOOGLE_RECAPTCHA='secret_key'
   API_GOOGLE_RECAPTCHA_CLIENT='site_key'
   ```

---

## Key Files Reference

### Controllers
| File | Purpose | Routes |
|------|---------|--------|
| `LinksController.php` | Base controller for link operations | N/A (abstract) |
| `ShortenLinkController.php` | URL shortening | `POST /shorten` |
| `RedirectController.php` | Redirect logic | `GET /{hash}` |
| `HomeController.php` | Homepage + direct redirect | `GET /`, `GET /?{url}` |
| `ReportLinkController.php` | Report malicious links | `GET/POST /report` |
| `My/MyLinksController.php` | User link management | `GET /my`, `GET /admin/links` |
| `Admin/AdminController.php` | Admin dashboard | `GET /admin/reports` |
| `StaticPagesController.php` | CMS pages | `/about`, `/about/terms`, etc. |

### Models
| File | Table | Purpose |
|------|-------|---------|
| `Links.php` | `links` | Shortened URLs |
| `Reports.php` | `link_reports` | User reports |
| `Contents.php` | `contents` | CMS content |
| `User.php` | `users` | Authentication |
| `UserActivation.php` | `user_activations` | Email verification |

### Services
| File | Purpose | Lifecycle |
|------|---------|-----------|
| `UrlServices.php` | URL parsing/reconstruction | Per-request |
| `MetaDataService.php` | SEO meta tags | Singleton |

### Middleware
| File | Alias | Purpose |
|------|-------|---------|
| `AjaxMiddleware.php` | `ajax` | Require AJAX requests |
| `AdminMiddleware.php` | `admin` | Admin-only routes (user_id=2) |

### Views
| File | Purpose |
|------|---------|
| `home.blade.php` | Homepage with shortener form |
| `anonymous.blade.php` | Intermediate redirect page |
| `my/links.blade.php` | User's shortened links |
| `report.blade.php` | Report link form |
| `admin/reports.blade.php` | Admin: View reports |
| `static.blade.php` | Generic CMS page template |

### Helpers
| File | Key Functions |
|------|---------------|
| `helpers/helpers.php` | `excluded_words()`, `get_ip()`, `is_valid_url()`, `flash()`, `carbon()` |

### Migrations
| File | Purpose |
|------|---------|
| `2016_03_15_095138_create_new_links_table.php` | Create links table + migrate old data |
| `2017_03_27_165541_create_link_reports_table.php` | Create reports table |
| `2017_03_27_180536_create_contents_table.php` | Create CMS + seed content |
| `2016_03_14_211254_create_user_tables.php` | Create users table |
| `2017_03_24_152329_create_user_activations_table.php` | Email verification |

---

## Migration Considerations

### What to Keep (Core Logic)

1. **URL Component Storage**: The parsed URL approach is sound for duplicate detection
2. **6-Character Hash**: Good balance of brevity and collision avoidance
3. **Excluded Words**: Prevents offensive hashes
4. **Caching Strategy**: 24-hour Redis cache is effective
5. **Anonymous-First**: No registration required aligns with privacy goals
6. **Duplicate Detection**: Checking all URL components prevents true duplicates

### What to Modernize

1. **Laravel 5.4 → 12**: Framework upgrade required
   - Replace deprecated methods
   - Update to new directory structure
   - Use modern routing (`routes/web.php` consolidation)

2. **Bootstrap 3 → Tailwind 4**: Already in progress
   - Replace Glyphicons with Heroicons/Lucide
   - Rebuild forms with Flux UI components
   - Modernize responsive design

3. **jQuery → Alpine.js/Livewire**: JavaScript modernization
   - Livewire Volt for interactive components
   - Alpine.js for simple interactions
   - Remove jQuery dependency

4. **Laravel Collective → Native**: Form handling
   - Use Livewire forms or native Blade
   - Wire models for reactive forms

5. **Hard-Coded Admin (ID=2) → Roles/Permissions**: Proper authorization
   - Use Laravel policies or Spatie permissions
   - Database-driven role management

6. **Middleware Registration**: Laravel 12 structure
   - Register in `bootstrap/app.php` not `Kernel.php`

7. **Custom Validators → Form Requests**: Better organization
   - Move validation rules to FormRequest classes
   - Keep custom validators in ServiceProvider

8. **Email Activation → Fortify**: Already using Fortify
   - Leverage built-in email verification
   - Remove custom activation system

### What to Add

1. **Comprehensive Testing**: Original has minimal tests
   - Feature tests for all endpoints (Pest v4)
   - Browser tests for redirect flow
   - Unit tests for UrlService

2. **API Rate Limiting**: More granular than 20/minute
   - Per-user rate limits
   - Redis-backed tracking
   - Configurable limits

3. **Analytics Dashboard**: Original only tracks via Google Analytics
   - Click tracking
   - Geographic distribution
   - Popular links

4. **Link Expiration**: Optional TTL per link
   - User-configurable expiration
   - Cleanup jobs for expired links

5. **Custom Short Codes**: User-defined hashes
   - Vanity URLs (if available)
   - Premium feature potential

6. **API Endpoints**: RESTful API for programmatic access
   - OAuth2 authentication
   - API key management
   - Rate limiting

### Backward Compatibility

**Database**:
- Keep `links` table structure (already compatible)
- Existing hashes remain valid
- No migration of old data needed

**URLs**:
- `/{hash}` pattern unchanged
- Existing short URLs continue working
- Add new features as opt-in

**Caching**:
- Cache keys unchanged (hash-based)
- Gradual invalidation as cache expires
- No manual purge needed

### Security Improvements

1. **Input Sanitization**: Already good, maintain
2. **Rate Limiting**: Expand beyond 20/minute
3. **CAPTCHA**: Consider moving to invisible reCAPTCHA v3
4. **HTTPS Enforcement**: Ensure all redirects use HTTPS
5. **Content Security Policy**: Add CSP headers
6. **Signed URLs**: For sensitive operations (delete, etc.)

### Performance Optimizations

1. **Database Indexes**: Already present on `created_by`
   - Add composite index on URL components for faster duplicate detection
   - Index on `hash` already unique

2. **Query Optimization**: Current queries are efficient
   - Consider eager loading for admin views
   - Maintain single-query lookups

3. **CDN Integration**: Already Cloudflare-ready
   - Expand static asset caching
   - Consider edge redirects (Cloudflare Workers)

4. **Redis Optimization**: Current usage is good
   - Consider Redis Cluster for scale
   - Add cache warming for popular links

---

## Appendix: Deprecated/Unused Features

### Features NOT in Use

1. **Broadcasting** (`routes/channels.php`): Empty, never implemented
2. **API Routes** (`routes/api.php`): Empty file
3. **Queue Jobs**: Mail jobs exist but optional
4. **Telescope/Horizon**: Not installed
5. **File Uploads**: No file storage features

### Commented Code

**AppServiceProvider** (`setDefaultMeta()`):
- Route-based meta tag setting was planned but never implemented
- Could be revived for automatic titles

**HomeController**:
- No commented code, clean implementation

### Obsolete Dependencies

**For Removal in Laravel 12**:
- `laravelcollective/html`: Use Livewire forms
- `doctrine/dbal`: Laravel 12 has native column modification
- jQuery: Replace with Alpine.js

**For Updating**:
- `geoip2/geoip2`: Update to latest version
- `google/recaptcha`: Consider reCAPTCHA v3

---

## Summary

The legacy anon.to codebase is well-structured for Laravel 5.4 era, with clean separation of concerns and effective use of caching. The core architecture (URL component storage, duplicate detection, Redis caching) is sound and should be preserved in the modernization.

Key modernization priorities:
1. Framework upgrade (Laravel 5.4 → 12)
2. Frontend stack (Bootstrap/jQuery → Tailwind/Livewire)
3. Testing coverage (minimal → comprehensive Pest tests)
4. Authorization (hard-coded admin → proper roles)
5. Feature additions (analytics, API, custom codes)

The migration path is straightforward with minimal breaking changes due to database compatibility and URL pattern preservation.

---

**Document Version**: 1.0
**Created**: 2025-11-07
**Branch Analyzed**: `origin/laravel-5.4` (commit: ce62e6a)
**Target Migration**: Laravel 12 with Livewire/Volt/Flux
