# Spec Requirements: Admin Panel

## Initial Description
Build a comprehensive admin panel for moderating and managing the anon.to platform. The admin panel will replace the current `/dashboard` route (currently used for regular user dashboard) and provide administrative controls for content moderation, user management, abuse reports, and domain filtering. This is a critical feature for Phase 7 of the product roadmap.

## Requirements Discussion

### First Round Questions

**Q1:** Should the admin panel replace the current `/dashboard` route or use a separate route like `/admin`?
**Answer:** Replace current `/dashboard` with `/admin`. The existing dashboard at `/dashboard` should become the admin panel.

**Q2:** What metrics should appear on the admin dashboard overview? (Proposed: total links, total notes, total users, pending reports count, today's activity)
**Answer:** Use recommended metrics: total links, total notes, total users, pending reports count, and today's activity (links/notes created today).

**Q3:** For link management, should admins be able to: view all links (paginated), search/filter by hash/URL/user, delete links, toggle active/inactive status, view link owner? Are there any additional capabilities needed?
**Answer:** Yes to all recommendations (view all, search/filter, delete, toggle active/inactive, view owner), but IMPORTANT: there is NO link expiration feature - that was removed from the product. Do not include any expiration-related management features.

**Q4:** For note management, do you want individual note management (view, delete) or bulk operations (bulk delete, bulk toggle active)?
**Answer:** Individual management is sufficient. No bulk operations needed.

**Q5:** For user management, should admins be able to ban users (soft delete/flag) or just delete accounts entirely?
**Answer:** Just delete users for MVP. No banning system needed.

**Q6:** For the report queue, what information should be displayed and what actions should be available? (Proposed: show reported content, reporter info, category, comment, timestamp; actions: delete content, ban reporter if malicious, dismiss report, add admin notes)
**Answer:** Keep it simple, use recommendations (show reported content, reporter info, category, comment, timestamp; actions: delete content, ban reporter, dismiss report, optional admin notes).

**Q7:** For allow/block list management, what features are essential? (Proposed: add domain form with pattern type selector, table showing all rules with edit/delete actions, test domain utility, display hit counter)
**Answer:** Keep it simple, use recommendations (add domain form with pattern type, table with edit/delete, test domain utility, hit counter display).

**Q8:** Should there be audit logging for admin actions (who deleted what, when)?
**Answer:** Not required for MVP.

**Q9:** Do you need CSV import/export for allow/block lists?
**Answer:** Not required for MVP.

**Q10:** For authorization, should we use a simple `is_admin` flag check via middleware, or implement more granular role-based permissions (e.g., moderator vs super-admin)?
**Answer:** Use recommendations (EnsureAdmin middleware checking is_admin === true, simple admin/non-admin flag).

**Q11:** Given this is an MVP, are there any features I should recommend removing or deferring to keep scope manageable?
**Answer:** User should recommend what to keep/exclude for MVP based on the answers provided.

### Existing Code to Reference

**Similar Features Identified:**
- Feature: Current `/dashboard` route - Path: `/Users/abi/Sites/anon.to/routes/web.php` (line mentioning dashboard)
- Feature: Existing Livewire Dashboard component - Path: `/Users/abi/Sites/anon.to/app/Livewire/Dashboard/Index.php`
- Feature: User authentication with Fortify - Already implemented throughout application
- Components to potentially reuse: Existing Flux UI components used in dashboard and settings pages
- Backend logic to reference: User model with is_admin flag, Link/Note/Report/AllowList models all exist

**Models Available:**
- `User` model with `is_admin` boolean flag (ready for authorization)
- `Link` model with `is_active`, `is_reported`, `user_id`, `visits` fields
- `Note` model with `is_active`, `is_reported`, `user_id`, `views` fields
- `Report` model with polymorphic relationship to Link/Note, includes category, comment, status, admin_notes
- `AllowList` model with domain, pattern_type, hit_count, added_by fields

### Follow-up Questions

No follow-up questions were needed - the user provided clear, comprehensive answers to all initial questions.

## Visual Assets

### Files Provided:
No visual files found.

### Visual Insights:
No visual assets provided. The admin panel should follow the existing design patterns from the current dashboard, settings pages, and overall application aesthetic (Flux UI + Tailwind CSS 4 with dark mode support).

## Requirements Summary

### Functional Requirements

**Dashboard Overview Page**
- Display key metrics in card format:
  - Total links (count from links table)
  - Total notes (count from notes table)
  - Total users (count from users table)
  - Pending reports (count of reports where status = 'pending')
  - Today's activity (links + notes created today)
- Real-time or near-real-time data (acceptable to cache for 5-10 minutes)
- Mobile responsive layout

**Link Management**
- Paginated table of all links (50 per page recommended)
- Display columns: hash, full URL (truncated), owner (username or "Anonymous"), visits, created_at, is_active status
- Search/filter functionality:
  - Search by: hash, URL, user
  - Filter by: active/inactive status, date range
- Row actions:
  - View link details (opens link info page or modal)
  - Toggle active/inactive (soft disable without deletion)
  - Delete link (with confirmation modal)
  - View owner profile (if link has user_id)
- NOTE: NO link expiration management (feature doesn't exist)

**Note Management**
- Paginated table of all notes (50 per page recommended)
- Display columns: hash, title (truncated), owner (username or "Anonymous"), views, created_at, is_active status
- Search/filter functionality:
  - Search by: hash, title/content, user
  - Filter by: active/inactive status, date range
- Individual row actions only:
  - View note (opens note view page)
  - Delete note (with confirmation modal)
- NO bulk operations needed for MVP

**User Management**
- Paginated table of all users (50 per page recommended)
- Display columns: username, email, is_admin flag, links count, notes count, created_at
- Search by: username, email
- Row actions:
  - View user's links (filtered link management view)
  - View user's notes (filtered note management view)
  - Delete user account (with confirmation modal - hard delete for MVP)
- Simple delete only - no banning/soft delete system needed for MVP

**Report Queue**
- Paginated table of reports (50 per page recommended)
- Filter by: status (pending, resolved, dismissed), category, date range
- Display columns:
  - Reported content type (Link/Note)
  - Content preview (URL for links, title for notes)
  - Reporter info (email if provided, or "Anonymous")
  - Category (spam, malware, illegal, copyright, harassment, other)
  - Comment from reporter
  - Timestamp (created_at)
  - Status (pending/resolved/dismissed)
- Row actions:
  - View reported content (link to actual link/note)
  - Delete content (delete the reported link/note with confirmation)
  - Delete reporter (if reporter has user account and is malicious)
  - Dismiss report (mark as resolved without action)
  - Add admin notes (optional textarea for internal notes)
- Quick action buttons for bulk triage (optional enhancement)

**Allow/Block List Management**
- Form to add new domain rule:
  - Domain input field (e.g., "example.com", "*.spam.com", etc.)
  - Pattern type selector (exact, wildcard, regex)
  - Type selector (allow or block)
  - Reason textarea (why this rule exists)
- Paginated table of existing rules (50 per page recommended)
- Display columns: domain pattern, pattern type, type (allow/block), hit count, added_by (admin username), created_at
- Row actions:
  - Edit rule (change pattern, type, or reason)
  - Delete rule (with confirmation)
  - Toggle active/inactive
- Test domain utility:
  - Input field to test a domain
  - Shows which rules (if any) would match
  - Indicates if domain would be allowed or blocked
- Hit counter display (shows how many times each rule has been triggered)
- NO CSV import/export for MVP

**Authorization**
- Create `EnsureAdmin` middleware
- Check `$user->is_admin === true`
- Apply to all admin routes (route group with middleware)
- Redirect non-admins to home page or show 403 error
- Simple two-tier system: admin or non-admin (no granular roles for MVP)

### Reusability Opportunities

**Existing Components to Reuse:**
- Flux UI components (tables, buttons, modals, forms, badges) - already used throughout app
- Livewire Volt pattern from existing dashboard (`/app/Livewire/Dashboard/Index.php`)
- Existing authentication patterns from Fortify setup
- Existing Tailwind CSS 4 styling and dark mode support

**Backend Patterns to Follow:**
- Policy classes for authorization (similar to existing policies for Link/Note)
- Action classes for complex operations (follow app conventions)
- Form Request classes for validation
- Eloquent relationships already defined in models

**Similar Features to Model After:**
- User dashboard structure (tabs, tables, row actions) at `/dashboard` route
- Settings pages layout and form handling
- Existing table pagination patterns
- Modal confirmation patterns for destructive actions

### Scope Boundaries

**In Scope (MVP):**
- Admin dashboard overview with key metrics
- Link management (view, search, filter, delete, toggle active)
- Note management (view, search, filter, delete individually)
- User management (view, search, delete accounts)
- Report queue (view, filter, delete content, dismiss reports, add notes)
- Allow/block list management (CRUD operations, pattern matching, hit counter, test utility)
- Simple authorization (EnsureAdmin middleware with is_admin check)
- Replace `/dashboard` route with admin panel
- Mobile responsive design
- Dark mode support (consistent with rest of app)

**Out of Scope (MVP):**
- Link expiration management (feature doesn't exist in product)
- Bulk operations for notes (individual management only)
- User banning system (just delete for MVP)
- Audit logging of admin actions (deferred to future phase)
- CSV import/export for allow/block lists (deferred to future phase)
- Granular role-based permissions (simple admin flag only)
- Advanced analytics or reporting
- Email notifications for admin actions
- Real-time updates (WebSockets/broadcasting)
- API endpoints for admin operations

**Future Enhancements (Post-MVP):**
- Audit logging for compliance and accountability
- CSV import/export for domain rules
- Bulk operations for notes and links
- User banning/suspension system (soft delete)
- More granular permissions (moderator vs super-admin)
- Advanced search with full-text search
- Report analytics and trends
- Automated spam detection integration
- Email notifications for pending reports
- Real-time dashboard updates

### Technical Considerations

**Route Structure:**
- Move existing `/dashboard` to `/admin`
- Create new `/dashboard` route for regular users (links management - Phase 6)
- All admin routes should be grouped under `/admin` prefix with `EnsoreAdmin` middleware

**Database Queries:**
- Use eager loading to prevent N+1 queries (e.g., load user relationships with links/notes)
- Consider caching dashboard metrics (5-10 minute TTL acceptable)
- Paginate all tables (50 per page default)
- Use indexes for search performance (hash, user_id, created_at)

**Authorization:**
- Create `EnsureAdmin` middleware in `app/Http/Middleware/`
- Register middleware in `bootstrap/app.php` (Laravel 12 structure)
- Apply to route group in `routes/web.php`
- Use gates/policies for fine-grained checks if needed later

**UI/UX:**
- Follow existing Flux UI component patterns
- Maintain dark mode support with `dark:` Tailwind variants
- Use Livewire Volt for component structure (match existing dashboard)
- Include loading states for async operations
- Confirmation modals for all destructive actions
- Toast notifications for action feedback

**Privacy Compliance:**
- Respect `LOG_CHANNEL=null` - no logging of admin actions that includes user data
- Display hashed IPs only (not raw IPs)
- Admin notes stored in database only, never logged
- Deleted content should be permanently removed (no soft deletes for MVP)

**Testing:**
- Feature tests for all admin routes (authorization checks)
- Feature tests for CRUD operations on all models
- Feature tests for middleware (admin access only)
- Browser tests for critical admin flows (Pest 4)
- Policy tests for authorization logic
- Test that non-admins cannot access any admin routes

**Performance:**
- Cache dashboard metrics for 5-10 minutes
- Paginate all tables (never load all records at once)
- Use select() to limit columns fetched where appropriate
- Consider indexes on frequently queried columns
- Redis caching for expensive queries

**Similar Code Patterns:**
- Models: User, Link, Note, Report, AllowList all exist with relationships
- Existing dashboard at `/app/Livewire/Dashboard/Index.php` shows Volt component structure
- Follow Laravel 12 conventions (no app/Http/Kernel.php, use bootstrap/app.php)
- Use Fortify authentication patterns already in place

**Technology Stack:**
- Laravel 12 (existing)
- Livewire 3 + Volt (existing pattern)
- Flux UI components (existing, free edition)
- Tailwind CSS 4 with dark mode (existing)
- MySQL for persistence (existing)
- Redis for caching (existing)
- Pest 4 for testing (existing)

**Middleware Registration:**
- Register in `bootstrap/app.php` (Laravel 12 structure, not app/Http/Kernel.php)
- Apply to route group in routes/web.php

**Notes Integration:**
- Link model already has `is_active`, `is_reported`, `user_id` fields
- Note model already has `is_active`, `is_reported`, `user_id` fields
- Report model supports polymorphic relationships to both Link and Note
- AllowList model ready with domain, pattern_type, hit_count fields
