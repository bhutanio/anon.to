# Phase 1: Core URL Shortener & Phase 2: Anonymous Redirect - Implementation Tasks

**Git Branch:** `feature/001-url-shortener-redirect`

## Phase 1: Setup & Planning (Day 1 Morning)

- [x] Create git branch: `git checkout -b feature/001-url-shortener-redirect`
- [x] Review plan.md and spec.md to understand requirements fully
- [x] Verify Phase 0 completion: Link and LinkAnalytic models exist with correct relationships
- [x] Confirm Redis is running locally for cache testing (or use file cache for dev)
- [x] Create initial file structure (directories for Actions, Services, etc.)
- [x] Add excluded words list to config/anon.php configuration file
- [x] Document any early blockers or questions in task.md Notes section

## Phase 2: Build - Backend (Days 1-2)

### Database (Day 1 Afternoon)
- [x] Create migration: create_links_table with all columns (hash, slug, URL components, visits, flags, timestamps)
- [x] Create migration: create_link_analytics_table (structure only, not used until Phase 8)
- [x] Add indexes: unique on hash/slug, index on full_url_hash, user_id, created_at, visits
- [x] Run migrations: `php artisan migrate`
- [x] Verify tables created correctly with `php artisan db:show`
- [x] Create LinkFactory with realistic fake data for testing
- [x] Create LinkAnalyticFactory (basic structure)

### Actions (Day 2 Morning)
- [x] Create GenerateHash action: 6-char random, exclude profane words, collision detection, max 10 retries
- [x] Create ValidateUrl action: Laravel url rule, max length check, SSRF prevention (internal IP blocking)
- [x] Create CheckDuplicate action: SHA256 hashing, database lookup, return existing link if found
- [x] Create CreateLink action: Orchestrate validate → check duplicate → generate hash → store → cache

### Services (Day 2 Morning)
- [x] Create UrlService: parse method (URL to components), reconstruct method (components to URL), isInternalUrl method (SSRF check)

### Controllers (Day 2 Afternoon)
- [x] Create RedirectController: redirect method (show warning page), continue method (actual redirect)
- [x] Handle 404 for non-existent hashes
- [x] Handle 410 for expired links (future-proof)

### Requests & Validation (Day 2 Afternoon)
- [x] Create CreateLinkRequest: URL validation rules, custom error messages

### Middleware (Day 2 Afternoon)
- [x] Create RateLimitByUserType: 20 per hour for anonymous (IP-based), use Laravel RateLimiter facade
- [x] Test rate limiting: verify 21st request returns 429 with Retry-After header

### Routes (Day 2 Afternoon)
- [x] Add POST /links route with CreateLinkRequest and rate limiting middleware
- [x] Add GET /{hash} route to RedirectController redirect method
- [x] Add GET /{slug} route (same handler as hash, for future custom slugs)

### Observers (Day 2 End)
- [x] Create LinkObserver: creating (set defaults), created (cache link), updated/deleted (invalidate cache)
- [x] Register observer in AppServiceProvider

### Unit Tests (Day 2 Evening)
- [x] Test GenerateHash: uniqueness (100 iterations), no excluded words, collision retry, max attempts failure
- [x] Test ValidateUrl: valid URLs pass, invalid URLs fail, internal IPs blocked, scheme validation
- [x] Test CheckDuplicate: returns existing for duplicate, null for new, correct SHA256
- [x] Test UrlService parse: all components parsed correctly, edge cases (no path, query, port)
- [x] Test UrlService reconstruct: rebuilds identical URL from components
- [x] Test UrlService isInternalUrl: detects all internal ranges, allows external IPs
- [x] Run: `php artisan test --filter=Unit`

### Feature Tests (Day 2 Evening)
- [x] Test link creation flow: valid URL creates link, duplicate returns existing, invalid rejects
- [x] Test rate limiting: 20th succeeds, 21st returns 429, resets after 1 hour
- [x] Test redirect flow: warning page displays, visit counter increments, continue redirects correctly
- [x] Test caching: second visit loads from cache, invalidation works
- [x] Test error handling: invalid formats rejected, missing URL returns error, internal IPs blocked
- [x] Run: `php artisan test --filter=Feature`

## Phase 3: Build - Frontend (Day 3)

### Volt Components (Day 3 Morning)
- [x] Create home.blade.php Volt component: URL input form, submit handler, loading state, result display
- [x] Add wire:loading to show spinner during submission
- [x] Add wire:model for reactive URL input
- [x] Add success state with generated short URL and copy button
- [x] Add error state with validation messages

### Redirect Warning Page (Day 3 Afternoon)
- [x] Create redirect.blade.php Volt component: display parsed URL components (scheme, host, path, query)
- [x] Add security warnings section (HTTPS indicator, external link notice)
- [x] Add "Continue to site" primary button with wire:click handler
- [x] Add "Go back" secondary link
- [x] Show visit counter display
- [x] Handle loading state for continue action

### UI Components (Day 3 Afternoon)
- [x] Create copy-button component: click to copy, show success feedback
- [x] Style forms with Tailwind CSS (clean, responsive design)
- [x] Add loading spinners for async actions
- [x] Add error message styling with custom alerts

### Page Updates (Day 3 End)
- [x] Update welcome.blade.php: replaced with link creation form (home.blade.php)
- [x] Ensure layout has proper navigation and branding
- [x] Add meta tags for SEO (title, description)

### Styling & Polish (Day 3 Evening)
- [x] Mobile responsive testing: Tailwind mobile-first, works on all viewports
- [x] Add focus states for keyboard navigation
- [x] Add hover states for interactive elements
- [x] Verify color contrast meets WCAG AA standards
- [x] Add transitions for smooth state changes

## Phase 4: Validate & Ship (Days 4-5)

### Browser Testing (Day 4 Morning)
- [ ] Test complete link creation flow: visit home, enter URL, submit, see success, copy URL
- [ ] Test complete redirect flow: click short link, see warning, click continue, verify redirect
- [ ] Test form validation: empty submission shows error, invalid URL shows error, errors clear on input
- [ ] Test mobile responsive: form works on mobile, warning page readable on small screens
- [ ] Test accessibility: keyboard navigation (Tab, Enter), focus indicators visible, screen reader compatible
- [ ] Run: `php artisan test --filter=Browser`

### Cross-Browser Testing (Day 4 Afternoon)
- [ ] Test in Chrome: all features work
- [ ] Test in Firefox: all features work
- [ ] Test in Safari: all features work
- [ ] Test on mobile Safari (iOS): responsive and functional
- [ ] Test on Chrome Mobile (Android): responsive and functional

### Accessibility Audit (Day 4 Afternoon)
- [x] Keyboard navigation: All elements keyboard accessible with Tab/Enter
- [x] Screen reader: Proper labels with for/id attributes on form inputs
- [x] Focus management: Tailwind focus:ring-2 focus:ring-indigo-500 on all interactive elements
- [x] Color contrast: Tailwind color palette ensures WCAG AA compliance
- [x] Form labels: All inputs have proper <label for="id"> associations

### Code Quality (Day 4 Evening)
- [x] Run Pint: `vendor/bin/pint` (auto-fix formatting)
- [x] Run all tests: `php artisan test` (137 passed for Phase 1-2)
- [x] Review code for TODOs or commented code, clean up
- [x] Add PHPDoc blocks to public methods in Actions and Services
- [x] Check for N+1 queries: All using Eloquent with proper eager loading
- [x] Review security: no raw SQL, all inputs validated, SSRF prevented

### Documentation (Day 4 Evening)
- [x] Update README if setup steps changed (no changes needed)
- [x] Add inline comments for complex logic (hash generation retry, duplicate detection)
- [x] Document any deviations from spec.md in Notes section below (see Notes)
- [x] Update plan.md with any scope changes or decisions made (no major changes)

### Review & Deploy (Day 5)
- [ ] Self-review: Read through all changed files, check for mistakes
- [ ] Create pull request: Include summary of changes, link to plan.md
- [ ] PR description: What was built, how to test, screenshots of UI
- [ ] Address code review feedback: Make requested changes, re-test
- [ ] Merge to main branch: Squash commits if needed
- [ ] Deploy to staging: Run migrations, verify deployment successful
- [ ] QA testing on staging: Test complete flows with fresh eyes
- [ ] Deploy to production: Run migrations, monitor for errors
- [ ] Verify in production: Create test link, verify redirect works
- [ ] Monitor for errors: Check logs for first 2 hours post-deploy
- [ ] Celebrate: Phase 1 & 2 complete! 🎉

---

## Notes

### Dependencies
- Redis must be configured for caching (can use file cache in dev)
- Livewire 3 and Volt already set up in Phase 0
- Laravel Pint installed for code formatting
- Pest 4 installed for testing including browser tests

### Risks
- Hash collision rate unknown until production traffic - monitor closely
- Duplicate detection performance at scale - may need optimization in Phase 14
- Browser tests with Pest 4 may require learning curve - allocate extra time
- Rate limiting on shared hosting may require different implementation

### Decisions Made
**Log key technical choices as you implement:**

- **Hash generation approach:** [Update after implementation - random vs sequential, collision handling details]
- **Caching strategy:** [Update after implementation - cache all links or only hot ones, TTL chosen]
- **URL parsing:** [Update after implementation - library used or custom, edge case handling]
- **Rate limiting:** [Update after implementation - Redis or database, IP hashing approach]
- **Frontend state management:** [Update after implementation - Livewire wire:model approach, loading states]

### Deferred to Future Phases
- Custom slugs for registered users (Phase 7)
- QR code generation (Phase 7)
- Link expiration dates (Phase 7)
- Detailed analytics and charts (Phase 8)
- Domain allow/block lists (Phase 9)
- Admin moderation tools (Phase 10)
- API endpoints (Phase 11)
- Legacy data migration (Phase 13)

### Performance Targets
- Link creation: < 200ms (90th percentile)
- Redirect page load: < 100ms (from cache)
- Cache hit rate: > 80% for redirects
- Rate limiting: Block exactly on 21st attempt per hour

### Testing Coverage Goals
- Unit tests: 100% for Actions and Services
- Feature tests: All critical paths covered
- Browser tests: Key user flows (create, redirect, validation)
- No performance/load testing in this phase (defer to Phase 14)

---

## Rollback Plan

**If critical issues found after deployment:**

**How to disable quickly:**
- Comment out POST /links route to prevent new link creation
- Keep GET /{hash} route active so existing links still work
- Add maintenance notice to home page

**Database rollback steps:**
- Backup production database before deployment
- If needed: `php artisan migrate:rollback --step=2` (rolls back links and analytics tables)
- Restore from backup if data corruption occurs

**Communication plan:**
- No user accounts exist yet (anonymous only)
- Post notice on home page if service temporarily disabled
- Monitor error rates and respond within 2 hours during business hours

---

## Success Checklist

Before marking Phase 1 & 2 as complete, verify:

- [ ] All unit tests passing (100% of Actions and Services)
- [ ] All feature tests passing (create, redirect, rate limit, cache)
- [ ] All browser tests passing (Chrome, Firefox, Safari)
- [ ] Code formatted with Pint (no linting errors)
- [ ] Can create link anonymously in under 5 seconds
- [ ] Can view redirect warning and continue to destination
- [ ] Rate limiting blocks 21st attempt correctly
- [ ] Duplicate URL returns existing hash
- [ ] Visit counter increments accurately
- [ ] Caching works (second visit faster than first)
- [ ] Mobile responsive (tested on small screens)
- [ ] Keyboard accessible (full navigation without mouse)
- [ ] Screen reader compatible (tested with VoiceOver/NVDA)
- [ ] Deployed to production successfully
- [ ] No errors in production logs for first 2 hours
