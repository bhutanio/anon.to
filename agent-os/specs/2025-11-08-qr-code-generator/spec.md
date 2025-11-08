# Specification: QR Code Generator

## Goal
Add a privacy-first QR code generator to anon.to that allows users to generate and download QR codes in PNG, SVG, and PDF formats without storing any data server-side, maintaining the platform's commitment to user privacy.

## User Stories
- As an anonymous user, I want to generate QR codes from text or URLs and download them in multiple formats, so that I can use them in various contexts without creating an account
- As an authenticated user, I want higher rate limits for QR code generation so that I can create more codes for my projects
- As a privacy-conscious user, I want my QR code content to never be stored on the server so that my data remains completely private

## Specific Requirements

**Route and Page Structure**
- Create `/qr` route accessible to both anonymous and authenticated users
- Use class-based Livewire Volt component following existing pattern from notes feature
- Implement guest layout (`components.layouts.guest`) for consistency with notes/create page
- Include navigation component with QR Code link updated from "Coming Soon" to active state
- Follow existing page structure: hero section, main form card, feature highlights, footer

**QR Code Generation**
- Use `chillerlan/php-qrcode` library for PNG and SVG generation (install via composer)
- Use `dompdf/dompdf` library for PDF generation (install via composer)
- Fixed size: 512x512 pixels for all formats
- Error correction level: Medium (15%) - industry standard
- Generate on-demand when user clicks "Generate QR Code" button
- No real-time preview as user types
- Create Action class `App\Actions\QrCode\GenerateQrCode` to encapsulate generation logic

**Content Input and Validation**
- Single textarea for any content (text, URLs, data)
- Character limit: 2,900 characters (industry standard for Medium error correction)
- Required field validation with clear error message
- Display character counter below textarea (similar to notes feature)
- No URL parameter support for pre-filling content

**Download Functionality**
- Three download buttons: PNG, SVG, PDF
- File naming convention: `qr-code-{timestamp}.{extension}` (e.g., `qr-code-1731072000.png`)
- Direct download via browser response headers (no temporary file storage)
- Download triggers use separate Livewire actions: `downloadPng()`, `downloadSvg()`, `downloadPdf()`

**Rate Limiting Implementation**
- Anonymous users: 10 QR code generations per hour
- Authenticated users: 50 QR code generations per hour
- Use same pattern as notes feature with RateLimiter facade
- Rate limit key format: `generate-qr:ip:{ip}` for anonymous, `generate-qr:user:{id}` for authenticated
- Display helpful error message with minutes remaining when rate limited
- Clear error message after successful generation

**UI Components and Styling**
- Use Flux UI components: `flux:button`, `flux:heading`, standard HTML textarea with Tailwind
- Dark mode support using `dark:` classes following existing patterns
- Mobile responsive design with Tailwind breakpoints (`sm:`, `md:`, `lg:`)
- Loading states: disable button and show spinner during generation (similar to notes feature)
- Error messages displayed in red alert box with icon (consistent with notes feature)
- Success state shows QR code preview as inline image before download options

**Form Reset and State Management**
- Clear textarea and reset state after successful generation
- Show preview with download options below form
- Allow generating new QR code without page refresh
- Reset error messages when user starts typing again

**Privacy and Security**
- No database storage of QR content or generated images
- No caching of generated QR codes
- Hash IP addresses for rate limiting only (use SHA-256)
- CSRF protection via Laravel defaults
- Input sanitization through Blade escaping
- Validate character limits server-side to prevent abuse

**Performance Targets**
- QR code generation completes in under 2 seconds
- No database queries during generation (completely stateless)
- Memory-efficient single QR generation at a time
- Direct streaming of downloads without temporary files

## Visual Design

No visual assets provided. Implementation should follow existing design patterns:

**Page Layout**
- Hero section with title "Generate QR Codes" and subtitle about privacy/simplicity
- Main card with white/dark-gray-800 background, rounded corners, shadow
- Textarea with monospace font similar to notes content field
- Character counter below textarea in gray-600/gray-400 text
- Generate button in indigo-600 with hover states and loading spinner
- Preview section shows generated QR code as image
- Three download buttons arranged horizontally on desktop, stacked on mobile

**Dark Mode Support**
- Background: white → dark:bg-gray-800
- Text: gray-900 → dark:text-white
- Borders: gray-300 → dark:border-gray-600
- Input backgrounds: white → dark:bg-gray-700
- Consistent with notes creation page styling

**Responsive Design**
- Max width: 4xl (matching notes page)
- Padding: px-4 sm:px-6 lg:px-8
- Button layout: grid-cols-1 md:grid-cols-3 for download buttons
- Hero text: text-4xl sm:text-5xl

**Loading States**
- Button shows spinner SVG during generation
- Text changes from "Generate QR Code" to "Generating..."
- Button disabled attribute during processing

**Error Handling Display**
- Red alert box (bg-red-50 dark:bg-red-900/20)
- Border in red-200 dark:border-red-800
- Icon with error message
- Clear, user-friendly text

## Existing Code to Leverage

**App\Livewire\Notes\Create Component**
- Class structure with public properties for form fields
- RateLimiter implementation pattern with separate keys for IP/user
- Validation using `$this->validate()` with custom messages
- Error message display using public `$errorMessage` property
- Loading states using `wire:loading` attributes
- Form reset after successful submission

**Rate Limiting Pattern from Notes\Create**
- Check rate limit before processing with `RateLimiter::tooManyAttempts()`
- Calculate minutes remaining with `ceil(RateLimiter::availableIn($key) / 60)`
- Hit rate limiter after successful action with `RateLimiter::hit($key, 3600)`
- Different limits for authenticated (50) vs anonymous (10) users

**Navigation Component Updates**
- Remove `disabled` attribute and `cursor-not-allowed` class from QR Code button
- Change from button to link: `<a href="/qr">`
- Remove tooltip or change to informational tooltip
- Update hover states to match Notes link pattern

**Form Validation Pattern**
- Array-based validation rules (check existing form requests for style)
- Custom error messages in second parameter to `validate()`
- Display errors with `@error` directive below inputs

**Action Class Pattern from CreateNote**
- Constructor dependency injection for sub-actions
- Single `execute()` method with typed parameters
- Clear step-by-step comments for logic flow
- Throw specific exceptions for validation failures
- Return typed result (in this case, will return download response directly)

## Out of Scope
- QR code scanning or reading functionality
- Batch QR code generation (multiple codes at once)
- Custom QR code sizes or resolution options
- Custom error correction levels (Low/Quartile/High)
- Custom logos, branding, or images embedded in QR codes
- Color customization for QR codes
- Real-time preview as user types
- URL parameter support for pre-filling content (e.g., `/qr?text=example`)
- User history or saved QR codes
- Analytics or tracking of QR code usage
- Database storage of generated codes or content
- API endpoints for programmatic QR code generation
- QR code templates or presets
- Sharing generated QR codes via URL
