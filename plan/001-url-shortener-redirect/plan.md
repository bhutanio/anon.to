# Phase 1: Core URL Shortener & Phase 2: Anonymous Redirect

**Created:** 2025-11-07
**Type:** Full-stack Feature (Backend + Frontend)
**Timeline:** 5-7 days (Week 2-3)

## What & Why
This feature implements the core URL shortening functionality with anonymous redirect warnings - the foundation of anon.to. Users can paste any URL and receive a short anon.to link that displays a privacy-focused warning page before redirecting, protecting users from unknown destinations while maintaining complete anonymity by default.

## Who
- **Privacy-conscious users** sharing sensitive links without tracking
- **Anonymous users** (no account required) - primary use case
- **General users** wanting quick temporary link shortening
- **Future registered users** who will gain custom slugs and analytics (Phase 4+)

## Scope

### MVP - Must Have
- Anonymous link creation via simple form (paste URL, get short link instantly)
- Six-character hash generation with collision prevention and excluded word filtering
- URL validation and duplicate detection (reuse existing hash for same URL)
- Anonymous redirect warning page showing parsed URL components before redirect
- Visit counter tracking (increment on each click)
- Rate limiting (20 links per hour for anonymous users, IP-based)
- Basic caching for frequently accessed links

### Nice to Have (Future Iterations)
- Custom slugs for registered users (Phase 7)
- QR code generation (Phase 7)
- Link expiration dates (Phase 7)
- Detailed analytics and charts (Phase 8)
- Admin moderation tools (Phase 10)
- API endpoints (Phase 11)

## Success Criteria
How will we know this feature is successful?

- **Functional:** User can create link and get shortened URL in under 5 seconds
- **Performance:** Link creation completes in under 200ms (90th percentile)
- **Performance:** Redirect warning page loads in under 100ms from cache
- **Security:** Rate limiting blocks 21st attempt within 1 hour
- **Quality:** Duplicate URLs return existing hash instead of creating new record
- **Quality:** Visit counter increments accurately even under concurrent load

## Risks & Blockers

### Technical Risks
- **Hash collisions:** While unlikely with 6 characters (56B+ combinations), need robust collision detection
  - Mitigation: Retry logic with max 10 attempts, track collision rate
- **Duplicate detection performance:** SHA256 hashing and lookup could slow down at scale
  - Mitigation: Indexed full_url_hash column, caching strategy
- **Race conditions on visit counter:** Concurrent requests might cause inaccurate counts
  - Mitigation: Use database-level atomic increment

### External Dependencies
- Redis must be configured and running for caching (can fallback to file cache in dev)
- Database migrations must complete successfully before testing
- Livewire 3 and Volt setup must be working (already confirmed in Phase 0)

### Timeline Constraints
- 5-7 days is tight for full-stack implementation with comprehensive testing
- Browser tests (Pest 4) are new to team and may require learning curve
- If behind schedule, defer browser tests to Phase 3

---

## User Stories

### As an Anonymous User
- As a privacy-conscious user, I want to shorten a URL without creating an account, so I can share it quickly without leaving traces
- As a cautious user, I want to see where a shortened link goes before visiting, so I can avoid malicious sites
- As a general user, I want shortened links to work immediately without delays, so I don't have to wait

### As a Future Registered User (Phase 4+)
- As a power user, I want to see how many times my links were clicked, so I can measure engagement
- As a content creator, I want custom memorable slugs, so my audience can easily remember links

---

## Analytics & Tracking
What we need to measure for this phase:

**Core Metrics:**
- Links created per day
- Total visits/redirects per day
- Average link creation time
- Cache hit rate for redirects
- Rate limit hit count

**Performance Metrics:**
- 90th percentile response time for link creation
- 95th percentile response time for redirect page load
- Database query count per request

**Error Tracking:**
- Failed validations (invalid URLs, blocked domains)
- Hash generation failures (collisions)
- Rate limit violations

---

## Accessibility Requirements
Beyond standard WCAG compliance:

- Redirect warning page must be fully keyboard navigable (Tab through all elements)
- "Continue" button must have clear focus indicator
- Screen readers must announce: "Warning: External link. You are about to visit [domain]"
- Form validation errors must be announced immediately to screen readers
- All interactive elements must have proper ARIA labels
