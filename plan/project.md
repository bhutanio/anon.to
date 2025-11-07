# anon.to - Project Specification

## Executive Summary

### Vision
Build a modern, privacy-focused platform that combines URL anonymization and ephemeral note sharing. anon.to empowers users to share links and text content safely and anonymously while providing optional account features for power users.

### Goals
- **Privacy First**: Anonymous usage by default, no tracking, intermediate warning pages
- **Modern UX**: Fast, intuitive interface built with Livewire 3 + Flux UI
- **Self-Service**: Users can manage their content, admins can moderate effectively
- **Performance**: Handle high traffic with smart caching and optimization
- **Security**: Robust anti-abuse measures, rate limiting, content validation

### Target Audience
- **Privacy-conscious users** sharing sensitive links
- **Developers** sharing code snippets and logs
- **General users** wanting temporary link shortening
- **Organizations** needing anonymous feedback/reporting channels

---

## Core Features

### 1. URL Shortener & Anonymizer

#### Anonymous Link Creation
- **One-click shortening**: Paste URL → Get short link
- **No registration required**: Fully functional without account
- **6-character hashes**: Short, memorable URLs (e.g., anon.to/aB3xYz)
- **Hash collision prevention**: Exclude common words, ensure uniqueness
- **Duplicate detection**: Reuse existing hash for same URL

#### Advanced Link Options
- **Expiration dates**: Auto-delete after 1hr, 1day, 1week, 1month, never
- **QR code generation**: Download or display QR codes for easy sharing
- **Password protection**: Optional password to access link (coming in future phase)

#### Anonymous Redirect Page
- **Warning screen**: Show users where they're going before redirect
- **Security info**: Display parsed URL components, safety warnings
- **Instant redirect option**: Skip warning for trusted links (user preference)
- **Continue button**: Explicit user action required

#### Link Analytics
- **Visit counter**: Total number of clicks
- **Last visited timestamp**: When link was last accessed
- **Referrer tracking**: Where traffic comes from (optional, privacy-focused)
- **Geographic data**: Country-level only, no personal tracking
- **Chart visualization**: Visit trends over time for registered users

#### Link Management (Registered Users)
- **My Links dashboard**: View all created links
- **Search & filter**: By date, visits, status, expiration
- **Bulk actions**: Delete multiple links, update expirations
- **Link editing**: Update destination URL, change expiration
- **Export data**: Download link history as CSV/JSON

### 2. Anonymous Notes (Pastebin)

#### Note Creation
- **No registration required**: Anonymous paste functionality
- **Large text support**: Up to 10MB per note
- **Syntax highlighting**: Auto-detect or manual selection
  - Languages: JavaScript, Python, PHP, HTML, CSS, SQL, JSON, XML, Bash, Go, Rust, Java, C++, and 50+ more
  - Auto-detection based on content patterns
- **Plain text mode**: For non-code content
- **Character/line counter**: Real-time feedback while typing

#### Expiration Options
- **10 minutes**: Ultra-short for sensitive data
- **1 hour**: Quick sharing
- **1 day**: Standard temporary paste
- **1 week**: Medium-term sharing
- **1 month**: Long-term paste
- **Never**: Permanent (requires account)
- **Auto-deletion**: Background job cleans expired notes

#### Privacy Features
- **Password protection**: Require password to view note
  - Bcrypt hashing, never stored in plain text
  - Unlimited attempts (with rate limiting)
  - No password recovery option (by design)
- **Burn after reading**: Auto-delete after N views
  - Options: 1, 5, 10, 50 views
  - View counter displayed to creator
  - Atomic counter to prevent race conditions
- **No edit history**: Content immutable after creation
- **No indexing**: robots.txt prevents search engine crawling

#### Note Viewing
- **Clean interface**: Distraction-free reading
- **Line numbers**: Toggle on/off
- **Raw text view**: View unformatted content
- **Download**: Save as .txt file with original formatting
- **Copy button**: One-click clipboard copy
- **Embed**: iframe embed code for registered users
- **Fork/Clone**: Create new note from existing (preserves content, new hash)

#### Note Management (Registered Users)
- **My Notes dashboard**: View all created notes
- **Search content**: Full-text search through notes
- **Filter by language**: Find all Python notes, etc.
- **Favorite notes**: Star important notes
- **Note statistics**: Views, created date, expiration countdown

### 3. User System

#### Anonymous Usage (Default)
- **No barriers**: Create links/notes immediately
- **Session tracking**: Temporary session for rate limiting only
- **No data retention**: Anonymous content orphaned after session
- **IP rate limiting**: Prevent abuse (20 links/notes per hour)

#### Optional Registration
- **Email + Password**: Standard Fortify authentication
- **Username**: Unique, URL-friendly usernames
- **Email verification**: Confirm email to unlock features
- **Benefits of registration**:
  - Manage all your links/notes in one place
  - Permanent notes (never expire)
  - View detailed analytics
  - API access tokens
  - Higher rate limits (100/hour)
  - Export your data

#### Account Features
- **Profile management**: Update name, email, password
- **Two-factor authentication**: TOTP via Fortify
- **API tokens**: Generate personal access tokens
- **Preferences**: Default expiration, theme, redirect behavior
- **Account deletion**: Full data export then permanent deletion

### 4. Abuse Prevention & Moderation

#### Domain Allow/Block Lists
- **Whitelist mode**: Only allow approved domains
- **Blacklist mode**: Block known malicious domains (default)
- **Pattern matching**: Block entire TLDs or patterns
- **Admin managed**: Add/remove domains via admin panel
- **Reason tracking**: Document why domain is blocked

#### Content Reporting
- **Public report form**: Anyone can report abusive content
- **Report categories**:
  - Spam/Advertising
  - Malware/Phishing
  - Illegal Content
  - Copyright Violation
  - Harassment/Abuse
  - Other
- **Reporter info**: Email (optional), comment (required), IP logged
- **Admin queue**: Review all reports in admin dashboard
- **Actions**: Mark dealt, delete content, ban user/IP, ignore

#### Rate Limiting
- **Tiered limits**:
  - Anonymous: 20 creates/hour
  - Registered: 100 creates/hour
  - Verified: 500 creates/hour
  - Admin: Unlimited
- **Per-IP tracking**: Prevent account circumvention
- **Exponential backoff**: Escalating penalties for violations
- **CAPTCHA**: Required after suspicious activity

#### Automated Scanning
- **URL reputation check**: Query Google Safe Browsing API
- **Spam detection**: Pattern matching for common spam
- **Duplicate prevention**: Block rapid identical submissions
- **Honeypot fields**: Catch bot submissions

### 5. Admin Dashboard

#### Overview
- **Real-time stats**: Total links/notes, active users, pending reports
- **Recent activity**: Live feed of creations, reports, deletions
- **System health**: Database size, cache hit rate, queue status
- **Charts**: Traffic trends, top domains, popular syntax languages

#### Link Management
- **Search all links**: By hash, URL, creator, date
- **Bulk moderation**: Delete multiple links at once
- **View analytics**: Per-link statistics
- **Edit links**: Change destination, expiration

#### Note Management
- **Search all notes**: By hash, content keywords, creator
- **View content**: Read any note (audit purposes)
- **Syntax distribution**: See most popular languages

#### User Management
- **User list**: Search, filter, sort all users
- **User details**: View user's links, notes, reports
- **User actions**: Ban, delete, verify, promote to admin
- **Ban management**: IP bans, email bans, username bans

#### Report Queue
- **Pending reports**: All unreviewed reports
- **Filter by type**: Show only malware reports, etc.
- **One-click actions**: Delete content, ban user, dismiss
- **Report history**: Audit log of all actions

#### Allow/Block Lists
- **Domain management**: Add/edit/remove domains
- **Import/Export**: CSV upload for bulk operations
- **Test utility**: Check if domain would be allowed

### 6. API

#### Public Endpoints
```
POST /api/v1/links - Create short link
GET  /api/v1/links/{hash} - Get link info (no redirect)
POST /api/v1/notes - Create note
GET  /api/v1/notes/{hash} - Get note content (text only)
```

#### Authenticated Endpoints
```
GET    /api/v1/my/links - List my links
DELETE /api/v1/my/links/{hash} - Delete my link
GET    /api/v1/my/notes - List my notes
DELETE /api/v1/my/notes/{hash} - Delete my note
GET    /api/v1/analytics/{hash} - Get detailed analytics
```

#### API Authentication
- **Bearer tokens**: Personal access tokens via Laravel Sanctum
- **Rate limiting**: Same as web (20/100/500 per hour)
- **CORS**: Configurable allowed origins
- **Versioning**: /api/v1 for future compatibility

---

## User Stories

### As an Anonymous User

**Story 1: Quick Link Shortening**
> "As a privacy-conscious user, I want to shorten a URL without creating an account, so I can share it quickly without leaving traces."

- Visit homepage
- Paste URL in input field
- Click "Shorten"
- Receive shortened link instantly
- Copy and share

**Story 2: Create Temporary Note**
> "As a developer, I want to share a code snippet that auto-deletes after 1 hour, so sensitive logs don't persist."

- Visit /notes/create
- Paste code snippet
- Select "PHP" syntax
- Choose "1 hour" expiration
- Click "Create Note"
- Share link with colleague

**Story 3: View Anonymous Redirect Warning**
> "As a cautious user, I want to see where a link goes before visiting, so I can avoid malicious sites."

- Click on anon.to link
- See warning page with full URL displayed
- Review URL components (scheme, host, path)
- Click "Continue to site" to proceed
- Redirected to destination

### As a Registered User

**Story 4: Manage My Links**
> "As a registered user, I want to see all my shortened links in one dashboard, so I can track and manage them easily."

- Log in to account
- Navigate to "My Links"
- See table of all created links
- Sort by visits, date, or expiration
- Delete unwanted links
- View analytics charts

**Story 5: Password-Protected Note**
> "As a team lead, I want to share credentials in a password-protected note, so only intended recipients can access it."

- Create new note
- Paste credentials
- Check "Password protected"
- Enter strong password
- Set "Burn after 5 views"
- Share link + password separately
- Note auto-deletes after 5 views

### As an Admin

**Story 6: Review Abuse Reports**
> "As an admin, I want to quickly review and act on abuse reports, so I can keep the platform safe."

- Navigate to Admin → Reports
- See list of pending reports
- Click report to view details
- See reported link/note content
- Click "Delete Content & Ban User"
- Report marked as dealt

**Story 7: Block Malicious Domain**
> "As an admin, I want to block an entire domain from being shortened, so users can't abuse the platform for phishing."

- Navigate to Admin → Allow Lists
- Click "Add Blocked Domain"
- Enter "evil-phishing-site.com"
- Add reason: "Confirmed phishing"
- Save
- Future attempts to shorten this domain are rejected

---

## User Flows

### Flow 1: Anonymous Link Shortening
```
Homepage → Enter URL → Validate → Check duplicate → Generate hash → Cache → Show result
```

**Detailed Steps:**
1. User visits homepage (/)
2. Sees prominent input field with placeholder "Paste your link here..."
3. Pastes URL, clicks "Shorten" or presses Enter
4. Client-side validation (valid URL format)
5. AJAX POST to /api/v1/links
6. Server validates URL format, checks rate limit
7. Query allow/block lists (reject if blocked)
8. Check if URL already exists in database
   - If exists: Return existing hash
   - If new: Generate unique 6-char hash, create record
9. Cache link data (24 hours)
10. Return JSON response with shortened URL
11. Frontend displays result with copy button, QR code button
12. User copies and shares

### Flow 2: Note Creation with Password
```
/notes/create → Write content → Select options → Set password → Create → Share
```

**Detailed Steps:**
1. User navigates to /notes/create
2. Sees large textarea with toolbar
3. Types or pastes content
4. Character counter updates in real-time
5. Selects syntax language from dropdown (or auto-detect)
6. Checks "Password protected" checkbox
7. Password input appears, enters strong password
8. Selects expiration: "1 day"
9. Selects "Burn after reading: 10 views"
10. Clicks "Create Note"
11. Server generates unique hash
12. Hashes password with bcrypt
13. Creates note record with view_limit=10, views=0
14. Returns note URL
15. Frontend shows success screen with URL, password reminder
16. User shares URL via secure channel, password via different channel

### Flow 3: Viewing Protected Note
```
Click link → Enter password → Verify → Increment view → Show content → Check burn limit
```

**Detailed Steps:**
1. Recipient clicks note link (anon.to/aB3xYz)
2. System checks if note exists and not expired
3. Detects password protection, shows password prompt
4. User enters password, clicks "Unlock"
5. Server verifies password (bcrypt compare)
   - If wrong: Show error, allow retry (rate limited)
   - If correct: Proceed
6. Atomically increment view counter
7. Check if burn limit reached (views >= view_limit)
8. Show note content with syntax highlighting
9. If burn limit reached, schedule note for deletion
10. Show banner: "This note will be deleted after viewing"

### Flow 4: Admin Moderation
```
Report received → Admin notification → Review content → Take action → Update report
```

**Detailed Steps:**
1. User submits abuse report via /report
2. Report created in database with status=pending
3. Admin receives notification (email/dashboard)
4. Admin navigates to Admin → Reports
5. Sees report list sorted by date
6. Clicks report to view details
7. Sees reported link/note, reporter's comment, IP
8. Views the actual content/destination
9. Decides on action:
   - Option A: Delete content only
   - Option B: Delete content + ban user
   - Option C: Dismiss report (no action)
10. Clicks action button
11. System executes action, logs in audit trail
12. Report marked as dealt_at=now()
13. Reporter receives notification (optional)

---

## Scope & Priorities

### Phase 1: MVP (Weeks 1-3)
**Must Have:**
- ✅ URL shortening with hash generation
- ✅ Anonymous redirect page
- ✅ Basic link storage and retrieval
- ✅ Note creation with plain text
- ✅ Expiration for links and notes
- ✅ User registration (Fortify)
- ✅ My Links dashboard
- ✅ My Notes dashboard
- ✅ Basic rate limiting
- ✅ Domain block list
- ✅ Abuse reporting

**Nice to Have:**
- Syntax highlighting
- Visit analytics
- Admin dashboard

### Phase 2: Enhancement (Weeks 4-5)
**Must Have:**
- ✅ Syntax highlighting (Prism.js)
- ✅ Password protection for notes
- ✅ Burn after reading
- ✅ Admin dashboard with moderation
- ✅ QR code generation
- ✅ Link analytics

**Nice to Have:**
- API endpoints
- Export functionality
- Advanced analytics

### Phase 3: Scale & Polish (Week 6+)
**Must Have:**
- ✅ API v1 (Sanctum)
- ✅ Import command for old data
- ✅ Performance optimization
- ✅ Comprehensive testing
- ✅ Production deployment

**Nice to Have:**
- Geographic analytics
- Embed functionality
- Browser extensions
- Mobile app

### Out of Scope (Future)
- Social features (comments, likes)
- Collaboration (shared notes)
- Version control for notes
- AI-powered content moderation
- Blockchain integration
- Cryptocurrency payments

---

## Success Metrics

### Performance
- ✅ Page load time < 500ms (cached links)
- ✅ Link creation < 200ms (90th percentile)
- ✅ Support 10,000 concurrent users
- ✅ 99.9% uptime
- ✅ Cache hit rate > 80%

### Security
- ✅ Zero successful phishing attacks via platform
- ✅ < 1% abuse rate (reported/total)
- ✅ Rate limiting blocks 99% of bot traffic
- ✅ No personal data breaches
- ✅ OWASP Top 10 compliance

### Usability
- ✅ < 10 seconds to create first link (new user)
- ✅ Mobile responsive (works on all screen sizes)
- ✅ Accessibility (WCAG 2.1 AA)
- ✅ Clear error messages
- ✅ Intuitive navigation (< 3 clicks to any feature)

### Business
- ✅ 25% conversion rate (anonymous → registered)
- ✅ Daily active users > 1,000
- ✅ Average 5+ links/notes per registered user
- ✅ Admin response time < 1 hour for abuse reports
- ✅ Successfully migrate 80%+ of old data

---

## Risk Assessment

### Technical Risks
| Risk | Impact | Likelihood | Mitigation |
|------|--------|------------|------------|
| Hash collisions | High | Low | Robust collision detection, 6-char = 56B+ combinations |
| Database growth | Medium | High | Scheduled cleanup, compression, archiving |
| Cache invalidation | Medium | Medium | TTL-based expiration, manual purge tools |
| Syntax highlighting XSS | High | Medium | Sanitize all input, use trusted libraries |
| Rate limit bypass | Medium | Medium | Multi-layer limits (IP, session, user) |

### Operational Risks
| Risk | Impact | Likelihood | Mitigation |
|------|--------|------------|------------|
| Abuse/spam waves | High | High | Automated detection, CAPTCHA, admin tools |
| DMCA takedown requests | Medium | Medium | Clear terms, quick response process |
| Server overload | High | Low | CDN, auto-scaling, queue system |
| Data loss | Critical | Low | Daily backups, redundant storage |

### Legal Risks
| Risk | Impact | Likelihood | Mitigation |
|------|--------|------------|------------|
| Illegal content hosting | High | Medium | Proactive scanning, clear ToS, quick removal |
| Privacy violations | High | Low | Minimal data collection, GDPR compliance |
| Copyright infringement | Medium | Medium | DMCA agent, takedown process |

---

## Timeline

### Week 1: Foundation
- Database migrations (links, notes, users, reports, allow_lists)
- Models with relationships
- Basic routes and controllers
- Authentication setup (Fortify)

### Week 2: Core Features
- Link shortening logic
- Note creation with expiration
- Anonymous redirect page
- Rate limiting middleware
- Domain block list

### Week 3: User Dashboards
- My Links dashboard (Livewire)
- My Notes dashboard (Livewire)
- User settings
- Basic admin panel

### Week 4: Advanced Features
- Syntax highlighting integration
- Password protection for notes
- Burn after reading
- QR code generation

### Week 5: Analytics & Moderation
- Visit tracking and analytics
- Admin moderation tools
- Report queue management
- Allow/block list management

### Week 6: API & Migration
- REST API endpoints
- Sanctum authentication
- Import command for old database
- Testing and bug fixes
- Production deployment

---

## Appendix

### Glossary
- **Hash**: Short, unique identifier for links/notes (e.g., aB3xYz)
- **Slug**: Human-readable custom URL segment
- **Burn after reading**: Auto-delete content after N views
- **Allow list**: Approved domains that can be shortened
- **Block list**: Banned domains that cannot be shortened
- **Anonymous user**: User without an account
- **Registered user**: User with verified account

### References
- Laravel 12 Documentation: https://laravel.com/docs/12.x
- Livewire 3 Documentation: https://livewire.laravel.com/docs
- Fortify Documentation: https://laravel.com/docs/12.x/fortify
- OWASP Top 10: https://owasp.org/www-project-top-ten/
- Google Safe Browsing API: https://developers.google.com/safe-browsing

### Change Log
- 2025-11-07: Initial specification created
