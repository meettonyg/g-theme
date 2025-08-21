<?php
/**
 * Single template for guest custom post type (Pods) - singular version
 * 
 * @package Guestify
 */

// Always use no header/footer for guest post type
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class('single-guest-pods no-header-footer'); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
    <div id="content" class="site-content">
        <main id="main" class="site-main">
            <?php
            while (have_posts()) :
                the_post();
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <div class="entry-content">
                        <?php the_content(); ?>
                    </div>
                </article>
            <?php endwhile; ?>
        </main>
    </div>
</div>

<?php wp_footer(); ?>
</body>
</html>