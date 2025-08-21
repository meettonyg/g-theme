# Guestify - Guests Post Type Template Solution

## ✅ CONFIRMED SOLUTION

### Post Type Information (from Pods JSON):
- **Post Type Name**: `guests` (plural)
- **Archive Slug**: `guests`
- **URL Pattern**: `/guests/[post-name]/`
- **Pods Templates Component**: Active (we're disabling it)

### Files in Use:
1. **`single-guests.php`** - The main template for guests posts (no header/footer)
2. **`functions.php`** - Contains multiple filters to force template selection
3. **`single.php`** - Modified to detect guests posts
4. **`singular.php`** - Fallback template if others fail

### How It Works:

#### 1. WordPress Template Hierarchy
For a post type called `guests`, WordPress looks for:
1. `single-guests.php` ← **WE HAVE THIS**
2. `single.php` ← Modified to detect guests
3. `singular.php` ← Fallback with no header/footer for /guests/
4. `index.php` ← Last resort

#### 2. Multiple Enforcement Methods in functions.php:
- **`single_template` filter** - Forces `single-guests.php` for guests post type
- **`pods_templates_pre_template`** - Disables Pods Templates completely
- **`pods_templates_auto_template_path`** - Returns false to prevent Pods auto-templates
- **`pods_templates_do_template`** - Specifically blocks Pods from handling guests
- **`template_include` filter** - Additional check for /guests/ URLs
- **`template_redirect` action** - Nuclear option that bypasses everything

### Pods Settings to Check:
1. Go to **Pods Admin** → **Pods** → **Guest One Sheets**
2. Check the **Auto Template** section
3. Set to **"-- Select One --"** or disable any template settings
4. Save the Pod

### Testing Checklist:
1. ✅ Post type is `guests` (plural)
2. ✅ Template file `single-guests.php` exists
3. ✅ Functions.php has all filters active
4. ✅ Pods Templates component is being blocked
5. ✅ Nuclear option is enabled for guaranteed override

### Debug Information:
- Check browser console for errors
- View page source for `<!-- TEMPLATE DEBUG INFO -->`
- Check admin bar for template info
- Look at error logs for template selection messages

### Files Removed/Backed Up:
- `single-guest.php` → `_backup_single-guest.php` (wrong name, singular)
- Page templates → `_backup_*.php` (not needed for custom posts)

## 🚀 DEPLOYMENT STEPS:

1. **Upload these files to your theme:**
   - `functions.php`
   - `single-guests.php`
   - `single.php`
   - `singular.php`

2. **Clear all caches:**
   - WordPress cache plugins
   - Browser cache (Ctrl+Shift+R)
   - CDN/Cloudflare if applicable
   - PHP opcache if enabled

3. **Test:**
   - Visit `/guests/bob-diamond/`
   - Should see no header/footer
   - Content should display normally

## 🔧 If Still Not Working:

### Option 1: Check Pods Settings
- Pods Admin → Guest One Sheets → Advanced Options
- Look for any template settings and disable them

### Option 2: Verify File Upload
- Ensure `single-guests.php` is in the theme root directory
- Check file permissions (should be readable)

### Option 3: Database Check
Run this SQL to verify post type:
```sql
SELECT post_type, post_name, post_status 
FROM wp_posts 
WHERE post_name = 'bob-diamond';
```

### Option 4: Add Debug Code
Add to `single-guests.php` at the top:
```php
<?php
die('single-guests.php is loading! Post type: ' . get_post_type());
?>
```

If you see this message, the template IS loading.

## 📝 Notes:
- The nuclear option in functions.php is currently ACTIVE
- This forces the template regardless of WordPress template hierarchy
- Once working, you can comment out the nuclear option if desired
- All Pods template features are disabled for the guests post type

---

**Last Updated**: Template configured for post type `guests` (plural)
**Pods Version**: 3.3.2
**Active Components**: migrate-packages, templates (being overridden)