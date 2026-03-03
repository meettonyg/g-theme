<?php
/**
* The template for displaying the footer
*
* Contains the closing of the #content div and all content after.
*
* @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
*
* @package Guestify
*/

?>

<?php if ( !is_app_page() && !is_blank_canvas_page() ) : ?>
<!-- Frontend Footer -->
<footer id="colophon" class="gfy-footer">
	<div class="gfy-footer__inner">
		<div class="gfy-footer__brand">
			<?php if ( has_custom_logo() ) : ?>
				<div class="gfy-footer__logo"><?php the_custom_logo(); ?></div>
			<?php else : ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="gfy-footer__site-title"><?php bloginfo( 'name' ); ?></a>
			<?php endif; ?>
			<p class="gfy-footer__tagline">Interview Authority System<br>Authority Is Recognized Trust. Interviews Transfer It Fastest.</p>
		</div>

		<nav class="gfy-footer__nav" aria-label="<?php esc_attr_e( 'Product', 'guestify' ); ?>">
			<h4 class="gfy-footer__nav-title"><?php esc_html_e( 'Product', 'guestify' ); ?></h4>
			<ul>
				<li><a href="<?php echo esc_url( home_url( '/product/' ) ); ?>">Overview</a></li>
				<li><a href="<?php echo esc_url( home_url( '/product/podcast-discovery/' ) ); ?>">Podcast Discovery</a></li>
				<li><a href="<?php echo esc_url( home_url( '/product/authority-positioning/' ) ); ?>">Authority Positioning</a></li>
				<li><a href="<?php echo esc_url( home_url( '/product/outreach-booking/' ) ); ?>">Outreach &amp; Booking</a></li>
				<li><a href="<?php echo esc_url( home_url( '/product/interview-tracking/' ) ); ?>">Interview Tracking</a></li>
				<li><a href="<?php echo esc_url( home_url( '/product/relationship-management/' ) ); ?>">Relationship Management</a></li>
			</ul>
		</nav>

		<nav class="gfy-footer__nav" aria-label="<?php esc_attr_e( 'Solutions', 'guestify' ); ?>">
			<h4 class="gfy-footer__nav-title"><?php esc_html_e( 'Solutions', 'guestify' ); ?></h4>
			<ul>
				<li><a href="<?php echo esc_url( home_url( '/for/experts-consultants/' ) ); ?>">Experts &amp; Consultants</a></li>
				<li><a href="<?php echo esc_url( home_url( '/for/business-owners/' ) ); ?>">Business Owners</a></li>
				<li><a href="<?php echo esc_url( home_url( '/for/authors-creators/' ) ); ?>">Authors &amp; Creators</a></li>
				<li><a href="<?php echo esc_url( home_url( '/for/agencies/' ) ); ?>">Agencies</a></li>
			</ul>
		</nav>

		<nav class="gfy-footer__nav" aria-label="<?php esc_attr_e( 'Resources', 'guestify' ); ?>">
			<h4 class="gfy-footer__nav-title"><?php esc_html_e( 'Resources', 'guestify' ); ?></h4>
			<ul>
				<li><a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">Blog</a></li>
				<li><a href="<?php echo esc_url( home_url( '/results/' ) ); ?>">Case Studies</a></li>
				<li><a href="<?php echo esc_url( home_url( '/resources/' ) ); ?>">Resources</a></li>
				<li><a href="<?php echo esc_url( home_url( '/webinar/' ) ); ?>">Webinar</a></li>
			</ul>
		</nav>

		<nav class="gfy-footer__nav" aria-label="<?php esc_attr_e( 'Company', 'guestify' ); ?>">
			<h4 class="gfy-footer__nav-title"><?php esc_html_e( 'Company', 'guestify' ); ?></h4>
			<ul>
				<li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About</a></li>
				<li><a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>">Pricing</a></li>
				<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a></li>
				<li><a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>">Privacy Policy</a></li>
				<li><a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>">Terms of Service</a></li>
			</ul>
		</nav>
	</div>

	<div class="gfy-footer__bottom">
		<p class="gfy-footer__copyright">&copy; <?php echo esc_html( date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. All rights reserved.</p>
	</div>
</footer>
<?php endif; ?>
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
