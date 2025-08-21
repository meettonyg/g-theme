# WordPress Template Debugging for Pods Custom Posts

## 🔍 How to Find Out What's Happening

### Step 1: Check the Actual Post Type Name
The debug info in `single.php` will now show you:
- The exact post type name Pods is using
- What template file is actually being loaded
- The URL structure

Visit `/guests/bob-diamond/` and look for the yellow debug box.

### Step 2: Understanding the Template Hierarchy

For a custom post type called `guest`, WordPress looks for templates in this order:
1. `single-guest-bob-diamond.php` (specific to this post slug)
2. `single-guest.php` (for all 'guest' posts)
3. `single.php` (for all single posts)
4. `singular.php` (for all singular content)
5. `index.php` (fallback)

### Step 3: Common Issues with Pods

#### Issue 1: Pods Template Override
Pods might be forcing its own template. Check:
- **Pods Admin** → **Edit Pod** → **guest** (or your post type)
- Look for **"Auto Template"** or **"Single Template"** settings
- Set to **"-- Select One --"** or **"Disable"**

#### Issue 2: Wrong Post Type Name
The post type might not be `guest` or `guests`. Check in Pods Admin:
- What's the exact **Pod Name**?
- Is it singular or plural?
- Is there a custom slug?

#### Issue 3: Pods Page/Post Association
Check if Pods is associating these with a WordPress Page:
- Go to **Pages** in WordPress Admin
- Look for a page called "Guests"
- Check if it has a special template selected

### Step 4: Force Template Assignment (Pods Method)

In Pods Admin for your guest post type:
1. Go to **Pods Admin** → **Edit Pod** → **[Your Pod Name]**
2. Click on **"Advanced Options"** tab
3. Look for **"Associated Post Type"** section
4. Check **"Single Template"** - set it to **"-- Select One --"**
5. Save Pod

### Step 5: Manual Template Override in functions.php

```php
// Force specific template for Pods posts
add_filter('single_template', function($template) {
    global $post;
    
    // Get the actual post type - update this based on debug info!
    $post_type = get_post_type($post);
    
    // Debug - remove later
    if (strpos($_SERVER['REQUEST_URI'], '/guests/') === 0) {
        error_log('Post type detected: ' . $post_type);
    }
    
    // Replace 'YOUR_ACTUAL_POST_TYPE' with what the debug shows
    if ($post_type === 'YOUR_ACTUAL_POST_TYPE') {
        $new_template = locate_template(array('single-guest-no-header.php'));
        if (!empty($new_template)) {
            return $new_template;
        }
    }
    
    return $template;
}, 99);
```

### Step 6: Check Pods Settings

1. **In WordPress Admin**, go to **Pods Admin** → **Settings**
2. Check if **"Templates"** component is enabled
3. Check if **"Pods Pages"** component is enabled
4. These might be overriding normal template behavior

### Step 7: Direct Pods Template Control

```php
// Add to functions.php to bypass Pods template selection
add_filter('pods_templates_auto_template_path', function($template, $pod_name) {
    if ($pod_name === 'guest' || $pod_name === 'guests') {
        // Return false to disable Pods auto-template
        return false;
    }
    return $template;
}, 10, 2);
```

## 🎯 What You Need to Do Now:

1. **Visit** `/guests/bob-diamond/`
2. **Look** for the yellow debug box
3. **Tell me**:
   - What does "Post Type:" show?
   - What does "Template File:" show?
   - Is it `single.php` or something else?

4. **Check Pods Admin**:
   - Go to **Pods Admin** → **Pods**
   - What's the exact name of your Pod?
   - Click edit on it - are there any template settings?

## 📝 Quick Test

Create this simple file to test if templates work at all:

**single-post.php** (for regular blog posts)
```php
<?php
// Test if custom templates work
echo '<h1>This is the custom single-post.php template</h1>';
the_content();
```

If this works for regular blog posts but not for guests, then Pods is overriding templates.

## 🔧 Nuclear Option - Force It

If all else fails, add this to `functions.php`:

```php
// Nuclear option - force template for /guests/ URLs
add_action('template_redirect', function() {
    if (strpos($_SERVER['REQUEST_URI'], '/guests/') === 0) {
        // Load our custom template and exit
        include(get_template_directory() . '/single-guest-no-header.php');
        exit;
    }
});
```

---

**The key is finding out:**
1. The exact post type name Pods is using
2. Whether Pods has template settings that override WordPress
3. What template file is actually being loaded

Once we know these, we can properly map the template!