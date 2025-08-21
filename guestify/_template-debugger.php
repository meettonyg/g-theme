<?php
/**
 * Template Name: Debug Template Info
 * Description: Shows what template WordPress is trying to use
 */

// Add this to the very top of your single.php temporarily to debug
?>
<div style="background: yellow; border: 2px solid red; padding: 20px; margin: 20px; position: fixed; top: 50px; right: 10px; z-index: 99999; max-width: 400px;">
    <h3>🔍 Template Debug Info</h3>
    <p><strong>Post Type:</strong> <?php echo get_post_type(); ?></p>
    <p><strong>Post ID:</strong> <?php echo get_the_ID(); ?></p>
    <p><strong>Post Slug:</strong> <?php echo get_post_field('post_name'); ?></p>
    <p><strong>Current Template:</strong> <?php echo basename(get_page_template()); ?></p>
    <p><strong>Template Being Used:</strong> <?php global $template; echo basename($template); ?></p>
    <p><strong>Is Singular:</strong> <?php echo is_singular() ? 'Yes' : 'No'; ?></p>
    <p><strong>Is Single:</strong> <?php echo is_single() ? 'Yes' : 'No'; ?></p>
    <p><strong>URL:</strong> <?php echo $_SERVER['REQUEST_URI']; ?></p>
    <?php
    // Check what templates WordPress is looking for
    $post_type = get_post_type();
    $possible_templates = array(
        "single-{$post_type}.php",
        'single.php',
        'singular.php',
        'index.php'
    );
    ?>
    <p><strong>WordPress will look for:</strong></p>
    <ol>
        <?php foreach($possible_templates as $tpl): ?>
            <li><?php echo $tpl; ?> <?php echo file_exists(get_template_directory() . '/' . $tpl) ? '✅' : '❌'; ?></li>
        <?php endforeach; ?>
    </ol>
</div>