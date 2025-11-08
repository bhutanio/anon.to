# Spec Requirements: Notes/Pastebin System

## Initial Description

This is a major feature for anon.to - a privacy-first URL shortener that's being rebuilt on Laravel 12. The Notes/Pastebin System will allow users to share code snippets and text content anonymously with features like:

- Syntax highlighting (50+ languages)
- Password protection
- Burn after reading (view limits)
- Expiration options
- Anonymous and authenticated usage

### Current State

The database schema is already complete (notes table with 22 columns), and the project currently has Phase 1-3 complete (core URL shortening, redirects, authentication with 20 passing tests).

### Context

- Project: anon.to
- Type: Privacy-first URL shortener
- Framework: Laravel 12
- Current Progress: Phase 1-3 complete (core URL shortening, redirects, authentication)
- Test Coverage: 20 passing tests

## Requirements Discussion

### First Round Questions

**Q1: Note Metadata Display - How should we display note metadata?**

**Answer:** Show in header section above code:
- Created date (relative: "2 days ago")
- Expires on date (countdown: "Expires in 5 days" OR "Expired")
- Language/syntax type badge (e.g., "PHP" or "Plain Text")
- View count: "42 views" (YES, track views - column exists in schema)
- Password-protected indicator: lock icon if password is set
- Layout: Clean header bar with these details, then code below

**Q2: Dashboard Note Management - What actions should authenticated users have in their dashboard?**

**Answer:** Simple actions for MVP:
- **View** (click to open note)
- **Copy URL** (clipboard copy button)
- **Delete** (with confirmation)
- Table columns: Hash, Title (if any), Language, Views, Expires, Created
- **NO**: Extend expiration, Change password (notes are immutable for MVP)

**Q3: Syntax Highlighting Library - Which library should we use?**

**Answer:** **Prism.js** (already mentioned in roadmap.md and tech.md)
- 270+ languages
- Customizable themes
- Good documentation and Laravel integration
- CDN or npm install via package.json

**Q4: Password Protection for Note Owners - Should note owners bypass password protection?**

**Answer:** **Owners bypass password automatically**
- If authenticated AND user_id matches note.user_id, skip password
- Otherwise (anonymous or different user), require password
- Rate limiting applies to non-owners only
- Better UX for managing own notes

**Q5: Expiration Time Display - How should expiration options be displayed?**

**Answer:** **Simple labels only** in dropdown:
- "10 minutes"
- "1 hour"
- "1 day"
- "1 week"
- "1 month" (default, pre-selected)
- "Never" (only if authenticated)
- NO relative times in dropdown (cleaner UI)

**Q6: Auto-delete Job Strategy - When should expired notes be deleted?**

**Answer:** **Both approaches**:
- **Immediate on access**: Check `expires_at` when viewing, return 410 Gone if expired
- **Scheduled job**: `DeleteExpiredNotes` runs every 10 minutes (per roadmap Phase 12)
- Job query: `Note::where('expires_at', '<', now())->delete()`
- Why both: Instant feedback + cleanup of unaccessed notes

### Existing Code to Reference

No similar existing features identified for reference. This is a new feature set being added to the anon.to platform.

### Follow-up Questions

No follow-up questions were needed. All requirements were clarified in the first round.

## Visual Assets

### Files Provided:

No visual assets provided.

### Visual Insights:

No visual assets provided.

## Requirements Summary

### Functional Requirements

**Core Note Creation:**
- Anonymous and authenticated users can create notes
- Support for syntax highlighting across 270+ languages via Prism.js
- Optional password protection (hashed using bcrypt)
- Optional burn-after-reading with configurable view limits
- Configurable expiration times (10 min, 1 hour, 1 day, 1 week, 1 month, never)
- "Never" expiration only available to authenticated users
- Default expiration: 1 month (pre-selected)
- Optional title field for notes
- Generate unique 8-character hash for each note URL

**Note Viewing:**
- Public viewing via /n/{hash} route
- Password prompt if password-protected (except for owners)
- View counter increments on each access
- Show metadata header above content:
  - Created date (relative format: "2 days ago")
  - Expiration countdown ("Expires in 5 days" or "Expired")
  - Language/syntax badge
  - View count ("42 views")
  - Password-protected indicator (lock icon)
- Syntax-highlighted code display using Prism.js
- Raw text view option
- Copy to clipboard functionality
- 410 Gone response for expired notes on access

**Owner Privileges:**
- Note owners (authenticated, user_id matches) bypass password protection
- Owners can view their own password-protected notes without entering password
- Rate limiting does not apply to owners viewing their own notes

**Dashboard (Authenticated Users):**
- List all user's notes in table format
- Table columns: Hash, Title (if any), Language, Views, Expires, Created
- Actions per note:
  - View (click to open note)
  - Copy URL (clipboard copy button)
  - Delete (with confirmation modal)
- Notes are immutable (no editing, no extending expiration for MVP)

**Burn After Reading:**
- Configurable view limit (1-100 views)
- Note automatically deleted after reaching view limit
- Display remaining views in metadata ("3 views remaining")
- Countdown updates in real-time

**Expiration Handling:**
- Immediate check on access: return 410 Gone if expired
- Scheduled cleanup job: `DeleteExpiredNotes` runs every 10 minutes
- Job deletes all notes where `expires_at < now()`
- Both mechanisms ensure expired notes are handled promptly

**Rate Limiting:**
- Note creation rate limited (e.g., 10 notes per hour for anonymous, 50 for authenticated)
- Password attempt rate limiting (5 attempts per 15 minutes per note)
- Rate limiting does not apply to owners viewing their own notes

### Reusability Opportunities

**Existing Patterns to Follow:**
- Authentication system already in place (Fortify)
- URL shortening hash generation logic can be adapted for note hashes
- Rate limiting patterns from link creation can be reused
- Database migration patterns established in Phase 1-3
- Test structure and patterns from existing 20 tests

**Components to Reference:**
- Existing authentication views and Fortify integration
- Flash UI components already in use
- Livewire Volt patterns if used in current dashboard

### Scope Boundaries

**In Scope:**
- Note creation (anonymous and authenticated)
- Syntax highlighting with Prism.js (270+ languages)
- Password protection with owner bypass
- Burn-after-reading functionality
- Expiration options and auto-deletion
- Note viewing with metadata display
- Dashboard for authenticated users (view, copy URL, delete)
- View counter tracking
- Rate limiting for creation and password attempts
- Copy to clipboard functionality
- Raw text view option
- Scheduled cleanup job for expired notes

**Out of Scope:**
- Note editing (immutable for MVP)
- Extending expiration after creation
- Changing password after creation
- Note collaboration/multi-user editing
- Note versioning/history
- Search functionality within notes
- Note categories/tags
- Public note gallery/discovery
- Note embedding in other sites
- API endpoints (may be added later)
- File uploads (text/code only for MVP)
- Custom note slugs (only 8-char hash)
- Note analytics/statistics beyond view count
- Email notifications for note access

**Future Enhancements (Mentioned but Deferred):**
- Note editing capability
- Ability to extend expiration
- Ability to change password
- Advanced dashboard features
- Public note discovery

### Technical Considerations

**Database:**
- Notes table already exists with 22 columns (schema complete)
- Columns include: hash, title, content, language, password, expires_at, max_views, current_views, user_id, etc.
- Use existing Laravel migration patterns

**Frontend:**
- Prism.js for syntax highlighting (via CDN or npm)
- Flux UI components for consistent design
- Livewire Volt for interactivity (if used in existing dashboard)
- Tailwind CSS v4 for styling
- Alpine.js (included with Livewire) for client-side interactions

**Backend:**
- Laravel 12 framework
- Fortify for authentication
- Bcrypt for password hashing
- Rate limiting via Laravel's built-in throttle middleware
- Scheduled job for cleanup (every 10 minutes)
- Form Request validation for note creation
- Eloquent models and relationships

**Testing:**
- Pest v4 for all tests
- Feature tests for note creation, viewing, deletion
- Browser tests for user flows
- Test password protection, burn-after-reading, expiration
- Test rate limiting behavior
- Test owner bypass functionality
- Maintain existing 20 passing tests

**Security:**
- Password protection using bcrypt hashing
- Rate limiting on creation and password attempts
- CSRF protection (Laravel default)
- XSS prevention in note content display
- Validation on all inputs
- Owner verification for bypassing password

**Integration Points:**
- Existing authentication system (Fortify)
- Existing user model and relationships
- Flash UI component library
- Rate limiting middleware
- Scheduled task runner (for cleanup job)

**Technology Stack (from tech-stack.md):**
- PHP 8.4.14
- Laravel 12
- Livewire v3 / Volt v1
- Flux UI v2 (free)
- Tailwind CSS v4
- Pest v4 / PHPUnit v12
- Prism.js (for syntax highlighting)

**Similar Code Patterns:**
- Follow existing Laravel 12 structure (streamlined)
- Use Volt for interactive components (if established pattern)
- Follow existing test patterns from Phase 1-3
- Reuse hash generation logic from URL shortening
- Follow existing Form Request validation patterns
- Use Flux UI components consistently
