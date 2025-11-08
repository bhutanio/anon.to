# Specification: Improve Link Creation and Anonymous Redirect

## Goal
Enhance the anon.to URL shortener with improved navigation, domain trust management for anonymous users, and direct URL anonymization without database persistence, while maintaining the privacy-first approach.

## User Stories
- As an anonymous user, I want to skip redirect warnings for domains I trust so that I can quickly access frequently visited sites
- As any user, I want to anonymize a URL on-the-fly using `/?url=...` so that I can share a de-referer link without creating a permanent short link
- As any user, I want clear navigation with theme switching and future feature visibility so that I understand what's available now and coming soon

## Specific Requirements

**Enhanced Navigation Bar**
- Left side: Home link, QR Code placeholder (non-functional, visible to all), Notes placeholder (non-functional, visible to all)
- Right side: Light/Dark/System theme switcher (visible to all), Authentication links (Login/Register for guests, Dashboard/Logout for authenticated users)
- Placeholders should display a tooltip or disabled state indicating "Coming Soon"
- Navigation must be responsive and work on mobile devices
- Theme switcher uses Alpine.js and localStorage (via `$flux.appearance` as shown in existing appearance settings)
- Replace existing simple header on homepage with new navigation component
- Navigation component should be reusable across guest and authenticated layouts

**Domain Trust Feature for Redirect Warning**
- Add checkbox on redirect warning page: "Don't warn me about [domain] in the future"
- Store trusted domains in localStorage under key `anon_trusted_domains` as JSON array
- Exact domain matching only: `example.com` does NOT trust `blog.example.com`
- Check localStorage before displaying warning page; if domain is trusted, redirect immediately
- No server-side storage or database changes required
- No management UI; users clear trusted domains via browser settings
- Display domain name dynamically in checkbox label (e.g., "Don't warn me about example.com in the future")
- Checkbox state persists only in localStorage, never sent to server

**Direct URL Anonymization (`/?url=...`)**
- Accept URL parameter on homepage route: `anon.to/?https://example.com`
- Display redirect warning page without creating database record
- Show original URL in warning page (not anon.to reference)
- No visual distinction from saved links on warning page
- No rate limiting applied to this endpoint
- No option to "Save as permanent link"
- Validate URL format and check for SSRF (same validation as CreateLink action)
- Pass URL components directly to view without Link model
- Support both `/?url=https://example.com` and `/?https://example.com` formats
- If URL parameter exists, skip normal link creation form display

**Redirect Warning Page Behavior**
- Keep existing metadata display (visit count, creation date) for saved links
- For direct anonymization, show warning page without visit count or creation date
- Display "Continue to Site" button (no auto-redirect timer)
- Check localStorage for trusted domains before rendering warning
- If domain is trusted, redirect immediately without showing warning
- Maintain all existing URL component parsing and display logic
- No enhanced metadata (favicon, title, description) beyond current implementation

**Homepage Link Creation Form**
- Keep simple: URL input field and submit button only
- No real-time validation feedback
- No advanced options (expiration, password, max uses)
- Validation only on submit (existing behavior)
- Error messages display below input field (existing behavior)
- Success state shows shortened URL with copy button (existing behavior)

## Visual Design
No visual mockups provided. Follow existing design patterns from `home.blade.php` and `redirect.blade.php`.

## Existing Code to Leverage

**`resources/views/livewire/home.blade.php` - Homepage Layout**
- Reuse header structure and convert to shared navigation component
- Keep existing form validation and error display patterns
- Maintain Alpine.js usage for interactive elements (copy button, loading states)
- Follow gradient background pattern: `bg-gradient-to-br from-indigo-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-900 dark:to-indigo-950`

**`app/Actions/Links/CreateLink.php` - URL Validation Logic**
- Reuse `ValidateUrl` action for direct anonymization validation
- Reuse `UrlService->parse()` for URL component extraction
- Follow same SSRF protection pattern for direct anonymization
- Do not call `CheckDuplicate` or `GenerateHash` for direct anonymization

**`resources/views/livewire/redirect.blade.php` - Warning Page Structure**
- Reuse URL component display layout for direct anonymization
- Maintain existing security indicator (HTTPS badge, HTTP warning)
- Keep protocol/domain/port/path/query/fragment breakdown format
- Adapt visit count and creation date display to show only for saved links

**`resources/views/livewire/settings/appearance.blade.php` - Theme Switcher Pattern**
- Use same Flux UI radio group pattern: `<flux:radio.group x-data variant="segmented" x-model="$flux.appearance">`
- Leverage built-in `$flux.appearance` functionality (values: light, dark, system)
- No custom JavaScript needed; Flux handles localStorage and theme application

**`app/Services/UrlService.php` - URL Parsing and Reconstruction**
- Use `parse()` method for direct anonymization URL decomposition
- Use `reconstruct()` method for building destination URL from components
- Leverage `isInternalUrl()` for SSRF protection on direct anonymization

## Out of Scope
- QR code generation feature (placeholder only)
- Notes feature (placeholder only)
- Analytics or tracking for links
- Link expiration features
- Password protection for links
- Custom slug generation
- Link editing functionality
- Real-time URL validation feedback
- Advanced link creation options on homepage form
- Auto-redirect timer on warning page
- Domain trust management UI for authenticated users
- Server-side storage of trusted domains
- Rate limiting on direct anonymization endpoint
- "Save as permanent link" option on direct anonymization warning
- Bookmarklet for direct anonymization
- Enhanced metadata display (favicon, title, description) on warning page
- Subdomain wildcard matching for domain trust (only exact matches)
- Option to trust domain from first visit (must be manual checkbox)
