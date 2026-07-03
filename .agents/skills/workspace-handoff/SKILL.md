---
name: workspace-handoff
description: "Use when you need to leave a compact handoff for continuing the same work from another computer or later session. Covers the current objective, completed work, files changed, commands run, blockers, and the next concrete steps."
---

# Workspace Handoff

Use this skill to write or update a short handoff note so another session can continue without rebuilding context.

## Purpose

Capture only the information needed to resume work quickly:

- what we are building
- what is already done
- what remains
- where the important files are
- what was verified
- what still needs attention

## Handoff Structure

Keep the note in this order:

1. `Objective`
2. `Current state`
3. `Completed`
4. `Files touched`
5. `Commands / tests`
6. `Blockers`
7. `Next steps`
8. `Notes for another computer`

## What To Include

- Use absolute file paths for any referenced files.
- Mention the branch or working context if it matters.
- List only the commands that were actually run and their outcome.
- Call out uncommitted changes or files that still need review.
- Record environment requirements if the work depends on them.
- Be explicit about anything that is incomplete, risky, or waiting on the user.

## What To Avoid

- Long explanations.
- Duplicate status updates.
- Background context that does not help the next session continue.
- Full logs unless a specific error is the blocker.

## Suggested Template

```markdown
# Handoff

## Objective
Short description of the goal.

## Current state
One or two sentences on where things stand now.

## Completed
- Item 1
- Item 2

## Files touched
- `/absolute/path/to/file`

## Commands / tests
- `command` -> result

## Blockers
- None
  or
- Specific blocker and why it matters

## Next steps
1. Concrete next action
2. Concrete follow-up action

## Notes for another computer
- Any setup, environment, or UI steps needed to continue
```

## Quality Check

Before finishing the handoff, confirm that someone else could:

- understand the goal in under a minute
- see exactly what changed
- know what to do next
- continue without guessing
