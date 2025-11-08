# Task Breakdown: QR Code Generator

## Overview
Total Tasks: 43 tasks organized into 6 major task groups
Estimated Complexity: Medium
Pattern Reference: Notes feature (/notes/create)

## Task List

### Task Group 1: Dependencies & Configuration
**Dependencies:** None

- [x] 1.0 Complete dependencies and configuration setup
  - [x] 1.1 Install QR code generation libraries
    - Install `chillerlan/php-qrcode` via composer (already in spec requirements)
    - Install `dompdf/dompdf` via composer for PDF generation
    - Verify installations with `composer show` commands
  - [x] 1.2 Verify library compatibility
    - Test basic QR code generation in tinker
    - Test PDF generation in tinker
    - Ensure no conflicts with Laravel 12 or PHP 8.4

**Acceptance Criteria:**
- Both libraries installed successfully
- No dependency conflicts
- Basic generation confirmed working

---

### Task Group 2: Action Layer - QR Generation Logic
**Dependencies:** Task Group 1

- [x] 2.0 Complete QR code generation action layer
  - [x] 2.1 Write 2-8 focused tests for QR generation
    - Limit to 2-8 highly focused unit tests maximum
    - Test only critical behaviors: PNG generation, SVG generation, PDF generation, character limit validation
    - File: `/Users/abi/Sites/anon.to/tests/Unit/Actions/QrCode/GenerateQrCodeTest.php`
    - Skip exhaustive edge case testing at this stage
  - [x] 2.2 Create GenerateQrCode action class
    - File: `/Users/abi/Sites/anon.to/app/Actions/QrCode/GenerateQrCode.php`
    - Follow pattern from `App\Actions\Notes\CreateNote`
    - Constructor with dependency injection if needed
    - Single `execute()` method accepting content and format
    - Return base64-encoded image data for preview
  - [x] 2.3 Implement PNG generation method
    - Use `chillerlan/php-qrcode` library
    - Fixed size: 512x512 pixels
    - Error correction level: Medium (15%)
    - Return PNG binary data
  - [x] 2.4 Implement SVG generation method
    - Use `chillerlan/php-qrcode` library
    - Fixed size: 512x512 pixels
    - Error correction level: Medium (15%)
    - Return SVG markup string
  - [x] 2.5 Implement PDF generation method
    - Use `dompdf/dompdf` library
    - Embed QR code (from PNG method) in PDF
    - Letter size page with centered QR code
    - Return PDF binary data
  - [x] 2.6 Add content validation
    - Character limit: 2,900 characters (industry standard)
    - Required field validation
    - Throw `\InvalidArgumentException` for validation failures
  - [x] 2.7 Ensure action layer tests pass
    - Run ONLY the 2-8 tests written in 2.1
    - Verify all three formats generate correctly
    - Do NOT run the entire test suite at this stage

**Acceptance Criteria:**
- The 2-8 tests written in 2.1 pass
- GenerateQrCode action class follows existing patterns
- All three formats (PNG, SVG, PDF) generate correctly
- Character limit validation works
- No database storage occurs

---

### Task Group 3: Livewire Component & Routes
**Dependencies:** Task Group 2

- [x] 3.0 Complete Livewire component and routing
  - [x] 3.1 Write 2-8 focused tests for Livewire component
    - Limit to 2-8 highly focused feature tests maximum
    - Test only critical actions: QR generation with valid content, rate limiting enforcement, character limit validation, download triggers
    - File: `/Users/abi/Sites/anon.to/tests/Feature/Livewire/QrCode/CreateTest.php`
    - Skip exhaustive testing of all scenarios
  - [x] 3.2 Create QR code Livewire component (class-based Volt)
    - File: `/Users/abi/Sites/anon.to/app/Livewire/QrCode/Create.php`
    - Follow pattern from `App\Livewire\Notes\Create`
    - Public properties: `content`, `qrCodeDataUrl`, `errorMessage`, `format`
    - Rate limiting implementation matching notes pattern
    - Validation rules for content (required, max:2900)
  - [x] 3.3 Implement generateQrCode() method
    - Reset state at start
    - Check rate limit (10/hour anonymous, 50/hour authenticated)
    - Hash IP address with SHA-256 for rate limit key
    - Validate content using `$this->validate()`
    - Call GenerateQrCode action with PNG format
    - Store base64-encoded preview in `qrCodeDataUrl` property
    - Hit rate limiter after successful generation
    - Clear content after successful generation
    - Handle exceptions with user-friendly error messages
  - [x] 3.4 Implement download methods
    - Create `downloadPng()` method: calls GenerateQrCode action, streams binary data with proper headers
    - Create `downloadSvg()` method: calls GenerateQrCode action, streams SVG data with proper headers
    - Create `downloadPdf()` method: calls GenerateQrCode action, streams PDF data with proper headers
    - File naming: `qr-code-{timestamp}.{extension}` (e.g., `qr-code-1731072000.png`)
    - Use `response()->streamDownload()` for direct download
  - [x] 3.5 Implement render() method
    - Return view: `livewire.qr-code.create`
    - Use guest layout: `components.layouts.guest`
  - [x] 3.6 Create route for QR code page
    - File: `/Users/abi/Sites/anon.to/routes/web.php`
    - Route: `GET /qr` -> `App\Livewire\QrCode\Create` component
    - Public access (no auth middleware)
  - [x] 3.7 Ensure component tests pass
    - Run ONLY the 2-8 tests written in 3.1
    - Verify QR generation works for authenticated and anonymous users
    - Verify rate limiting enforced correctly
    - Do NOT run the entire test suite at this stage

**Acceptance Criteria:**
- The 2-8 tests written in 3.1 pass
- Livewire component follows existing patterns
- Rate limiting works (10/hour anonymous, 50/hour authenticated)
- All download methods stream files correctly
- Route accessible at `/qr`

---

### Task Group 4: Frontend UI Components
**Dependencies:** Task Group 3

- [x] 4.0 Complete QR code UI design
  - [x] 4.1 Create QR code Blade view
    - File: `/Users/abi/Sites/anon.to/resources/views/livewire/qr-code/create.blade.php`
    - Follow pattern from `resources/views/livewire/notes/create.blade.php`
    - Use guest layout with navigation component
    - Structure: hero section, main form card, feature highlights, footer
  - [x] 4.2 Build hero section
    - Centered title: "Generate QR Codes" with "Securely" in indigo accent color
    - Subtitle about privacy and simplicity
    - Text sizes: `text-4xl sm:text-5xl` for title
    - Follow dark mode pattern: `text-gray-900 dark:text-white`
  - [x] 4.3 Create main form card
    - White/dark-gray-800 background with rounded corners and shadow
    - Class: `bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8`
    - Form with `wire:submit.prevent="generateQrCode"`
  - [x] 4.4 Build content textarea
    - Label with required asterisk: "Content *"
    - Textarea with `wire:model.defer="content"`
    - Rows: 10-12 for optimal UX
    - Placeholder: "Enter text, URL, or any data to generate QR code..."
    - Monospace font similar to notes feature
    - Character counter below: "{{ mb_strlen($content) }} / 2,900 characters"
    - Display validation errors with `@error('content')` directive
  - [x] 4.5 Create generate button
    - Full width button with indigo-600 background
    - Loading state: disable button, show spinner, text "Generating..."
    - Use `wire:loading` and `wire:loading.remove` attributes
    - Pattern from notes create button
  - [x] 4.6 Build error message display
    - Conditional display with `@if($errorMessage)`
    - Red alert box: `bg-red-50 dark:bg-red-900/20`
    - Border: `border-red-200 dark:border-red-800`
    - Include error icon SVG
    - Display `{{ $errorMessage }}`
  - [x] 4.7 Create QR code preview section
    - Conditional display with `@if($qrCodeDataUrl)`
    - Centered image tag with base64 data URL source
    - Rounded corners and shadow for visual polish
    - Max width: 512px (native QR size)
  - [x] 4.8 Build download buttons section
    - Three buttons: "Download PNG", "Download SVG", "Download PDF"
    - Grid layout: `grid-cols-1 md:grid-cols-3 gap-4`
    - Each button triggers respective download method
    - Indigo-600 background with hover states
    - Icon for each format (optional, follow existing patterns)
  - [x] 4.9 Add feature highlights section
    - Grid: `grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6`
    - Highlight cards for: "No Storage", "Multiple Formats", "Privacy First"
    - Icon, title, and description for each
    - Follow pattern from notes create page
  - [x] 4.10 Build footer
    - Border top with copyright text
    - Centered, gray text
    - Follow pattern from notes create page
  - [x] 4.11 Implement responsive design
    - Test mobile (320px - 768px): stacked layout, full-width buttons
    - Test tablet (768px - 1024px): 2-column features, partial button grid
    - Test desktop (1024px+): 3-column features, full button grid
    - Max width: 4xl (matching notes page)
    - Padding: `px-4 sm:px-6 lg:px-8`
  - [x] 4.12 Apply dark mode styling
    - Background colors: `white → dark:bg-gray-800`
    - Text colors: `gray-900 → dark:text-white`
    - Borders: `gray-300 → dark:border-gray-600`
    - Input backgrounds: `white → dark:bg-gray-700`
    - Test dark mode toggle functionality
  - [x] 4.13 Add loading and interaction states
    - Button disabled state during generation
    - Spinner animation during generation
    - Character counter updates live
    - Error messages clear when user starts typing (optional enhancement)

**Acceptance Criteria:**
- Page renders correctly at `/qr` route
- Matches visual design patterns from notes feature
- Responsive across all breakpoints
- Dark mode fully functional
- Loading states work correctly
- Form validation displays properly

---

### Task Group 5: Integration & Navigation
**Dependencies:** Task Group 4

- [x] 5.0 Complete navigation and integration
  - [x] 5.1 Update navigation component
    - File: `/Users/abi/Sites/anon.to/resources/views/components/navigation.blade.php`
    - Find QR Code placeholder button (lines 18-29)
    - Change from button to link: `<a href="/qr">`
    - Remove `disabled` attribute
    - Remove `cursor-not-allowed` class
    - Update classes to match Notes link pattern (lines 32-40)
    - Update or remove tooltip (optional: change to "Generate QR Codes")
  - [x] 5.2 Test navigation integration
    - Verify link visible on desktop navigation
    - Verify link clickable and routes to `/qr`
    - Verify link styling matches other navigation items
    - Verify hover states work correctly
  - [x] 5.3 Test end-to-end user flow manually
    - Navigate from home to `/qr` via navigation
    - Enter content and generate QR code
    - Verify preview displays
    - Download PNG and verify file
    - Download SVG and verify file
    - Download PDF and verify file
    - Test rate limiting by generating 11 codes as anonymous user
    - Test with authenticated user for higher limit

**Acceptance Criteria:**
- Navigation link active and functional
- QR Code link matches styling of Notes link
- End-to-end flow works without errors
- Rate limiting enforced correctly

---

### Task Group 6: Testing & Polish
**Dependencies:** Task Groups 1-5

- [x] 6.0 Review existing tests and fill critical gaps only
  - [x] 6.1 Review tests from Task Groups 2-3
    - Review the 2-8 tests written by backend for action layer (Task 2.1)
    - Review the 2-8 tests written by full-stack for Livewire component (Task 3.1)
    - Total existing tests: approximately 4-16 tests
  - [x] 6.2 Analyze test coverage gaps for QR feature only
    - Identify critical user workflows lacking test coverage
    - Focus ONLY on gaps related to QR code generation requirements
    - Do NOT assess entire application test coverage
    - Prioritize integration and end-to-end workflows
  - [x] 6.3 Write up to 10 additional strategic tests maximum
    - Add maximum of 10 new tests to fill identified critical gaps
    - Consider: browser tests for complete user flow
    - Consider: edge cases for rate limiting boundary conditions
    - Consider: format-specific download tests (file headers, content type)
    - Consider: content validation edge cases (empty, exactly 2900 chars, 2901 chars)
    - File locations:
      - Feature tests: `/Users/abi/Sites/anon.to/tests/Feature/QrCode/`
      - Browser tests: `/Users/abi/Sites/anon.to/tests/Browser/QrCode/`
    - Do NOT write comprehensive coverage for all scenarios
    - Skip performance tests, accessibility tests unless business-critical
  - [x] 6.4 Write browser test for complete user journey
    - File: `/Users/abi/Sites/anon.to/tests/Browser/QrCode/GenerateQrCodeTest.php`
    - Test: Visit /qr, fill content, click generate, verify preview, download PNG
    - Use Pest 4 browser testing features
    - Assert no JavaScript errors
    - Assert QR code image displays
  - [x] 6.5 Run feature-specific tests only
    - Run ONLY tests related to QR code feature
    - Use filter: `php artisan test --filter=QrCode`
    - Expected total: approximately 14-26 tests maximum
    - Do NOT run the entire application test suite (212 tests)
    - Verify all QR code tests pass
  - [x] 6.6 Run Laravel Pint code formatter
    - Execute: `vendor/bin/pint --dirty`
    - Do NOT use `--test` flag
    - Ensure all new PHP files formatted correctly
    - Verify no formatting issues remain
  - [x] 6.7 Manual QA checklist
    - Test as anonymous user: generate QR, hit rate limit, verify error message
    - Test as authenticated user: generate QR, verify higher limit (50/hour)
    - Test character limit: exactly 2900 chars (success), 2901 chars (error)
    - Test all download formats: PNG, SVG, PDF open correctly
    - Test file naming: verify timestamp format in filenames
    - Test dark mode: toggle theme, verify all elements styled correctly
    - Test mobile responsive: verify layout on iPhone viewport
    - Test form reset: verify content clears after successful generation
    - Test error recovery: trigger error, then successfully generate
  - [x] 6.8 Performance verification
    - Time QR generation: should complete in under 2 seconds
    - Verify no database queries during generation (check Laravel Debugbar if available)
    - Verify no temporary files created (all streaming)
    - Monitor memory usage during generation (should be minimal)

**Acceptance Criteria:**
- All feature-specific tests pass (approximately 14-26 tests total)
- Critical user workflows for QR feature fully covered
- No more than 10 additional tests added when filling gaps
- Code formatted with Laravel Pint
- Manual QA checklist complete
- Performance targets met

---

## Execution Order

Recommended implementation sequence:

1. **Task Group 1: Dependencies & Configuration** (1-2 hours)
   - Install required libraries
   - Verify compatibility

2. **Task Group 2: Action Layer** (3-4 hours)
   - Write focused tests for QR generation
   - Build GenerateQrCode action class
   - Implement PNG, SVG, PDF generation methods
   - Add validation logic

3. **Task Group 3: Livewire Component & Routes** (4-5 hours)
   - Write focused tests for component
   - Create Livewire component
   - Implement rate limiting
   - Add download methods
   - Register route

4. **Task Group 4: Frontend UI** (5-6 hours)
   - Create Blade view
   - Build form and input components
   - Add preview and download sections
   - Implement responsive design
   - Apply dark mode styling

5. **Task Group 5: Integration** (1-2 hours)
   - Update navigation component
   - Test user flows
   - Verify end-to-end functionality

6. **Task Group 6: Testing & Polish** (3-4 hours)
   - Review existing tests
   - Fill critical coverage gaps
   - Write browser tests
   - Run formatters
   - Complete QA checklist

**Total Estimated Time:** 17-23 hours

---

## File Reference Guide

### New Files to Create:
- `/Users/abi/Sites/anon.to/app/Actions/QrCode/GenerateQrCode.php`
- `/Users/abi/Sites/anon.to/app/Livewire/QrCode/Create.php`
- `/Users/abi/Sites/anon.to/resources/views/livewire/qr-code/create.blade.php`
- `/Users/abi/Sites/anon.to/tests/Unit/Actions/QrCode/GenerateQrCodeTest.php`
- `/Users/abi/Sites/anon.to/tests/Feature/Livewire/QrCode/CreateTest.php`
- `/Users/abi/Sites/anon.to/tests/Browser/QrCode/GenerateQrCodeTest.php`

### Files to Modify:
- `/Users/abi/Sites/anon.to/routes/web.php` (add QR route)
- `/Users/abi/Sites/anon.to/resources/views/components/navigation.blade.php` (activate QR link)
- `/Users/abi/Sites/anon.to/composer.json` (will be modified by composer install commands)

### Reference Files (Existing Patterns):
- `/Users/abi/Sites/anon.to/app/Livewire/Notes/Create.php` (component pattern)
- `/Users/abi/Sites/anon.to/resources/views/livewire/notes/create.blade.php` (view pattern)
- `/Users/abi/Sites/anon.to/app/Actions/Notes/CreateNote.php` (action pattern)

---

## Key Technical Decisions

**QR Code Generation:**
- Library: `chillerlan/php-qrcode` for PNG and SVG
- Library: `dompdf/dompdf` for PDF
- Size: 512x512 pixels (fixed)
- Error correction: Medium (15%)

**Rate Limiting:**
- Anonymous: 10 per hour
- Authenticated: 50 per hour
- Key format: `generate-qr:ip:{hashed-ip}` or `generate-qr:user:{id}`
- IP hashing: SHA-256

**Data Privacy:**
- No database storage
- No caching of QR codes
- No logging of content
- Stateless operation

**Download Implementation:**
- Direct streaming (no temporary files)
- File naming: `qr-code-{timestamp}.{extension}`
- Content-Type headers: image/png, image/svg+xml, application/pdf
- Content-Disposition: attachment

**Testing Strategy:**
- 2-8 focused tests per task group during development
- Maximum 10 additional tests for gap filling
- Total target: 14-26 tests for QR feature
- Browser tests for end-to-end verification
- Do NOT run full test suite (212 tests) during development
