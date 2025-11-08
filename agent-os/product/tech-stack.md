# anon.to Technical Stack

This document outlines all technology choices for the anon.to platform, covering backend, frontend, infrastructure, and third-party services.

---

## Backend Framework & Runtime

### Laravel 12.37.0
**Why**: Modern PHP framework with excellent ecosystem and developer experience
- Latest stable Laravel version with streamlined file structure
- Robust routing, middleware, and request lifecycle
- Built-in authentication, authorization, and validation
- Active community and extensive documentation
- Proven at scale with millions of applications

### PHP 8.4.14
**Why**: Latest PHP version with performance and type safety improvements
- JIT compiler for improved performance
- Named arguments and constructor property promotion
- Improved type system with union types and nullsafe operator
- Better error handling and debugging
- Compatible with Laravel 12

### Composer 2.x
**Why**: Standard PHP dependency manager
- Fast parallel downloads
- Lock file for reproducible builds
- PSR-4 autoloading
- Scripts for automation

---

## Frontend Stack

### Livewire 3.6.4
**Why**: Build dynamic interfaces without writing JavaScript
- Full-stack framework for Laravel
- Real-time validation and form handling
- Wire models for reactive data binding
- Pagination, file uploads, and events built-in
- Reduces JavaScript complexity while maintaining interactivity

### Volt 1.9.0
**Why**: Single-file components for Livewire (functional and class-based)
- Blade templates with co-located PHP logic
- Reduces boilerplate compared to traditional Livewire components
- Supports both functional and class-based approaches
- Perfect for small to medium components
- Maintains full Livewire functionality

### Flux UI 2.6.1 (Free Edition)
**Why**: Pre-built UI component library specifically for Livewire
- Consistent design language out of the box
- Accessible components (WCAG compliant)
- Tailwind-based styling for easy customization
- Components: buttons, inputs, modals, tables, badges, etc.
- Free tier includes all essential components
- Reduces development time for common UI patterns

### Tailwind CSS 4.1.17
**Why**: Utility-first CSS framework for rapid UI development
- CSS-first configuration using `@theme` directive (v4 feature)
- No separate tailwind.config.js needed
- Faster build times than v3
- Dark mode support via `dark:` variants
- Mobile-first responsive design
- Tree-shaking removes unused styles in production

### Alpine.js (bundled with Livewire)
**Why**: Lightweight JavaScript for simple interactions
- Declarative syntax similar to Vue.js
- Perfect for dropdowns, modals, toggles
- Included with Livewire 3 (no separate install)
- Plugins: persist, intersect, collapse, focus

### ~~Prism.js~~ (Removed)
**Status**: Intentionally removed from notes feature
**Why Removed**: Syntax highlighting removed to keep the platform simple and privacy-focused
- Notes now display plain text only
- Reduces complexity and potential XSS vectors
- Faster rendering and smaller bundle size
- Aligns with minimalist, distraction-free design philosophy
- Simplifies content storage and processing

---

## Authentication & Authorization

### Laravel Fortify 1.31.2
**Why**: Headless authentication backend for Laravel
- Email/password authentication
- Email verification
- Two-factor authentication (TOTP)
- Password reset functionality
- Integrates seamlessly with Livewire
- No frontend opinions - full control over UI

### Laravel Sanctum
**Why**: API token authentication for mobile/SPA apps
- Simple token-based authentication
- Personal access tokens for API access
- CSRF protection for SPAs
- Built into Laravel, no complex OAuth setup
- Perfect for our REST API needs (Phase 10)

### Laravel Gates & Policies
**Why**: Authorization logic for access control
- Define permissions in code (no database overhead)
- Policy classes for model-based authorization (LinkPolicy, NotePolicy)
- Blade directives for UI (`@can`, `@cannot`)
- Middleware for route protection

---

## Database & Storage

### MySQL 8.0+
**Why**: Reliable relational database with strong ecosystem
- Proven at scale (242K+ links already stored)
- Full-text search support for note content
- JSON column support for flexible data
- Window functions for analytics
- Strong community and tooling
- Cost-effective for self-hosted deployments

**Alternative**: PostgreSQL 15+ (if advanced features needed later)

### Laravel Eloquent ORM
**Why**: Database abstraction built into Laravel
- Fluent query builder with type safety
- Model relationships (hasMany, belongsTo, polymorphic)
- Migrations for version-controlled schema
- Seeders and factories for testing
- Query logging and debugging

### Redis 7.x
**Why**: In-memory data store for caching and queues
- Sub-millisecond read/write performance
- Cache popular links (24-hour TTL)
- Cache popular notes (24-hour TTL)
- Session storage (database alternative)
- Queue driver for background jobs
- Pub/sub for real-time features (future)
- Data persistence for durability

---

## Testing & Quality

### Pest 4.1.3
**Why**: Modern PHP testing framework built on PHPUnit
- Beautiful, expressive syntax
- Browser testing with headless Chrome/Firefox
- Parallel test execution for speed
- Visual regression testing (future use)
- Architectural testing (enforce rules)
- Perfect for Laravel applications

### PHPUnit 12.4.1
**Why**: Underlying test runner for Pest
- Industry standard for PHP testing
- Code coverage reports
- Data providers and mocking
- Integration with CI/CD pipelines

### Laravel Pint 1.25.1
**Why**: Opinionated code formatter for PHP
- Zero-configuration Laravel code style
- Automatically fixes formatting issues
- Fast (uses PHP-CS-Fixer under the hood)
- CI/CD integration for enforcement
- Runs via `vendor/bin/pint`

---

## Development Tools & DevOps

### Laravel Sail 1.47.0
**Why**: Docker-based local development environment
- Pre-configured containers (PHP, MySQL, Redis)
- One-command setup (`sail up`)
- Consistent across team members
- Includes Mailhog for email testing
- Production parity for reliable deployments

### Vite 7.2.2
**Why**: Modern asset bundler with HMR (Hot Module Replacement)
- Fast build times compared to Webpack
- Hot reload for CSS/JS during development
- Tree-shaking for smaller production bundles
- Official Laravel integration
- PostCSS and autoprefixer built-in

### GitHub Actions
**Why**: CI/CD pipeline integrated with repository
- Free for public repositories
- YAML-based configuration
- Matrix builds (multiple PHP versions)
- Automated testing on every push
- Deployment automation
- Secret management for credentials

---

## Monitoring & Logging

### Production Logging: DISABLED
**Why**: Privacy-first architecture
- `LOG_CHANNEL=null` in production environment
- Prevents accidental logging of user data, URLs, or IPs
- No user-generated content ever appears in logs
- Development can use logging for debugging
- Critical security feature for privacy compliance

### Laravel Telescope (Development Only)
**Why**: Debug assistant for Laravel applications
- Request/response logging
- Query profiling (N+1 detection)
- Exception tracking
- Mail preview
- Scheduled job monitoring
- Only enabled in development/staging, NEVER in production

### Sentry (Production - Planned Phase 17)
**Why**: Error tracking and performance monitoring
- Real-time error alerts
- Stack traces with context
- Performance monitoring (APM)
- Release tracking
- Issue assignment and resolution
- Integrates with Laravel

### Laravel Horizon (Queue Monitoring - Planned)
**Why**: Dashboard for Redis queue workers
- Real-time queue metrics
- Job throughput and runtime
- Failed job management
- Worker configuration
- Beautiful UI built into Laravel

---

## Third-Party Services

### QR Code Generation: chillerlan/php-qrcode 5.0
**Why**: Generate QR codes for shortened links and any content
- Pure PHP implementation (no external API)
- PNG/SVG/PDF output formats
- Customizable size and quality
- No rate limits or costs
- Works offline/on-premise
- Privacy-first: No content sent to third parties
- Multi-format downloads (Phase 5.5 complete)

### PDF Generation: dompdf/dompdf 3.1
**Why**: Generate PDF files for QR code downloads
- Pure PHP implementation (no external dependencies)
- HTML/CSS to PDF conversion
- Supports embedding images (QR codes)
- No external API calls
- Privacy-preserving (all processing in-memory)

### Google Safe Browsing API (Optional - Phase 10)
**Why**: Check URLs for malware/phishing
- Free tier: 10K lookups per day
- Identifies malicious sites
- Update 4 protocol (latest version)
- Reduces manual moderation burden
- Privacy-friendly (hashed URL lookups)

### hCaptcha or Google reCAPTCHA v3 (Planned Phase 14)
**Why**: Bot protection for forms
- Invisible CAPTCHA (better UX)
- Only shown after suspicious activity
- Prevents spam report submissions
- Free tier sufficient for traffic
- GDPR compliant (hCaptcha better for EU)

### GeoIP2 (MaxMind) - Planned Phase 9
**Why**: Country-level geolocation for analytics
- Privacy-focused (country only, no city/IP tracking)
- Downloadable database (no API calls)
- Free GeoLite2-Country database
- Updates via Composer script
- Works offline

### Email Service: AWS SES / Mailgun / Postmark
**Why**: Reliable transactional email delivery
- Password resets, email verification
- Report notifications to admins
- High deliverability rates
- API and SMTP support
- Cost-effective for low volume

---

## Infrastructure & Hosting

### Web Server: Nginx
**Why**: High-performance HTTP server
- Handles static assets efficiently
- Reverse proxy for PHP-FPM
- SSL/TLS termination
- HTTP/2 support
- Gzip compression

### PHP-FPM
**Why**: FastCGI Process Manager for PHP
- Better performance than mod_php
- Process pooling for concurrency
- Resource limits per pool
- Graceful restarts

### Supervisor
**Why**: Process control system for queue workers
- Keeps queue workers running
- Auto-restart on failure
- Multiple workers for concurrency
- Log rotation
- Easy configuration

### Cloudflare (CDN & DDoS Protection)
**Why**: Performance and security layer
- Global CDN for static assets
- DDoS protection (free tier)
- SSL/TLS encryption
- Caching at edge locations
- CF-Connecting-IP header support (already implemented)
- Analytics and insights

---

## Configuration & Environment

### Environment Variables (.env)
**Why**: Secure configuration management
- Sensitive data kept out of version control
- Different configs per environment (dev/staging/prod)
- Laravel's `config()` helper for access
- Never use `env()` outside config files
- `LOG_CHANNEL=null` for production privacy

### Laravel Configuration
**Why**: Centralized, cached configuration
- Config files in `config/` directory
- Cache config for production (`php artisan config:cache`)
- Type-safe access via facades
- Custom configs: `config/anon.php` for app-specific settings

---

## Asset Management

### NPM
**Why**: JavaScript package manager
- Install frontend dependencies
- Scripts for build automation
- Lock file for reproducible builds
- Version pinning for stability

### Vite (Laravel Mix Replacement)
**Why**: Asset compilation pipeline
- Compile Tailwind CSS
- Bundle JavaScript modules
- Minify for production
- Versioning for cache busting
- Source maps for debugging
- Hot Module Replacement (HMR) for development

### Axios 1.13.2
**Why**: HTTP client for JavaScript
- Promise-based requests
- CSRF token handling
- Response interceptors
- Included with Laravel by default

### Concurrently 9.2
**Why**: Run multiple development servers in parallel
- Single command to start all services (server, queue, logs, vite)
- Color-coded output for each process
- Kill all processes together
- Used in `composer run dev` script

---

## Security Technologies

### HTTPS/SSL
**Why**: Encrypt all traffic
- Let's Encrypt for free certificates
- Automatic renewal via Certbot
- HSTS header for forced HTTPS
- No mixed content warnings

### Content Security Policy (CSP) - Planned Phase 14
**Why**: Prevent XSS attacks
- Whitelist allowed resources
- Block inline scripts (except trusted)
- Reduce attack surface
- Reporting endpoint for violations

### CSRF Protection
**Why**: Prevent cross-site request forgery
- Laravel's built-in VerifyCsrfToken middleware
- Token included in all forms
- Validated on every POST/PUT/DELETE

### Bcrypt/Argon2
**Why**: Secure password hashing
- One-way hashing (irreversible)
- Salt automatically generated
- Resistant to rainbow table attacks
- Configurable work factor

### SHA256 IP Address Hashing
**Why**: Privacy-compliant rate limiting
- IP addresses hashed before storage
- Prevents storing raw IPs in database
- Enables rate limiting without privacy violation
- Used throughout the application for anonymous users

---

## Current Implementation Status

### ✅ Fully Operational Technologies
These are actively used in the current implementation:

**Backend**: Laravel 12, PHP 8.4, Eloquent ORM, Redis caching
**Frontend**: Livewire 3, Volt 1, Flux UI 2 (free), Tailwind CSS 4, Alpine.js
**Authentication**: Fortify 1 (email verification, 2FA, password reset)
**Testing**: Pest 4 (212+ tests), PHPUnit 12, Pint 1
**Development**: Sail 1, Vite 7, GitHub (version control), Concurrently 9
**Security**: Bcrypt password hashing, SHA256 IP hashing, CSRF protection, SSRF prevention
**Privacy**: Production logging disabled (`LOG_CHANNEL=null`)
**QR Codes**: chillerlan/php-qrcode 5.0 (PNG, SVG, PDF generation)
**PDF Generation**: dompdf/dompdf 3.1

### 📦 Installed But Not Yet Utilized
These are installed/configured but not actively used:

**Laravel Sanctum**: Installed but no API endpoints created yet (planned Phase 10)
**Queue System**: Redis queue driver configured but no jobs dispatched yet
**GeoIP2**: Database setup but analytics not recording detailed data (planned Phase 9)
**Laravel Horizon**: Not installed (queue monitoring for when queues are used)
**Laravel Telescope**: Not installed (for development debugging)

### 🚧 Planned Technologies
These will be added in future phases:

**Chart.js/ApexCharts**: For Phase 9 (analytics dashboard)
**hCaptcha/reCAPTCHA**: For Phase 14 (security hardening)
**Safe Browsing API**: For Phase 10 (malware URL detection)
**Sentry**: For Phase 17 (production error tracking)
**Supervisor**: For Phase 17 (production queue workers)

---

## Database Schema Status

### Active Tables (In Use)
- ✅ **users** - User accounts with authentication
- ✅ **links** - Shortened URLs with metadata
- ✅ **notes** - Ephemeral text notes with password protection
- ✅ **sessions** - User session data
- ✅ **personal_access_tokens** - Sanctum tokens (ready for Phase 10)
- ✅ **cache** - Redis cache entries
- ✅ **jobs** - Queue jobs table

### Schema Ready (Not Yet Used)
- 📦 **reports** - Polymorphic abuse reports (Phase 8)
- 📦 **allow_lists** - Domain filtering rules (Phase 11)
- 📦 **link_analytics** - Detailed visit tracking (Phase 9)

---

## Why This Stack?

### 1. Modern & Maintainable
All technologies are actively maintained with long-term support commitments. Laravel 12 and PHP 8.4 represent the latest stable releases.

### 2. Privacy-First
Every technology choice supports our privacy mission:
- No tracking pixels or analytics SDKs
- Self-hosted components where possible (QR codes, PDF generation - no external APIs)
- Minimal third-party dependencies
- Data sovereignty (we control the stack)
- Production logging completely disabled

### 3. Performance-Optimized
Redis caching, HTTP/2, CDN integration, and compiled assets ensure sub-second page loads even at scale.

### 4. Developer Experience
Laravel's ecosystem provides excellent tooling (Sail, Telescope, Pint) that reduces friction and increases productivity.

### 5. Proven at Scale
This isn't experimental tech - every component has been battle-tested in production across thousands of applications.

### 6. Cost-Effective
Open-source foundation with generous free tiers for third-party services. Self-hostable without vendor lock-in.

### 7. Future-Proof
Modern syntax (PHP 8.4, Livewire 3, Tailwind 4) with clear upgrade paths as technologies evolve.

---

## Alignment with User Standards

Based on the user's tech stack preferences documented in `/Users/abi/.claude/CLAUDE.md` and project CLAUDE.md, anon.to aligns perfectly with standard choices:

- ✅ Laravel 12 (framework)
- ✅ PHP 8.4 (latest version policy)
- ✅ Livewire 3 + Volt (frontend)
- ✅ Tailwind CSS 4 (styling)
- ✅ Pest 4 (testing)
- ✅ Redis (caching/queues)
- ✅ MySQL (database)
- ✅ Flux UI (component library)
- ✅ Fortify (authentication)

**No major deviations** - this project follows best practices established in the user's standards documentation and always installs latest package versions without constraints.

---

## Technology Changelog

### Recent Changes (November 2025)

**Added:**
- ✅ QR Code Generator (chillerlan/php-qrcode 5.0) - Phase 5.5 complete
- ✅ PDF Generation (dompdf/dompdf 3.1) for QR code downloads
- ✅ Multi-format downloads (PNG, SVG, PDF)
- ✅ SHA256 IP hashing throughout application
- ✅ Concurrently 9.2 for parallel development servers

**Removed:**
- ❌ Prism.js syntax highlighting (intentionally removed for simplicity)
- ❌ Custom slug support for links (simplified to hash-only)

### Laravel 5.4 → Laravel 12 (Current Rebuild)

**What Changed**:
- File structure simplified (no `app/Console/Kernel.php`)
- Middleware registered in `bootstrap/app.php`
- Commands auto-discover from `app/Console/Commands/`
- Modern Blade components and slots
- Database query improvements (native eager loading limits)

**Why**: Laravel 5.4 is no longer supported. Laravel 12 provides better DX, performance, and security.

### Bootstrap 3 + jQuery → Tailwind 4 + Alpine.js

**What Changed**:
- Utility-first CSS instead of component classes
- Declarative JavaScript instead of jQuery DOM manipulation
- Smaller bundle sizes (no jQuery dependency)
- Better dark mode support

**Why**: Modern stack, faster development, better performance, easier maintenance.

### Laravel Collective (Forms) → Livewire Forms

**What Changed**:
- Wire models replace form helpers
- Validation moved to Livewire components
- Real-time validation without page refresh

**Why**: Laravel Collective is deprecated. Livewire provides better UX and simpler code.

---

## Privacy-First Technology Decisions

### Production Logging: Disabled
- `LOG_CHANNEL=null` prevents accidental user data logging
- No URLs, IPs, or user content ever written to logs
- Development can use logging, production cannot
- Critical architectural decision for privacy compliance

### IP Address Hashing
- All IPs hashed with SHA256 before storage
- Rate limiting works without storing raw IPs
- Analytics (future) use hashed IPs only
- No personally identifiable information stored

### QR Code Generation
- Pure PHP implementation (no external API calls)
- User content never sent to third parties
- Generated in-memory and streamed to user
- No storage of QR code content or history

### PDF Generation
- Pure PHP implementation via dompdf
- All processing happens server-side in-memory
- No external API calls or third-party services
- User content never leaves the server

### Notes Storage
- Plain text only (no syntax highlighting to reduce XSS risk)
- Content encrypted when password-protected
- Burn-after-reading deletes immediately
- Ephemeral by design with automatic expiration

---

**Version**: 3.1
**Last Updated**: 2025-11-08
**Status**: Reflects Phase 1-5.5 implementation (Core + Notes + QR Codes)
**Key Changes**:
- Updated Tailwind CSS version to 4.1.17 (was 4.1.11)
- Updated Vite version to 7.2.2 (was 7.2)
- Added Axios 1.13.2 documentation
- Added Concurrently 9.2 documentation
- Added dompdf/dompdf 3.1 documentation
- Enhanced privacy-first technology decisions section
- Updated implementation status to reflect actual package versions
**Next Review**: When adding new major dependencies
