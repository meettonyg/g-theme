<?php
/**
 * Template for displaying single guests custom post type without header/footer
 * Works with Pods custom post types
 *
 * @package Guestify
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
    <style>
        /* Ensure no header/footer elements show */
        .site-header, #masthead,
        .site-footer, #colophon,
        .main-navigation,
        header.entry-header,
        footer.entry-footer,
        .nav-links,
        .post-navigation,
        .posts-navigation,
        .comment-navigation,
        .site-branding,
        .widget-area,
        #secondary {
            display: none !important;
        }
        
        /* Reset body and main content area */
        body {
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .site,
        .site-content,
        .content-area,
        .site-main {
            margin: 0 !important;
            padding: 0 !important;
            max-width: 100% !important;
        }
        
        /* Ensure content takes full width */
        .entry-content {
            margin: 0;
            padding: 0;
            max-width: 100%;
        }
    </style>
</head>

<body <?php body_class('single-guests no-header-footer'); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
    <div id="content" class="site-content">
        <div id="primary" class="content-area">
            <main id="main" class="site-main">

            <?php
            while (have_posts()) :
                the_post();
                ?>

                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <?php 
                    // For debugging - remove in production
                    if (isset($_GET['debug'])) {
                        echo '<div style="background: yellow; padding: 10px; margin: 10px;">';
                        echo 'Post Type: ' . get_post_type() . '<br>';
                        echo 'Template: ' . basename(get_page_template()) . '<br>';
                        echo 'URL: ' . $_SERVER['REQUEST_URI'];
                        echo '</div>';
                    }
                    ?>
                    
                    <div class="entry-content">
                        <?php
                        the_content();

                        wp_link_pages(
                            array(
                                'before' => '<div class="page-links">' . esc_html__('Pages:', 'guestify'),
                                'after'  => '</div>',
                            )
                        );
                        ?>
                    </div><!-- .entry-content -->
                </article><!-- #post-<?php the_ID(); ?> -->

                <?php
                // If comments are open or we have at least one comment, load up the comment template.
                if (comments_open() || get_comments_number()) :
                    comments_template();
                endif;

            endwhile; // End of the loop.
            ?>

            </main><!-- #main -->
        </div><!-- #primary -->
    </div><!-- #content -->
</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>