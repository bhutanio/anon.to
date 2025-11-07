# Phase 1: Core URL Shortener & Phase 2: Anonymous Redirect - Technical Specification

## Architecture Overview
We're building a privacy-focused URL shortener with a two-step redirect flow. The system accepts any valid URL, generates a unique 6-character hash, and stores the parsed URL components in the database. When users visit the short link, they first see a warning page displaying the destination URL's components, then explicitly click "Continue" to redirect. This architecture prioritizes user safety and transparency while maintaining anonymous usage by default.

The implementation follows Laravel's Action pattern for business logic, keeping controllers thin. URL parsing and reconstruction logic lives in a dedicated UrlService. Caching via Redis ensures fast redirects for popular links. Rate limiting prevents abuse without requiring authentication.

## Data

### What We're Storing

**Links Table (Primary Entity):**
The core table storing all shortened URLs with the following structure:
- Unique identifier and auto-generated hash (6 characters)
- Optional custom slug (for future registered users)
- Parsed URL components: scheme, host, port, path, query string, fragment
- Full URL string for quick reconstruction
- SHA256 hash of full URL for duplicate detection
- Metadata: title and description (fetched asynchronously, Phase 7+)
- Expiration timestamp (nullable, for future auto-deletion)
- Password hash (nullable, for future protected links)
- Visit counters: total visits and unique visits
- Last visited timestamp
- Status flags: is_active, is_reported
- Creator information: user_id (nullable for anonymous), hashed IP address, user agent
- Standard timestamps: created_at, updated_at

**Link Analytics Table (Future Use - Phase 8):**
Detailed visit tracking per link:
- Foreign key to links table
- Visit timestamp
- Hashed visitor IP address (privacy-focused, no raw IPs)
- Country code (2-letter ISO)
- HTTP referrer
- User agent string
- Creation timestamp

This table will be partitioned by month for efficient querying and archival.

### Key Data Considerations

**Validation Rules:**
- URL must be valid HTTP/HTTPS format (Laravel url rule)
- URL maximum length: 2048 characters
- Host must not be internal IP (SSRF prevention: reject 127.0.0.1, 192.168.x.x, 10.x.x.x)
- Hash must be exactly 6 characters, alphanumeric only
- Hash must not match excluded words list (profanity, common words)
- Custom slug (future): 3-50 characters, alphanumeric and dashes only

**Duplicate Detection Strategy:**
Before creating a new link, compute SHA256 hash of the full URL and check if it exists. If found, return the existing link instead of creating a duplicate. This saves database space and provides consistent short URLs for the same destination.

**Data Retention:**
- Links without expiration date persist indefinitely
- Anonymous links (no user_id) are never cleaned up by default
- Future: Implement optional expiration dates (Phase 7)
- Analytics data older than 90 days will be archived (Phase 8+)

**Database Indexes Needed:**
- Unique index on hash column (primary lookup)
- Unique index on slug column (custom slugs, nullable)
- Index on full_url_hash (duplicate detection performance)
- Index on user_id (user's links lookup, future)
- Index on created_at (sorting, pagination)
- Index on visits (popular links queries, future)
- Composite index on is_active and is_reported (admin filtering, future)

## Components to Build

### Backend Components

**Migrations:**
- create_links_table migration with all columns defined above
- create_link_analytics_table migration (structure only, not used until Phase 8)

**Models (Already Exist - Phase 0):**
- Link model with relationships: belongsTo User, hasMany LinkAnalytic, morphMany Report
- LinkAnalytic model with relationship: belongsTo Link
- Confirm casts are correct: expires_at, last_visited_at as datetime, visits as integer, is_active as boolean

**Actions (Business Logic):**
- CreateLink: Orchestrates entire link creation flow (validate, check duplicate, generate hash, store, cache)
- GenerateHash: Creates collision-free 6-character hash, excludes profane words, retries on collision (max 10 attempts)
- ValidateUrl: Validates URL format, checks against allow/block lists (Phase 9 integration point), prevents SSRF
- CheckDuplicate: Computes SHA256 of full URL, queries database for existing match, returns existing link if found

**Services (Reusable Logic):**
- UrlService: Three methods - parse URL into components, reconstruct URL from database record, check if URL is internal IP

**Controllers:**
- RedirectController: Two methods - redirect method shows warning page with parsed URL, continue method (or button click) performs actual redirect

**Form Requests (Validation):**
- CreateLinkRequest: Validates URL format, length, and required fields; custom error messages for user-friendly feedback

**Middleware:**
- RateLimitByUserType: Tiered rate limiting - 20 per hour for anonymous users based on IP address, higher limits for future authenticated users

**Routes:**
- POST /links - Create shortened link (anonymous, rate limited)
- GET /{hash} - Show redirect warning page (cached, public)
- GET /{slug} - Same as above but for custom slugs (future)

**Events (Optional for Phase 1-2):**
- LinkCreated event - Future use for caching, notifications
- LinkVisited event - Future use for analytics aggregation

**Observers:**
- LinkObserver: On creating, set default values and validate; on created, cache the link; on updated/deleted, invalidate cache

### Frontend Components

**Volt Components (Livewire Single-File):**
- Home page component: Simple form with single URL input field, submit button, loading state, result display with copy button
- Redirect warning page component: Display parsed URL components (scheme, host, path), security warnings if applicable, "Continue" button, "Go Back" link
- Result component (embedded in home): Shows generated short URL, copy-to-clipboard button with success feedback, QR code placeholder for future

**Pages:**
- Welcome/Home (already exists): Replace with link creation form
- Redirect warning page: New dedicated page for anonymous redirect flow

**UI Elements:**
- URL input field: Large, prominent, with placeholder text "Paste your link here"
- Submit button: Primary action, shows loading spinner during submission
- Result display: Appears after successful creation, includes short URL and copy button
- Warning page layout: Clean, centered, shows URL breakdown with visual hierarchy
- Continue button: Primary action, large and obvious
- Security badges: Icons or text showing HTTPS status, external link indicator

**User Flows:**
1. Link Creation Flow: Visit home → Paste URL → Click shorten → See loading state → View result with copy button
2. Redirect Flow: Click short link → See warning page with URL details → Review destination → Click continue → Redirect to actual URL
3. Error Flow: Submit invalid URL → See validation error → Fix and retry

**State Management:**
- Form state: URL input value, loading boolean, error messages, success result
- Redirect page state: Link data from cache, parsed URL components, loading state for continue action
- Loading states: Disable form during submission, show spinner, prevent double submission
- Error states: Display validation errors below input, highlight invalid field, clear on input change
- Empty states: Placeholder text in input, helpful examples or instructions

### Integration Points

**Internal Services:**
- Link model and database for CRUD operations
- Cache service (Redis) for storing hot links and reducing database queries
- Rate limiter service for abuse prevention

**External Services (Future Phases):**
- Google Safe Browsing API for URL reputation checking (Phase 12, optional)
- GeoIP service for country-level analytics (Phase 8)

**Caching Strategy:**
- Cache key format: "link:{hash}"
- Cache duration: 24 hours for all links
- Cache invalidation: On link update or deletion
- Cache warming: Future optimization for top 100 most-visited links

## Security

**Critical security considerations:**

**Authentication/Authorization:**
- Phase 1-2 is completely anonymous, no authentication required
- Rate limiting acts as primary abuse prevention mechanism
- Future phases will add optional user accounts with higher limits

**Input Validation:**
- Frontend validation: URL format check with JavaScript before submission
- Backend validation: Laravel url rule, maximum length check, scheme validation (http/https only)
- SSRF Prevention: Custom validation rule rejects internal IP addresses (localhost, 127.0.0.1, 192.168.x.x, 10.x.x.x, 172.16-31.x.x)
- Allow/block list checking: Future Phase 9 will add domain filtering

**Sensitive Data Handling:**
- IP addresses are hashed with SHA256 before storage (privacy protection)
- User agents stored for analytics but not exposed publicly
- No cookies or tracking for anonymous users
- No session data stored for link creation

**Common Vulnerabilities Prevention:**
- SQL Injection: Use Eloquent ORM exclusively, never raw queries with user input
- XSS: Blade auto-escaping for all user-generated content, strict CSP headers
- CSRF: Laravel CSRF protection enabled for all POST routes
- SSRF: Custom validation rule blocks internal network requests
- Open Redirect: Not applicable - redirect destination is user-controlled by design
- Mass Assignment: Use fillable arrays on models, never trust raw request input

**Rate Limiting Implementation:**
- Anonymous users: 20 link creations per hour per IP address
- Use Laravel's built-in RateLimiter facade with Redis store
- Return 429 Too Many Requests with Retry-After header
- Block based on IP hash (not raw IP) for privacy

## Performance

**Key performance considerations:**

**Caching Strategy:**
- What to cache: All link records accessed via redirect (GET /{hash})
- Cache backend: Redis in production, file cache in development
- Cache duration: 24 hours for standard links
- Cache warming: Not needed for Phase 1-2, consider for Phase 8+
- Cache invalidation: On link update, deletion, or manual admin action

**Database Optimization:**
- Primary indexes: Unique on hash, slug columns for fast lookups
- Performance indexes: Index on full_url_hash for duplicate detection, composite index on user_id + created_at for future user dashboards
- Query optimization: Use Eloquent select() to fetch only needed columns, avoid N+1 queries with eager loading for future features
- Connection pooling: Laravel default configuration sufficient for Phase 1-2

**API Performance:**
- Response time target: Link creation under 200ms (90th percentile)
- Redirect page load target: Under 100ms from cache
- Rate limiting prevents abuse and maintains performance
- Pagination: Not needed for Phase 1-2 (no listing pages)

**Frontend Optimization:**
- Asset optimization: Vite handles CSS/JS minification and bundling
- Lazy loading: Not needed for simple forms
- Code splitting: Not needed for Phase 1-2
- Image optimization: No images in Phase 1-2 (QR codes in Phase 7)
- Livewire wire:loading directive for instant UI feedback

**Monitoring:**
- Log slow queries over 100ms
- Track cache hit rate (target 80%+)
- Monitor rate limit violations
- Alert on hash generation failures

## Testing Strategy

**What needs testing:**

**Unit Tests:**
- GenerateHash action: Uniqueness across 100 iterations, excluded words never generated, collision retry logic, max attempts failure handling
- ValidateUrl action: Valid URLs accepted, invalid URLs rejected, internal IPs blocked, scheme validation (http/https only, reject ftp/file)
- CheckDuplicate action: Returns existing link for duplicate URL, returns null for new URL, SHA256 hashing correctness
- UrlService parse method: Correctly parses all URL components, handles edge cases (no path, no query, no port)
- UrlService reconstruct method: Rebuilds identical URL from parsed components
- UrlService isInternalUrl method: Detects all internal IP ranges, allows external IPs

**Feature Tests:**
- Link creation flow: Valid URL creates link successfully, duplicate URL returns existing hash, invalid URL returns validation error
- Rate limiting enforcement: 20th creation succeeds, 21st creation returns 429, rate limit resets after 1 hour
- Redirect warning page: Displays correct URL components, visit counter increments, continue button redirects to correct URL
- Cache behavior: Second visit to same hash loads from cache, cache invalidation on link update
- Error handling: Invalid URL formats rejected, missing URL returns validation error, internal IPs blocked

**Browser Tests (Pest 4):**
- Complete link creation flow: Visit home page, enter URL, submit form, see success message, copy short URL to clipboard
- Complete redirect flow: Click short link, see warning page with URL details, click continue button, verify redirected to correct URL
- Form validation: Submit empty form shows error, submit invalid URL shows error, error clears when user types
- Mobile responsive: Form works on mobile viewport, warning page readable on small screens
- Accessibility: Keyboard navigation works (Tab through form, Enter to submit), screen reader announces errors and success messages

**Cross-browser/Device Compatibility:**
- Test in Chrome, Firefox, Safari (latest versions)
- Test on desktop, tablet, mobile viewports
- Verify Livewire wire:loading states work in all browsers

**Accessibility Testing:**
- Keyboard navigation: Tab through all interactive elements, Enter/Space to activate buttons
- Screen reader testing: VoiceOver (Mac) or NVDA (Windows) announces all labels, errors, and state changes
- Focus management: Visible focus indicators on all interactive elements
- Color contrast: WCAG AA compliance for text and buttons

---

## Background Processing

**Jobs to Queue:**
- FetchLinkMetadata: Asynchronously fetch page title and description from destination URL (defer to Phase 7)
- CheckUrlReputation: Query Google Safe Browsing API to check for malware/phishing (defer to Phase 12)

**For Phase 1-2:**
- No background jobs required for MVP
- All operations are synchronous and fast enough for real-time responses

**Future Considerations:**
- DeleteExpiredLinks job will run hourly (Phase 7)
- AggregateAnalytics job will run nightly (Phase 8)

---

## Browser/Device Support

**Minimum Browser Versions:**
- Chrome 90+ (2021)
- Firefox 88+ (2021)
- Safari 14+ (2020)
- Edge 90+ (2021)
- No IE11 support

**Mobile Browser Support:**
- iOS Safari 14+
- Chrome Mobile (latest)
- Firefox Mobile (latest)
- Samsung Internet (latest)

**Device-Specific Features:**
- Responsive design: Mobile-first approach, works on 320px+ screens
- Touch targets: Minimum 44x44px for all buttons (WCAG compliance)
- No device-specific features required for Phase 1-2

---

## Migration Strategy

**For Phase 1-2:**
- Fresh database migrations for new installations
- No data migration from legacy system (deferred to Phase 13)
- Links table structure designed to accommodate legacy data import later

**Legacy Migration Considerations (Phase 13):**
- Keep hash column compatible with existing legacy hashes
- Preserve visit counts during import
- Map legacy user IDs to new system
- Validate data integrity after import

**Backwards Compatibility:**
- No breaking changes for Phase 1-2 (new system)
- Future phases must maintain hash format compatibility
