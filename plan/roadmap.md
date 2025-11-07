# anon.to - Development Roadmap

## Project Overview

**Project**: anon.to - Anonymous URL Shortener + Notes Platform (Complete Rebuild)
**Framework**: Laravel 12 + Livewire 3 + Volt + Flux UI
**Timeline**: 14-17 weeks (~3.5-4 months)
**Team Size**: 1-3 developers
**Total Phases**: 18

### Goals
- Build modern, privacy-focused URL shortening and anonymous notes platform
- Migrate 242K links and 25K users from legacy database
- Implement robust security and abuse prevention
- Provide optional user accounts with management dashboards
- Deliver RESTful API for third-party integrations

---

## Timeline Overview

```
Weeks 1-3   : Foundation & Core (Phases 0-3)
Weeks 4-7   : User Features (Phases 4-7)
Weeks 8-10  : Analytics & Moderation (Phases 8-10)
Weeks 11-12 : API & Automation (Phases 11-12)
Weeks 13-14 : Migration & Optimization (Phases 13-14)
Weeks 15-16 : Security & Testing (Phases 15-16)
Weeks 17    : Launch (Phases 17-18)
```

### Milestones
- ✅ **Week 0**: Planning Complete (project.md, tech.md, roadmap.md)
- 🎯 **Week 3**: MVP - Basic URL shortening and notes working
- 🎯 **Week 7**: Beta - Full feature set with user dashboards
- 🎯 **Week 11**: Feature Complete - API and admin tools ready
- 🎯 **Week 14**: Migration Complete - Legacy data imported
- 🎯 **Week 17**: Production Launch - Live deployment

---

## Phase Details

### Phase 0: Foundation Setup
**Timeline**: Week 1 (5-7 days)
**Priority**: 🔴 Critical
**Effort**: 5-7 person-days
**Status**: ⬜ Not Started

#### Goals
Set up clean Laravel 12 environment and core infrastructure

#### Features
- [ ] Fresh Laravel 12 installation (already done)
- [ ] Database migrations for all tables
  - [ ] users (with Fortify 2FA columns)
  - [ ] links (with analytics columns)
  - [ ] notes (with expiration and password)
  - [ ] reports (polymorphic)
  - [ ] allow_lists
  - [ ] link_analytics
- [ ] Model creation with relationships
- [ ] Factory and seeder setup
- [ ] Basic routing structure
- [ ] Fortify configuration (already set up)
- [ ] Redis configuration for caching and queues
- [ ] Pest testing environment setup

#### Components to Create
```
database/migrations/
  - create_links_table.php
  - create_notes_table.php
  - create_reports_table.php
  - create_allow_lists_table.php
  - create_link_analytics_table.php

app/Models/
  - Link.php (with relationships)
  - Note.php (with relationships)
  - Report.php (polymorphic)
  - AllowList.php
  - LinkAnalytic.php

database/factories/
  - LinkFactory.php
  - NoteFactory.php
  - ReportFactory.php

database/seeders/
  - AllowListSeeder.php (initial blocked domains)
  - AdminUserSeeder.php
```

#### Deliverables
- ✅ Working database schema with all tables
- ✅ All models with properly defined relationships
- ✅ Factories for testing
- ✅ Basic authentication flow (Fortify)
- ✅ Test suite foundation

#### Tests
- [ ] Model relationship tests
- [ ] Factory generation tests
- [ ] Database migration rollback tests

#### Dependencies
- None (first phase)

#### Risks
- Database design changes later are costly
- Missing relationships discovered during development

#### Success Criteria
- All migrations run successfully
- All models have working factories
- Basic auth (login/register) works

---

### Phase 1: Core URL Shortener
**Timeline**: Week 2 (5-7 days)
**Priority**: 🔴 Critical
**Effort**: 7-10 person-days
**Status**: ⬜ Not Started

#### Goals
Implement basic link shortening functionality

#### Features
- [ ] Anonymous link creation (no account required)
- [ ] Hash generation (6 characters, avoid excluded words)
- [ ] URL validation and parsing (scheme, host, port, path, query, fragment)
- [ ] Duplicate detection via full_url_hash
- [ ] Basic link storage
- [ ] Simple redirect (direct, no warning page yet)
- [ ] Rate limiting (20/hour for anonymous users)
- [ ] IP tracking (hashed for privacy)

#### Components to Create
```
app/Actions/Links/
  - CreateLink.php (main orchestration)
  - GenerateHash.php (collision-free hash)
  - ValidateUrl.php (security checks)
  - CheckDuplicate.php (find existing)

app/Services/
  - UrlService.php (parse/reconstruct URLs)

app/Http/Controllers/Web/
  - RedirectController.php (handle /{hash})

app/Http/Middleware/
  - RateLimitByUserType.php

app/Http/Requests/
  - CreateLinkRequest.php

routes/web.php
  - POST /links (create)
  - GET /{hash} (redirect)

resources/views/livewire/
  - home.blade.php (Volt component with form)

config/
  - anon.php (excluded_words array)
```

#### Deliverables
- ✅ Working link creation via web form
- ✅ Hash generation with collision detection
- ✅ Direct redirects working
- ✅ Rate limiting enforced
- ✅ Duplicate URLs return existing hash

#### Tests
- [ ] Unit: Hash generation uniqueness
- [ ] Unit: Excluded words never generated
- [ ] Unit: URL parsing correctness
- [ ] Feature: Link creation flow
- [ ] Feature: Duplicate detection
- [ ] Feature: Rate limiting enforcement
- [ ] Feature: Invalid URL rejection

#### Dependencies
- Phase 0 (database and models)

#### Risks
- Hash collision in high volume (mitigated by 6-char = 56B combinations)
- Slow duplicate detection on large dataset (mitigated by indexed hash)

#### Success Criteria
- Can create 100 links in quick succession
- All links redirect correctly
- Rate limit blocks 21st attempt

---

### Phase 2: Anonymous Redirect Page
**Timeline**: Week 2-3 (3-4 days)
**Priority**: 🔴 Critical
**Effort**: 3-4 person-days
**Status**: ⬜ Not Started

#### Goals
Implement warning page before redirect for privacy and security

#### Features
- [ ] Anonymous redirect warning page
- [ ] Display parsed URL components (scheme, host, path, etc.)
- [ ] Security warnings for suspicious URLs
- [ ] Continue/Cancel buttons
- [ ] Cache link data for performance (24hr TTL)
- [ ] Visit tracking (increment counter)
- [ ] Last visited timestamp

#### Components to Create
```
app/Actions/Analytics/
  - RecordVisit.php (async visit tracking)

resources/views/livewire/
  - redirect.blade.php (Volt component)

Enhanced:
  app/Http/Controllers/Web/RedirectController.php
```

#### Deliverables
- ✅ Warning page shows before redirect
- ✅ URL components clearly displayed
- ✅ Visit counter increments
- ✅ Cached links load instantly

#### Tests
- [ ] Feature: Redirect warning page displays
- [ ] Feature: Continue button redirects
- [ ] Feature: Visit counter increments
- [ ] Feature: Cache hit on second visit
- [ ] Browser: Complete redirect flow (Pest 4)

#### Dependencies
- Phase 1 (basic redirect)

#### Risks
- Cache invalidation bugs
- Race conditions on visit counter

#### Success Criteria
- Warning page loads in < 100ms (cached)
- Visit counter accurate under concurrent load
- Users understand where they're going

---

### Phase 3: Basic Notes System
**Timeline**: Week 3 (5-7 days)
**Priority**: 🔴 Critical
**Effort**: 7-9 person-days
**Status**: ⬜ Not Started

#### Goals
Implement pastebin functionality with plain text

#### Features
- [ ] Anonymous note creation
- [ ] Plain text storage (up to 10MB)
- [ ] Note viewing
- [ ] Expiration options (10min, 1hr, 1day, 1week, 1month, never)
- [ ] Character/line counter (real-time)
- [ ] Raw text view
- [ ] Download as .txt file
- [ ] Hash generation (6-8 characters)

#### Components to Create
```
app/Actions/Notes/
  - CreateNote.php
  - GenerateNoteHash.php (similar to links)

resources/views/livewire/notes/
  - create.blade.php (Volt component with textarea)
  - show.blade.php (Volt component for viewing)

routes/web.php
  - GET /notes/create
  - POST /notes
  - GET /notes/{hash}
  - GET /notes/{hash}/raw
  - GET /notes/{hash}/download

app/Http/Controllers/Web/
  - NoteController.php (download, raw)
```

#### Deliverables
- ✅ Note creation working
- ✅ Notes display correctly
- ✅ Expiration logic functional
- ✅ Download works

#### Tests
- [ ] Feature: Create note with various expirations
- [ ] Feature: View note
- [ ] Feature: Download note
- [ ] Feature: Raw text view
- [ ] Feature: Expired notes return 410
- [ ] Unit: Expiration date calculation

#### Dependencies
- Phase 0 (Note model)

#### Risks
- Large notes (10MB) could cause memory issues
- No syntax highlighting yet (plain only)

#### Success Criteria
- Can create and view note in < 5 seconds
- 10MB note uploads successfully
- Expired notes inaccessible

---

### 🎯 MILESTONE: MVP Complete (End of Week 3)
**Deliverables**: Basic URL shortening + basic notes working
**Demo-able**: Yes, core functionality live

---

### Phase 4: User Dashboard
**Timeline**: Week 4 (5-7 days)
**Priority**: 🟡 High
**Effort**: 7-9 person-days
**Status**: ⬜ Not Started

#### Goals
User management dashboards for links and notes

#### Features
- [ ] "My Links" dashboard (table view)
- [ ] "My Notes" dashboard (table view)
- [ ] Search functionality (full-text)
- [ ] Filter by date, status, expiration
- [ ] Bulk delete actions
- [ ] User profile settings page
- [ ] Associate anonymous content with user after registration
- [ ] Pagination (20 items per page)

#### Components to Create
```
resources/views/livewire/
  - my-links.blade.php (Volt component)
  - my-notes.blade.php (Volt component)
  - settings/profile.blade.php (already exists, enhance)

app/Policies/
  - LinkPolicy.php (canView, canDelete)
  - NotePolicy.php (canView, canDelete)

routes/web.php (auth middleware group)
  - GET /my/links
  - GET /my/notes
  - DELETE /my/links/{hash}
  - DELETE /my/notes/{hash}
```

#### Deliverables
- ✅ Working dashboards for authenticated users
- ✅ Search and filter functional
- ✅ Bulk actions working
- ✅ Policies enforcing ownership

#### Tests
- [ ] Feature: User can view only their links
- [ ] Feature: User can delete their link
- [ ] Feature: User cannot delete others' links
- [ ] Feature: Search returns correct results
- [ ] Feature: Bulk delete works

#### Dependencies
- Phase 1, 3 (links and notes exist)
- Phase 0 (authentication)

#### Risks
- Performance on users with thousands of items
- Complex search queries

#### Success Criteria
- Dashboard loads in < 500ms with 100 items
- Search works across title and content
- Policies prevent unauthorized access

---

### Phase 5: Syntax Highlighting
**Timeline**: Week 5 (3-4 days)
**Priority**: 🟡 High
**Effort**: 3-5 person-days
**Status**: ⬜ Not Started

#### Goals
Add code highlighting for 50+ programming languages

#### Features
- [ ] Language auto-detection
- [ ] Manual language selection (dropdown with 50+ languages)
- [ ] Prism.js or Highlight.js integration
- [ ] Line numbers toggle
- [ ] Theme selection (light/dark)
- [ ] Copy code button
- [ ] Language badge display

#### Components to Create
```
resources/js/
  - prism-setup.js (initialization)

resources/views/components/
  - syntax-selector.blade.php

Enhanced:
  resources/views/livewire/notes/create.blade.php (add language picker)
  resources/views/livewire/notes/show.blade.php (add highlighting)

config/anon.php
  - syntax_languages array (50+ languages)
```

#### Deliverables
- ✅ Code highlights correctly
- ✅ 50+ languages supported
- ✅ Auto-detection works for common languages
- ✅ Copy button functional

#### Tests
- [ ] Feature: Language detection for PHP, JS, Python
- [ ] Feature: Manual language selection
- [ ] Browser: Code displays with highlighting

#### Dependencies
- Phase 3 (notes exist)

#### Risks
- Large code blocks slow to render
- XSS vulnerabilities if not properly escaped

#### Success Criteria
- Highlighting renders in < 100ms
- No XSS possible through code injection
- Mobile responsive

---

### Phase 6: Advanced Note Features
**Timeline**: Week 5 (4-5 days)
**Priority**: 🟡 High
**Effort**: 5-7 person-days
**Status**: ⬜ Not Started

#### Goals
Password protection and burn-after-reading

#### Features
- [ ] Password protection (bcrypt hashing)
- [ ] Password unlock flow with validation
- [ ] Burn after reading (view limits: 1, 5, 10, 50)
- [ ] Atomic view counter (race condition safe)
- [ ] Auto-delete on burn limit reached
- [ ] Fork/clone functionality
- [ ] View limit warning banner

#### Components to Create
```
app/Actions/Notes/
  - EncryptNote.php (password hashing)
  - IncrementViews.php (atomic counter)
  - CheckBurnLimit.php (auto-delete logic)
  - ForkNote.php (clone to new hash)

Enhanced:
  resources/views/livewire/notes/create.blade.php (password + burn options)
  resources/views/livewire/notes/show.blade.php (unlock flow)

Migration:
  - Add password_hash, view_limit, views columns to notes
```

#### Deliverables
- ✅ Password-protected notes working
- ✅ Burn-after-reading functional
- ✅ Fork creates new note from existing
- ✅ No race conditions on view counter

#### Tests
- [ ] Feature: Password protection locks note
- [ ] Feature: Wrong password rejected
- [ ] Feature: Correct password unlocks
- [ ] Feature: Burn limit auto-deletes
- [ ] Feature: Fork creates new note
- [ ] Unit: Atomic view increment under load

#### Dependencies
- Phase 3 (notes)
- Phase 5 (syntax highlighting to preserve in fork)

#### Risks
- Password brute-forcing (need rate limiting)
- Race condition on view counter

#### Success Criteria
- Password unlock works 100% reliably
- Note deletes immediately on burn limit
- No duplicate views counted

---

### Phase 7: Link Enhancements
**Timeline**: Week 6 (3-5 days)
**Priority**: 🟡 High
**Effort**: 4-6 person-days
**Status**: ⬜ Not Started

#### Goals
QR codes and link expiration

#### Features
- [ ] QR code generation (PNG download)
- [ ] QR code display on link page
- [ ] Link expiration dates
- [ ] Auto-delete expired links (scheduled job)
- [ ] Expiration countdown display

#### Components to Create
```
app/Services/
  - QrCodeService.php (using chillerlan/php-qrcode)

app/Jobs/
  - DeleteExpiredLinks.php (hourly cron)

app/Http/Controllers/Web/
  - QrCodeController.php (generate and download)

resources/views/components/
  - expiration-picker.blade.php

routes/web.php
  - GET /links/{hash}/qr
  - GET /links/{hash}/qr/download

composer.json
  - Add chillerlan/php-qrcode
```

#### Deliverables
- ✅ QR codes generate correctly
- ✅ Link expiration functional
- ✅ Expired links auto-deleted

#### Tests
- [ ] Feature: QR code generation
- [ ] Feature: Expiration countdown
- [ ] Feature: Expired link returns 410
- [ ] Unit: DeleteExpiredLinks job

#### Dependencies
- Phase 1 (links)

#### Risks
- QR code generation slow for batch operations
- Job queue not running (expired links pile up)

#### Success Criteria
- QR code generates in < 200ms
- Expired links deleted within 1 hour

---

### 🎯 MILESTONE: Beta Complete (End of Week 7)
**Deliverables**: Full feature set with user dashboards
**Demo-able**: Yes, production-ready core features

---

### Phase 8: Analytics System
**Timeline**: Week 7 (5-7 days)
**Priority**: 🟢 Medium
**Effort**: 7-10 person-days
**Status**: ⬜ Not Started

#### Goals
Visit tracking and analytics dashboard

#### Features
- [ ] Detailed visit tracking (IP, referrer, country, user-agent)
- [ ] Geographic data (country-level via GeoIP)
- [ ] Referrer analysis
- [ ] Link analytics dashboard
- [ ] Charts and visualizations (visits over time)
- [ ] Popular links view (top 10)
- [ ] Analytics API endpoint
- [ ] Privacy-focused (hashed IPs, no PII)
- [ ] Analytics data aggregation (daily rollups)

#### Components to Create
```
app/Actions/Analytics/
  - RecordVisit.php (enhanced with more data)
  - AggregateAnalytics.php (daily rollup)

app/Jobs/
  - AggregateAnalytics.php (nightly cron)

resources/views/livewire/analytics/
  - link-analytics.blade.php (Volt component with charts)

Third-party:
  - Chart.js or ApexCharts integration
  - GeoIP2 database (optional)

routes/web.php
  - GET /links/{hash}/analytics (auth required)
```

#### Deliverables
- ✅ Visit tracking storing detailed data
- ✅ Analytics dashboard with charts
- ✅ Geographic and referrer insights
- ✅ Privacy compliant (hashed IPs)

#### Tests
- [ ] Feature: Visit recorded with all data
- [ ] Feature: Analytics display correctly
- [ ] Feature: Daily aggregation job
- [ ] Feature: Privacy (no raw IPs stored)
- [ ] Unit: IP hashing function

#### Dependencies
- Phase 1, 2 (links and redirects)
- Phase 4 (auth for viewing analytics)

#### Risks
- Analytics table grows very large
- Chart rendering slow with large datasets
- GeoIP database licensing costs

#### Success Criteria
- Analytics page loads in < 1 second
- Charts render with 10K+ data points
- No PII stored in database

---

### Phase 9: Abuse Prevention & Moderation
**Timeline**: Week 8 (5-7 days)
**Priority**: 🟡 High
**Effort**: 6-8 person-days
**Status**: ⬜ Not Started

#### Goals
Domain filtering and reporting system

#### Features
- [ ] Domain allow/block lists
- [ ] Pattern matching (exact, wildcard, regex)
- [ ] Block entire TLDs
- [ ] Abuse report submission (public form)
- [ ] Report categories (spam, malware, illegal, copyright, harassment, other)
- [ ] Email + comment required for reports
- [ ] IP logging for reports
- [ ] Report queue for admins
- [ ] Automated URL scanning (Google Safe Browsing - optional)

#### Components to Create
```
app/Http/Middleware/
  - CheckAllowList.php (validate before link creation)

app/Services/
  - SafeBrowsingService.php (Google API integration - optional)

resources/views/
  - report.blade.php (public report form)

resources/views/livewire/admin/
  - allow-lists.blade.php (Volt component)

routes/web.php
  - GET /report
  - POST /report

app/Http/Requests/
  - ReportContentRequest.php
```

#### Deliverables
- ✅ Allow/block lists enforced on link creation
- ✅ Report form accessible to public
- ✅ Reports stored with all context
- ✅ Pattern matching working (exact, wildcard, regex)

#### Tests
- [ ] Feature: Blocked domain rejected
- [ ] Feature: Allowed domain accepted
- [ ] Feature: Wildcard pattern matching
- [ ] Feature: Report submission
- [ ] Feature: Report requires email and comment
- [ ] Unit: Pattern matching logic

#### Dependencies
- Phase 1 (links)
- Phase 0 (Report model)

#### Risks
- Regex patterns could be exploited (ReDoS)
- False positives blocking legitimate domains
- Report spam from bots

#### Success Criteria
- Blocked domains rejected instantly
- No false positives on legitimate URLs
- Report form has CAPTCHA after 3 submissions

---

### Phase 10: Admin Dashboard
**Timeline**: Week 9 (5-7 days)
**Priority**: 🟡 High
**Effort**: 8-10 person-days
**Status**: ⬜ Not Started

#### Goals
Complete admin moderation tools

#### Features
- [ ] Admin overview dashboard (real-time stats)
- [ ] Statistics (total links, notes, users, reports)
- [ ] Recent activity feed (live updates)
- [ ] Link management (search, view, edit, delete)
- [ ] Note management (search, view, delete)
- [ ] User management (search, ban, verify, promote to admin)
- [ ] Report moderation queue (deal, dismiss, ban user)
- [ ] Allow/block list management (add, edit, remove, import CSV)
- [ ] Audit logging (all admin actions)
- [ ] One-click actions (delete + ban user)

#### Components to Create
```
resources/views/livewire/admin/
  - dashboard.blade.php (Volt - overview)
  - links.blade.php (Volt - link management)
  - notes.blade.php (Volt - note management)
  - users.blade.php (Volt - user management)
  - reports.blade.php (Volt - report queue)
  - allow-lists.blade.php (Volt - domain lists)
  - audit-log.blade.php (Volt - action history)

app/Models/
  - AuditLog.php (track admin actions)

routes/web.php (admin middleware)
  - GET /admin
  - GET /admin/links
  - GET /admin/notes
  - GET /admin/users
  - GET /admin/reports
  - GET /admin/allow-lists
  - GET /admin/audit-log

Middleware:
  - EnsureUserIsAdmin.php
```

#### Deliverables
- ✅ Complete admin dashboard
- ✅ All management interfaces functional
- ✅ Bulk actions working
- ✅ Audit log tracking everything

#### Tests
- [ ] Feature: Admin can access dashboard
- [ ] Feature: Non-admin blocked from admin area
- [ ] Feature: Admin can delete link
- [ ] Feature: Admin can ban user
- [ ] Feature: Report moderation works
- [ ] Feature: Audit log records actions
- [ ] Feature: Bulk actions work

#### Dependencies
- Phase 1, 3 (links, notes)
- Phase 9 (reports)
- Phase 0 (users)

#### Risks
- Admin dashboard slow with large datasets
- Missing authorization checks (security risk)

#### Success Criteria
- Admin dashboard loads in < 500ms
- All actions have authorization gates
- Audit log immutable

---

### 🎯 MILESTONE: Feature Complete (End of Week 10)
**Deliverables**: All core features + admin tools + analytics
**Demo-able**: Yes, feature-complete system

---

### Phase 11: API Development
**Timeline**: Week 10 (5-7 days)
**Priority**: 🟢 Medium
**Effort**: 7-9 person-days
**Status**: ⬜ Not Started

#### Goals
RESTful API with authentication

#### Features
- [ ] Public endpoints (create link/note, get info)
- [ ] Authenticated endpoints (list, delete, analytics)
- [ ] Sanctum token authentication
- [ ] API rate limiting (tiered: 20/100/500 per hour)
- [ ] API documentation (OpenAPI/Swagger)
- [ ] CORS configuration
- [ ] Versioning (/api/v1)
- [ ] JSON error responses
- [ ] API Resources for consistent formatting

#### Components to Create
```
app/Http/Controllers/Api/V1/
  - LinkController.php (index, store, show, destroy)
  - NoteController.php (index, store, show, destroy)
  - AnalyticsController.php (show)

app/Http/Resources/
  - LinkResource.php
  - NoteResource.php
  - AnalyticsResource.php

routes/api.php (v1 routes)
  - Public: POST /api/v1/links, GET /api/v1/links/{hash}
  - Public: POST /api/v1/notes, GET /api/v1/notes/{hash}
  - Auth: GET /api/v1/my/links, DELETE /api/v1/my/links/{hash}
  - Auth: GET /api/v1/my/notes, DELETE /api/v1/my/notes/{hash}
  - Auth: GET /api/v1/analytics/{hash}

config/cors.php
  - Configure allowed origins

Sanctum:
  - Personal access token generation UI
```

#### Deliverables
- ✅ Working API endpoints
- ✅ Sanctum authentication
- ✅ API documentation
- ✅ Rate limiting enforced

#### Tests
- [ ] Feature: Public link creation via API
- [ ] Feature: Authenticated link listing
- [ ] Feature: Token authentication works
- [ ] Feature: Rate limiting enforced
- [ ] Feature: CORS headers correct
- [ ] Feature: Error responses formatted correctly

#### Dependencies
- Phase 1, 3 (links, notes)
- Phase 4 (authentication)

#### Risks
- API versioning complexity
- Rate limiting bypass via multiple IPs

#### Success Criteria
- API responds in < 100ms (cached)
- Documentation complete and accurate
- Rate limits prevent abuse

---

### Phase 12: Background Jobs & Automation
**Timeline**: Week 10-11 (3-4 days)
**Priority**: 🟡 High
**Effort**: 4-5 person-days
**Status**: ⬜ Not Started

#### Goals
Scheduled tasks and queue processing

#### Features
- [ ] Delete expired links (hourly cron)
- [ ] Delete expired notes (every 10 minutes cron)
- [ ] Google Safe Browsing integration (optional, queued)
- [ ] Report notifications to admins (queued, email)
- [ ] Link metadata fetching (title, description - queued)
- [ ] Queue configuration (Redis driver)
- [ ] Laravel Horizon dashboard (queue monitoring)
- [ ] Failed job handling and retry logic

#### Components to Create
```
app/Jobs/
  - DeleteExpiredLinks.php (dispatch hourly)
  - DeleteExpiredNotes.php (dispatch every 10 min)
  - CheckUrlReputation.php (dispatch on link creation)
  - SendReportNotification.php (dispatch on report)
  - FetchLinkMetadata.php (dispatch on link creation)

app/Console/Kernel.php
  - Schedule all recurring jobs

config/queue.php
  - Configure Redis connection

config/horizon.php
  - Configure Horizon dashboard

Supervisor config:
  - horizon.conf (process manager)
```

#### Deliverables
- ✅ All jobs working
- ✅ Scheduler running
- ✅ Horizon monitoring active
- ✅ Failed jobs retrying

#### Tests
- [ ] Unit: DeleteExpiredLinks job logic
- [ ] Unit: DeleteExpiredNotes job logic
- [ ] Feature: Scheduled job runs on time
- [ ] Feature: Failed job retries

#### Dependencies
- Phase 7 (expiration)
- Phase 9 (reports)

#### Risks
- Queue worker not running (jobs pile up)
- Memory leaks in long-running workers
- External API failures (Safe Browsing)

#### Success Criteria
- Jobs process within 1 minute of dispatch
- No failed jobs in production
- Horizon accessible to admins

---

### Phase 13: Legacy Data Migration
**Timeline**: Week 11 (4-5 days)
**Priority**: 🟡 High
**Effort**: 5-7 person-days
**Status**: ⬜ Not Started

#### Goals
Import old database (242K links, 25K users)

#### Features
- [ ] Import command (`import:legacy-data`)
- [ ] Dry-run mode (test without writing)
- [ ] User migration (username, email, password hashes)
- [ ] Link migration (preserve hashes, visits, timestamps)
- [ ] Report migration (polymorphic mapping)
- [ ] Allow list migration (domain block list)
- [ ] Progress reporting (progress bar)
- [ ] Validation and verification (count checks)
- [ ] Rollback strategy (transaction support)
- [ ] Chunked processing (1000 records at a time)

#### Components to Create
```
app/Console/Commands/
  - ImportLegacyData.php (main command)

app/Services/
  - LegacyMigrationService.php (migration logic)
  - LegacyUserMapper.php (map old to new user)
  - LegacyLinkMapper.php (map old to new link)

config/database.php
  - Add legacy database connection

.env
  - LEGACY_DB_HOST, LEGACY_DB_DATABASE, etc.

Tests:
  - Import dry-run validation
  - Data integrity checks
```

#### Deliverables
- ✅ All legacy data imported
- ✅ Data integrity validated
- ✅ Zero data loss
- ✅ Performance acceptable (< 1 hour total)

#### Tests
- [ ] Feature: Dry-run import works
- [ ] Feature: User import preserves data
- [ ] Feature: Link import preserves data
- [ ] Feature: Hash collisions handled
- [ ] Feature: Progress reporting accurate
- [ ] Unit: Data mapping logic

#### Dependencies
- Phase 0, 1, 3, 9 (all models exist)
- Legacy database accessible

#### Risks
- Hash collisions with new data
- Password hash compatibility
- Data corruption during import
- Import takes too long (> 4 hours)

#### Success Criteria
- 95%+ of records imported successfully
- All hashes still work (old links redirect)
- Users can login with old passwords
- Import completes in < 2 hours

---

### Phase 14: Performance Optimization
**Timeline**: Week 12 (4-5 days)
**Priority**: 🟢 Medium
**Effort**: 5-6 person-days
**Status**: ⬜ Not Started

#### Goals
Caching, indexing, and performance tuning

#### Features
- [ ] Redis caching implementation
- [ ] Cache warming strategies (prime hot links)
- [ ] Database query optimization (eliminate N+1)
- [ ] Eager loading review (relationships)
- [ ] Index optimization (analyze slow queries)
- [ ] CDN setup (optional, Cloudflare)
- [ ] Asset optimization (minify, compress)
- [ ] HTTP caching headers (ETag, Cache-Control)
- [ ] Database connection pooling
- [ ] Query result caching

#### Components to Create
```
app/Services/
  - CacheService.php (centralized cache logic)

app/Console/Commands/
  - WarmCache.php (prime popular links)
  - AnalyzeSlowQueries.php (find N+1)

config/cache.php
  - Redis tags configuration

Middleware:
  - SetCacheHeaders.php (HTTP caching)

Performance tests:
  - Load testing scripts (Apache Bench or k6)
```

#### Deliverables
- ✅ Cache hit rate > 80%
- ✅ Page load time < 500ms (cached)
- ✅ No N+1 queries
- ✅ Database indexes optimized

#### Tests
- [ ] Performance: Link redirect < 100ms
- [ ] Performance: Dashboard load < 500ms
- [ ] Performance: Cache hit rate monitoring
- [ ] Unit: Cache invalidation logic

#### Dependencies
- All previous phases (optimize existing code)

#### Risks
- Over-caching causes stale data
- Cache invalidation bugs
- CDN costs

#### Success Criteria
- 95th percentile response time < 500ms
- Cache hit rate consistently > 80%
- Zero N+1 queries detected

---

### 🎯 MILESTONE: Migration & Optimization Complete (End of Week 12)
**Deliverables**: Legacy data imported, system optimized
**Demo-able**: Yes, production-ready performance

---

### Phase 15: Security Hardening
**Timeline**: Week 12-13 (3-4 days)
**Priority**: 🔴 Critical
**Effort**: 4-5 person-days
**Status**: ⬜ Not Started

#### Goals
Security audit and hardening

#### Features
- [ ] CAPTCHA integration (hCaptcha or reCAPTCHA)
- [ ] Content Security Policy headers (CSP)
- [ ] SSRF prevention (reject internal IPs)
- [ ] XSS prevention audit (escape all user input)
- [ ] SQL injection review (no raw queries)
- [ ] Rate limiting refinement (exponential backoff)
- [ ] Abuse detection patterns (spam keywords)
- [ ] Security headers (HSTS, X-Frame-Options, etc.)
- [ ] Input sanitization review
- [ ] Dependency vulnerability scan (composer audit)

#### Components to Create
```
app/Http/Middleware/
  - RequireCaptcha.php (after suspicious activity)
  - SetSecurityHeaders.php (CSP, HSTS, etc.)

app/Services/
  - CaptchaService.php (hCaptcha API)

config/security.php
  - Security configuration

Validation Rules:
  - NoInternalIpRule.php (prevent SSRF)
  - NoScriptTagsRule.php (prevent XSS)

Third-party:
  - hCaptcha or reCAPTCHA package
```

#### Deliverables
- ✅ CAPTCHA working
- ✅ Security headers set
- ✅ SSRF prevented
- ✅ No XSS vulnerabilities
- ✅ No SQL injection vulnerabilities

#### Tests
- [ ] Security: SSRF attack blocked (127.0.0.1, 192.168.x.x)
- [ ] Security: XSS attempt escaped
- [ ] Security: SQL injection prevented
- [ ] Security: CAPTCHA required after threshold
- [ ] Security: CSP headers present
- [ ] Security: HTTPS enforced (production)

#### Dependencies
- All previous phases (audit existing code)

#### Risks
- CAPTCHA impacts UX
- CSP breaks external resources
- False positives on SSRF check

#### Success Criteria
- OWASP Top 10 compliant
- Zero high/critical vulnerabilities
- Security headers score A+ (securityheaders.com)

---

### Phase 16: Testing & Quality Assurance
**Timeline**: Week 13 (5-7 days)
**Priority**: 🔴 Critical
**Effort**: 7-10 person-days
**Status**: ⬜ Not Started

#### Goals
Comprehensive testing coverage

#### Features
- [ ] Complete unit test coverage (80%+ coverage)
- [ ] Feature test coverage (all user flows)
- [ ] Browser tests (Pest 4, critical paths)
- [ ] API tests (all endpoints)
- [ ] Performance tests (load testing)
- [ ] Security tests (OWASP ZAP scan)
- [ ] Cross-browser testing (Chrome, Firefox, Safari)
- [ ] Mobile responsive testing (iOS, Android)
- [ ] Accessibility audit (WCAG 2.1 AA)
- [ ] Regression test suite

#### Components to Create
```
tests/Unit/
  - Complete unit test coverage (Actions, Services)

tests/Feature/
  - Complete feature test coverage (all controllers)

tests/Browser/
  - End-to-end user flows (Pest 4)

tests/Performance/
  - Load testing scripts (k6 or Locust)

CI/CD:
  - GitHub Actions workflow (run tests on push)
  - Code coverage reporting (Codecov)

Documentation:
  - Test documentation
  - QA checklist
```

#### Deliverables
- ✅ 80%+ code coverage
- ✅ All tests passing
- ✅ CI/CD pipeline working
- ✅ No critical bugs

#### Tests
- [ ] Unit: 80%+ coverage target
- [ ] Feature: All user flows covered
- [ ] Browser: Critical paths tested
- [ ] Performance: System handles 10K concurrent users
- [ ] Security: OWASP ZAP scan clean
- [ ] Accessibility: WCAG 2.1 AA compliance

#### Dependencies
- All previous phases (test everything)

#### Risks
- Low test coverage areas
- Flaky browser tests
- Performance bottlenecks discovered late

#### Success Criteria
- All tests green on CI
- Code coverage > 80%
- No critical or high bugs
- Performance targets met

---

### Phase 17: Documentation & Polish
**Timeline**: Week 14 (3-4 days)
**Priority**: 🟢 Medium
**Effort**: 3-4 person-days
**Status**: ⬜ Not Started

#### Goals
User documentation and UI polish

#### Features
- [ ] User guide (how to use the platform)
- [ ] API documentation (OpenAPI spec + examples)
- [ ] Terms of Service page
- [ ] Privacy Policy page
- [ ] FAQ page
- [ ] About page
- [ ] UI/UX refinements (polish rough edges)
- [ ] Error messages improvement (user-friendly)
- [ ] Accessibility audit fixes (WCAG 2.1 AA)
- [ ] Mobile responsive fixes

#### Components to Create
```
resources/views/
  - terms.blade.php (Terms of Service)
  - privacy.blade.php (Privacy Policy)
  - faq.blade.php (FAQ)
  - about.blade.php (About)
  - guide.blade.php (User Guide)

public/
  - API documentation (Swagger UI or similar)

Documentation:
  - README.md (setup instructions)
  - CONTRIBUTING.md (for future contributors)
  - DEPLOYMENT.md (deployment guide)
```

#### Deliverables
- ✅ Complete user documentation
- ✅ API documentation
- ✅ Legal pages (Terms, Privacy)
- ✅ UI polish complete

#### Tests
- [ ] Browser: All pages accessible
- [ ] Accessibility: Screen reader compatible
- [ ] Accessibility: Keyboard navigation works

#### Dependencies
- Phase 11 (API for documentation)

#### Risks
- Legal review needed for ToS/Privacy
- Documentation outdated quickly

#### Success Criteria
- All documentation pages live
- Legal pages reviewed by lawyer (if applicable)
- UI looks professional

---

### Phase 18: Deployment & Launch
**Timeline**: Week 14-15 (3-5 days)
**Priority**: 🔴 Critical
**Effort**: 4-6 person-days
**Status**: ⬜ Not Started

#### Goals
Production deployment and monitoring

#### Features
- [ ] Production environment setup (VPS/cloud)
- [ ] Server configuration (Nginx, PHP-FPM, Redis, MySQL)
- [ ] SSL certificate (Let's Encrypt)
- [ ] Domain configuration (DNS, CDN)
- [ ] Monitoring setup (logs, metrics, alerts)
- [ ] Backup strategy (daily automated backups)
- [ ] Deployment script (zero-downtime)
- [ ] Health checks (uptime monitoring)
- [ ] Rollback plan (revert to previous version)
- [ ] Launch announcement

#### Components to Create
```
Deployment:
  - deploy.sh (automated deployment script)
  - .env.production (production environment)
  - nginx.conf (web server config)
  - supervisor.conf (queue worker)
  - backup.sh (daily backup script)

Monitoring:
  - Sentry integration (error tracking)
  - Laravel Telescope (debugging)
  - Uptime monitoring (UptimeRobot or Pingdom)

Documentation:
  - DEPLOYMENT.md (deployment guide)
  - ROLLBACK.md (rollback procedure)
```

#### Deliverables
- ✅ Production environment live
- ✅ SSL certificate active
- ✅ Monitoring in place
- ✅ Backups automated
- ✅ Launch successful

#### Tests
- [ ] Smoke tests on production
- [ ] Health check endpoint working
- [ ] Backup restoration test
- [ ] Rollback procedure test

#### Dependencies
- All previous phases (deploy complete system)

#### Risks
- Production issues not seen in dev
- DNS propagation delays
- SSL certificate issues
- Downtime during deployment

#### Success Criteria
- Zero downtime deployment
- All services healthy
- 99.9% uptime in first week
- Monitoring alerts working

---

### 🎯 MILESTONE: Production Launch (End of Week 17)
**Deliverables**: Live, production-ready system
**Demo-able**: Public launch!

---

## Phase Dependencies

### Critical Path (Must be sequential)
```
Phase 0 → Phase 1 → Phase 2 → Phase 3 → Phase 13 → Phase 18
```

### Parallel Work Opportunities

**Can run parallel:**
- Phase 5 + Phase 6 (both enhance notes)
- Phase 8 + Phase 9 (independent features)
- Phase 11 + Phase 12 (API + jobs independent)
- Phase 14 + Phase 15 (optimization + security can overlap)

**Depends on multiple phases:**
- Phase 4 depends on Phase 1, 3 (needs links and notes)
- Phase 10 depends on Phase 1, 3, 9 (admin for everything)
- Phase 13 depends on Phase 0, 1, 3, 9 (all models)
- Phase 16 depends on ALL phases (test everything)

---

## Resource Planning

### Team Size Recommendations

**Option 1: Solo Developer**
- Duration: 17 weeks
- Work full-time on project
- All phases sequential

**Option 2: 2 Developers**
- Duration: 10-12 weeks
- Split phases (one on core, one on features)
- Parallel work opportunities

**Option 3: 3 Developers**
- Duration: 8-10 weeks
- Frontend, backend, DevOps split
- Maximum parallelization

### Effort Breakdown by Category

| Category | Phases | Person-Days | Percentage |
|----------|--------|-------------|------------|
| Core Features | 0-3 | 22-27 | 25% |
| User Features | 4-7 | 28-33 | 30% |
| Admin & Analytics | 8-10 | 21-27 | 23% |
| API & Jobs | 11-12 | 11-14 | 12% |
| Migration | 13 | 5-7 | 6% |
| Quality & Launch | 14-18 | 23-30 | 26% |
| **TOTAL** | **0-18** | **110-138** | **100%** |

---

## Risk Management

### High-Risk Items
1. **Phase 13**: Legacy data migration (data loss risk)
2. **Phase 15**: Security hardening (vulnerabilities)
3. **Phase 18**: Production deployment (downtime)

### Mitigation Strategies
1. **Migration**: Dry-run mode, backups, rollback plan
2. **Security**: External audit, penetration testing
3. **Deployment**: Zero-downtime strategy, health checks

### Contingency Plan
- Add 20% buffer to timeline (3-4 extra weeks)
- Prioritize MVP phases (0-4) over nice-to-haves
- Defer Phase 8 (analytics) if behind schedule
- Defer Phase 11 (API) if not critical for launch

---

## Success Metrics

### Phase Completion Criteria
- [ ] All features implemented
- [ ] All tests passing
- [ ] Code reviewed
- [ ] Documentation updated
- [ ] Demo-able to stakeholders

### Project Success Criteria
- [ ] 95%+ legacy data migrated
- [ ] 99.9% uptime in first month
- [ ] < 1% abuse rate
- [ ] 80%+ code coverage
- [ ] OWASP Top 10 compliant
- [ ] Page load < 500ms (95th percentile)
- [ ] No critical bugs in production

---

## Progress Tracking

### Weekly Check-ins
- [ ] Week 1: Phase 0 complete
- [ ] Week 2: Phase 1 complete
- [ ] Week 3: Phase 2-3 complete (MVP milestone)
- [ ] Week 4: Phase 4 complete
- [ ] Week 5: Phase 5-6 complete
- [ ] Week 6: Phase 7 complete
- [ ] Week 7: Phase 8 complete (Beta milestone)
- [ ] Week 8: Phase 9 complete
- [ ] Week 9: Phase 10 complete
- [ ] Week 10: Phase 11-12 complete (Feature complete milestone)
- [ ] Week 11: Phase 13 complete
- [ ] Week 12: Phase 14 complete (Migration milestone)
- [ ] Week 13: Phase 15-16 complete
- [ ] Week 14: Phase 17 complete
- [ ] Week 15-17: Phase 18 complete (Launch milestone)

### Burn-down Chart Template
```
Week | Remaining Phases | Completed Phases | On Track?
-----|------------------|------------------|----------
  1  |        17        |        1         |    ✓
  2  |        16        |        2         |    ✓
  3  |        15        |        3         |    ✓
  4  |        14        |        4         |    ✓
  5  |        12        |        6         |    ✓
  6  |        11        |        7         |    ✓
  7  |        10        |        8         |    ✓
  8  |         9        |        9         |    ✓
  9  |         8        |       10         |    ✓
 10  |         6        |       12         |    ✓
 11  |         5        |       13         |    ✓
 12  |         4        |       14         |    ✓
 13  |         2        |       16         |    ✓
 14  |         1        |       17         |    ✓
 15  |         0        |       18         |    ✅
```

---

## Appendix

### Phase Summary Table

| Phase | Name | Duration | Priority | Effort | Dependencies |
|-------|------|----------|----------|--------|--------------|
| 0 | Foundation Setup | 5-7d | 🔴 Critical | 5-7pd | None |
| 1 | Core URL Shortener | 5-7d | 🔴 Critical | 7-10pd | 0 |
| 2 | Anonymous Redirect | 3-4d | 🔴 Critical | 3-4pd | 1 |
| 3 | Basic Notes | 5-7d | 🔴 Critical | 7-9pd | 0 |
| 4 | User Dashboard | 5-7d | 🟡 High | 7-9pd | 1,3 |
| 5 | Syntax Highlighting | 3-4d | 🟡 High | 3-5pd | 3 |
| 6 | Advanced Notes | 4-5d | 🟡 High | 5-7pd | 3,5 |
| 7 | Link Enhancements | 5-7d | 🟡 High | 6-8pd | 1,4 |
| 8 | Analytics | 5-7d | 🟢 Medium | 7-10pd | 1,2,4 |
| 9 | Abuse Prevention | 5-7d | 🟡 High | 6-8pd | 1 |
| 10 | Admin Dashboard | 5-7d | 🟡 High | 8-10pd | 1,3,9 |
| 11 | API | 5-7d | 🟢 Medium | 7-9pd | 1,3,4 |
| 12 | Background Jobs | 3-4d | 🟡 High | 4-5pd | 7,9 |
| 13 | Migration | 4-5d | 🟡 High | 5-7pd | 0,1,3,9 |
| 14 | Optimization | 4-5d | 🟢 Medium | 5-6pd | ALL |
| 15 | Security | 3-4d | 🔴 Critical | 4-5pd | ALL |
| 16 | Testing | 5-7d | 🔴 Critical | 7-10pd | ALL |
| 17 | Documentation | 3-4d | 🟢 Medium | 3-4pd | 11 |
| 18 | Deployment | 3-5d | 🔴 Critical | 4-6pd | ALL |

**Legend:**
- 🔴 Critical - Must have, blocks launch
- 🟡 High - Important, delays launch if missing
- 🟢 Medium - Nice to have, can defer post-launch
- pd = person-days

---

## Next Steps

1. **Review this roadmap** with your team/stakeholders
2. **Adjust timeline** based on team size and priorities
3. **Set up project management** (GitHub Projects, Jira, Trello)
4. **Create Phase 0 sprint** with specific tasks
5. **Begin implementation** starting with database migrations

---

**Document Version**: 1.0
**Last Updated**: 2025-11-07
**Status**: Planning Complete, Ready for Development
