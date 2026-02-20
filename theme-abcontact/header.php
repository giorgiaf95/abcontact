<?php
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
	<a class="skip-link sr-only" href="#content"><?php esc_html_e( 'Salta al contenuto', 'theme-abcontact' ); ?></a>

	<div id="page" class="site">
		<?php if ( is_active_sidebar( 'topbar-1' ) ) : ?>
			<div class="topbar">
				<div class="container">
					<?php dynamic_sidebar( 'topbar-1' ); ?>
				</div>
			</div>
		<?php endif; ?>

		<header id="site-header" class="site-header header" role="banner" aria-label="<?php bloginfo( 'name' ); ?>">
			<div class="container header-row">

				<div class="site-branding">
					<?php
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
					$header_cta_label = get_theme_mod( 'header_cta_label', __( 'Contattaci', 'theme-abcontact' ) );
					$header_cta_link  = get_theme_mod( 'header_cta_link', home_url( '/contatti' ) );

					get_template_part( 'template-parts/components/button', null, array(
						'label'   => $header_cta_label,
						'href'    => $header_cta_link,
						'variant' => 'white',
						'size'    => 'md',
						'class'   => 'header-contact',
					) );
					?>
				</div>

			</div>
		</header>

		<main id="content" class="site-main container" role="main">