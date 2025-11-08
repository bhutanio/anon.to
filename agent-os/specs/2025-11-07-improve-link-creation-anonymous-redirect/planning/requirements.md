# Spec Requirements: Improve link creation and Anonymous redirect

## Initial Description

Anonymous Redirect - Do you want to link anonymously to other web sites without sending any referrer?

Use anon.to to de-referer or null-referer your links.

Just put http://anon.to/? in front of your links. Eg: http://anon.to/?http://www.google.com

**Context:** This is for the anon.to project, a privacy-first URL shortener being rebuilt on Laravel 12. The current implementation (Phase 1-3) has basic link creation and redirect warning pages, but we want to improve these features.

## Requirements Discussion

### First Round Questions

**Q1:** For the "Skip warning for this domain" trust feature, I'm thinking we should implement option A (localStorage for anonymous users only) since it's simpler and ships faster. Is that correct?

**Answer:** A (Anonymous users only with localStorage) - simplest, ships faster

**Q2:** For domain trust matching, should we use exact domain matching (trusting `example.com` does NOT automatically trust `blog.example.com`), or should we use root domain matching (trusting `example.com` automatically trusts all subdomains like `blog.example.com`, `shop.example.com`)?

**Answer:** Exact domain only (trusting `example.com` does NOT trust `blog.example.com`)

**Q3:** For the navigation enhancement, I see you mentioned "Notes" as a future feature. Should this just be a placeholder link in the navigation (perhaps linking to a "Coming Soon" page or just disabled), or do you want me to build out the notes feature as part of this spec?

**Answer:** "Notes" is a placeholder for future feature

**Q4:** For the direct anonymization feature (`/?url=...`), when showing the warning page without creating a database record:
   - Should we show the original URL (example.com) or show that it came via anon.to?
   - Should there be any indication this is a "temporary preview" vs a saved link?

**Answer:**
- Show the original URL (example.com)
- NO - Should NOT have any indication this is a "temporary preview" vs a saved link (treat it the same visually)

**Q5:** Are there any features you specifically DON'T want in this spec? For example:
   - Analytics/tracking for links
   - Link expiration
   - Password protection
   - Custom slugs (I see this was removed in a recent commit)
   - Link editing

**Answer:** Keep scope focused on the core features described. Advanced features like analytics, expiration, passwords are out of scope.

**Q6:** For the link creation form on the homepage, should we add any real-time validation feedback (e.g., "Invalid URL format" as they type), or keep it simple with validation only on submit?

**Answer:** Keep it simple (no real-time validation)

**Q7:** For the QR code generation feature mentioned in the navigation - should this be built as part of this spec, or is it also a future feature placeholder?

**Answer:** QR Code generation is future work (not in this spec)

**Q8:** For the homepage link creation form, should we add any advanced options like:
   - Setting custom expiration
   - Requiring password to access
   - Setting max number of uses

**Answer:** No - keep the homepage form simple, just URL input and submit

### Existing Code to Reference

No similar existing features identified for reference.

### Follow-up Questions

**Follow-up 1:** Just to clarify the navigation layout - you mentioned:
- Left side: Home, QR Code (future), Notes (future)
- Right side: Theme switcher, Auth links

For unauthenticated users, should the future feature links (QR Code, Notes) be:
- Visible but disabled/grayed out with a tooltip "Coming Soon"
- Hidden completely for unauthenticated users
- Visible and clickable but lead to a "Sign up to access this feature" page?

**Answer:** Unauthenticated users see placeholder links

**Follow-up 2:** For the anonymous redirect warning page, should we enhance the metadata displayed (favicon, title, description) beyond what's currently shown, or keep the existing display format?

**Answer:** Keep current metadata (no enhancements)

**Follow-up 3:** For the "continue to site" behavior on the warning page, should there be:
- Just a button (current behavior)
- Button + auto-redirect timer option (e.g., "Redirecting in 5 seconds...")
- Both options configurable by user preference?

**Answer:** Manual continue (no auto-redirect timer)

**Follow-up 4:** For the direct anonymization feature (`/?url=...`), should we:
- Add any rate limiting on this endpoint to prevent abuse
- Provide an option to "Save this as a permanent link" on the warning page
- Show a bookmarklet users can drag to their toolbar for easy access?

**Answer:**
- No rate limits
- No option to save as permanent link
- No bookmarklet

## Visual Assets

### Files Provided:
No visual assets provided.

### Visual Insights:
No visual assets available for analysis.

## Requirements Summary

### Functional Requirements

**Link Creation Enhancement:**
- Homepage form remains simple (URL input + submit)
- No real-time validation feedback
- No advanced options (expiration, password, max uses)
- QR code generation is future work (not in this spec)

**Navigation Enhancement:**
- Left side: Home, QR Code (placeholder/future), Notes (placeholder/future)
- Right side: Light/Dark theme switcher, Authentication links (Login/Register or Profile/Logout)
- Placeholder links visible for unauthenticated users

**Anonymous Redirect Warning Page Enhancement:**
- Add "Skip warning for [domain]" checkbox feature
- Implement using localStorage (anonymous users only)
- Exact domain matching (no subdomain wildcard matching)
- No management UI for trusted domains (users clear via browser settings)
- Keep current metadata display format
- Manual continue button only (no auto-redirect timer)

**Direct Anonymization Feature (`/?url=...`):**
- Accept URL parameter and show warning page without creating database record
- Display original URL (not anon.to reference)
- No visual distinction from saved links
- No rate limiting on this endpoint
- No option to save as permanent link
- No bookmarklet implementation

### Reusability Opportunities

No existing similar features identified by user. This appears to be new functionality for the application.

### Scope Boundaries

**In Scope:**
- Simple link creation form (URL only)
- Enhanced navigation with placeholders for future features
- Light/Dark theme switcher in navigation
- "Skip warning for domain" checkbox on redirect warning page
- localStorage-based domain trust for anonymous users
- Exact domain matching for trust
- Direct anonymization via `/?url=` parameter
- Warning page display for direct anonymization (without database record)

**Out of Scope:**
- Analytics or tracking for links
- Link expiration features
- Password protection for links
- Custom slug generation
- Link editing functionality
- Real-time URL validation feedback
- Advanced link creation options
- Auto-redirect timer on warning page
- Domain trust management UI
- Rate limiting on direct anonymization
- "Save as permanent link" option
- Bookmarklet for direct anonymization
- QR code generation (future work)
- Notes feature (future work)
- Enhanced metadata display on warning page

### Technical Considerations

**Technology Stack:**
- Laravel 12 framework
- Livewire 3 with Volt for interactivity
- Flux UI components (free edition)
- Tailwind CSS v4 for styling
- Alpine.js (included with Livewire)

**Domain Trust Implementation:**
- Client-side only (localStorage)
- Key format: `trusted_domains` array
- Exact domain matching (e.g., `example.com` ≠ `blog.example.com`)
- No server-side storage or authentication integration

**Direct Anonymization:**
- No database record creation for `/?url=...` usage
- Same warning page display as saved links
- Original URL shown (not anon.to reference)

**Existing Patterns:**
- Follow existing Livewire Volt component patterns (check if project uses functional or class-based)
- Use Flux UI components where available
- Follow existing navigation layout patterns
- Maintain consistent styling with existing pages

### Design Philosophy

- Privacy-first approach (localStorage for anonymous users)
- Simple, focused user experience (no feature overload)
- Clear separation between current and future features
- Consistent visual treatment (no distinction between saved and direct anonymization)
- Manual user control (no auto-redirects)