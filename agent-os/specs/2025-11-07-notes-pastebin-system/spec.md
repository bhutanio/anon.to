# Specification: Notes/Pastebin System

## Goal
Enable privacy-first sharing of code snippets and text content with syntax highlighting, password protection, burn-after-reading, and expiration options for both anonymous and authenticated users.

## User Stories
- As an anonymous user, I want to create password-protected notes with expiration times so that I can share sensitive code snippets securely without creating an account
- As an authenticated user, I want to view all my created notes in a dashboard so that I can manage and access my shared content easily
- As a note owner, I want to access my password-protected notes without entering the password so that I have seamless access to my own content

## Specific Requirements

**Note Creation Form**
- Accept content input via textarea with 1MB max size (1048576 bytes)
- Provide syntax language dropdown with 40+ languages from config('anon.syntax_languages')
- Include optional title field (max 255 characters)
- Offer expiration dropdown with options: 10 minutes, 1 hour, 1 day, 1 week, 1 month (default), Never (authenticated only)
- Provide optional password field with confirmation (bcrypt hashed when stored)
- Include optional burn-after-reading with view limit selector (1-100 views)
- Generate 8-character unique hash using adapted GenerateHash action pattern
- Rate limit creation: 10 notes/hour for anonymous users, 50 notes/hour for authenticated users

**Note Model and Database**
- Use existing notes table migration with 22 columns including hash, title, content, syntax, password_hash, expires_at, view_limit, views, user_id
- Create Note model with fillable fields, casts, and relationships (belongsTo User, morphMany Reports)
- Store content_hash (SHA256 of content) for duplicate detection
- Track char_count and line_count on creation
- Store hashed IP address (SHA256) and user_agent for anonymous notes
- Set is_active to true, is_public to true, is_code based on syntax selection by default

**Note Viewing Logic**
- Route: GET /n/{hash} where hash is 8 characters [a-zA-Z0-9]{8}
- Check note exists and is_active, return 404 if not found
- Check expires_at on access, return 410 Gone with "This note has expired" if expired
- If password_hash exists and viewer is not owner (user_id != auth id), show password prompt
- Password attempts rate limited to 5 attempts per 15 minutes per note per IP
- If view_limit exists, check if views >= view_limit, show 410 Gone "This note has been deleted"
- Increment views and unique_views counters, update last_viewed_at timestamp
- If views reaches view_limit after incrementing, hard delete note immediately
- Cache note data for 24 hours using Cache::put("note:{$hash}", $note, 86400)

**Note Display Interface**
- Show metadata header bar with: created date (relative "2 days ago"), expiration countdown ("Expires in 5 days"), language badge, view count ("42 views"), password lock icon if protected
- Display burn-after-reading countdown if view_limit set ("3 views remaining")
- Render content with Prism.js syntax highlighting based on syntax field
- Provide "Copy to Clipboard" button using JavaScript clipboard API
- Provide "View Raw" toggle to show plain text without highlighting
- Use Flux UI components for buttons, badges, and layout consistency
- Support dark mode via Tailwind dark: classes

**Owner Bypass Logic**
- If auth()->check() and auth()->id() === note->user_id, skip password prompt entirely
- Owner sees all metadata including password protection status but bypasses entry
- Rate limiting for password attempts does not apply to owners viewing their own notes
- Owners can view their expired notes in dashboard history but public access returns 410

**Dashboard Integration**
- Add "Notes" tab to existing dashboard alongside "Links" tab
- Display table with columns: Hash (link to view), Title, Language, Views, Expires, Created
- Provide row actions: View (opens /n/{hash}), Copy URL (clipboard), Delete (with confirmation modal)
- Sort by created_at DESC by default
- Show empty state with "Create your first note" message and link to creation form
- Use Livewire component pattern similar to Home.php for interactivity

**Password Protection Flow**
- Show password prompt overlay when accessing password-protected note (except for owners)
- Validate password using Hash::check() against password_hash
- On successful password entry, store session flag to bypass on refresh for 15 minutes
- On failed password, show error message and decrement rate limit attempts
- After 5 failed attempts within 15 minutes, show "Too many attempts, try again in X minutes"

**Expiration and Cleanup**
- Immediate check: on every note access via /n/{hash}, verify expires_at > now() or return 410
- Scheduled job: create DeleteExpiredNotes command running every 10 minutes via scheduler
- Job query: Note::where('expires_at', '<', now())->delete() to cleanup unaccessed expired notes
- "Never" expiration sets expires_at to null, only available when auth()->check()
- Default expiration when none selected: 1 month from creation (now()->addMonth())

**Action Classes Pattern**
- Create CreateNote action following CreateLink pattern with constructor injection
- Include ValidateNote, GenerateNoteHash, CheckNoteDuplicate sub-actions
- CreateNote->execute() accepts array with content, syntax, title, password, expires_at, view_limit, user_id
- Hash IP address using hash('sha256', request()->ip()) for anonymous notes
- Calculate char_count using mb_strlen() and line_count using substr_count($content, "\n") + 1
- Store content_hash using hash('sha256', $content) for duplicate detection
- Cache note after creation using Cache::put("note:{$hash}", $note, 86400)

**Form Request Validation**
- Create CreateNoteRequest following CreateLinkRequest pattern with array-based rules
- Validate content: required, string, max:1048576 bytes
- Validate syntax: nullable, string, in:config('anon.syntax_languages')
- Validate title: nullable, string, max:255
- Validate password: nullable, string, min:8, max:255, confirmed
- Validate expires_at: nullable, date, after:now (skip if authenticated and "never" selected)
- Validate view_limit: nullable, integer, min:1, max:100
- Provide custom error messages for each validation rule

**Syntax Highlighting Integration**
- Install Prism.js via npm: prism-themes, prismjs packages
- Include Prism core and language components in app.js via Vite
- Use data-language attribute on code blocks to specify language
- Load Prism theme CSS (suggest "prism-tomorrow" for light, "prism-tomorrow-night" for dark mode)
- Auto-detect and escape HTML entities in content before rendering to prevent XSS
- Fallback to "plaintext" language if syntax field is null or invalid

**Rate Limiting Strategy**
- Note creation: use RateLimiter::tooManyAttempts() with key pattern 'create-note:user:{id}' or 'create-note:ip:{ip}'
- Anonymous users: 10 attempts per hour (RateLimiter::hit($key, 3600))
- Authenticated users: 50 attempts per hour
- Password attempts: key pattern 'note-password:{hash}:ip:{ip}', 5 attempts per 15 minutes (900 seconds)
- Clear rate limit on successful password entry for better UX
- Return remaining seconds in error message when rate limited

**Security Measures**
- Use bcrypt hashing for password_hash via Hash::make()
- Hash IP addresses with SHA256 before storing
- Escape all user-generated content before rendering in views
- Apply CSRF protection (Laravel default) on all POST routes
- Validate all inputs via Form Request classes
- Implement rate limiting on creation and password attempts
- Use SQL parameter binding (Eloquent default) to prevent SQL injection
- Set CSP headers to prevent XSS attacks via middleware

**Testing Requirements**
- Create NoteFactory with faker data for testing, including states for withPassword, withExpiration, withViewLimit
- Feature tests: note creation, duplicate detection, viewing, password protection, burn-after-reading, expiration, owner bypass
- Feature tests: dashboard listing, note deletion, rate limiting on creation and password attempts
- Browser tests: full user flow for creating note, viewing with password, burn-after-reading countdown
- Test expired note returns 410 Gone, test view limit deletion, test owner bypass
- Test caching behavior for note retrieval
- Minimum 15 new tests covering all requirements, aim for 20+

## Visual Design

No visual assets provided. Follow existing anon.to design patterns:

**Note Creation Page**
- Clean form layout similar to Home.php link creation form
- Use Flux UI textarea component for content input with monospace font
- Flux select dropdown for language selection with search functionality
- Flux input for optional title field
- Flux select for expiration dropdown with clear labels
- Flux password input with show/hide toggle for password and confirmation
- Flux checkbox and number input for burn-after-reading with view limit selector
- Primary Flux button "Create Note" with loading state via wire:loading
- Show success message with note URL and copy button after creation
- Display rate limit error in Flux alert/callout component when exceeded

**Note View Page**
- Metadata header bar with clean typography, neutral background, rounded corners
- Badge components for language (colored), lock icon (muted), view count (neutral)
- Relative timestamps for created date, countdown for expiration in warning color when < 24 hours
- Code block with Prism.js syntax highlighting, line numbers, horizontal scroll for long lines
- Sticky header with action buttons: Copy to Clipboard, View Raw toggle
- Password prompt overlay with Flux modal, centered input, primary submit button
- Burn-after-reading warning banner at top when view_limit approaching (< 5 views remaining)
- 410 Gone error page with friendly message and link to create new note

**Dashboard Notes Tab**
- Tabbed interface with Links | Notes tabs at top
- Responsive table with hover states on rows
- Hash column as clickable link in monospace font
- Action buttons (View, Copy URL, Delete) as icon buttons with tooltips
- Delete confirmation Flux modal with danger button
- Empty state illustration with "No notes yet" message and CTA button
- Loading skeleton while fetching notes data

## Existing Code to Leverage

**CreateLink Action Pattern**
- Follow constructor injection pattern with sub-actions (ValidateUrl, GenerateHash, CheckDuplicate)
- Use same hash generation logic but adapted for 8-character length instead of 6
- Replicate caching strategy after creation using Cache::put()
- Follow IP address hashing pattern using hash('sha256', request()->ip())
- Use similar error handling with InvalidArgumentException and RuntimeException

**Link Model Structure**
- Copy fillable array pattern, casts() method, and relationship methods (belongsTo User, morphMany Reports)
- Follow same naming conventions for timestamps (expires_at, last_viewed_at)
- Replicate boolean field patterns (is_active, is_reported, is_public)
- Use integer casts for counter fields (views, unique_views, view_limit)

**Home.php Livewire Component**
- Replicate rate limiting logic using RateLimiter facade with user/IP-based keys
- Follow same error message display pattern with errorMessage property
- Use similar validation approach with inline validate() method
- Copy clipboard handling pattern with markAsCopied() method and dispatch events
- Follow same render() pattern with layout('components.layouts.guest')

**CreateLinkRequest Validation**
- Copy structure with authorize(), rules(), messages(), attributes() methods
- Follow array-based validation rules pattern (existing convention in codebase)
- Replicate custom error message structure for better UX
- Use config() values for dynamic validation rules (max lengths)

**GenerateHash Action**
- Adapt hash generation logic from 6-character to 8-character length
- Reuse excluded words checking logic from config('anon.excluded_words')
- Copy collision detection pattern with database exists() check
- Follow maxAttempts pattern with RuntimeException on failure
- Use same config-based approach for hash_length setting (add 'note_hash_length' config)

## Out of Scope
- Note editing or updating after creation (immutable for MVP)
- Extending expiration dates after note is created
- Changing password after note is created
- Note collaboration or multi-user editing features
- Version history or revision tracking for notes
- Search functionality within note content or across notes
- Categorization with tags or folders for organizing notes
- Public gallery or discovery page for browsing notes
- Social features like comments, likes, or sharing to social media
- Embedding notes in external websites via iframe
- REST API endpoints for programmatic note creation (future phase)
- File upload support, only text/code content in MVP
- Custom vanity URLs or slugs, only 8-character hash allowed
- Advanced analytics beyond basic view counter
- Email notifications when notes are accessed or about to expire
- Note forking or cloning (forked_from_id field exists but not implemented in MVP)
- Batch operations on multiple notes in dashboard
- Export notes to file formats (PDF, Markdown, etc.)
- Syntax language auto-detection from content
- Line-by-line commenting or annotations on code
- Diff view for comparing note versions (no versions in MVP)
