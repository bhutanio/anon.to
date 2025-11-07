# Phase 0 - Implementation Tasks

**Git Branch:** `feature/001-phase-0`

## Phase 1: Setup & Planning
- [x] Create git branch `feature/001-phase-0`
- [x] Review tech.md database schema section
- [x] Verify MySQL connection works (test with `php artisan db:show`)
- [x] Ensure Redis is installed and running (test with `redis-cli ping`)
- [x] Document any questions or concerns about schema design

## Phase 2: Build - Backend

### Database Migrations
- [x] Create migration: create_links_table
- [x] Create migration: create_notes_table
- [x] Create migration: create_reports_table
- [x] Create migration: create_allow_lists_table
- [x] Create migration: create_link_analytics_table
- [x] Run migrations to verify structure
- [x] Test migration rollback works

### Eloquent Models
- [x] Create Link model with relationships and casts
- [x] Create Note model with relationships and casts
- [x] Create Report model with polymorphic relationships
- [x] Create AllowList model with relationships
- [x] Create LinkAnalytic model with relationships
- [x] Verify all model relationships work (test in tinker)

### Factories
- [x] Create LinkFactory with realistic test data
- [x] Create NoteFactory with realistic test data
- [x] Create ReportFactory with realistic test data
- [x] Test factory generation (generate 10 of each, verify valid)

### Seeders
- [x] Create AllowListSeeder with initial blocked domains
- [x] Create AdminUserSeeder with secure admin account
- [x] Run seeders and verify data created
- [x] Test seeders are idempotent (can run multiple times)

### Configuration
- [x] Configure Redis in config/cache.php
- [x] Configure Redis in config/queue.php
- [x] Set CACHE_STORE=redis in .env
- [x] Set QUEUE_CONNECTION=redis in .env
- [x] Verify Redis connection works

## Phase 3: Validate & Ship

### Manual Testing
- [x] Test happy path: Create user, login works
- [x] Test models: Use tinker to create records with relationships
- [x] Test factories: Generate test data for each model
- [x] Test seeders: Fresh database can be seeded successfully
- [x] Test cache: Redis caching works (check redis-cli)

### Code Quality
- [x] Run `vendor/bin/pint` to format all code
- [x] Review migration files for correctness
- [x] Review model relationships for completeness
- [x] Add PHPDoc blocks to models where helpful
- [x] Remove any debug code or comments

### Review & Deploy
- [x] Self-review all migrations (column types, indexes, foreign keys)
- [x] Verify no sensitive data in migrations (passwords, keys, etc.)
- [ ] Create pull request with description of Phase 0 work
- [ ] Merge to main/master branch
- [ ] Run migrations on development database
- [ ] Verify authentication still works via Fortify
- [ ] Confirm ready to start Phase 1 (Core URL Shortener)

---

## Notes

**Dependencies:**
- MySQL 8.0+ must be running
- Redis 7.x must be running
- Existing Laravel 12 + Fortify setup (already done)

**Risks:**
- Schema changes after migration to production are costly
- Missing indexes discovered under load
- Foreign key constraints that limit flexibility
- Nullable vs non-nullable column decisions

**Decisions Made:**
- Using MySQL as primary database (not PostgreSQL)
- Storing IP addresses hashed for privacy (SHA256)
- Using polymorphic relationships for reports table
- Separating analytics into dedicated table for performance
- Supporting both anonymous and authenticated content (nullable user_id)
- Custom slugs only for registered users (enforced at application layer)

**Quick Reference:**

Table count: 6 new tables (links, notes, reports, allow_lists, link_analytics + enhanced users)
Model count: 5 new models (Link, Note, Report, AllowList, LinkAnalytic)
Factory count: 3 factories (Link, Note, Report)
Seeder count: 2 seeders (AllowList, AdminUser)

**After Phase 0:**

You'll be ready to start Phase 1 (Core URL Shortener) which builds the link creation logic, hash generation, URL validation, duplicate detection, and redirect handling on top of this database foundation.
