# Phase 0 - Technical Specification

## Architecture Overview

Phase 0 establishes a clean, normalized relational database schema following Laravel conventions. We're using MySQL as the primary database with a multi-table design that separates concerns: links for URL shortening, notes for pastebin functionality, reports for abuse moderation, allow_lists for domain filtering, and link_analytics for visit tracking. The schema supports both anonymous and authenticated usage patterns, with optional user_id foreign keys that can be NULL for anonymous content.

Key architectural decisions: polymorphic relationships for reports (can report links OR notes), separate analytics table for performance (partitionable by date), hashed URLs for duplicate detection, and component-based storage for URL reconstruction (scheme, host, port, path, query, fragment).

## Data

### What We're Storing

**Users Table (Enhanced)**
Adding columns to the existing users table to support our application's specific needs: username (optional, unique), is_admin flag, is_verified flag (for higher rate limits), api_rate_limit (default 100), last_login_at timestamp, and all Fortify two-factor authentication columns (already added).

**Links Table**
The core URL shortening functionality. Each link stores: a unique hash (6 characters), optional custom slug (registered users only), parsed URL components (scheme, host, port, path, query, fragment), the full reconstructed URL, a SHA256 hash of the full URL for duplicate detection, optional metadata (title, description), expiration timestamp, visit counters (total and unique), last visited timestamp, active/reported status flags, and optional creator user_id (nullable for anonymous). IP address and user agent stored hashed for privacy.

**Notes Table**
For pastebin-style content sharing. Each note stores: unique hash (6-8 characters), optional slug, optional title, content (longtext up to 10MB), SHA256 content hash for duplicate detection, syntax language for highlighting, character and line counts, expiration timestamp, optional password hash (bcrypt), optional view limit for burn-after-reading, view counter, last viewed timestamp, active/reported/public flags, optional user_id, optional forked_from_id for note cloning, and hashed IP/user agent.

**Reports Table**
Polymorphic abuse reporting for both links and notes. Each report stores: reportable_type and reportable_id (polymorphic), category (spam, malware, illegal, copyright, harassment, other), optional external URL, reporter email (optional), comment (required), reporter IP address, optional reporter user_id, status (pending, reviewing, dealt, dismissed), admin notes, dealt_by admin user_id, and dealt_at timestamp.

**Allow Lists Table**
Domain filtering rules for link creation. Each entry stores: domain or pattern, type (allow or block), pattern_type (exact, wildcard, regex), reason for the rule, is_active flag, hit_count for tracking usage, and added_by admin user_id.

**Link Analytics Table**
Detailed visit tracking separated for performance and archiving. Each record stores: link_id foreign key, visited_at timestamp, hashed visitor IP, country_code (2-char ISO), referrer URL, and user agent. This table will grow large and should be partitioned by month for efficient querying and archival.

### Key Data Considerations

- Validation rules: URLs max 2048 chars, notes max 10MB, hashes must be unique, email format validation, password minimum strength requirements
- Data migration: Will need to map old database structure to new schema in Phase 13 (legacy import command)
- Data retention: Expired links/notes deleted hourly by scheduled job, analytics data aggregated and archived monthly, anonymous session data not retained
- Indexes needed: unique on hashes and slugs, composite on link_id + visited_at for analytics, full-text on note content for search, hash indexes on full_url_hash and content_hash for duplicate detection

## Components to Build

### Backend Components

**Database Migrations**
Five new migration files to create links, notes, reports, allow_lists, and link_analytics tables. These must define all columns with proper types, nullable flags, default values, foreign key constraints, and indexes. The migrations should be reversible (down methods) and idempotent.

**Eloquent Models**
Five new model classes (Link, Note, Report, AllowList, LinkAnalytic) with proper configuration: table name (if non-standard), fillable or guarded attributes, casts for dates and booleans, relationships (belongsTo, hasMany, morphTo/morphMany), and any custom accessors or mutators needed. Models should use constructor property promotion in PHP 8.4 where applicable.

**Model Relationships**
Critical relationships to define: User hasMany Links and Notes; Link belongsTo User and hasMany Reports and LinkAnalytics; Note belongsTo User and hasMany Reports and Notes (for forks); Report morphTo reportable (Link or Note) and belongsTo User (reporter) and User (admin who dealt); AllowList belongsTo User (admin who added).

**Factories**
Three factory classes (LinkFactory, NoteFactory, ReportFactory) using Laravel's factory pattern. Factories should generate realistic test data using Faker, respect validation rules, handle optional relationships (nullable user_id), and support factory states for common scenarios (expired, reported, password-protected, etc.).

**Seeders**
Two seeder classes: AllowListSeeder to populate initial blocked domains (common spam, malware, phishing domains), and AdminUserSeeder to create the first admin account with secure credentials. Seeders should be idempotent (check for existing data before inserting).

**Configuration**
Redis configuration in database.php and cache.php for caching and queue drivers. Ensure CACHE_STORE and QUEUE_CONNECTION are set to redis in production environment.

### Integration Points

**Fortify Authentication**
Phase 0 relies on Fortify for user registration, login, password reset, and two-factor authentication. The existing users table already has 2FA columns added. No changes needed to Fortify configuration, but verify authentication works after adding custom user columns.

**Redis Service**
Redis will be used for caching (link and note data), session storage (authenticated users), and queue driver (background jobs). Must be running and accessible in development and production environments.

**Legacy Database**
Not implemented in Phase 0, but schema design considers future import from old database structure. The legacy connection configuration can be added in Phase 13 when implementing the import command.

## Security

**Critical security considerations:**

- Authentication/Authorization: Anonymous users can create links/notes but cannot set custom slugs, view analytics, or access dashboards. Registered users own their content. Admins have full access to moderation tools.

- Input Validation: All user input validated via Form Request classes. URLs validated for proper format, scheme (http/https only), and max length. Notes validated for max size and content type. Email addresses validated for format. No raw SQL queries - only Eloquent ORM and query builder with parameter binding.

- Sensitive Data: Passwords hashed with bcrypt before storage. Note passwords hashed with bcrypt. IP addresses hashed with SHA256 + salt before storage (never stored raw). User emails verified before unlocking features. Two-factor authentication secrets encrypted at rest.

- Common Vulnerabilities: SQL injection prevented via Eloquent ORM. XSS prevented via Blade auto-escaping. CSRF protection via Laravel middleware (already enabled). Mass assignment protection via fillable/guarded properties on models. SSRF prevention by validating and blocking internal IP addresses in URLs.

## Performance

**Key performance considerations:**

- Caching: Hot links and notes cached in Redis with 24-hour TTL. Allow/block list cached for fast validation. User statistics cached with 5-minute TTL. Cache invalidation on update/delete operations via model events.

- Database: Critical indexes on hash columns (unique), full_url_hash (duplicate detection), user_id (ownership queries), expires_at (cleanup jobs), and composite index on link_analytics (link_id, visited_at) for time-series queries. Consider partitioning link_analytics by month for large-scale deployments.

- API: Rate limiting middleware by user type (anonymous: 20/hour, registered: 100/hour, verified: 500/hour, admin: unlimited). Implement token bucket algorithm using Laravel's RateLimiter facade. Pagination for list endpoints (20 items per page default).

## Testing Strategy

**Phase 0 Testing Approach:**

Testing is deferred to keep timeline quick (1-3 days). Focus on getting the foundation working first, then add tests during Phase 1 implementation or later.

**When tests are added later, they should cover:**

- Model relationships work correctly (can navigate user to links to analytics)
- Factories generate valid data that passes validation rules
- Migrations create correct table structure (column types, indexes, foreign keys)
- Migration rollback works without errors
- Seeders populate expected initial data
- Duplicate detection works via hashed URL comparison
- Polymorphic relationships resolve correctly for reports

**Testing approach once implemented:**

Use Pest 4 for all tests. Write unit tests for model logic (relationships, scopes, accessors). Write feature tests for database operations (create, read, update, delete). Use factories to generate test data efficiently. Test both anonymous and authenticated scenarios.

---

## Database Schema Details

### Foreign Key Strategy

All foreign keys are defined with proper cascading behavior: user_id foreign keys set to NULL on user deletion (preserve anonymous content), reportable foreign keys cascade delete (delete reports when content deleted), admin foreign keys set to NULL (preserve audit trail if admin deleted).

### Index Strategy

Unique indexes on hash and slug columns for fast lookups and constraint enforcement. Composite indexes on frequently joined columns. Full-text indexes on searchable content (note title and content). Hash indexes on duplicate detection columns (full_url_hash, content_hash). Consider adding covering indexes after analyzing production query patterns.

### Character Set and Collation

Use utf8mb4 character set and utf8mb4_unicode_ci collation to support emoji and international characters in notes and URLs. Ensure all text columns use this encoding.

### Data Types

Use appropriate data types: bigint unsigned for IDs and counters, varchar with explicit lengths for constrained text, text/longtext for unlimited content, timestamp for dates, boolean for flags, enum for fixed choices (where appropriate). Avoid nullable columns where possible - use defaults instead.

### Timestamps

All tables have created_at and updated_at timestamps (Laravel convention). Some tables have additional timestamps: last_visited_at, last_viewed_at, dealt_at, expires_at, email_verified_at, last_login_at. Use appropriate indexes on timestamp columns used for sorting or filtering.
