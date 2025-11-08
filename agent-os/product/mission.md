# anon.to Product Mission

## Vision

**anon.to** is a privacy-first URL anonymization platform that empowers users to share links safely without sacrificing their digital privacy.

We believe privacy should be the default, not a premium feature. Since our original launch on Laravel 5.4, we've served over 242,000 links to 25,000+ users who value anonymity, speed, and simplicity. Now rebuilt on modern technology (Laravel 12 + Livewire 3), we're positioned to serve the next generation of privacy-conscious internet users.

## Purpose

In an era of pervasive tracking and data harvesting, anon.to provides essential privacy tools:

1. **Anonymous URL Shortening**: Create short links without registration, tracking, or data collection
2. **Privacy-Aware Redirects**: Strip referrer headers and provide intermediate warning pages before redirecting users to their destination
3. **Ephemeral Note Sharing**: Share text notes with password protection, burn-after-reading, and expiration
4. **QR Code Generation**: Generate scannable QR codes for any content (links, text, contact info) with multi-format downloads
5. **User Authentication** _(optional)_: Higher rate limits and link management for power users

## Core Values

### Privacy First
- **No registration required** - Create links and notes instantly without an account
- **No tracking** - We don't log clicks, store IPs permanently, or sell user data
- **Anonymous by default** - All content is created anonymously unless you choose to register
- **Transparent redirects** - Users see exactly where they're going before being redirected
- **Hashed IPs** - When IP tracking is necessary (rate limiting), we use SHA256 hashing
- **Production logging disabled** - `LOG_CHANNEL=null` ensures no user data appears in logs

### Speed & Simplicity
- **Sub-second redirects** - Redis caching ensures lightning-fast link resolution (< 100ms)
- **Minimal UI** - Clean, distraction-free interface focused on core functionality
- **One-click creation** - Paste a URL, get a short link - that's it
- **Dark mode support** - Beautiful interface that respects user preferences

### Trust & Safety
- **Rate limiting** - 20 requests/hour anonymous, 100/hour authenticated
- **SSRF protection** - Blocks internal IPs and private networks
- **SSL-secured** - All traffic encrypted end-to-end
- **Warning pages** - Users see URL components before visiting unknown links
- **Profanity filter** - 196 words excluded from short URL generation

## Target Users

### Primary: Privacy-Conscious Individuals
**Who they are**: Tech-savvy users who value digital privacy and anonymity

**Pain points**:
- Traditional URL shorteners track clicks and sell user data
- Can't share links without creating permanent tracking records
- Need to strip referrer headers when sharing links
- Want simple, fast tools without feature bloat

**How we help**: Anonymous link creation, no-tracking redirects, transparent privacy policy, open-source codebase for auditability

### Secondary: Developers & Technical Teams
**Who they are**: Software engineers, DevOps professionals, security researchers

**Pain points**:
- Need to share temporary links during development
- Want API access for automation
- Require reliable uptime for production integrations
- Need self-hosted options for enterprise deployments

**How we help**: REST API (coming soon), high rate limits for verified users, open-source for self-hosting, two-factor authentication for security

### Tertiary: Organizations & Enterprises
**Who they are**: Companies needing anonymous feedback channels or secure link sharing

**Pain points**:
- Need anonymous reporting mechanisms
- Require secure sharing of temporary access links
- Want control over link expiration and access
- Need audit trails and admin moderation tools

**How we help**: User management (coming soon), link expiration, admin panel (coming soon), detailed analytics (coming soon)

## Problem We Solve

### The Core Problem: Privacy Invasion Through URL Shorteners

Traditional URL shorteners like bit.ly and tinyurl.com:
- **Track every click** with unique identifiers and cookies
- **Sell analytics data** to advertisers and third parties
- **Expose referrer information** revealing where users came from
- **Require registration** for basic features, collecting personal data
- **Retain data indefinitely** creating permanent digital footprints

**Our Solution**:
Anonymous-first design with no click tracking, optional registration only for power users, intermediate redirect pages that strip referrer headers, and transparent data handling with production logging completely disabled.

## Key Differentiators

### 1. Anonymous by Default
**Unlike**: bit.ly, tinyurl.com (require accounts for history)
**We provide**: Full functionality with zero registration
**Benefit**: No personal data collected, no login barriers, instant privacy

### 2. Privacy-Aware Redirects
**Unlike**: Direct 301 redirects that leak referrer data
**We provide**: Intermediate warning page showing URL components
**Benefit**: Users see where they're going, referrer headers stripped, informed consent before visiting

### 3. Zero Logging in Production
**Unlike**: Most shorteners that log everything
**We provide**: `LOG_CHANNEL=null` - no user data, URLs, or IPs ever logged
**Benefit**: Your links and content never appear in our logs, even by accident

### 4. Proven Track Record
**Unlike**: New privacy tools with unknown longevity
**We provide**: 242,000+ links served since Laravel 5.4 era, 25,000+ users
**Benefit**: Established service with years of reliable operation

### 5. Open-Source Foundation
**Unlike**: Proprietary closed-source shorteners
**We provide**: Modern Laravel 12 stack with transparent code on GitHub
**Benefit**: Community auditable, self-hostable, trustworthy by design

### 6. Modern Technology
**Unlike**: Legacy shorteners on outdated tech
**We provide**: Laravel 12 + Livewire 3 + Volt + Flux UI + Tailwind 4
**Benefit**: Fast, secure, maintainable, excellent developer experience

### 7. Performance-Optimized
**Unlike**: Slow redirects with multiple server hops
**We provide**: Redis caching with sub-100ms redirect times
**Benefit**: Fast user experience without sacrificing privacy

## Current Implementation Status

### ✅ Fully Operational (Phase 1-5 Complete)
- **Anonymous URL Shortening**: Create links without account, 6-character hashes, profanity filter
- **Redirect Warning Pages**: URL component breakdown, security indicators, visit counters
- **Rate Limiting**: IP-based (20/hr) and user-based (100/hr) with Redis caching
- **User Authentication**: Registration, login, email verification, password reset, two-factor auth
- **Security**: SSRF protection, CSRF tokens, bcrypt hashing, IP address hashing (SHA256)
- **Settings Management**: Profile, password, 2FA setup, appearance preferences
- **Link Features**: Duplicate detection (SHA256 URL hash), expiration dates, active/inactive states
- **Notes/Pastebin**: Plain text notes with password protection, burn-after-reading, expiration
- **QR Code Generator**: Generate QR codes for any content with PNG, SVG, and PDF download formats
- **Caching**: 24-hour Redis cache for popular links and notes
- **Dark Mode**: System-wide dark theme support
- **Mobile Responsive**: Works beautifully on all screen sizes
- **Test Coverage**: 212+ comprehensive Pest tests passing

### 🚧 In Development (Database Schema Ready)
- **Detailed Analytics**: Geographic data, referrer tracking, visit charts
- **Reporting System**: User-submitted abuse reports with admin moderation
- **Admin Panel**: User management, content moderation, allow/block lists
- **REST API**: Public and authenticated endpoints with Sanctum tokens
- **User Dashboard**: Link management, search, bulk operations

## Success Criteria

### User Success
- ✅ Users can create their first link in under 10 seconds
- ✅ Zero learning curve for basic functionality
- ✅ Mobile-responsive interface works on all devices
- 🚧 Accessibility compliant (WCAG 2.1 AA) - in progress

### Privacy Success
- ✅ Zero personal data breaches to date
- ✅ No click tracking or analytics sold to third parties
- ✅ Production logging completely disabled
- ✅ Transparent privacy policy users can actually understand
- 🎯 < 1% abuse rate (target for when reporting is implemented)

### Technical Success
- ✅ < 500ms page load times (95th percentile achieved)
- ✅ > 80% cache hit rate on popular links (Redis implementation)
- ✅ Zero critical security vulnerabilities (212+ comprehensive Pest tests)
- 🎯 99.9% uptime (target for production launch)

### Business Success
- 🎯 Successfully migrate 80%+ of 242K legacy links
- 🎯 25% conversion rate from anonymous to registered users
- 🎯 Daily active users exceed 1,000
- 🎯 Average 5+ links per registered user

## Product Principles

### 1. Privacy by Design
Privacy isn't a feature - it's the foundation. Every design decision prioritizes user anonymity and data minimization. We hash IPs with SHA256, strip referrer headers, never track clicks for analytics, and disable all production logging via `LOG_CHANNEL=null`.

### 2. Simplicity Over Feature Bloat
We resist the urge to add features that compromise our core mission. Better to do URL shortening excellently than ten things poorly. Our homepage has one input field, one button - that's intentional.

### 3. Transparency & Trust
Our code is open-source (GitHub), our privacy policy is clear, and our data handling is documented. Users should never wonder "what are they doing with my data?" Answer: Nothing. We're not tracking you.

### 4. Performance Matters
Privacy tools must be fast. Slow experiences drive users back to convenient-but-invasive alternatives. We achieve sub-100ms redirects through Redis caching and optimized database queries.

### 5. Accessibility for All
Privacy is a human right, not a premium feature. Our service is free and accessible to everyone regardless of technical skill or ability. Flux UI components ensure WCAG compliance.

## What We Are NOT

- **Not a competitor to Google Analytics** - We don't provide detailed click analytics or user profiling
- **Not a permanent link repository** - Links can expire, and we don't guarantee eternal storage (by design)
- **Not a content hosting platform** - We don't host files, images, or videos - only text URLs and notes
- **Not a social network** - No profiles, followers, likes, or comments
- **Not a monetization-first business** - Privacy is our product, not user data

## Key Features

### Core Features
- **Anonymous Link Creation**: No account required, instant short links with profanity-filtered hashes
- **Privacy-First Redirects**: Warning page shows URL components, strips referrer headers, no tracking
- **Ephemeral Notes**: Share text with password protection, burn-after-reading, and automatic expiration
- **QR Code Generation**: Create scannable QR codes for links, text, or any content with PNG/SVG/PDF downloads

### User Features
- **Optional Authentication**: Register for higher rate limits (100/hr vs 20/hr) and link management
- **Dashboard**: View and manage all your links and notes in one place
- **Link Expiration**: Set automatic expiration for temporary sharing
- **Password Protection**: Secure notes with bcrypt-hashed passwords

### Security Features
- **SSRF Protection**: Blocks internal IPs, localhost, and private networks
- **Rate Limiting**: IP-based and user-based with Redis backing
- **Two-Factor Authentication**: TOTP support for registered users
- **Zero Logging**: Production logging disabled to prevent accidental data leaks

### Advanced Features
- **Redis Caching**: 24-hour TTL for popular content, sub-100ms response times
- **Duplicate Detection**: SHA256 hashing prevents duplicate URLs
- **Visit Tracking**: Privacy-friendly visit counters (no user identification)
- **Dark Mode**: Beautiful UI that respects system preferences

## The Road Ahead

### Near-Term (6 months)
- ✅ Complete core URL shortening with authentication
- ✅ Implement notes/pastebin functionality
- ✅ Implement QR code generator
- 🚧 Build user dashboard for link management
- 🚧 Build admin moderation tools
- 🚧 Migrate all 242K legacy links with zero downtime

### Mid-Term (12 months)
- Build REST API with Sanctum authentication
- Add privacy-focused analytics (country-level only, hashed IPs)
- Launch browser extensions for one-click shortening
- Achieve 99.9% uptime reliability

### Long-Term (24+ months)
- Mobile apps (iOS/Android) for on-the-go sharing
- Self-hosted deployment guides for enterprises
- Advanced features: custom domains, team accounts, webhooks
- Partnership channels with privacy-focused organizations

## Measuring Success

We define success not by revenue or user growth alone, but by:

1. **Privacy Impact**: Number of users protected from tracking (current: 25,000+)
2. **Trust Score**: Percentage of users who recommend us (target: 70%+)
3. **Reliability**: Uptime and performance metrics (target: 99.9% uptime, < 500ms p95)
4. **Code Quality**: Test coverage and security audits (current: 212+ comprehensive tests)
5. **Sustainability**: Ability to operate without compromising privacy values

---

**Version**: 3.0
**Last Updated**: 2025-11-08
**Status**: Phase 1-5 Complete (Core + Notes + QR Codes), Active Development
**Key Change**: Added QR code generator feature, updated to reflect syntax highlighting removal and current implementation status
