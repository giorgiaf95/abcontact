<?php
/**
 * Footer section component.
 *
 * Modular footer component with 3 columns, widgets, and footer bottom.
 * Can be included via get_template_part('parts/footer') or used by footer.php.
 *
 * @package theme-abcontact
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<footer id="colophon" class="site-footer" role="contentinfo">
    <div class="footer-widgets container" aria-label="<?php esc_attr_e( 'Footer widgets', 'theme-abcontact' ); ?>">
        <?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
            <?php dynamic_sidebar( 'footer-1' ); ?>
        <?php else : ?>
            <div class="footer-col footer-col-1">
                <?php
                // Logo footer
                $footer_logo    = get_theme_mod( 'footer_logo' );
                $custom_logo_id = (int) get_theme_mod( 'custom_logo' );

                if ( $footer_logo ) {
                    // If numeric ID
                    if ( is_numeric( $footer_logo ) && (int) $footer_logo ) {
                        echo '<div class="site-footer__logo">';
                        echo wp_get_attachment_image( (int) $footer_logo, 'full', false, array(
                            'class'   => 'footer-logo',
                            'alt'     => get_bloginfo( 'name' ),
                            'loading' => 'lazy',
                        ) );
                        echo '</div>';
                    } else {
                        echo '<div class="site-footer__logo">';
                        echo '<img src="' . esc_url( $footer_logo ) . '" class="footer-logo" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '" loading="lazy" />';
                        echo '</div>';
                    }
                } elseif ( $custom_logo_id ) {
                    echo '<div class="site-footer__logo">';
                    echo wp_get_attachment_image( $custom_logo_id, 'full', false, array(
                        'class'   => 'footer-logo custom-logo',
                        'alt'     => get_bloginfo( 'name' ),
                        'loading' => 'lazy',
                    ) );
                    echo '</div>';
                } else {
                    $asset_url = get_stylesheet_directory_uri() . '/assets/img/ab-contact-footer.png';
                    echo '<div class="site-footer__logo">';
                    echo '<img src="' . esc_url( $asset_url ) . '" class="footer-logo" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '" loading="lazy" />';
                    echo '</div>';
                }

                // About text
                $about = get_theme_mod( 'footer_about_text', '' );
                if ( $about ) {
                    echo '<div class="footer-about">' . wp_kses_post( wpautop( $about ) ) . '</div>';
                } else {
                    echo '<h3 class="widget-title">' . esc_html__( 'About', 'theme-abcontact' ) . '</h3>';
                    echo '<p>' . esc_html__( 'Testo breve con slogan o call to action inerente ai nostri servizi e/o obiettivi', 'theme-abcontact' ) . '</p>';
                }

                // Social icons
                $social_html = '';
                for ( $i = 1; $i <= 4; $i++ ) {
                    $img_val = get_theme_mod( "footer_social_{$i}_image", '' );
                    $url     = get_theme_mod( "footer_social_{$i}_url", '' );

                    if ( $img_val ) {
                        $src = '';

                        if ( is_numeric( $img_val ) && (int) $img_val ) {
                            $src = wp_get_attachment_image_url( (int) $img_val, 'thumbnail' );
                        } else {
                            // sanitize URL for output
                            $src = esc_url( $img_val );
                        }

                        if ( $src ) {
                            $a  = '<a class="footer-social-link" href="' . esc_url( $url ? $url : '#' ) . '" target="_blank" rel="noopener noreferrer">';
                            $a .= '<img src="' . esc_url( $src ) . '" alt="" loading="lazy" />';
                            $a .= '</a>';
                            $social_html .= $a;
                        }
                    }
                }
                if ( $social_html ) {
                    echo '<div class="footer-social">' . wp_kses_post( $social_html ) . '</div>';
                }
                ?>
            </div>
        <?php endif; ?>

        <?php if ( is_active_sidebar( 'footer-2' ) ) : ?>
            <?php dynamic_sidebar( 'footer-2' ); ?>
        <?php else : ?>
            <div class="footer-col footer-col-2">
                <h3 class="widget-title"><?php esc_html_e( 'Link Rapidi', 'theme-abcontact' ); ?></h3>
                <?php
                if ( has_nav_menu( 'footer' ) ) {
                    wp_nav_menu( array(
                        'theme_location' => 'footer',
                        'menu_class'     => 'footer-menu',
                        'container'      => false,
                    ) );
                } else {
                    echo '<ul class="footer-menu"><li><a href="#">' . esc_html__( 'Voce link 01', 'theme-abcontact' ) . '</a></li></ul>';
                }
                ?>
            </div>
        <?php endif; ?>

        <?php if ( is_active_sidebar( 'footer-3' ) ) : ?>
            <?php dynamic_sidebar( 'footer-3' ); ?>
        <?php else : ?>
            <div class="footer-col footer-col-3">
                <h3 class="widget-title"><?php esc_html_e( 'Contatti', 'theme-abcontact' ); ?></h3>
                <address>
                    <?php
                    $address = get_theme_mod( 'footer_address', '' );
                    $phone   = get_theme_mod( 'footer_phone', '' );
                    $email   = get_theme_mod( 'footer_email', '' );

                    if ( $address ) {
                        echo '<p class="footer-address">' . esc_html( $address ) . '</p>';
                    } else {
                        echo '<p>' . esc_html__( 'Indirizzo, città', 'theme-abcontact' ) . '</p>';
                    }

                    if ( $phone ) {
                        echo '<p class="footer-phone"><a href="tel:' . esc_attr( preg_replace( '/\s+/', '', $phone ) ) . '">' . esc_html( $phone ) . '</a></p>';
                    } else {
                        echo '<p><a href="tel:+39000000000">+39 000 000 0000</a></p>';
                    }

                    if ( $email ) {
                        echo '<p class="footer-email"><a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a></p>';
                    } else {
                        echo '<p><a href="mailto:info@example.com">info@example.com</a></p>';
                    }
                    ?>
                </address>
            </div>
        <?php endif; ?>
    </div><!-- .footer-widgets -->

    <div class="footer-bottom">
        <div class="container footer-bottom-row">
            <div class="footer-copyright">
                <?php
                $copyright = get_theme_mod( 'footer_copyright', '' );
                if ( $copyright ) {
                    echo wp_kses_post( $copyright );
                } else {
                    echo '&copy; ' . date_i18n( _x( 'Y', 'copyright date format', 'theme-abcontact' ) ) . ' ' . get_bloginfo( 'name' ) . '. ' . esc_html__( 'Tutti i diritti riservati.', 'theme-abcontact' );
                }
                ?>
            </div>

            <div class="footer-actions">
                <?php
                // small footer menu if registered (optional)
                if ( has_nav_menu( 'footer_services' ) ) {
                    wp_nav_menu( array(
                        'theme_location' => 'footer_services',
                        'menu_class'     => 'footer-bottom-menu',
                        'container'      => false,
                    ) );
                }
                ?>
                <button id="back-to-top" class="back-to-top" aria-label="<?php esc_attr_e( 'Torna su', 'theme-abcontact' ); ?>">↑</button>
            </div>
        </div>
    </div><!-- .footer-bottom -->
</footer><!-- #colophon -->
