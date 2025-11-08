# Spec Initialization: Admin Panel

## Feature Description

Complete admin panel for anon.to platform with comprehensive moderation and management capabilities.

## Context from Product

This is for anon.to, a privacy-focused anonymous URL shortening platform with:
- URL shortening with redirect warnings
- Anonymous note/pastebin sharing
- QR code generator (PNG/SVG/PDF)
- User authentication with 2FA
- Reporting and moderation system

## Tech Stack

- Laravel 12.37.0 + PHP 8.4.14
- Livewire 3.6.4 + Volt 1.9.0
- Flux UI 2.6.1 (free edition)
- Tailwind CSS 4.1.17
- Pest 4.1.3 (testing)
- MySQL database

## Existing Database Models

- User (with is_admin flag)
- Link (URL shortening)
- Note (pastebin)
- Report (polymorphic reporting system)
- AllowList (domain allowlist/blocklist)

## Privacy Requirements

- No production logging (LOG_CHANNEL=null)
- SHA256 hashed IP addresses
- No tracking of user behavior

## From Product Roadmap

Phase 7: Admin Moderation Tools is planned with the following features:

- Admin middleware (check `is_admin` flag)
- Admin dashboard overview:
  - Real-time stats (total links, notes, users, pending reports)
  - Recent activity feed
  - System health metrics
- Link management:
  - View all links (paginated)
  - Search by hash, URL, user
  - Bulk delete
  - Toggle active/inactive
- Note management:
  - View all notes (paginated, preview content)
  - Search by hash, content
  - Delete notes
- User management:
  - View all users
  - Ban/unban users
  - Verify users (higher rate limits)
  - Promote to admin
  - View user's links/notes
- Report queue:
  - View pending reports
  - One-click actions: Delete content, Ban user, Dismiss
  - Add admin notes
  - Mark as dealt
- Allow/block list management:
  - Add/edit/remove domains
  - Pattern type (exact, wildcard, regex)
  - CSV import/export
  - Test utility (check if domain would be blocked)
- Audit logging:
  - Track all admin actions
  - Immutable log
  - Filter by admin, action type, date

## Success Criteria (From Roadmap)

- Admin dashboard loads in < 500ms
- All actions have authorization gates (only admins)
- Audit log is complete and immutable
- Bulk operations handle 1000+ items
- Tests: Admin authorization, all CRUD operations

## Initial Status

- Database schema: `is_admin` column exists on users table
- No admin routes currently exist
- No admin moderation interface
- No user management UI
- Reports model exists but no UI for viewing/managing reports
- AllowList model exists but no admin interface
