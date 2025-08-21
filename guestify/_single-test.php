<?php
/**
 * This is a test - if this shows up, templates ARE being recognized
 * File: single-test.php - for testing if WordPress sees our templates
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Template Test</title>
    <style>
        body {
            margin: 0;
            padding: 40px;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            min-height: 100vh;
        }
        .success-box {
            background: rgba(255,255,255,0.2);
            border: 3px solid white;
            padding: 40px;
            border-radius: 10px;
            max-width: 800px;
            margin: 0 auto;
        }
        h1 { margin-top: 0; }
        .info { background: rgba(0,0,0,0.2); padding: 20px; border-radius: 5px; margin: 20px 0; }
        code { background: rgba(255,255,255,0.3); padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="success-box">
        <h1>✅ Template System is Working!</h1>
        <p>If you're seeing this page, WordPress IS loading custom templates correctly.</p>
        
        <div class="info">
            <h2>Debug Information:</h2>
            <p><strong>Current URL:</strong> <code><?php echo $_SERVER['REQUEST_URI']; ?></code></p>
            <p><strong>Post Type:</strong> <code><?php echo get_post_type(); ?></code></p>
            <p><strong>Post ID:</strong> <code><?php echo get_the_ID(); ?></code></p>
            <p><strong>Template File:</strong> <code><?php global $template; echo basename($template); ?></code></p>
        </div>
        
        <div class="info">
            <h2>What This Means:</h2>
            <ul>
                <li>✅ WordPress can find and load custom templates</li>
                <li>✅ The template hierarchy is working</li>
                <li>✅ Your theme folder is configured correctly</li>
            </ul>
        </div>
        
        <div class="info">
            <h2>Next Steps:</h2>
            <ol>
                <li>Check what post type name Pods is using (shown above)</li>
                <li>Create a file named <code>single-[POST-TYPE].php</code></li>
                <li>That template will automatically be used for that post type</li>
            </ol>
        </div>
        
        <div class="info" style="background: rgba(255,0,0,0.2);">
            <h2>⚠️ Important:</h2>
            <p>The post type shown above is: <strong><code><?php echo get_post_type(); ?></code></strong></p>
            <p>You need a template file named: <strong><code>single-<?php echo get_post_type(); ?>.php</code></strong></p>
        </div>
    </div>
</body>
</html>