# Specification: Admin Panel

## Goal
Create a comprehensive admin panel for anon.to that enables administrators to moderate content, manage users, handle reports, and maintain domain allow/block lists while adhering to strict privacy requirements.

## User Stories
- As an admin, I want to view system statistics and recent activity at a glance so I can monitor platform health
- As an admin, I want to moderate reported content and ban malicious users so I can maintain platform integrity
- As an admin, I want to manage domain allow/block lists so I can prevent abuse while tracking all actions in an immutable audit log

## Specific Requirements

**Admin Authentication & Authorization**
- Create middleware `EnsureUserIsAdmin` that checks `$user->is_admin === true`
- Register middleware in `bootstrap/app.php` as `admin` alias
- Add authorization gates for admin-only operations
- Extend existing LinkPolicy and NotePolicy with admin override methods (`forceView`, `forceDelete`)
- Create new policies: UserPolicy, ReportPolicy, AllowListPolicy
- All admin routes must be protected with `auth` and `admin` middleware
- Non-admin users attempting access receive 403 Forbidden response

**Dashboard Overview Component**
- Create Livewire Volt functional component at `/admin` route
- Display real-time statistics: total links, notes, users, pending reports
- Show recent activity feed (last 10 admin actions from audit log)
- Display system health metrics: disk usage, cache status, queue status
- Must load all data in single query (under 500ms target)
- Use Flux UI cards and badges for visual consistency
- Auto-refresh statistics every 60 seconds using wire:poll

**Link Management Component**
- Create Livewire Volt functional component at `/admin/links`
- Paginated table (25 per page) with columns: hash, URL (truncated), user, visits, status, created_at
- Real-time search by hash, full_url, or user email/name
- Bulk select with actions: Delete, Toggle Active/Inactive
- Individual row actions: View details, Edit status, Delete
- Filter by status: All, Active, Inactive, Reported
- Use Eloquent eager loading for user relationship to prevent N+1
- Delete action creates audit log entry and soft deletes if needed

**Note Management Component**
- Create Livewire Volt functional component at `/admin/notes`
- Paginated table (25 per page) with columns: hash, title, content preview (50 chars), user, views, status, created_at
- Real-time search by hash, title, or content using database full-text search
- Individual row actions: View full note, Delete
- Filter by status: All, Active, Inactive, Reported, Expired
- Use Eloquent eager loading for user relationship
- Delete action creates audit log entry

**User Management Component**
- Create Livewire Volt functional component at `/admin/users`
- Paginated table (25 per page) with columns: name, email, links count, notes count, is_admin, is_verified, created_at
- Real-time search by name or email
- Individual row actions: View profile, Ban/Unban, Verify/Unverify, Promote to Admin
- Ban action sets `is_active` flag and deactivates all user content
- Verify action sets `is_verified` flag and increases `api_rate_limit`
- Promote action sets `is_admin` flag (requires confirmation modal)
- Each action creates audit log entry

**Report Queue Component**
- Create Livewire Volt functional component at `/admin/reports`
- Paginated table (25 per page) with columns: type, content, category, reporter, status, created_at
- Display polymorphic reportable content (Link or Note) with preview
- Filter by status: Pending, Resolved, Dismissed
- Individual row actions: View details, Delete content, Ban user, Dismiss, Add admin notes
- Quick action buttons: Delete & Resolve, Ban User & Resolve, Dismiss
- Admin notes textarea with character counter (max 500 chars)
- Each action updates report status, sets dealt_by and dealt_at, creates audit log entry

**Allow/Block List Management Component**
- Create Livewire Volt functional component at `/admin/allowlist`
- Paginated table (50 per page) with columns: domain, type, pattern_type, hit_count, is_active, added_by, created_at
- Add new rule form: domain input, type select (allow/block), pattern_type select (exact/wildcard/regex), reason textarea
- Validate regex patterns before saving
- CSV import functionality: upload file, validate format, bulk insert with transaction
- CSV export functionality: download all rules in CSV format
- Test utility: input domain, check if it matches any rule, display which rule matches
- Toggle active/inactive status per rule
- Each action creates audit log entry

**Audit Logging System**
- Create new `audit_logs` table migration with columns: id, admin_id, action, model_type, model_id, old_values (json), new_values (json), ip_address (hashed), created_at
- Create AuditLog model with relationships to User (admin)
- Audit logs are immutable (no updates or deletes)
- Create helper trait `LogsAdminActions` with method `logAdminAction()`
- Log all admin actions: content deletion, user ban, report resolution, allowlist changes
- Store only model IDs and action type, never store user-generated content
- Admin panel displays audit logs with filter by admin, action type, date range

**Database Schema Changes**
- Create migration for `audit_logs` table with proper indexes
- Add `banned_at` timestamp column to `users` table
- Add `banned_by` foreign key to `users` table
- Ensure all polymorphic relationships have proper indexes
- Add composite index on reports (status, created_at) for queue performance

**UI/UX Design Patterns**
- Use Flux UI table component for all data tables
- Use Flux UI modal component for confirmation dialogs
- Use Flux UI badge component for status indicators (Active/Inactive, Pending/Resolved)
- Use Flux UI button variants: primary for main actions, danger for destructive actions
- Implement loading states with wire:loading on all actions
- Use Tailwind dark mode classes following existing pattern
- Match existing color scheme: indigo for primary, red for danger, green for success
- Responsive design: stack tables vertically on mobile, maintain usability

**Privacy & Security Compliance**
- NEVER log user-generated content (URLs, notes, passwords) in audit logs or application logs
- Store only SHA256 hashed IP addresses in audit_logs table
- Ensure all admin actions validate authorization before execution
- Use CSRF protection on all forms (Laravel default)
- Implement rate limiting on search queries (60 per minute per admin)
- Display privacy warning on admin dashboard about data handling responsibilities

**Performance Optimization**
- Use database indexes for all search and filter columns
- Implement query pagination with cursor pagination for large datasets
- Eager load relationships to prevent N+1 queries
- Cache dashboard statistics for 60 seconds using Laravel cache
- Use database transactions for bulk operations
- Implement queue jobs for bulk deletion (>100 items)

## Existing Code to Leverage

**Existing Policies (LinkPolicy, NotePolicy)**
- Reuse policy structure with admin override methods for force operations
- Follow existing pattern: `view`, `create`, `update`, `delete`, `forceDelete`
- Add admin-specific methods that check `is_admin` flag before allowing action
- Register policies in AuthServiceProvider if not auto-discovered

**Livewire Volt Functional Components**
- Follow existing pattern from `resources/views/livewire/home.blade.php` and `resources/views/livewire/notes/create.blade.php`
- Use `@volt` directive with functional API using `state()`, `computed()` functions
- Implement wire:model.live for real-time search with debounce
- Use wire:loading states for all actions with spinner animation
- Structure: PHP logic block, then Blade template in same file

**Flux UI Component Library**
- Reuse existing Flux components: button, input, textarea, select, field, modal, badge, separator
- Follow existing variant patterns: variant="primary" for main actions, variant="danger" for destructive
- Use Flux icons with variant="mini" for inline icons
- Implement form fields with Flux field wrapper for consistent styling

**Navigation Component**
- Add admin menu items to existing `resources/views/components/navigation.blade.php`
- Display admin menu only when `auth()->user()?->is_admin === true`
- Use Flux dropdown component for admin submenu items
- Menu items: Dashboard, Links, Notes, Users, Reports, Allow List, Audit Log

**Form Validation Pattern**
- Create Form Request classes following Laravel convention
- Place in `app/Http/Requests/Admin/` namespace
- Include both validation rules and custom error messages
- Use array-based validation rules (check existing FormRequests for pattern)

## Out of Scope
- User role system beyond simple admin boolean flag (no moderator, super-admin, etc.)
- Automated content moderation using AI or pattern matching
- Email notifications for admins when new reports submitted
- Scheduled reports or analytics exports
- Multi-language support for admin panel
- Advanced analytics dashboard with charts and graphs
- Batch import of users or content via CSV
- Content versioning or revision history
- Geographic or IP-based content blocking
- Integration with external moderation services
