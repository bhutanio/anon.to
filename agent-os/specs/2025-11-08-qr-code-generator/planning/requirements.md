# Spec Requirements: QR Code Generator

## Initial Description
QR Code generator, simple page to generate QR Code and download, we dont save anything, (formats for download: PNG, SVG and PDF). One textarea for any content.

## Requirements Discussion

### First Round Questions

**Q1: Rate limiting - Since this is a free public tool, should we apply similar rate limits as the notes feature (10/hour anonymous, 50/hour authenticated)?**
**Answer:** Yes - 10/hour for anonymous users, 50/hour for authenticated users.

**Q2: QR Code content limits - Should we enforce a character limit on the textarea? Industry standard is ~2,900 characters for Medium error correction.**
**Answer:** Industry standard (use practical QR code limits).

**Q3: Download file naming - Should downloads follow a pattern like `qr-code-{timestamp}.png`?**
**Answer:** `qr-code-{timestamp}.png` / `qr-code-{timestamp}.svg` / `qr-code-{timestamp}.pdf`

**Q4: Error correction level - QR codes have error correction levels (Low/Medium/Quartile/High). Should we use Medium (15%) as the default, or expose this as a user option?**
**Answer:** Industry standard (Medium/15%).

**Q5: QR Code size/resolution - Should we allow users to choose sizes (e.g., 256x256, 512x512, 1024x1024), or use a fixed high-resolution output?**
**Answer:** 512x512px (fixed size).

**Q6: Component architecture - Based on the existing codebase using Livewire Volt, should this be a functional or class-based Volt component?**
**Answer:** Class-based Volt component.

**Q7: URL parameter support - Should users be able to pre-fill the content via URL parameters (e.g., `/qr?text=Hello+World`) for sharing or API integration?**
**Answer:** No - manual input only.

**Q8: Are there any features you specifically want to exclude from this initial implementation?**
**Answer:** Excluded features:
- QR code scanning/reading
- Batch generation
- Analytics
- Custom logos/branding
- Real-time preview
- Content history/saving

### Existing Code to Reference

**Similar Features Identified:**
No similar existing features identified for reference by the user.

**Note:** Based on the product roadmap, the notes feature (/notes) follows similar patterns:
- Standalone page with simple form
- Rate limiting implementation
- No database storage for the generated output
- Anonymous and authenticated user support

### Follow-up Questions
No follow-up questions were needed.

## Visual Assets

### Files Provided:
No visual assets provided.

### Visual Insights:
No visual assets to analyze. Implementation should follow existing design patterns:
- Flux UI components for consistency
- Tailwind CSS 4 for styling
- Dark mode support (following existing patterns)
- Mobile responsive design

## Requirements Summary

### Functional Requirements

**Core Functionality**
- Single-page QR code generator accessible at `/qr` route
- One textarea input for content (any text, URLs, or data)
- Character limit: ~2,900 characters (industry standard for Medium error correction)
- Generate button to create QR code (not real-time/live generation)
- QR code preview displayed after generation
- Download buttons for three formats: PNG, SVG, PDF

**QR Code Specifications**
- Size: 512x512 pixels (fixed)
- Error correction level: Medium (15%)
- File naming convention: `qr-code-{timestamp}.{extension}`
  - Example: `qr-code-1699488234.png`

**User Actions Enabled**
- Paste or type content into textarea
- Click generate button to create QR code
- Preview generated QR code on screen
- Download QR code in PNG format
- Download QR code in SVG format
- Download QR code in PDF format
- Clear form and generate new QR code

**Data Management**
- No database storage (completely stateless)
- No user association with generated QR codes
- No history or logs of generated QR codes
- QR codes generated on-demand and served directly as downloads

**Rate Limiting**
- Anonymous users: 10 QR code generations per hour
- Authenticated users: 50 QR code generations per hour
- Follows existing rate limiting pattern from notes feature

**Authentication Requirements**
- No login required to access the page
- No login required to generate QR codes
- Optional authentication provides higher rate limits only

### Reusability Opportunities

**Component Patterns**
- Similar to notes creation page (standalone, simple form, no storage)
- Rate limiting implementation from notes feature
- Flux UI components (button, textarea, field, heading)
- Livewire Volt class-based component architecture

**Backend Patterns**
- Rate limiting middleware (existing implementation)
- Form validation patterns from notes/links features
- Action classes for generation logic

**Frontend Patterns**
- Dark mode support (existing implementation)
- Loading states during generation
- Error message display (existing patterns)
- Mobile responsive layouts (existing conventions)

### Scope Boundaries

**In Scope:**
- QR code generation from text input
- PNG, SVG, and PDF download formats
- 512x512px fixed resolution
- Medium error correction level (15%)
- Rate limiting (10/hour anonymous, 50/hour authenticated)
- Preview of generated QR code
- Character limit validation (~2,900 characters)
- Manual content input only
- Stateless operation (no database storage)

**Out of Scope:**
- QR code scanning/reading functionality
- Batch QR code generation
- Custom sizes or resolution options
- Custom error correction levels
- Custom logos or branding on QR codes
- Color customization
- Real-time/live preview as user types
- URL parameter support for pre-filling content
- User history or saved QR codes
- Analytics on QR code usage
- Database storage of generated codes
- API endpoints for programmatic generation

### Technical Considerations

**Integration Points**
- Route: `/qr` (new standalone page)
- Rate limiting middleware: Same as notes feature (10/hour, 50/hour)
- Authentication: Optional (uses existing Fortify setup)

**Technology Requirements**
- Library: `chillerlan/php-qrcode` for PNG and SVG generation (already in tech stack)
- Library: `dompdf/dompdf` for PDF generation (needs installation)
- Component: Livewire Volt class-based component
- UI: Flux UI free edition components
- Styling: Tailwind CSS 4 with dark mode support

**Existing System Integration**
- Follows existing Livewire Volt patterns (class-based)
- Uses existing Flux UI component library
- Implements existing rate limiting patterns
- Follows existing dark mode implementation
- Uses existing mobile responsive patterns
- No conflicts with authentication system

**Similar Code Patterns to Follow**
- Notes creation form structure and validation
- Rate limiting implementation from notes and links
- Flux UI component usage from existing pages
- Dark mode class patterns from existing components
- Error handling patterns from form validation

**Standards Compliance**
- Follows Laravel 12 conventions (routes, middleware, actions)
- Follows Livewire 3 + Volt 1 patterns
- Uses Pest 4 for testing (create comprehensive tests)
- Follows Pint code formatting (run before finalizing)
- Uses proper Form Request classes for validation
- Implements CSRF protection (Laravel default)
- XSS prevention through Blade escaping

**Performance Considerations**
- QR code generation should complete in < 2 seconds
- No database queries (stateless)
- Redis cache not needed (no repeated generations)
- Rate limiting to prevent abuse
- Memory-efficient generation (single QR at a time)

**Security Considerations**
- Input validation on textarea content
- Character limit enforcement to prevent abuse
- Rate limiting to prevent service degradation
- XSS prevention through proper escaping
- No storage of potentially sensitive QR content
- CSRF protection on form submission

**Testing Requirements**
- Feature test: QR code generation for authenticated users
- Feature test: QR code generation for anonymous users
- Feature test: Rate limiting enforcement (10/hour, 50/hour)
- Feature test: Character limit validation
- Feature test: PNG download functionality
- Feature test: SVG download functionality
- Feature test: PDF download functionality
- Feature test: Form validation (empty content, too long)
- Unit test: QR code generation action
- Browser test: Complete user flow (input, generate, download)
