# Guestify Theme - Header/Footer Removal for Pods Custom Posts

## ✅ Current Active Solution

### Files Currently in Use:
1. **functions.php** - Contains CSS injection and template detection
2. **single.php** - Modified to detect guests custom posts
3. **single-guest.php** - Template for 'guest' post type
4. **single-guests.php** - Template for 'guests' post type
5. **single-guests-no-header.php** - Fallback template

### How It Works:
1. **CSS Injection** (Primary Method)
   - Triggers on any URL starting with `/guests/`
   - Hides all header/footer elements via CSS
   - Works regardless of template or post type

2. **Template Detection** (Backup Method)
   - Modified `single.php` checks if it's a guests post
   - Specific templates for guest/guests post types
   - No header/footer included in these templates

### Backup Files (Not Active):
Files prefixed with `_backup_` are page templates from earlier attempts.
These were for regular WordPress pages, not Pods custom posts.
- `_backup_page-guests.php`
- `_backup_template-guests.php`
- `_backup_template-guests-debug.php`
- `_backup_template-test-no-header.php`

You can safely delete these backup files if the solution is working.

## 🔧 Testing
1. Visit any `/guests/` URL
2. Check page source for `<style id="guestify-hide-header-footer">`
3. Header and footer should be hidden

## 🚀 To Restore Backups
If needed, remove `_backup_` prefix from any file to restore it.

## 📝 Important Functions in functions.php:
- `guestify_remove_header_footer_for_guests()` - CSS injection
- `guestify_force_guests_template()` - Template routing
- Both should remain active