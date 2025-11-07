---
description: "Execute and implement feature plans phase by phase"
argument-hint: "<plan-folder-number-or-name>"
---

You are implementing a planned feature from the plan folder: **$ARGUMENTS**

## Step 1: Locate and Load Plan Files

### Find the Plan Folder
- If $ARGUMENTS is a number (e.g., "003"), find `plan/003-*/`
- If $ARGUMENTS includes the slug (e.g., "003-user-auth"), find exact match
- If $ARGUMENTS is just the slug (e.g., "user-auth"), search for matching folder
- If not found or multiple matches, list options and ask user to clarify

### Load All Planning Documents
Use the Read tool to load all three files:
- `plan.md` - Understand what and why
- `spec.md` - Understand technical approach
- `task.md` - Understand implementation tasks

### Understand the project
Quickly search the project for related existing code using Glob/Grep to understand what already exists.

### Parse Current Progress
- Parse task.md to identify all phases
- Count completed tasks: `- [x]` vs incomplete: `- [ ]`
- Determine which phase to start from (first phase with incomplete tasks)
- Calculate overall progress percentage

## Step 2: Show Implementation Overview

Present a clear overview to the user:

```
📋 Implementation Plan: [Feature Name]

**What:** [Brief what/why from plan.md]
**Scope:** [Frontend/Backend/Full-stack]
**Branch:** [Git branch name from task.md]

**Progress:** [X]% complete ([completed]/[total] tasks)

**Phases:**
- [x] Phase 1: Setup & Planning (5/5 tasks)
- [ ] Phase 2: Build - Backend (0/8 tasks) ← Starting here
- [ ] Phase 3: Build - Frontend (0/8 tasks)
- [ ] Phase 4: Validate & Ship (0/18 tasks)

**Ready to implement Phase 2?**
```

**Wait for user confirmation before proceeding.**

If user says:
- "yes/go/proceed" → Continue to Step 3
- "start from phase X" → Jump to that phase
- "show me X" → Show details from plan/spec
- "cancel/stop" → Exit gracefully

## Step 3: Execute Phases (Iterative)

For each incomplete phase, follow this workflow:

### 3.1 Phase Preview

Show the user what will be done:

```
🚀 Starting: Phase [N]: [Phase Name]

**Tasks to complete:**
- [ ] [Task 1]
- [ ] [Task 2]
...

**Key context from spec.md:**
[Relevant section from spec that applies to this phase]

**Proceeding with implementation...**
```

### 3.2 Implementation

**Use TodoWrite** to track sub-tasks for this phase internally (for your own tracking during implementation).

**Implementation guidance by phase:**

**Phase 1 (Setup & Planning):**
- Create git branch if not exists
- Install/verify dependencies
- Create initial file/folder structure
- Review plan/spec for any blockers

**Phase 2 (Build - Backend):**
- Reference spec.md "Data" and "Backend Components" sections
- Create database migrations/schema
- Create models/entities with relationships
- Implement validation rules
- Implement business logic
- Create API endpoints/routes
- Implement auth/authorization
- Write unit tests for critical logic
- Run tests to verify

**Phase 3 (Build - Frontend):**
- Reference spec.md "Frontend Components" section
- Create UI pages/screens
- Build reusable components
- Implement forms and validation
- Connect to backend APIs
- Handle loading/error/empty states
- Implement responsive design
- Test UI flows manually

**Phase 4 (Validate & Ship):**
- Run comprehensive testing (unit, integration, E2E)
- Test on different browsers/devices
- Test accessibility
- Run code formatters and linters
- Create pull request (if deployment phase reached)
- Follow deployment checklist

**During implementation:**
- Check plan.md and spec.md frequently for guidance
- Follow existing codebase conventions
- Write tests as you go
- Run tests after significant changes
- Ask user for clarification if spec is unclear
- Document key decisions in code comments

### 3.3 Run Tests

After completing implementation for the phase:
- Run relevant automated tests (unit, integration, feature tests)
- Report test results (passed/failed)
- If tests fail: Show errors and ask user how to proceed

### 3.4 Phase Summary

Show a clear summary of what was accomplished:

```
✅ Phase [N] Complete: [Phase Name]

**Files Changed:**
- Created: [list new files]
- Modified: [list changed files]
- Deleted: [list removed files]

**Tests:**
- ✅ [X] tests passed
- ❌ [Y] tests failed (if any)

**Key Changes:**
- [Bullet point summary of major changes]
- [What was implemented]
- [Any important decisions made]

**Test Results:** [Pass/Fail with details]

---

**Ready to mark this phase complete?**
Type "yes" to continue, "retry" to re-run this phase, or "pause" to stop here.
```

### 3.5 User Verification

**CRITICAL: Wait for user response before proceeding.**

**If user says "yes":**
- Proceed to Step 3.6 (mark complete)

**If user says "retry":**
- Ask what needs to be fixed
- Re-implement the phase
- Return to Step 3.3 (run tests)

**If user says "pause/stop":**
- Show current progress
- Exit gracefully with instructions to resume later

**If user says "show me X":**
- Show relevant files or test output
- Return to waiting for verification

**If tests failed:**
- Don't automatically mark complete
- Show errors clearly
- Ask: "Tests failed. Would you like me to: (a) fix the errors, (b) skip for now, or (c) pause?"

### 3.6 Mark Phase Complete

Use the Edit tool to update task.md:
- Find all `- [ ]` checkboxes in the current phase
- Replace with `- [x]` to mark complete
- Preserve exact formatting and indentation

After updating:
```
📝 Updated task.md - Phase [N] marked complete

**Progress:** [X]% complete ([completed]/[total] tasks)
```

### 3.7 Continue to Next Phase

If there are more phases:
- Brief pause, then show next phase preview (return to Step 3.1)

If all phases complete:
- Proceed to Step 4 (Completion)

## Step 4: Implementation Complete

Show final summary:

```
🎉 Implementation Complete: [Feature Name]

**Summary:**
- All [N] phases completed
- [X] files created/modified
- [Y] tests passing
- Git branch: [branch name]

**Files Changed:**
[Comprehensive list of all files touched during implementation]

**Next Steps:**
1. Review all changes manually
2. Run full test suite: `[test command]`
3. Test feature manually in browser/app
4. Create pull request when ready
5. Deploy to staging for QA

**Commands:**
- Run tests: `[appropriate test command]`
- Start dev server: `[appropriate dev command]`
- Format code: `[appropriate format command]`

---

Great work! The feature is implemented and ready for review. 🚀
```

## Step 5: Error Handling

### If plan folder not found:
- List available plan folders in `plan/` directory
- Ask user to specify which one to implement

### If files are missing:
- Report which files are missing (plan.md, spec.md, or task.md)
- Ask user if they want to continue anyway (risky) or cancel

### If spec is unclear or incomplete:
- Stop implementation at that point
- Show the unclear section from spec.md
- Ask user for clarification
- Update spec.md with clarification (if user provides it)
- Continue implementation

### If implementation fails (errors, can't proceed):
- Show the error clearly
- Show relevant context (what you were trying to do)
- Ask user: "Would you like me to: (a) try a different approach, (b) skip this task, or (c) pause for manual intervention?"

### If tests fail:
- Show test output clearly
- Don't mark phase complete automatically
- Analyze failures and attempt to fix
- If can't fix: Ask user for guidance

### If user needs to pause:
- Show exactly where you stopped (phase, task)
- Show current progress percentage
- Explain they can resume by running `/implement [folder]` again

## Important Guidelines

### Context Awareness:
- Keep plan.md, spec.md, and task.md context in mind throughout
- Re-read relevant sections when implementing each phase
- Reference spec.md for technical decisions
- Stay true to the MVP scope defined in plan.md

### Code Quality:
- Follow existing codebase conventions (check sibling files)
- Write clean, readable code
- Add comments for complex logic
- Write tests alongside implementation
- Run formatters and linters

### Testing:
- Write tests as you implement (don't save for later)
- Run tests frequently during implementation
- Don't mark phase complete if tests are failing
- Test both happy paths and error cases

### User Communication:
- Be concise in summaries - focus on what matters
- Show clear progress indicators
- Always wait for approval before marking complete
- If stuck, ask for help rather than guessing

### Safety:
- Never skip phases without user approval
- Never mark incomplete work as complete
- Never proceed with failing tests without user acknowledgment
- Always create git branch before making changes

### Efficiency:
- Implement multiple related tasks together where logical
- Don't ask for approval on every tiny change
- Use TodoWrite internally to track sub-tasks without bothering user
- Only pause for user approval at phase boundaries

---

**Remember:** You're implementing a planned feature phase by phase. Stay focused on the plan, implement thoroughly, test rigorously, and get user approval at each phase boundary. Ship working, tested code. 🚀
