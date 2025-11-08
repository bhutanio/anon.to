# anon.to - Technical Specification

## Architecture Overview

### Application Structure
```
anon.to (Laravel 12)
├── Frontend (Livewire 3 + Volt + Flux UI)
├── Backend (Laravel Controllers + Actions)
├── API (RESTful + Sanctum)
├── Database (MySQL/PostgreSQL)
├── Cache (Redis/Memcached)
├── Queue (Redis + Horizon)
└── Storage (Local/S3)
```

### Technology Stack

#### Core Framework
- **Laravel 12.37.0** - Latest stable version
- **PHP 8.4.14** - Modern PHP with performance improvements
- **Composer 2.x** - Dependency management

#### Frontend
- **Livewire 3.6.4** - Dynamic interfaces without JavaScript
- **Volt 1.9.0** - Functional/class-based single-file components
- **Flux UI 2.6.1** - Pre-built UI component library
- **Tailwind CSS 4.1.11** - Utility-first styling
- **Alpine.js** (bundled with Livewire) - Lightweight JavaScript
- **Prism.js** or **Highlight.js** - Syntax highlighting

#### Authentication & Authorization
- **Fortify 1.31.2** - Headless authentication backend
- **Sanctum** - API token authentication
- **Laravel Gates & Policies** - Authorization logic

#### Database & Caching
- **MySQL 8.0+** or **PostgreSQL 15+** - Production database
- **Redis 7.x** - Caching and queue driver
- **Laravel Horizon** - Queue monitoring dashboard

#### Testing
- **Pest 4.1.3** - Modern PHP testing framework
- **PHPUnit 12.4.1** - Underlying test runner
- **Pest Browser Testing** - End-to-end browser tests

#### Code Quality
- **Laravel Pint 1.25.1** - Opinionated code formatter
- **PHPStan** - Static analysis (optional)
- **Larastan** - Laravel-specific static analysis

#### DevOps
- **Laravel Sail 1.47.0** - Docker development environment
- **Vite** - Asset bundling and HMR
- **GitHub Actions** - CI/CD pipeline

#### Third-Party Services
- **QR Code Generator** - chillerlan/php-qrcode or simple-qrcode/simple-qrcode
- **Google Safe Browsing API** - URL reputation checking
- **CAPTCHA** - hCaptcha or Google reCAPTCHA v3
- **Email** - AWS SES, Mailgun, or Postmark

---

## Database Schema

### Entity Relationship Diagram

```
users (1) ----< (N) links
users (1) ----< (N) notes
users (1) ----< (N) reports
links (1) ----< (N) reports
notes (1) ----< (N) reports
users (1) ----< (N) api_tokens (Sanctum)
```

### Tables

#### 1. users
**Purpose**: User accounts with authentication data

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint unsigned | PK, auto_increment | Unique user ID |
| username | varchar(255) | unique, nullable | Unique username (optional) |
| name | varchar(255) | | Display name |
| email | varchar(255) | unique | Email address |
| email_verified_at | timestamp | nullable | Email verification timestamp |
| password | varchar(255) | | Bcrypt hashed password |
| remember_token | varchar(100) | nullable | Session token |
| is_admin | boolean | default: false | Admin flag |
| is_verified | boolean | default: false | Verified user flag (higher limits) |
| api_rate_limit | integer | default: 100 | API calls per hour |
| last_login_at | timestamp | nullable | Last login tracking |
| two_factor_secret | text | nullable | 2FA secret (Fortify) |
| two_factor_recovery_codes | text | nullable | 2FA recovery codes |
| two_factor_confirmed_at | timestamp | nullable | 2FA confirmation |
| created_at | timestamp | | Account creation |
| updated_at | timestamp | | Last update |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (email)
- UNIQUE (username)
- INDEX (email_verified_at)
- INDEX (is_admin)

**Relationships:**
- hasMany: links, notes, reports, personal_access_tokens

---

#### 2. links
**Purpose**: Shortened URLs with analytics

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint unsigned | PK, auto_increment | Unique link ID |
| hash | varchar(255) | unique | Short code (6 chars) |
| url_scheme | varchar(10) | | http or https |
| url_host | varchar(255) | | Domain name |
| url_port | integer | nullable | Port number |
| url_path | text | nullable | URL path |
| url_query | text | nullable | Query string |
| url_fragment | text | nullable | Fragment (#anchor) |
| full_url | text | | Complete URL (for quick access) |
| full_url_hash | varchar(64) | index | SHA256 hash for duplicate detection |
| title | varchar(500) | nullable | Page title (fetched metadata) |
| description | text | nullable | Meta description |
| expires_at | timestamp | nullable | Auto-delete after this time |
| password_hash | varchar(255) | nullable | Bcrypt hash (future feature) |
| visits | bigint unsigned | default: 0 | Total visit count |
| unique_visits | bigint unsigned | default: 0 | Unique IP visits |
| last_visited_at | timestamp | nullable | Last click timestamp |
| is_active | boolean | default: true | Active/disabled flag |
| is_reported | boolean | default: false | Has pending reports |
| user_id | bigint unsigned | nullable, FK | Creator (null = anonymous) |
| ip_address | varchar(45) | nullable | Creator IP (hashed for privacy) |
| user_agent | text | nullable | Creator user agent |
| created_at | timestamp | | Creation timestamp |
| updated_at | timestamp | | Last update |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (hash)
- INDEX (full_url_hash) - For duplicate detection
- INDEX (user_id)
- INDEX (expires_at)
- INDEX (created_at)
- INDEX (visits) - For popular links
- INDEX (is_active, is_reported)
- FULLTEXT (url_host) - For domain searches

**Relationships:**
- belongsTo: user
- hasMany: reports, link_analytics

---

#### 3. link_analytics
**Purpose**: Detailed visit tracking per link

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint unsigned | PK, auto_increment | Unique analytics ID |
| link_id | bigint unsigned | FK | Associated link |
| visited_at | timestamp | | Visit timestamp |
| ip_address | varchar(45) | nullable | Visitor IP (hashed) |
| country_code | char(2) | nullable | ISO country code |
| referrer | varchar(500) | nullable | HTTP referrer |
| user_agent | text | nullable | Browser user agent |
| created_at | timestamp | | Record creation |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (link_id, visited_at) - Time-series queries
- INDEX (country_code)
- INDEX (visited_at) - For aggregations

**Relationships:**
- belongsTo: link

**Partitioning Strategy**: Partition by month for efficient querying and archival

---

#### 4. notes
**Purpose**: Anonymous pastebin-style plain text sharing

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint unsigned | PK, auto_increment | Unique note ID |
| hash | varchar(255) | unique | Short code (6-8 chars) |
| title | varchar(255) | nullable | Optional note title |
| content | longtext | | Note content (10MB max) |
| content_hash | varchar(64) | index | SHA256 for duplicate detection |
| char_count | integer | default: 0 | Character count |
| line_count | integer | default: 0 | Line count |
| expires_at | timestamp | nullable | Auto-delete after this time |
| password_hash | varchar(255) | nullable | Bcrypt hash for password protection |
| view_limit | integer | nullable | Max views before deletion |
| views | integer | default: 0 | Current view count |
| unique_views | integer | default: 0 | Unique IP views |
| last_viewed_at | timestamp | nullable | Last view timestamp |
| is_active | boolean | default: true | Active/disabled flag |
| is_reported | boolean | default: false | Has pending reports |
| is_public | boolean | default: true | Show in public listing |
| user_id | bigint unsigned | nullable, FK | Creator (null = anonymous) |
| forked_from_id | bigint unsigned | nullable, FK | Parent note if forked |
| ip_address | varchar(45) | nullable | Creator IP (hashed) |
| user_agent | text | nullable | Creator user agent |
| created_at | timestamp | | Creation timestamp |
| updated_at | timestamp | | Last update |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (hash)
- INDEX (content_hash) - Duplicate detection
- INDEX (user_id)
- INDEX (expires_at)
- INDEX (created_at)
- INDEX (is_active, is_reported)
- INDEX (view_limit, views) - Burn-after-read logic
- FULLTEXT (title, content) - Content search

**Relationships:**
- belongsTo: user, parent_note (forked_from)
- hasMany: reports, child_notes (forks)

---

#### 5. reports
**Purpose**: Abuse reporting for links and notes

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint unsigned | PK, auto_increment | Unique report ID |
| reportable_type | varchar(255) | | Polymorphic: Link or Note |
| reportable_id | bigint unsigned | | ID of reported item |
| category | varchar(50) | | spam, malware, illegal, copyright, harassment, other |
| url | text | nullable | Reported URL (if external) |
| email | varchar(255) | nullable | Reporter email |
| comment | text | | Report description |
| ip_address | varchar(45) | nullable | Reporter IP |
| user_id | bigint unsigned | nullable, FK | Reporter (if logged in) |
| status | varchar(20) | default: pending | pending, reviewing, dealt, dismissed |
| admin_notes | text | nullable | Admin comments |
| dealt_by | bigint unsigned | nullable, FK | Admin who handled |
| dealt_at | timestamp | nullable | Resolution timestamp |
| created_at | timestamp | | Report submission |
| updated_at | timestamp | | Last update |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (reportable_type, reportable_id) - Polymorphic
- INDEX (status, created_at) - Pending queue
- INDEX (user_id)
- INDEX (dealt_by)

**Relationships:**
- morphTo: reportable (Link or Note)
- belongsTo: user (reporter), admin (dealt_by)

---

#### 6. allow_lists
**Purpose**: Domain whitelist/blacklist for link creation

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint unsigned | PK, auto_increment | Unique ID |
| domain | varchar(255) | unique | Domain or pattern |
| type | enum | allow, block | Whitelist or blacklist |
| pattern_type | enum | exact, wildcard, regex | Match type |
| reason | text | nullable | Why this rule exists |
| is_active | boolean | default: true | Enable/disable rule |
| hit_count | integer | default: 0 | Times rule triggered |
| added_by | bigint unsigned | nullable, FK | Admin who added |
| created_at | timestamp | | Rule creation |
| updated_at | timestamp | | Last update |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (domain, type)
- INDEX (type, is_active) - Active rules lookup
- INDEX (added_by)

**Relationships:**
- belongsTo: admin (added_by)

---

#### 7. sessions
**Purpose**: Laravel session storage (database driver)

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | varchar(255) | PK | Session ID |
| user_id | bigint unsigned | nullable, FK | Authenticated user |
| ip_address | varchar(45) | nullable | Client IP |
| user_agent | text | nullable | Browser info |
| payload | longtext | | Serialized session data |
| last_activity | integer | | Unix timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (user_id)
- INDEX (last_activity) - Cleanup old sessions

---

#### 8. personal_access_tokens
**Purpose**: Laravel Sanctum API tokens

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint unsigned | PK, auto_increment | Token ID |
| tokenable_type | varchar(255) | | Polymorphic (User) |
| tokenable_id | bigint unsigned | | User ID |
| name | varchar(255) | | Token name/label |
| token | varchar(64) | unique | Hashed token |
| abilities | text | nullable | Permissions JSON |
| last_used_at | timestamp | nullable | Last API call |
| expires_at | timestamp | nullable | Token expiration |
| created_at | timestamp | | Token creation |
| updated_at | timestamp | | Last update |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (token)
- INDEX (tokenable_type, tokenable_id)

---

#### 9. cache & cache_locks
**Purpose**: Laravel cache table driver

Standard Laravel cache table structure (already exists).

---

#### 10. jobs, failed_jobs, job_batches
**Purpose**: Laravel queue system

Standard Laravel queue tables (already exist).

---

### Migration Strategy

#### Creating Fresh Database
```bash
# 1. Create all migrations
php artisan make:migration create_links_table
php artisan make:migration create_notes_table
php artisan make:migration create_reports_table
php artisan make:migration create_allow_lists_table
php artisan make:migration create_link_analytics_table

# 2. Run migrations
php artisan migrate

# 3. Seed initial data
php artisan db:seed --class=AllowListSeeder
```

#### Importing Old Data
```bash
# Custom artisan command
php artisan import:legacy-data {--dry-run} {--users} {--links} {--reports}

# Examples:
php artisan import:legacy-data --dry-run  # Test without writing
php artisan import:legacy-data --users    # Import users only
php artisan import:legacy-data            # Import everything
```

**Import Logic:**
1. **Users Table**:
   - Map old `username` → new `username`
   - Map old `email` → new `email`
   - Keep old `password` hashes (compatible)
   - Set `is_admin` based on old roles
   - Preserve `created_at` timestamps

2. **Links Table**:
   - Map URL components to new structure
   - Copy `hash` exactly
   - Concatenate to `full_url` for convenience
   - Generate `full_url_hash` for duplicates
   - Map `created_by` to `user_id`
   - Preserve `visits` and timestamps

3. **Reports Table**:
   - Map old `link_id` → `reportable_id` with `reportable_type=Link`
   - Copy comment, email, IP
   - Map `dealt_at` → `status=dealt`

4. **Contents Table**:
   - Convert static pages to new CMS or hardcode

5. **Allow Lists**:
   - Import domains with `type=block`

**Validation:**
- Count records before/after import
- Verify hash uniqueness
- Test random links redirect correctly
- Check user login still works

---

## Application Architecture

### Directory Structure

```
app/
├── Actions/              # Single-purpose business logic
│   ├── Links/
│   │   ├── CreateLink.php
│   │   ├── GenerateHash.php
│   │   ├── ValidateUrl.php
│   │   └── CheckDuplicate.php
│   ├── Notes/
│   │   ├── CreateNote.php
│   │   ├── EncryptNote.php
│   │   ├── IncrementViews.php
│   │   └── CheckBurnLimit.php
│   └── Analytics/
│       └── RecordVisit.php
│
├── Models/
│   ├── User.php
│   ├── Link.php
│   ├── Note.php
│   ├── Report.php
│   ├── AllowList.php
│   └── LinkAnalytic.php
│
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── V1/
│   │   │   │   ├── LinkController.php
│   │   │   │   ├── NoteController.php
│   │   │   │   └── AnalyticsController.php
│   │   └── Web/
│   │       ├── RedirectController.php
│   │       └── QrCodeController.php
│   │
│   ├── Middleware/
│   │   ├── CheckAllowList.php
│   │   ├── RateLimitByUserType.php
│   │   └── TrackAnalytics.php
│   │
│   └── Requests/
│       ├── CreateLinkRequest.php
│       ├── CreateNoteRequest.php
│       └── ReportContentRequest.php
│
├── Livewire/           # Livewire components (if not using Volt)
│   ├── Links/
│   │   ├── CreateLink.php
│   │   └── MyLinks.php
│   └── Admin/
│       └── ReportQueue.php
│
├── Services/
│   ├── UrlService.php          # URL parsing/validation
│   ├── QrCodeService.php       # QR generation
│   ├── SyntaxHighlighter.php   # Code highlighting
│   └── SafeBrowsingService.php # Malware checking
│
├── Jobs/
│   ├── DeleteExpiredLinks.php
│   ├── DeleteExpiredNotes.php
│   ├── CheckUrlReputation.php
│   └── SendReportNotification.php
│
├── Console/
│   └── Commands/
│       ├── ImportLegacyData.php
│       ├── CleanupExpired.php
│       └── GenerateSitemap.php
│
└── Policies/
    ├── LinkPolicy.php
    ├── NotePolicy.php
    └── ReportPolicy.php

resources/
├── views/
│   ├── livewire/         # Volt components
│   │   ├── links/
│   │   │   ├── create.blade.php
│   │   │   ├── show.blade.php
│   │   │   └── my-links.blade.php
│   │   ├── notes/
│   │   │   ├── create.blade.php
│   │   │   ├── show.blade.php
│   │   │   └── my-notes.blade.php
│   │   ├── admin/
│   │   │   ├── dashboard.blade.php
│   │   │   ├── reports.blade.php
│   │   │   └── allow-lists.blade.php
│   │   └── redirect.blade.php
│   │
│   ├── components/
│   │   ├── link-card.blade.php
│   │   ├── note-card.blade.php
│   │   ├── syntax-selector.blade.php
│   │   └── expiration-picker.blade.php
│   │
│   └── layouts/
│       ├── app.blade.php
│       ├── guest.blade.php
│       └── admin.blade.php
│
├── css/
│   └── app.css          # Tailwind imports
│
└── js/
    ├── app.js           # Alpine components
    └── prism-setup.js   # Syntax highlighting config

database/
├── migrations/
├── factories/
│   ├── UserFactory.php
│   ├── LinkFactory.php
│   └── NoteFactory.php
└── seeders/
    ├── DatabaseSeeder.php
    ├── AllowListSeeder.php
    └── AdminUserSeeder.php

tests/
├── Feature/
│   ├── Links/
│   │   ├── CreateLinkTest.php
│   │   ├── RedirectTest.php
│   │   └── CustomSlugTest.php
│   ├── Notes/
│   │   ├── CreateNoteTest.php
│   │   ├── PasswordProtectionTest.php
│   │   └── BurnAfterReadingTest.php
│   ├── Admin/
│   │   ├── ModerationTest.php
│   │   └── AllowListTest.php
│   └── Api/
│       ├── LinkApiTest.php
│       └── NoteApiTest.php
│
├── Unit/
│   ├── Actions/
│   │   ├── GenerateHashTest.php
│   │   └── ValidateUrlTest.php
│   └── Services/
│       └── UrlServiceTest.php
│
└── Browser/           # Pest 4 browser tests
    ├── CreateLinkFlowTest.php
    └── AdminModerationTest.php
```

---

## Key Implementations

### 1. Link Creation Action

**File**: `app/Actions/Links/CreateLink.php`

```php
<?php

namespace App\Actions\Links;

use App\Models\Link;
use App\Services\UrlService;
use Illuminate\Support\Facades\Cache;

class CreateLink
{
    public function __construct(
        protected UrlService $urlService,
        protected GenerateHash $hashGenerator,
        protected ValidateUrl $urlValidator,
        protected CheckDuplicate $duplicateChecker,
    ) {}

    public function execute(
        string $url,
        ?int $userId = null,
        ?string $expiresAt = null,
    ): Link {
        // 1. Validate URL
        $this->urlValidator->execute($url);

        // 2. Parse URL into components
        $parsed = $this->urlService->parse($url);

        // 3. Check for duplicate
        if ($existing = $this->duplicateChecker->execute($parsed)) {
            return $existing;
        }

        // 4. Generate unique hash
        $hash = $this->hashGenerator->execute();

        // 5. Create link
        $link = Link::create([
            'hash' => $hash,
            'url_scheme' => $parsed['scheme'],
            'url_host' => $parsed['host'],
            'url_port' => $parsed['port'],
            'url_path' => $parsed['path'],
            'url_query' => $parsed['query'],
            'url_fragment' => $parsed['fragment'],
            'full_url' => $url,
            'full_url_hash' => hash('sha256', $url),
            'user_id' => $userId,
            'expires_at' => $expiresAt,
            'ip_address' => request()->ip(),
        ]);

        // 6. Cache the link
        Cache::put("link:{$hash}", $link, now()->addDay());

        return $link;
    }
}
```

---

### 2. Hash Generation

**File**: `app/Actions/Links/GenerateHash.php`

```php
<?php

namespace App\Actions\Links;

use App\Models\Link;
use Illuminate\Support\Str;

class GenerateHash
{
    protected array $excludedWords;

    public function __construct()
    {
        $this->excludedWords = config('anon.excluded_words', []);
    }

    public function execute(): string
    {
        return $this->generateUniqueHash();
    }

    protected function generateUniqueHash(): string
    {
        $maxAttempts = 10;

        for ($i = 0; $i < $maxAttempts; $i++) {
            $hash = Str::random(6);

            // Skip excluded words
            if (in_array(strtolower($hash), $this->excludedWords)) {
                continue;
            }

            // Check uniqueness
            if (!Link::where('hash', $hash)->exists()) {
                return $hash;
            }
        }

        throw new \RuntimeException('Failed to generate unique hash');
    }
}
```

---

### 3. Redirect with Analytics

**File**: `app/Http/Controllers/Web/RedirectController.php`

```php
<?php

namespace App\Http\Controllers\Web;

use App\Actions\Analytics\RecordVisit;
use App\Http\Controllers\Controller;
use App\Models\Link;
use App\Services\UrlService;
use Illuminate\Support\Facades\Cache;

class RedirectController extends Controller
{
    public function __construct(
        protected UrlService $urlService,
        protected RecordVisit $recordVisit,
    ) {}

    public function redirect(string $hash)
    {
        // Try cache first
        $link = Cache::remember(
            "link:{$hash}",
            now()->addDay(),
            fn () => Link::where('hash', $hash)->firstOrFail()
        );

        // Check if expired
        if ($link->expires_at && $link->expires_at->isPast()) {
            abort(410, 'This link has expired');
        }

        // Check if active
        if (!$link->is_active) {
            abort(403, 'This link has been disabled');
        }

        // Record visit asynchronously
        $this->recordVisit->execute($link);

        // Reconstruct full URL
        $url = $this->urlService->reconstruct($link);

        // Show anonymous redirect warning page
        return view('livewire.redirect', [
            'url' => $url,
            'link' => $link,
            'parsed' => $this->urlService->parse($url),
        ]);
    }
}
```

---

### 4. Note with Password Protection (Volt)

**File**: `resources/views/livewire/notes/show.blade.php`

```blade
@volt('notes.show')
@php
use App\Models\Note;
use Illuminate\Support\Facades\Hash;
use function Livewire\Volt\{state, computed, mount, on};

state([
    'hash' => null,
    'note' => null,
    'password' => '',
    'unlocked' => false,
    'error' => null,
]);

mount(function (string $hash) {
    $this->hash = $hash;
    $this->note = Note::where('hash', $hash)
        ->where('is_active', true)
        ->firstOrFail();

    // Check expiration
    if ($this->note->expires_at && $this->note->expires_at->isPast()) {
        abort(410, 'This note has expired');
    }

    // If no password, show content immediately
    if (!$this->note->password_hash) {
        $this->unlocked = true;
        $this->incrementView();
    }
});

$unlock = function () {
    if (!Hash::check($this->password, $this->note->password_hash)) {
        $this->error = 'Incorrect password';
        return;
    }

    $this->unlocked = true;
    $this->error = null;
    $this->incrementView();
};

$incrementView = function () {
    $this->note->increment('views');
    $this->note->update(['last_viewed_at' => now()]);

    // Check burn after reading
    if ($this->note->view_limit && $this->note->views >= $this->note->view_limit) {
        $this->note->delete();
        session()->flash('burned', true);
    }

    $this->note->refresh();
};

$download = function () {
    $filename = $this->note->title
        ? Str::slug($this->note->title) . '.txt'
        : $this->note->hash . '.txt';

    return response()->streamDownload(function () {
        echo $this->note->content;
    }, $filename, ['Content-Type' => 'text/plain']);
};
@endphp

<div class="max-w-4xl mx-auto p-6">
    @if(!$unlocked)
        {{-- Password prompt --}}
        <flux:card>
            <flux:heading size="lg">This note is password protected</flux:heading>

            <form wire:submit="unlock" class="mt-4">
                <flux:field>
                    <flux:label>Enter password</flux:label>
                    <flux:input
                        type="password"
                        wire:model="password"
                        placeholder="Password"
                        autofocus
                    />
                    @if($error)
                        <flux:error>{{ $error }}</flux:error>
                    @endif
                </flux:field>

                <flux:button type="submit" variant="primary" class="mt-4">
                    Unlock Note
                </flux:button>
            </form>
        </flux:card>
    @else
        {{-- Show note content --}}
        <div class="flex justify-between items-center mb-4">
            <div>
                @if($note->title)
                    <flux:heading size="xl">{{ $note->title }}</flux:heading>
                @endif

                <div class="flex gap-4 text-sm text-zinc-500 mt-2">
                    <span>{{ $note->char_count }} characters</span>
                    <span>{{ $note->line_count }} lines</span>
                    <span>{{ $note->views }} views</span>

                    @if($note->view_limit)
                        <span class="text-red-600">
                            Burns after {{ $note->view_limit }} views
                        </span>
                    @endif

                    @if($note->expires_at)
                        <span>Expires {{ $note->expires_at->diffForHumans() }}</span>
                    @endif
                </div>
            </div>

            <div class="flex gap-2">
                <flux:button wire:click="download" variant="ghost">
                    Download
                </flux:button>
                <flux:button href="/notes/create?fork={{ $note->hash }}" variant="ghost">
                    Fork
                </flux:button>
            </div>
        </div>

        @if(session('burned'))
            <flux:callout variant="warning" class="mb-4">
                This note has reached its view limit and will be deleted.
            </flux:callout>
        @endif

        {{-- Plain text content --}}
        <flux:card class="overflow-x-auto">
            <pre class="whitespace-pre-wrap">{{ $note->content }}</pre>
        </flux:card>
    @endif
</div>
@endvolt
```

---

### 5. Admin Moderation (Volt)

**File**: `resources/views/livewire/admin/reports.blade.php`

```blade
@volt('admin.reports')
@php
use App\Models\Report;
use function Livewire\Volt\{state, computed};

state(['filter' => 'pending']);

$reports = computed(function () {
    return Report::with(['reportable', 'user'])
        ->when($this->filter !== 'all', fn($q) => $q->where('status', $this->filter))
        ->latest()
        ->paginate(20);
});

$deleteContent = function (Report $report) {
    $report->reportable->delete();
    $report->update([
        'status' => 'dealt',
        'dealt_by' => auth()->id(),
        'dealt_at' => now(),
        'admin_notes' => 'Content deleted',
    ]);

    $this->dispatch('report-updated');
};

$dismissReport = function (Report $report) {
    $report->update([
        'status' => 'dismissed',
        'dealt_by' => auth()->id(),
        'dealt_at' => now(),
    ]);

    $this->dispatch('report-updated');
};

$banUser = function (Report $report) {
    if ($report->reportable->user) {
        $report->reportable->user->update(['is_active' => false]);
    }

    $report->reportable->delete();
    $report->update([
        'status' => 'dealt',
        'dealt_by' => auth()->id(),
        'dealt_at' => now(),
        'admin_notes' => 'Content deleted, user banned',
    ]);

    $this->dispatch('report-updated');
};
@endphp

<div>
    <flux:heading size="xl">Abuse Reports</flux:heading>

    {{-- Filter tabs --}}
    <div class="flex gap-4 my-4">
        <flux:button
            wire:click="$set('filter', 'pending')"
            :variant="$filter === 'pending' ? 'primary' : 'ghost'"
        >
            Pending ({{ Report::where('status', 'pending')->count() }})
        </flux:button>

        <flux:button
            wire:click="$set('filter', 'dealt')"
            :variant="$filter === 'dealt' ? 'primary' : 'ghost'"
        >
            Dealt
        </flux:button>

        <flux:button
            wire:click="$set('filter', 'dismissed')"
            :variant="$filter === 'dismissed' ? 'primary' : 'ghost'"
        >
            Dismissed
        </flux:button>

        <flux:button
            wire:click="$set('filter', 'all')"
            :variant="$filter === 'all' ? 'primary' : 'ghost'"
        >
            All
        </flux:button>
    </div>

    {{-- Reports table --}}
    <flux:table>
        <flux:columns>
            <flux:column>Type</flux:column>
            <flux:column>Category</flux:column>
            <flux:column>Content</flux:column>
            <flux:column>Reporter</flux:column>
            <flux:column>Date</flux:column>
            <flux:column>Actions</flux:column>
        </flux:columns>

        <flux:rows>
            @foreach($this->reports as $report)
                <flux:row>
                    <flux:cell>
                        <flux:badge>{{ class_basename($report->reportable_type) }}</flux:badge>
                    </flux:cell>

                    <flux:cell>
                        <flux:badge variant="warning">{{ $report->category }}</flux:badge>
                    </flux:cell>

                    <flux:cell>
                        @if($report->reportable_type === 'App\\Models\\Link')
                            <a href="{{ url($report->reportable->hash) }}" target="_blank" class="text-blue-600">
                                {{ $report->reportable->full_url }}
                            </a>
                        @else
                            <a href="{{ route('notes.show', $report->reportable->hash) }}" target="_blank" class="text-blue-600">
                                {{ Str::limit($report->reportable->content, 50) }}
                            </a>
                        @endif

                        <div class="text-sm text-zinc-500 mt-1">
                            {{ $report->comment }}
                        </div>
                    </flux:cell>

                    <flux:cell>
                        @if($report->user)
                            {{ $report->user->email }}
                        @else
                            {{ $report->email ?? 'Anonymous' }}
                        @endif
                    </flux:cell>

                    <flux:cell>
                        {{ $report->created_at->diffForHumans() }}
                    </flux:cell>

                    <flux:cell>
                        @if($report->status === 'pending')
                            <div class="flex gap-2">
                                <flux:button
                                    wire:click="deleteContent({{ $report->id }})"
                                    variant="danger"
                                    size="sm"
                                >
                                    Delete
                                </flux:button>

                                <flux:button
                                    wire:click="banUser({{ $report->id }})"
                                    variant="danger"
                                    size="sm"
                                >
                                    Ban User
                                </flux:button>

                                <flux:button
                                    wire:click="dismissReport({{ $report->id }})"
                                    variant="ghost"
                                    size="sm"
                                >
                                    Dismiss
                                </flux:button>
                            </div>
                        @else
                            <span class="text-sm text-zinc-500">
                                {{ $report->status }} by {{ $report->dealtBy->name ?? 'System' }}
                            </span>
                        @endif
                    </flux:cell>
                </flux:row>
            @endforeach
        </flux:rows>
    </flux:table>

    <div class="mt-4">
        {{ $this->reports->links() }}
    </div>
</div>
@endvolt
```

---

## API Design

### Endpoints

#### Public Endpoints

**POST /api/v1/links**
Create a short link (anonymous or authenticated)

Request:
```json
{
  "url": "https://example.com/very/long/url",
  "expires_at": "2025-12-31" // optional
}
```

Response:
```json
{
  "success": true,
  "data": {
    "hash": "aB3xYz",
    "short_url": "https://anon.to/aB3xYz",
    "full_url": "https://example.com/very/long/url",
    "expires_at": null,
    "created_at": "2025-11-07T12:00:00Z"
  }
}
```

---

**GET /api/v1/links/{hash}**
Get link information (no redirect)

Response:
```json
{
  "success": true,
  "data": {
    "hash": "aB3xYz",
    "full_url": "https://example.com/very/long/url",
    "title": "Example Domain",
    "visits": 42,
    "created_at": "2025-11-07T12:00:00Z"
  }
}
```

---

**POST /api/v1/notes**
Create a note

Request:
```json
{
  "content": "This is my plain text note",
  "title": "My Note", // optional
  "expires_at": "1h", // 10m, 1h, 1d, 1w, 1m, never
  "password": "secret123", // optional
  "view_limit": 5 // optional
}
```

Response:
```json
{
  "success": true,
  "data": {
    "hash": "xY9zAb",
    "url": "https://anon.to/notes/xY9zAb",
    "expires_at": "2025-11-07T13:00:00Z",
    "view_limit": 5,
    "created_at": "2025-11-07T12:00:00Z"
  }
}
```

---

#### Authenticated Endpoints

**GET /api/v1/my/links**
List my links

Response:
```json
{
  "success": true,
  "data": [
    {
      "hash": "aB3xYz",
      "short_url": "https://anon.to/aB3xYz",
      "full_url": "https://example.com",
      "visits": 42,
      "created_at": "2025-11-07T12:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 100
  }
}
```

---

**DELETE /api/v1/my/links/{hash}**
Delete my link

Response:
```json
{
  "success": true,
  "message": "Link deleted successfully"
}
```

---

**GET /api/v1/analytics/{hash}**
Get detailed analytics

Response:
```json
{
  "success": true,
  "data": {
    "hash": "aB3xYz",
    "visits": 1234,
    "unique_visits": 892,
    "last_visited_at": "2025-11-07T11:59:00Z",
    "referrers": {
      "twitter.com": 45,
      "facebook.com": 32,
      "direct": 815
    },
    "countries": {
      "US": 450,
      "GB": 200,
      "CA": 150
    },
    "daily_visits": [
      {"date": "2025-11-01", "visits": 120},
      {"date": "2025-11-02", "visits": 145}
    ]
  }
}
```

---

### Rate Limiting

**Strategy**: Token bucket algorithm via Laravel's rate limiter

**Limits**:
- Anonymous: 20 requests/hour per IP
- Registered: 100 requests/hour per user
- Verified: 500 requests/hour per user
- Admin: Unlimited

**Headers**:
```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1699372800
```

**Implementation** (`app/Http/Middleware/RateLimitByUserType.php`):
```php
public function handle($request, Closure $next)
{
    $key = $request->user()
        ? 'api:' . $request->user()->id
        : 'api:' . $request->ip();

    $limit = match(true) {
        $request->user()?->is_admin => PHP_INT_MAX,
        $request->user()?->is_verified => 500,
        $request->user() => 100,
        default => 20,
    };

    RateLimiter::attempt(
        $key,
        $limit,
        fn() => true,
        3600 // 1 hour
    );

    return $next($request);
}
```

---

## Caching Strategy

### What to Cache

1. **Hot Links** (frequently accessed)
   - Key: `link:{hash}`
   - TTL: 24 hours
   - Invalidate: On link update/delete

2. **Hot Notes** (frequently viewed)
   - Key: `note:{hash}`
   - TTL: 1 hour (shorter due to burn-after-read)
   - Invalidate: On view increment

3. **Allow/Block Lists**
   - Key: `allow_list:domains`
   - TTL: 1 hour
   - Invalidate: On list update

4. **User Statistics**
   - Key: `user:{id}:stats`
   - TTL: 5 minutes
   - Invalidate: On user action

5. **Analytics Aggregations**
   - Key: `analytics:{hash}:daily`
   - TTL: 6 hours
   - Invalidate: Nightly rebuild

### Cache Driver

**Development**: File cache (default)
**Production**: Redis 7.x

**Redis Configuration**:
```
# .env
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
```

**Cache Tags** (Redis only):
```php
Cache::tags(['links', 'user:123'])->put('link:abc', $link, 3600);
Cache::tags(['user:123'])->flush(); // Clear all user's cached data
```

---

## Queue System

### Queue Jobs

1. **DeleteExpiredLinks**
   - Runs: Hourly via cron
   - Action: Delete links where `expires_at < now()`
   - Batch: 1000 at a time

2. **DeleteExpiredNotes**
   - Runs: Every 10 minutes via cron
   - Action: Delete notes where `expires_at < now()` or view_limit reached
   - Batch: 500 at a time

3. **CheckUrlReputation** (Optional)
   - Runs: On link creation (queued)
   - Action: Query Google Safe Browsing API
   - Mark link as reported if malicious

4. **SendReportNotification**
   - Runs: On new report
   - Action: Email admin team
   - Include: Report details, quick action links

5. **GenerateLinkPreview** (Future)
   - Runs: On link creation (queued)
   - Action: Fetch page title, description, og:image
   - Store: In link metadata

### Queue Configuration

**Driver**: Redis (production), Sync (development)

```
# .env
QUEUE_CONNECTION=redis
```

**Horizon** (optional but recommended):
```bash
composer require laravel/horizon
php artisan horizon:install
php artisan horizon
```

**Supervisor Configuration** (if not using Horizon):
```ini
[program:anon-worker]
command=php /path/to/artisan queue:work redis --sleep=3 --tries=3
autostart=true
autorestart=true
numprocs=3
user=www-data
```

---

## Security Measures

### 1. Input Validation

**URL Validation**:
- Laravel's `url` rule
- Check against allow/block lists
- Verify scheme is http/https only
- Reject local/internal IPs (SSRF prevention)
- Max URL length: 2048 characters

**Note Content**:
- Max size: 10MB
- Sanitize for display (no execution)
- XSS prevention via escaping

### 2. Rate Limiting

**Layers**:
1. IP-based (anonymous users)
2. User-based (authenticated users)
3. Endpoint-specific (e.g., report submission: 5/hour)

**Implementation**:
```php
RateLimiter::for('create-link', function (Request $request) {
    return $request->user()
        ? Limit::perHour(100)->by($request->user()->id)
        : Limit::perHour(20)->by($request->ip());
});
```

### 3. CAPTCHA Integration

**When to Show**:
- After 3 failed validation attempts
- If rate limit almost exceeded
- Suspicious user agent patterns

**Library**: hCaptcha or reCAPTCHA v3

```php
// In form request
public function rules(): array
{
    return [
        'url' => 'required|url',
        'h-captcha-response' => 'required|hcaptcha', // Custom rule
    ];
}
```

### 4. CSRF Protection

Laravel's built-in CSRF protection enabled for all POST/PUT/DELETE routes.

**API**: Sanctum tokens instead of CSRF

### 5. Password Hashing

**Notes**: Bcrypt (Laravel default)
**Users**: Bcrypt via Fortify

```php
// Never compare raw passwords
Hash::check($input, $hashed); // ✓
$input === $hashed; // ✗
```

### 6. SQL Injection Prevention

**Always use**:
- Eloquent ORM
- Query builder with bindings
- Never raw queries with user input

```php
// Safe
Link::where('hash', $userInput)->first();

// Unsafe
DB::select("SELECT * FROM links WHERE hash = '$userInput'");
```

### 7. XSS Prevention

**Blade Auto-Escaping**:
```blade
{{ $userContent }} // Escaped automatically
{!! $userContent !!} // Raw HTML (avoid!)
```

**For Plain Text Notes**:
- Always escape user content
- No raw HTML rendering
- Content Security Policy headers

### 8. Content Security Policy

```php
// In middleware
response()->header('Content-Security-Policy', implode('; ', [
    "default-src 'self'",
    "script-src 'self' 'unsafe-inline'",
    "style-src 'self' 'unsafe-inline'",
    "img-src 'self' data: https:",
    "font-src 'self' data:",
    "connect-src 'self'",
    "frame-ancestors 'none'",
]));
```

### 9. SSRF Prevention

**Reject Internal URLs**:
```php
protected function isInternalUrl(string $url): bool
{
    $host = parse_url($url, PHP_URL_HOST);

    // Reject localhost, 127.0.0.1, 192.168.x.x, 10.x.x.x, etc.
    return in_array($host, ['localhost', '127.0.0.1'])
        || str_starts_with($host, '192.168.')
        || str_starts_with($host, '10.')
        || str_starts_with($host, '172.16.');
}
```

### 10. Abuse Detection

**Honeypot Fields**:
```blade
<input type="text" name="website" style="display:none" tabindex="-1">
```

**Spam Patterns**:
```php
protected array $spamPatterns = [
    '/\b(viagra|cialis|casino|porn)\b/i',
    '/\b\d{10,}\b/', // Long numbers (credit cards?)
    '/http.*http.*http/', // Multiple URLs
];
```

**Duplicate Prevention**:
```php
// Block identical content from same IP within 1 hour
$hash = hash('sha256', $content . $ip);
if (Cache::has("submission:$hash")) {
    throw new TooManyRequestsHttpException();
}
Cache::put("submission:$hash", true, 3600);
```

---

## Performance Optimization

### 1. Database Indexing

**Critical Indexes**:
- `links.hash` (unique)
- `links.full_url_hash` (duplicate detection)
- `notes.hash` (unique)
- `link_analytics.link_id, visited_at` (composite)
- `users.email` (unique)
- `allow_lists.domain, type` (composite)

### 2. Eager Loading

**Prevent N+1 Queries**:
```php
// Bad
$reports = Report::all();
foreach ($reports as $report) {
    echo $report->user->name; // N+1 query
}

// Good
$reports = Report::with('user')->get();
foreach ($reports as $report) {
    echo $report->user->name; // Eager loaded
}
```

### 3. Query Optimization

**Use Chunk for Large Datasets**:
```php
Link::where('expires_at', '<', now())
    ->chunkById(1000, function ($links) {
        foreach ($links as $link) {
            $link->delete();
        }
    });
```

**Select Only Needed Columns**:
```php
Link::select(['hash', 'full_url', 'visits'])
    ->where('user_id', $userId)
    ->get();
```

### 4. HTTP Caching

**ETag Headers**:
```php
return response($content)
    ->setETag(md5($content))
    ->setPublic()
    ->setMaxAge(3600);
```

**Cache-Control for Static Assets**:
```
Cache-Control: public, max-age=31536000, immutable
```

### 5. CDN Integration

**Static Assets**:
- Serve CSS/JS/images via CDN
- Use Laravel Mix versioning for cache busting

**Configuration**:
```php
// .env
ASSET_URL=https://cdn.anon.to
```

### 6. Lazy Loading

**Images**:
```blade
<img src="qrcode.png" loading="lazy" alt="QR Code">
```

**Livewire Components**:
```blade
<livewire:my-links lazy />
```

---

## Testing Strategy

### Unit Tests

**Test Actions in Isolation**:
```php
use App\Actions\Links\GenerateHash;

test('generates unique 6-character hash', function () {
    $generator = new GenerateHash();
    $hash = $generator->execute();

    expect($hash)->toHaveLength(6);
});

test('excludes profane words', function () {
    $generator = new GenerateHash();

    for ($i = 0; $i < 100; $i++) {
        $hash = $generator->execute();
        expect(strtolower($hash))->not->toBeIn(config('anon.excluded_words'));
    }
});
```

### Feature Tests

**Test User Flows**:
```php
use App\Models\{User, Link};
use function Pest\Laravel\{actingAs, postJson, assertDatabaseHas};

test('authenticated user can create link', function () {
    $user = User::factory()->create();

    $response = actingAs($user)
        ->postJson('/api/v1/links', [
            'url' => 'https://example.com',
        ]);

    $response->assertSuccessful();
    expect($response->json('data.hash'))->toHaveLength(6);

    assertDatabaseHas('links', [
        'full_url' => 'https://example.com',
        'user_id' => $user->id,
    ]);
});

test('note with burn after reading is deleted after view limit', function () {
    $note = Note::factory()->create([
        'view_limit' => 1,
        'views' => 0,
    ]);

    // First view
    get("/notes/{$note->hash}")->assertOk();

    // Note should be deleted
    expect(Note::find($note->id))->toBeNull();
});
```

### Browser Tests (Pest 4)

**End-to-End Testing**:
```php
test('user can create and view note with password', function () {
    $page = visit('/notes/create');

    $page->fill('content', 'Secret message')
        ->check('password_protected')
        ->fill('password', 'secure123')
        ->click('Create Note')
        ->assertSee('Note created successfully');

    $hash = $page->url()->afterLast('/');

    // Try viewing without password
    $viewPage = visit("/notes/{$hash}");
    $viewPage->assertSee('This note is password protected');

    // Enter wrong password
    $viewPage->fill('password', 'wrong')
        ->click('Unlock')
        ->assertSee('Incorrect password');

    // Enter correct password
    $viewPage->fill('password', 'secure123')
        ->click('Unlock')
        ->assertSee('Secret message');
});
```

### Test Coverage Goals

- **Unit Tests**: 80%+ coverage
- **Feature Tests**: All critical paths
- **Browser Tests**: Key user flows

### CI/CD Pipeline

**GitHub Actions Workflow**:
```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.4
          extensions: mbstring, pdo_mysql

      - name: Install dependencies
        run: composer install

      - name: Run Pint
        run: vendor/bin/pint --test

      - name: Run tests
        run: php artisan test --parallel
```

---

## Deployment

### Environment Setup

**Required Services**:
- Web server (Nginx/Apache)
- PHP 8.4+ with extensions: mbstring, pdo, redis, gd
- MySQL 8.0+ or PostgreSQL 15+
- Redis 7.x
- Supervisor (for queues)
- Node.js 20+ (for asset compilation)

### Server Configuration

**Nginx**:
```nginx
server {
    listen 80;
    server_name anon.to;
    root /var/www/anon.to/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Deployment Script

```bash
#!/bin/bash

# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci
npm run build

# Run migrations
php artisan migrate --force

# Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
php artisan queue:restart
sudo supervisorctl restart anon-worker:*

# Reload PHP-FPM
sudo systemctl reload php8.4-fpm
```

### Environment Variables

```env
APP_NAME="anon.to"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://anon.to

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=anonto
DB_USERNAME=anonto_user
DB_PASSWORD=secure_password

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=ses
MAIL_FROM_ADDRESS=noreply@anon.to

LEGACY_DB_HOST=127.0.0.1
LEGACY_DB_DATABASE=anondb
LEGACY_DB_USERNAME=root
LEGACY_DB_PASSWORD=

GOOGLE_SAFE_BROWSING_KEY=your_api_key
```

---

## Monitoring & Maintenance

### Logging

**Channels**:
- `stack` (default): File + Slack alerts for errors
- `daily`: Rotating daily logs
- `slack`: Critical errors only

**Custom Log Channels**:
```php
'links' => [
    'driver' => 'daily',
    'path' => storage_path('logs/links.log'),
],
'security' => [
    'driver' => 'daily',
    'path' => storage_path('logs/security.log'),
],
```

### Metrics to Track

1. **Performance**:
   - Average response time
   - 95th percentile response time
   - Cache hit rate
   - Database query count per request

2. **Usage**:
   - Links created per day
   - Notes created per day
   - Total visits/views
   - Active users

3. **Security**:
   - Rate limit hits
   - Failed login attempts
   - Reports submitted
   - Blocked URLs

### Health Checks

**Endpoint**: `/up`

```php
Route::get('/up', function () {
    return response()->json([
        'status' => 'ok',
        'services' => [
            'database' => DB::connection()->getPdo() ? 'up' : 'down',
            'cache' => Cache::get('health:check') ? 'up' : 'down',
            'queue' => true, // TODO: check queue
        ],
    ]);
});
```

### Scheduled Tasks

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule): void
{
    $schedule->job(DeleteExpiredLinks::class)->hourly();
    $schedule->job(DeleteExpiredNotes::class)->everyTenMinutes();
    $schedule->command('telescope:prune')->daily();
    $schedule->command('horizon:snapshot')->everyFiveMinutes();
}
```

---

## Appendix

### Configuration Files

**config/anon.php** (custom config):
```php
return [
    'hash_length' => env('HASH_LENGTH', 6),
    'max_url_length' => env('MAX_URL_LENGTH', 2048),
    'max_note_size' => env('MAX_NOTE_SIZE', 10485760), // 10MB
    'default_cache_ttl' => env('CACHE_TTL', 86400), // 24 hours
    'excluded_words' => [...], // From old helpers.php
];
```

### Third-Party Libraries

**Composer**:
```json
{
  "require": {
    "chillerlan/php-qrcode": "^5.0",
    "guzzlehttp/guzzle": "^7.0",
    "laravel/horizon": "^5.0",
    "league/commonmark": "^2.0"
  }
}
```

**NPM**:
```json
{
  "devDependencies": {
    "@tailwindcss/forms": "^0.5",
    "vite": "^5.0"
  }
}
```

### Useful Commands

```bash
# Development
php artisan serve
npm run dev
php artisan queue:work

# Testing
php artisan test
php artisan test --filter=CreateLinkTest
vendor/bin/pint

# Production
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Import legacy data
php artisan import:legacy-data --dry-run
php artisan import:legacy-data --users --links

# Maintenance
php artisan down --secret="maintenance-token"
php artisan up
```

---

**Document Version**: 1.0
**Last Updated**: 2025-11-07
**Author**: Development Team
