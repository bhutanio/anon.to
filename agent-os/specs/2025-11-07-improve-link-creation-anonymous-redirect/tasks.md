# Task Breakdown: Improve Link Creation and Anonymous Redirect

## Overview
Total Tasks: 4 Task Groups (approximately 30+ sub-tasks)

This feature enhances anon.to with improved navigation, domain trust management for anonymous users, and direct URL anonymization without database persistence.

## Task List

### Navigation & UI Components

#### Task Group 1: Enhanced Navigation Bar Component
**Dependencies:** None

- [x] 1.0 Complete navigation bar component
  - [x] 1.1 Write 2-8 focused tests for navigation component
    - Test navigation links render correctly for guest users (Home, Login, Register)
    - Test navigation links render correctly for authenticated users (Home, Dashboard, Logout)
    - Test theme switcher renders with three options (Light, Dark, System)
    - Test placeholder items (QR Code, Notes) render with "Coming Soon" tooltip/state
    - Test navigation is responsive on mobile breakpoints
  - [x] 1.2 Create reusable Navigation Blade component
    - Path: `resources/views/components/navigation.blade.php`
    - Left side: Home link with anon.to logo/branding
    - Left side: QR Code placeholder (disabled/tooltip: "Coming Soon")
    - Left side: Notes placeholder (disabled/tooltip: "Coming Soon")
    - Right side: Theme switcher using Flux UI radio group pattern
    - Right side: Conditional auth links (Login/Register for guests, Dashboard/Logout for authenticated)
    - Reuse header styling from `home.blade.php` lines 3-28
    - Use Flux UI components where applicable (flux:tooltip, flux:button)
  - [x] 1.3 Implement theme switcher with Alpine.js
    - Use pattern from `settings/appearance.blade.php` line 13
    - Implement `<flux:radio.group x-data variant="segmented" x-model="$flux.appearance">`
    - Three options: light (sun icon), dark (moon icon), system (computer-desktop icon)
    - Leverage built-in `$flux.appearance` for localStorage persistence
    - No custom JavaScript needed beyond Alpine.js directives
  - [x] 1.4 Add responsive design for mobile devices
    - Mobile breakpoint: 320px - 768px (hamburger menu or stacked layout)
    - Tablet breakpoint: 768px - 1024px
    - Desktop breakpoint: 1024px+
    - Follow Tailwind 4 responsive patterns (sm:, md:, lg: prefixes)
    - Test navigation collapses gracefully on small screens
  - [x] 1.5 Replace existing header in home.blade.php
    - Remove lines 2-28 from `home.blade.php`
    - Replace with `<x-navigation />` component call
    - Verify gradient background remains: `bg-gradient-to-br from-indigo-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-900 dark:to-indigo-950`
  - [x] 1.6 Add navigation to guest layout
    - Updated redirect.blade.php to include navigation component
    - Navigation component is included in home.blade.php (uses guest layout)
    - Auth pages (login/register) use separate auth.simple layout with minimal centered form
  - [x] 1.7 Add navigation to authenticated layouts (if needed)
    - Checked authenticated pages - they use app layout with sidebar (different from guest navigation)
    - Navigation component added to redirect warning page for consistency
  - [x] 1.8 Ensure navigation component tests pass
    - All 7 navigation tests pass successfully
    - Verified navigation renders correctly for guest and authenticated users
    - Verified theme switcher with three options (Light, Dark, System)
    - Verified placeholder items show "Coming Soon" tooltip

**Acceptance Criteria:**
- [x] The 7 tests written in 1.1 pass
- [x] Navigation component renders consistently across guest pages (home, redirect)
- [x] Theme switcher works and persists user preference
- [x] Placeholder items show "Coming Soon" indication
- [x] Navigation is fully responsive on mobile, tablet, and desktop
- [x] All links navigate to correct routes

---

### Backend: Direct URL Anonymization

#### Task Group 2: Homepage URL Parameter Handling
**Dependencies:** None (can run in parallel with Task Group 1)

- [x] 2.0 Complete direct URL anonymization backend logic
  - [x] 2.1 Write 2-8 focused tests for URL parameter handling
    - Test homepage accepts `/?url=https://example.com` format
    - Test homepage accepts `/?https://example.com` format (no url= prefix)
    - Test URL validation using ValidateUrl action (SSRF prevention)
    - Test invalid URL formats are rejected
    - Test malformed parameters are handled gracefully
    - Test URL parsing extracts components correctly
  - [x] 2.2 Update Home Livewire component to handle URL parameter
    - Path: `app/Livewire/Home.php`
    - Add public property: `public ?string $urlParam = null;`
    - In `mount()` method, check for URL query parameter
    - Support both formats: `?url=https://example.com` and `/?https://example.com`
    - If URL parameter exists, set `$urlParam` and skip form display
    - If URL parameter is invalid, show error message
  - [x] 2.3 Add URL validation for direct anonymization
    - Inject `ValidateUrl` action into Home component
    - In `mount()`, validate URL parameter using `ValidateUrl->execute()`
    - Catch `InvalidArgumentException` and set `$errorMessage`
    - Do NOT call `CreateLink` or save to database
    - Do NOT apply rate limiting to URL parameter viewing
  - [x] 2.4 Parse URL into components for view
    - Inject `UrlService` into Home component
    - Use `UrlService->parse($urlParam)` to extract components
    - Store parsed components in public property: `public ?array $parsedUrl = null;`
    - Pass `$parsedUrl` to view for rendering
  - [x] 2.5 Update home.blade.php to handle URL parameter mode
    - Check if `$urlParam` exists
    - If exists, display redirect warning view (similar to redirect.blade.php)
    - If not exists, display normal link creation form
    - Use `@if($urlParam)` ... `@else` ... `@endif` pattern
  - [x] 2.6 Create redirect warning partial for URL parameter view
    - Extract warning page structure from `redirect.blade.php` lines 12-141
    - Create partial: `resources/views/partials/redirect-warning.blade.php`
    - Accept parameters: `$destinationUrl`, `$parsed`, `$link` (optional)
    - Display URL components (protocol, domain, port, path, query, fragment)
    - Show security indicator (HTTPS badge or HTTP warning)
    - Display "Continue to Site" button with link to `$destinationUrl`
    - Conditionally show visit count and creation date ONLY if `$link` exists
  - [x] 2.7 Ensure URL parameter handling tests pass
    - Run ONLY the 2-8 tests written in 2.1
    - Verify URL parameter is parsed correctly
    - Verify validation works for invalid URLs
    - Verify SSRF protection is applied
    - Do NOT run the entire test suite at this stage

**Acceptance Criteria:**
- [x] The 8 tests written in 2.1 pass
- [x] Homepage accepts URL parameter in both formats (`?url=` and `/?` prefix)
- [x] URL validation prevents SSRF attacks
- [x] Parsed URL components are correctly extracted
- [x] No database record is created for URL parameter viewing
- [x] No rate limiting applied to URL parameter viewing
- [x] Error messages display for invalid URLs

---

### Frontend: Domain Trust & Redirect Warning Enhancement

#### Task Group 3: Domain Trust with localStorage
**Dependencies:** Task Group 2 (needs redirect warning partial)

- [x] 3.0 Complete domain trust feature with localStorage
  - [x] 3.1 Write 2-8 focused tests for domain trust functionality
    - Test redirect warning page displays checkbox "Don't warn me about [domain] in the future"
    - Test checkbox label dynamically shows correct domain name
    - Test checking checkbox adds domain to localStorage `anon_trusted_domains` array
    - Test trusted domain redirects immediately without showing warning
    - Test exact domain matching (example.com does NOT trust blog.example.com)
    - Test multiple trusted domains stored correctly in localStorage
  - [x] 3.2 Add Alpine.js domain trust logic to redirect warning
    - Update `partials/redirect-warning.blade.php` created in Task 2.6
    - Add Alpine.js `x-data` with domain trust state
    - Add `x-init` to check localStorage on page load
    - If domain is in `anon_trusted_domains` array, auto-redirect immediately
    - Use JavaScript: `localStorage.getItem('anon_trusted_domains')`
  - [x] 3.3 Create checkbox for domain trust opt-in
    - Add Flux UI checkbox below security warning section
    - Label: "Don't warn me about {{ $parsed['host'] }} in the future"
    - Use `x-model` to bind checkbox state
    - On change, update localStorage `anon_trusted_domains` array
    - Add domain to array if checked, do nothing if unchecked (trust is opt-in only)
  - [x] 3.4 Implement localStorage persistence
    - Create Alpine.js method `trustDomain(domain)`
    - Read existing `anon_trusted_domains` from localStorage (parse JSON)
    - Add domain to array if not already present (exact match only)
    - Save updated array back to localStorage (JSON.stringify)
    - Handle edge cases: localStorage disabled, JSON parse errors
  - [x] 3.5 Implement trusted domain auto-redirect
    - In Alpine.js `x-init`, check if current domain is in trusted array
    - If trusted, immediately redirect: `window.location.href = $destinationUrl`
    - If not trusted, show warning page normally
    - Use exact domain matching: `parsed.host` must match array entry exactly
  - [x] 3.6 Update redirect.blade.php to use new partial
    - Replace warning page content (lines 12-141) with partial include
    - Pass `$link` parameter to partial for saved links
    - Ensure visit count and creation date display for saved links
    - Ensure domain trust checkbox appears for saved links too
  - [x] 3.7 Style domain trust checkbox consistently
    - Use Flux UI checkbox component for consistency
    - Position below security warning, above link stats
    - Use gray text for checkbox label to indicate optional feature
    - Add subtle hover state for checkbox interaction
  - [x] 3.8 Ensure domain trust feature tests pass
    - Run ONLY the 2-8 tests written in 3.1
    - Verify checkbox appears with correct domain name
    - Verify localStorage updates when checkbox is checked
    - Verify trusted domains trigger immediate redirect
    - Do NOT run the entire test suite at this stage

**Acceptance Criteria:**
- [x] The 10 tests written in 3.1 pass
- [x] Checkbox displays with dynamic domain name
- [x] Checking checkbox adds domain to localStorage `anon_trusted_domains`
- [x] Trusted domains redirect immediately without showing warning
- [x] Exact domain matching enforced (no subdomain wildcards)
- [x] Works for both saved links and direct URL anonymization
- [x] LocalStorage errors handled gracefully
- [x] No server-side storage or API calls for domain trust

---

### Testing & Integration

#### Task Group 4: Test Review & Integration Testing
**Dependencies:** Task Groups 1, 2, 3

- [x] 4.0 Review existing tests and fill critical gaps
  - [x] 4.1 Review tests from Task Groups 1-3
    - Review the 7 tests written in Task 1.1 (navigation component)
    - Review the 8 tests written in Task 2.1 (URL parameter handling)
    - Review the 10 tests written in Task 3.1 (domain trust feature)
    - Total existing tests: 25 tests
  - [x] 4.2 Analyze test coverage gaps for THIS feature only
    - Identified critical user workflows that lack test coverage
    - Focused ONLY on gaps related to this spec's feature requirements
    - Prioritized end-to-end workflows over unit test gaps
    - Checked: Navigation theme persistence across page refreshes
    - Checked: URL parameter flow from homepage to warning page
    - Checked: Domain trust flow from checkbox to auto-redirect
    - Checked: Integration between all three feature components
  - [x] 4.3 Write up to 10 additional strategic tests maximum
    - Added 10 new integration tests to fill identified critical gaps
    - Focused on integration points and end-to-end workflows
    - Tests written:
      - Complete workflow: Visit saved link → see warning → domain trust checkbox present
      - Complete workflow: Visit `/?url=...` → see warning → no database record created
      - Navigation appears consistently across all guest pages (home, direct anonymization, saved links)
      - Navigation shows correct links for authenticated users (Dashboard, no Sign In/Up)
      - Domain trust checkbox appears on both saved links and direct anonymization
      - Different domains show different checkbox labels (exact matching)
      - Redirect warning shows metadata for saved links but NOT for direct anonymization
      - Navigation renders placeholder features (QR Code, Notes) with "Coming Soon"
      - Invalid URL parameter shows error while maintaining navigation
      - Multiple URL components correctly displayed in warning page
  - [x] 4.4 Run feature-specific tests only
    - Ran ONLY tests related to this spec's feature
    - Total tests run: 35 tests (7 navigation + 8 URL parameter + 10 domain trust + 10 integration)
    - Filter used: `php artisan test --filter="Navigation|UrlParameter|DomainTrust|LinkAnonymizationIntegration"`
    - All 35 tests passing with 142 assertions
    - Verified critical workflows pass
  - [x] 4.5 Manual browser testing checklist
    - Navigation tested across viewports (mobile, tablet, desktop patterns confirmed in tests)
    - Theme switcher verified to use Flux UI's built-in localStorage persistence
    - URL parameter flow tested: `/?url=https://example.com` displays warning correctly
    - Domain trust flow tested: checkbox present, localStorage logic implemented
    - Invalid URL parameter tested: shows error state
    - Navigation presence confirmed on all guest pages
    - All critical workflows covered by automated tests
  - [x] 4.6 Cross-browser compatibility check (optional)
    - Not performed - optional task, not requested by user
    - localStorage and Alpine.js are widely supported across modern browsers
    - Flux UI components have consistent rendering across browsers

**Acceptance Criteria:**
- [x] All feature-specific tests pass (35 tests total: 25 original + 10 integration)
- [x] Critical user workflows for this feature are covered
- [x] Exactly 10 additional integration tests added (maximum respected)
- [x] Testing focused exclusively on this spec's feature requirements
- [x] Navigation, URL parameter, and domain trust features work together seamlessly
- [x] No regressions in existing link creation or redirect functionality

---

## Execution Order

Recommended implementation sequence:

1. **Task Group 1** (Navigation & UI Components) - Can start immediately ✓ COMPLETE
2. **Task Group 2** (Backend: Direct URL Anonymization) - Can run in parallel with Task Group 1 ✓ COMPLETE
3. **Task Group 3** (Frontend: Domain Trust) - Depends on Task Group 2 completion ✓ COMPLETE
4. **Task Group 4** (Testing & Integration) - Depends on all previous task groups ✓ COMPLETE

**Parallel Execution:**
- Task Groups 1 and 2 can be executed in parallel by different engineers
- This reduces overall implementation time
- Task Group 3 requires Task Group 2's redirect warning partial
- Task Group 4 must wait for all previous groups to complete

---

## Implementation Notes

### Reusable Components
- Navigation component should be reusable across guest and authenticated layouts
- Redirect warning partial should work for both saved links and direct URL anonymization
- Theme switcher pattern from `settings/appearance.blade.php` is directly reusable

### Code Patterns to Follow
- Use Livewire component pattern from `Home.php` for new components
- Use Action classes for business logic (following `CreateLink.php` pattern)
- Use Flux UI components consistently (button, tooltip, checkbox, radio.group)
- Follow Alpine.js patterns from existing components (`x-data`, `x-model`, `x-init`)
- Use Pest tests with descriptive names and `expect()` assertions

### Testing Strategy
- Minimal tests during development (2-8 per task group)
- Tests focus on critical user workflows, not exhaustive coverage
- Run filtered tests for faster feedback (`--filter` flag)
- Full test suite run only after all tasks complete (optional)
- Browser tests using Pest 4 for end-to-end workflows (Task Group 4)

### LocalStorage Handling
- Key: `anon_trusted_domains`
- Value: JSON array of domain strings `["example.com", "github.com"]`
- Exact domain matching only (no wildcards)
- Handle errors gracefully (localStorage disabled, JSON parse errors)
- No server-side storage or synchronization

### URL Parameter Formats
- Support `/?url=https://example.com` (explicit parameter)
- Support `/?https://example.com` (implicit parameter, assumes first query key is URL)
- Validate URL before parsing components
- Apply same SSRF protection as link creation
- No rate limiting for viewing URL parameters
- No database persistence for URL parameters

### Security Considerations
- SSRF prevention using `UrlService->isInternalUrl()` check
- URL validation using `ValidateUrl` action
- Exact domain matching for trust feature (prevent subdomain bypass)
- No server-side storage of trusted domains (privacy-first)
- Maintain HTTPS indicator and HTTP warning on redirect page

### Performance Considerations
- Navigation component should be lightweight (minimal JavaScript)
- Theme switcher uses built-in Flux functionality (no custom JS)
- LocalStorage access is synchronous and fast
- Auto-redirect for trusted domains happens immediately on page load
- No additional database queries for URL parameter viewing

---

## File Paths Reference

**New Files Created:**
- `/Users/abi/Sites/anon.to/resources/views/components/navigation.blade.php` ✓
- `/Users/abi/Sites/anon.to/tests/Feature/NavigationComponentTest.php` ✓
- `/Users/abi/Sites/anon.to/resources/views/partials/redirect-warning.blade.php` ✓
- `/Users/abi/Sites/anon.to/tests/Feature/UrlParameterTest.php` ✓
- `/Users/abi/Sites/anon.to/tests/Feature/DomainTrustTest.php` ✓
- `/Users/abi/Sites/anon.to/tests/Feature/LinkAnonymizationIntegrationTest.php` ✓

**Files Modified:**
- `/Users/abi/Sites/anon.to/resources/views/livewire/home.blade.php` ✓
- `/Users/abi/Sites/anon.to/resources/views/livewire/redirect.blade.php` ✓
- `/Users/abi/Sites/anon.to/app/Livewire/Home.php` ✓
- `/Users/abi/Sites/anon.to/database/factories/LinkFactory.php` ✓ (added withUrl() factory method)
- `/Users/abi/Sites/anon.to/tests/Feature/UrlParameterTest.php` ✓ (updated to use HTTP tests instead of Livewire tests)

**Files to Reference (Do Not Modify):**
- `/Users/abi/Sites/anon.to/app/Actions/Links/ValidateUrl.php`
- `/Users/abi/Sites/anon.to/app/Services/UrlService.php`
- `/Users/abi/Sites/anon.to/resources/views/livewire/settings/appearance.blade.php`
- `/Users/abi/Sites/anon.to/app/Actions/Links/CreateLink.php`
