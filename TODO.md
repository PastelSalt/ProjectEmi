# TODO - Fix incorrect syntaxes

## Step 1: Identify syntax-broken files
- [x] Search PHP files for common syntax markers and broken blocks
- [x] Read candidate files (job-details.php, config/database.php, includes/header.php, api/update-theme.php)

## Step 2: Fix parse errors / incorrect syntax
- [x] Repair `job-details.php` malformed transaction/application blocks (stray braces/parentheses)
- [ ] Ensure consistent function usage (`AuthHelper::...` vs global functions) in the same file


## Step 3: Validate
- [ ] Re-run search to ensure no remaining malformed patterns in fixed files
- [ ] (Optional) run a local PHP lint / syntax check via CLI if available

## Step 4: Report
- [ ] Summarize changes made and files affected

