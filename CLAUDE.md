# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Nette Code Checker is a command-line tool that checks and optionally repairs formal errors in source code. It's distributed as a standalone tool (not a library dependency) and validates multiple file formats including PHP, Latte templates, NEON, JSON, and YAML.

## Essential Commands

### Running Code Checker

```bash
# Check current directory (read-only mode)
php code-checker

# Check and fix files
php code-checker --fix

# Check specific directory
php code-checker -d path/to/check

# Check with strict_types validation
php code-checker --strict-types

# Normalize line endings
php code-checker --eol

# Syntax-only checks (faster)
php code-checker --only-syntax
```

### Development Commands

```bash
# Run all tests
composer run tester
# or
vendor/bin/tester tests -s -C

# Run single test file
vendor/bin/tester tests/Tasks.latteSyntaxChecker.phpt -s -C

# Run PHPStan static analysis
composer run phpstan
# or
phpstan analyse
```

## Architecture Overview

### Core Components

The codebase has a simple, task-based architecture:

1. **Checker** (`src/Checker.php`): Main orchestrator that:
   - Scans directories using file patterns
   - Processes each file through registered tasks
   - Handles read-only vs fix mode
   - Reports errors/warnings/fixes with color-coded output

2. **Tasks** (`src/Tasks.php`): Collection of static validation/fixing methods:
   - Each task is a static method with signature: `(string &$contents, Result $result): void`
   - Tasks can modify `$contents` (for fixers) or just add messages to `$result`
   - Tasks are pattern-matched to file types (e.g., `*.php`, `*.latte`)

3. **Result** (`src/Result.php`): Accumulates messages from tasks:
   - `error()`: Critical issues that prevent acceptance
   - `warning()`: Non-critical issues
   - `fix()`: Issues that were/can be automatically fixed

4. **bootstrap.php** (`src/bootstrap.php`): Entry point that:
   - Parses command-line arguments
   - Registers tasks based on options
   - Runs the checker
   - Returns appropriate exit codes

### Task Registration Pattern

Tasks are registered in `bootstrap.php` with optional file patterns:

```php
// Apply to all files
$checker->addTask([$tasks, 'controlCharactersChecker']);

// Apply only to specific file types
$checker->addTask([$tasks, 'phpSyntaxChecker'], '*.php,*.phpt');

// Conditional registration
if (isset($options['--strict-types'])) {
    $checker->addTask([$tasks, 'strictTypesDeclarationChecker'], '*.php,*.phpt');
}
```

### Task Implementation Patterns

**Checker tasks** (read-only validation):
```php
public static function taskName(string $contents, Result $result): void
{
    if (/* check fails */) {
        $result->error('Error message', $lineNumber);
    }
}
```

**Fixer tasks** (modify contents):
```php
public static function taskName(string &$contents, Result $result): void
{
    $new = /* transform contents */;
    if ($new !== $contents) {
        $result->fix('description of fix', $lineNumber);
        $contents = $new;
    }
}
```

### Latte Syntax Checker Integration

The `latteSyntaxChecker` task demonstrates integration with Nette ecosystem:
- Creates a full Latte engine with all standard extensions (UI, Forms, Cache, Assets)
- Compiles templates to PHP code
- Validates generated PHP using `phpSyntaxChecker`
- Downgrades "Unexpected tag" errors to warnings (for extensibility)

This is important when modifying: always ensure Latte checker has all necessary extensions registered.

## Testing Strategy

Tests use Nette Tester with `.phpt` files:

- Each task typically has its own test file: `Tasks.{taskName}.phpt`
- Use the `test()` function wrapper defined in `tests/bootstrap.php`
- Tests create `Result` objects and verify messages/fixes
- No test descriptions needed - the test file name indicates what's tested

Example test pattern:
```php
test(function () {
    $result = new Result;
    Tasks::someChecker('input content', $result);
    Assert::same([[Result::Error, 'Expected message', 1]], $result->getMessages());
});
```

## Key Implementation Details

### File Pattern Matching

The `Checker::matchFileName()` method supports:
- Comma-separated patterns: `*.php,*.phpt`
- Negative patterns with `!` prefix: `!*.sh`
- Case-insensitive matching via `FNM_CASEFOLD`

### Line Number Calculation

Tasks use `Tasks::offsetToLine()` to convert byte offsets to line numbers for error reporting. This is critical for accurate error messages.

### Read-Only vs Fix Mode

In read-only mode:
- Fix-type results are reported as "FOUND" instead of "FIX"
- Files are never modified
- Exit code reflects whether fixes are needed
- Used for CI/validation pipelines

### Token-Based PHP Analysis

Many PHP tasks use `token_get_all()` for parsing:
- Wrapped in `@` to suppress parse errors (checked separately)
- Allows pattern detection without full AST parsing
- Used for: phpDoc validation, escape sequence checking, tab detection

### Exit Codes

- `0`: Success (no errors)
- `1`: Errors found or fixes needed (in read-only mode)
- `2`: Exception/fatal error

## Dependencies

Core dependencies (all from Nette ecosystem):
- `nette/command-line`: CLI argument parsing
- `nette/utils`: String manipulation, file finding
- `latte/latte`: Template syntax validation
- `nette/neon`: NEON format validation
- `nette/application`, `nette/forms`, `nette/caching`, `nette/assets`: Latte extensions
