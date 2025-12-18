<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Guestify
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'guestify' ); ?></a>

	<?php if ( is_blank_canvas_page() ) : ?>
		<!-- Blank Canvas - No Header -->
	<?php elseif ( is_app_page() ) : ?>
		<!-- App Header -->
		<?php get_template_part( 'template-parts/app-navigation' ); ?>
	<?php else : ?>
		<!-- Clean Front-End Header -->
		<header id="masthead" class="site-header frontend-header">
			<div class="frontend-header__container">
				<div class="frontend-header__logo">
					<?php
					if ( has_custom_logo() ) :
						the_custom_logo();
					else :
						?>
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="frontend-header__site-title" rel="home">
							<?php bloginfo( 'name' ); ?>
						</a>
					<?php endif; ?>
				</div><!-- .frontend-header__logo -->

				<nav id="site-navigation" class="frontend-header__nav">
					<button class="frontend-header__menu-toggle" aria-controls="frontend-menu" aria-expanded="false">
						<span class="frontend-header__menu-icon"></span>
						<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'guestify' ); ?></span>
					</button>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'menu-1',
							'menu_id'        => 'frontend-menu',
							'menu_class'     => 'frontend-header__menu',
							'container'      => false,
							'depth'          => 2,
							'fallback_cb'    => false,
						)
					);
					?>
				</nav><!-- #site-navigation -->
			</div><!-- .frontend-header__container -->
		</header><!-- #masthead -->
	<?php endif; ?>
