---
description: "Create feature planning documents with auto-numbered folder"
argument-hint: "<feature-name>"
---

You are creating a feature planning document structure for the feature: **$ARGUMENTS**

**IMPORTANT: Do NOT include any code samples, code snippets, or example code in any of the three files. 
All files should be purely descriptive and conceptual, using natural language only.**

## Step 1: Validate Input

First, check if $ARGUMENTS is provided and reasonable:
- If $ARGUMENTS is empty, stop and ask the user to provide a feature name
- If $ARGUMENTS is longer than 60 characters, truncate to first 60 chars for the folder slug (but use full name in documents)
- Show the user the generated folder slug and confirm before proceeding

## Step 2: Ask Clarifying Questions

**Before asking questions:** Quickly search the project for related existing code using Glob/Grep to understand 
what already exists and what can be reused. Mention findings to the user before asking questions.

Use the AskUserQuestion tool to gather the following information to customize the planning documents:
**ultrathink** and ask relevant questions.

Wait for their answers, then use this information to customize the plan, spec, and task files appropriately.

## Step 2.5: Determine Template Customizations

Based on user answers, prepare to customize the templates:

**If "Frontend only":**
- Remove "Backend Components" subsection from spec.md
- Remove Phase 2 (Backend Development) from task.md

**If "Backend only":**
- Remove "Frontend Components" subsection from spec.md
- Remove Phase 3 (Frontend Development) from task.md

**If "Enhancement to existing feature":**
- Adjust plan.md to focus on improvements rather than new capabilities
- In spec.md, emphasize "Modified Data Structures" over "New Data Structures"

**If "Quick (1-3 days)":**
- Simplify Phase 3 testing requirements in task.md
- Mark optional items clearly

**If specific test types selected:**
- Add corresponding tasks to Phase 3 in task.md

## Step 3: Create Folder Structure

Create `plan/` folder if it doesn't exist.

In the `plan/` directory, find the highest numbered folder (format: ###-name), increment the number,
and create a new folder using the next sequential number and a slugified version of the feature name
(lowercase, hyphens, **max 30 chars**, alphanumeric only). Format: `plan/###-feature-slug`

**Validation:**
- Before creating, check if a folder with this exact slug already exists (regardless of number)
- If it exists, warn the user and ask if they want to: (a) use a different name, (b) overwrite, or (c) cancel
- Store the folder number (e.g., "003") and slug separately for use in templates

## Step 4: Create Planning Files

Now create three files in the newly created folder. **Do NOT include the markdown code fence markers (```) in the actual files - only the content inside.**

Apply customizations from Step 2.5 when creating these files - remove or adjust sections based on user answers.

### File 1: plan.md

Create `{FOLDER}/plan.md` with this content (replace placeholders based on user answers):

```
# $ARGUMENTS

**Created:** [Current date in YYYY-MM-DD format]
**Type:** [Feature type from user answer]
**Timeline:** [Timeline from user answer]

## What & Why
[What is this feature? Why are we building it? What problem does it solve? (2-3 sentences)]

## Who
[Who will use this feature? Be specific about user types/personas]

## Scope

### MVP - Must Have
- [Core functionality that makes this feature viable]
- [Minimum deliverables to solve the problem]
- [2-5 items maximum - be ruthless]

### Nice to Have (Future Iterations)
- [Enhancements that can wait]
- [Features we're explicitly deferring]

## Success Criteria
[How will we know this feature is successful? (1-3 measurable outcomes)]
- [Quantitative: e.g., "50% of users complete onboarding"]
- [Qualitative: e.g., "Users can complete task without help docs"]

## Risks & Blockers
[What could prevent us from shipping this? What dependencies exist?]
- [Technical risks or unknowns]
- [External dependencies or prerequisites]
- [Resource or timeline constraints]

---

**Optional Sections** (add only if relevant for complex features):

### User Stories (if helpful for clarity)
- As a [user type], I want to [action] so that [benefit]

### Analytics & Tracking (if data collection needed)
- [Events to track]
- [Metrics to measure]

### Accessibility Requirements (if beyond standard compliance)
- [Specific WCAG level or requirements]
- [Screen reader considerations]

### Internationalization (if multi-language support needed)
- [Languages to support]
- [Locale-specific considerations]

### Compliance & Privacy (if handling sensitive data)
- [GDPR, HIPAA, or other regulatory requirements]
- [Data retention policies]
```

### File 2: spec.md

Create `{FOLDER}/spec.md` with this content (remove sections based on scope from Step 2.5):

```
# $ARGUMENTS - Technical Specification

## Architecture Overview
[High-level approach in 2-3 sentences: What are we building? How does it fit into the existing system?]

[Key architectural decisions and patterns]

## Data

### What We're Storing
[New data structures, tables, or entities needed - describe conceptually, not with code]

[Modified existing data structures]

[Relationships between data entities]

### Key Data Considerations
- Validation rules: [Required fields, formats, constraints]
- Data migration: [If changing existing data, how?]
- Data retention: [How long we keep data, cleanup strategy]

## Components to Build

### Backend Components
[API endpoints, services, business logic, background jobs, etc.]

[Authentication/authorization requirements]

[Data validation and processing]

### Frontend Components
[Pages, views, UI components, forms, etc.]

[User flows and interactions]

[State management approach]

[Loading, error, and empty states]

### Integration Points
[External APIs or services]

[Internal services or modules this feature interacts with]

[Webhooks, events, or real-time updates]

## Security

**Critical security considerations:**
- Authentication/Authorization: [Who can access what?]
- Input Validation: [Where we validate and sanitize user input]
- Sensitive Data: [How we handle passwords, PINs, personal info]
- Common Vulnerabilities: [SQL injection, XSS, CSRF prevention]

**Skip if:** Standard CRUD with normal auth requirements

## Performance

**Key performance considerations:**
- Caching: [What to cache and where]
- Database: [Query optimization, indexes needed]
- API: [Rate limiting, pagination strategy]
- Frontend: [Lazy loading, code splitting, asset optimization]

**Skip if:** Simple feature with low traffic

## Testing Strategy

**What needs testing:**
- [ ] Core functionality (happy path)
- [ ] Error handling and edge cases
- [ ] Authentication/authorization rules
- [ ] Data validation
- [ ] Cross-browser/device compatibility
- [ ] Accessibility (keyboard navigation, screen readers)

**For complex features, also test:**
- [ ] Performance under load
- [ ] Concurrent operations
- [ ] Data integrity
- [ ] Security vulnerabilities

---

**Optional Sections** (add only if relevant):

### Email & Notifications (if sending communications)
- Templates needed
- Delivery channels (email, SMS, push, in-app)
- Frequency and triggers

### File Storage & Media (if handling uploads)
- File types allowed
- Size limits
- Storage location and access controls
- Processing needed (resize, transcode, etc.)

### Search & Filtering (if complex search needed)
- Search algorithm approach
- Filter and sort options
- Performance optimization

### Background Processing (if async work needed)
- Jobs to queue
- Retry and timeout strategy
- Error handling

### Real-time Features (if using WebSockets, etc.)
- Connection management
- Event types
- Fallback for offline/disconnected states

### Third-Party Dependencies (if using external services)
- Libraries or SDKs to integrate
- API rate limits and quotas
- Fallback if service is unavailable

### Browser/Device Support (if specific requirements)
- Minimum browser versions
- Mobile browser support
- Device-specific features

### SEO Considerations (if public-facing)
- Meta tags and descriptions
- Sitemap updates
- Structured data

### Migration Strategy (if changing existing functionality)
- How to migrate existing data
- Backwards compatibility approach
- Deprecation timeline
```

### File 3: task.md

Create `{FOLDER}/task.md` with this content (remove phases based on scope from Step 2.5, use the actual folder number in the git branch task):

```
# $ARGUMENTS - Implementation Tasks

**Git Branch:** `feature/{FOLDER_NUMBER}-{FEATURE_SLUG}` (e.g., `feature/003-user-authentication`)

## Phase 1: Setup & Planning
- [ ] Create git branch
- [ ] Review plan and spec with team (if applicable)
- [ ] Set up development environment / dependencies
- [ ] Create initial file structure
- [ ] Document any early blockers

## Phase 2: Build - Backend
- [ ] Create database migrations
- [ ] Create data models/entities
- [ ] Implement validation rules
- [ ] Implement core business logic
- [ ] Create API endpoints/routes
- [ ] Implement authentication/authorization
- [ ] Write unit tests for critical logic
- [ ] Test error handling

## Phase 3: Build - Frontend
- [ ] Create UI screens/pages
- [ ] Build reusable components
- [ ] Implement forms and validation
- [ ] Connect to backend APIs
- [ ] Handle loading states
- [ ] Handle error states
- [ ] Handle empty states
- [ ] Implement responsive design

## Phase 4: Validate & Ship

### Testing
- [ ] Test happy path (core functionality works)
- [ ] Test error cases (validation, edge cases)
- [ ] Test on different browsers/devices
- [ ] Test accessibility (keyboard nav, screen readers)
- [ ] Run automated tests (all passing)
- [ ] Manual testing of complete user flows

### Code Quality
- [ ] Run code formatters and linters
- [ ] Fix any warnings or quality issues
- [ ] Review your own code
- [ ] Add inline documentation where needed

### Review & Deploy
- [ ] Create pull request with description
- [ ] Address code review feedback
- [ ] Merge to main branch
- [ ] Deploy to staging
- [ ] QA testing on staging
- [ ] Deploy to production
- [ ] Monitor for errors (first 24 hours)
- [ ] Verify feature works in production

---

**Optional Tasks** (add only if applicable):

### For Complex Features
- [ ] Create database indexes for performance
- [ ] Set up background job processing
- [ ] Configure caching
- [ ] Set up monitoring/alerting
- [ ] Create email/notification templates
- [ ] Performance testing (load, stress)
- [ ] Security testing (OWASP top 10)
- [ ] Create API documentation
- [ ] Update user-facing documentation

### For Gradual Rollout
- [ ] Set up feature flag
- [ ] Deploy with feature disabled
- [ ] Enable for internal testing
- [ ] Enable for beta users (10%)
- [ ] Enable for all users (100%)

### Rollback Plan (document before deploying)
- How to disable quickly: [feature flag, config, etc.]
- Database rollback steps: [if schema changed]
- Communication plan: [if users are affected]

---

## Notes

**Dependencies:** [Blockers that must be resolved first]

**Risks:** [Potential challenges or unknowns]

**Decisions Made:** [Key technical choices - update as you go]
```

## Step 5: Verify Creation

Use the Glob tool to verify all three files were created successfully in the new folder:
- Confirm `{FOLDER}/plan.md` exists
- Confirm `{FOLDER}/spec.md` exists
- Confirm `{FOLDER}/task.md` exists

If any files are missing, report the specific error and retry creation for the missing file(s).

## Step 6: Provide Summary

Show the user:
1. **Folder created:** `{FOLDER}` (the actual path, e.g., `plan/003-user-authentication`)
2. **Files created:**
   - `{FOLDER}/plan.md` - High-level planning and objectives
   - `{FOLDER}/spec.md` - Technical specification
   - `{FOLDER}/task.md` - Implementation checklist

3. **Git branch name:** `feature/{FOLDER_NUMBER}-{FEATURE_SLUG}` (e.g., `feature/003-user-authentication`)

4. **Customizations applied:**
   - [List any sections removed or modified based on user answers]
   - [Note any testing requirements added]

5. **Next steps:**
   - **Fill out plan.md first** (15 min) - focus on MVP scope
   - **Fill out spec.md** (30 min) - skip optional sections if not needed
   - **Use task.md as your checklist** during implementation
   - Remove or skip any sections marked "Optional" that don't apply
   - Keep it simple - you can always add more detail later

6. **Tips:**
   - **Keep it short** - these docs should guide you, not slow you down
   - **Focus on MVP** - ship fast, learn, iterate
   - **Skip optional sections** - only add them if truly needed
   - **Update as you go** - docs are living documents
   - **Mark decisions** - note key choices in task.md Notes section

## Error Handling

If folder creation fails:
- Check write permissions on the plan/ directory
- Verify the parent directory exists
- Report the specific error to the user with the exact path that failed

If numbering conflicts occur:
- Re-scan the plan/ directory for the highest number
- Increment and retry once
- If still fails, ask user for manual resolution

If file writing fails:
- Report which specific file failed (plan.md, spec.md, or task.md)
- Show the error message
- Offer to retry just that file

---

**FINAL REMINDER: All three files must contain ONLY descriptive text and conceptual explanations. NO code samples, code snippets, queries, or example code should be included.**
