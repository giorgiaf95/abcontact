<?php
/**
 * The header for our theme
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
	<header id="masthead" class="site-header" role="banner">
		<div class="container header-row">

			<div class="site-branding">
				<?php
				$logo_light_id = (int) get_theme_mod( 'header_logo_light', 0 );
				$logo_dark_id  = (int) get_theme_mod( 'header_logo_dark', 0 );

				// If both set, use the dual-logo switcher
				if ( $logo_light_id || $logo_dark_id ) :
				?>
					<a class="site-logo custom-logo-link" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
						<?php
						if ( $logo_light_id ) {
							echo wp_get_attachment_image(
								$logo_light_id,
								'full',
								false,
								array(
									'class' => 'site-logo-img site-logo-img--light',
									'alt'   => get_bloginfo( 'name' ),
								)
							);
						}

						if ( $logo_dark_id ) {
							echo wp_get_attachment_image(
								$logo_dark_id,
								'full',
								false,
								array(
									'class' => 'site-logo-img site-logo-img--dark',
									'alt'   => get_bloginfo( 'name' ),
								)
							);
						}
						?>
					</a>
				<?php
				else :
					// Fallback to default WP custom logo
					if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) {
						the_custom_logo();
					} else {
						if ( ! is_front_page() ) : ?>
							<a id="site-title" class="site-title" href="<?php echo esc_url( home_url( '/' ) ); ?>">
								<?php bloginfo( 'name' ); ?>
							</a>
							<p class="site-description"><?php bloginfo( 'description' ); ?></p>
						<?php
						endif;
					}
				endif;
				?>
			</div>

			<button class="menu-toggle" id="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
				<span class="menu-icon" aria-hidden="true"></span>
				<span class="menu-label"><?php esc_html_e( 'Menu', 'theme-abcontact' ); ?></span>
			</button>

			<nav id="site-navigation" class="main-navigation" role="navigation" aria-label="<?php esc_attr_e( 'Primary Menu', 'theme-abcontact' ); ?>">
				<?php
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_id'        => 'primary-menu',
					'menu_class'     => 'primary-menu',
				) );
				?>
			</nav>

			<div class="header-cta">
				<?php
				$header_cta_label     = get_theme_mod( 'header_cta_label', __( 'Contattaci', 'theme-abcontact' ) );
				$header_cta_mode      = get_theme_mod( 'header_cta_mode', 'link' );
				$header_cta_link      = get_theme_mod( 'header_cta_link', home_url( '/contatti' ) );
				$header_cta_shortcode = get_theme_mod( 'header_cta_shortcode', '' );

				if ( $header_cta_mode === 'shortcode' && ! empty( $header_cta_shortcode ) ) {
					// Most compatible with popup plugins (GreenPopup etc.): shortcode prints its own trigger markup.
					echo '<div class="abcontact-header-cta-shortcode">';
					echo do_shortcode( $header_cta_shortcode );
					echo '</div>';
				} else {
					get_template_part( 'template-parts/components/button', null, array(
						'label'   => $header_cta_label,
						'href'    => $header_cta_link,
						'variant' => 'white',
						'size'    => 'md',
						'class'   => 'header-contact',
					) );
				}
				?>
			</div>

		</div>
	</header>

	<main id="content" class="site-main container" role="main">