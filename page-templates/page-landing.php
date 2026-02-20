<?php
/**
 * Template Name: Landing Page
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$post_id = get_the_ID();

/* Enqueue CSS for this template */
$css_path = get_stylesheet_directory() . '/assets/css/page-landing.css';
if ( file_exists( $css_path ) ) {
    wp_enqueue_style(
        'ab-page-landing',
        get_stylesheet_directory_uri() . '/assets/css/page-landing.css',
        array( 'abcontact-main' ),
        filemtime( $css_path )
    );
}

get_header();

/* Data from metabox */
$logo_id   = absint( get_post_meta( $post_id, 'landing_logo_id', true ) );
$image_id  = absint( get_post_meta( $post_id, 'landing_image_id', true ) );
$form_sc   = get_post_meta( $post_id, 'landing_form_shortcode', true );
$contact_text = get_post_meta( $post_id, 'landing_contact_text', true );

/* Colors (admin-controlled) */
$bg_color          = get_post_meta( $post_id, 'landing_bg_color', true );
$header_bg_color   = get_post_meta( $post_id, 'landing_header_bg_color', true );
$footer_bg_color   = get_post_meta( $post_id, 'landing_footer_bg_color', true );
$footer_text_color = get_post_meta( $post_id, 'landing_footer_text_color', true );

if ( ! $bg_color ) $bg_color = '#3650b7';
if ( ! $header_bg_color ) $header_bg_color = '#2740a5';
if ( ! $footer_bg_color ) $footer_bg_color = '#0f1b33';
if ( ! $footer_text_color ) $footer_text_color = '#ffffff';

$socials = array();
for ( $i = 1; $i <= 3; $i++ ) {
    $socials[] = array(
        'label' => get_post_meta( $post_id, "landing_social_{$i}_label", true ),
        'url'   => get_post_meta( $post_id, "landing_social_{$i}_url", true ),
        'icon'  => absint( get_post_meta( $post_id, "landing_social_{$i}_icon_id", true ) ),
    );
}

/* Repeater blocks */
$blocks = get_post_meta( $post_id, '_landing_blocks', true );
if ( ! is_array( $blocks ) ) {
    $blocks = array();
}
?>

<main class="landing"
  role="main"
  aria-label="<?php echo esc_attr( get_the_title( $post_id ) ); ?>"
  style="<?php
    echo esc_attr(
        '--landing-bg:' . $bg_color . ';' .
        '--landing-header-bg:' . $header_bg_color . ';' .
        '--landing-footer-bg:' . $footer_bg_color . ';' .
        '--landing-footer-text:' . $footer_text_color . ';'
    );
  ?>">

  <section class="landing__header-zone" aria-label="<?php echo esc_attr__( 'Header landing', 'theme-abcontact' ); ?>">
    <div class="landing__container container">

      <header class="landing__top" aria-label="<?php echo esc_attr__( 'Logo', 'theme-abcontact' ); ?>">
        <a class="landing__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr__( 'Home', 'theme-abcontact' ); ?>">
          <?php
          if ( $logo_id ) {
              echo wp_get_attachment_image( $logo_id, 'full', false, array( 'class' => 'landing__logo', 'alt' => '' ) );
          } elseif ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) {
              the_custom_logo();
          } else {
              echo '<span class="landing__logo-text">' . esc_html( get_bloginfo( 'name' ) ) . '</span>';
          }
          ?>
        </a>
      </header>

      <?php if ( $image_id ) : ?>
        <section class="landing__media" aria-label="<?php echo esc_attr__( 'Immagine', 'theme-abcontact' ); ?>">
          <?php
          echo wp_get_attachment_image(
              $image_id,
              'full',
              false,
              array(
                  'class' => 'landing__image',
                  'loading' => 'eager',
                  'decoding' => 'async',
                  'alt' => '',
              )
          );
          ?>
        </section>
      <?php endif; ?>

    </div>
  </section>

  <section class="landing__content-zone" aria-label="<?php echo esc_attr__( 'Contenuto landing', 'theme-abcontact' ); ?>">
    <div class="landing__container container">

      <section class="landing__intro" aria-label="<?php echo esc_attr__( 'Contenuto', 'theme-abcontact' ); ?>">
        <?php
        while ( have_posts() ) :
            the_post();
            the_content();
        endwhile;
        ?>
      </section>

      <?php if ( ! empty( $blocks ) ) : ?>
        <section class="landing__blocks" aria-label="<?php echo esc_attr__( 'Sezioni', 'theme-abcontact' ); ?>">
          <?php foreach ( $blocks as $b ) :
              $body  = isset( $b['body'] ) ? $b['body'] : '';
              $btn_label = isset( $b['button_label'] ) ? $b['button_label'] : '';
              $btn_url   = isset( $b['button_url'] ) ? $b['button_url'] : '';
              $btn_variant = isset( $b['button_variant'] ) ? $b['button_variant'] : 'primary';
              ?>
            <article class="landing-block">
              <?php if ( $body ) : ?>
                <div class="landing-block__body"><?php echo wp_kses_post( wpautop( $body ) ); ?></div>
              <?php endif; ?>

              <?php if ( $btn_url && $btn_label ) : ?>
                <div class="landing-block__actions">
                  <?php
                  get_template_part( 'template-parts/components/button', null, array(
                      'label'   => $btn_label,
                      'href'    => $btn_url,
                      'variant' => ( $btn_variant === 'ghost' ) ? 'ghost' : 'primary',
                      'size'    => 'lg',
                  ) );
                  ?>
                </div>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
        </section>
      <?php endif; ?>

      <?php if ( $form_sc ) : ?>
        <section class="landing__form" aria-label="<?php echo esc_attr__( 'Contatto', 'theme-abcontact' ); ?>">
          <?php echo do_shortcode( $form_sc ); ?>
        </section>
      <?php endif; ?>

    </div>
  </section>

  <section class="landing__footer-zone" aria-label="<?php echo esc_attr__( 'Footer landing', 'theme-abcontact' ); ?>">
    <div class="landing__footer-inner container">
      <div class="landing__social" role="list" aria-label="<?php echo esc_attr__( 'Social', 'theme-abcontact' ); ?>">
        <?php foreach ( $socials as $s ) :
            if ( empty( $s['url'] ) ) continue;
            $label = $s['label'] ? $s['label'] : __( 'Social', 'theme-abcontact' );
            ?>
          <a class="landing__social-link" role="listitem" href="<?php echo esc_url( $s['url'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $label ); ?>">
            <?php if ( ! empty( $s['icon'] ) ) : ?>
              <?php echo wp_get_attachment_image( (int) $s['icon'], 'thumbnail', false, array( 'class' => 'landing__social-icon', 'alt' => '' ) ); ?>
            <?php else : ?>
              <span class="landing__social-fallback" aria-hidden="true"><?php echo esc_html( mb_substr( $label, 0, 1 ) ); ?></span>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
      </div>

      <?php if ( $contact_text ) : ?>
        <div class="landing__contact"><?php echo wp_kses_post( wpautop( $contact_text ) ); ?></div>
      <?php endif; ?>
    </div>
  </section>

</main>

<?php
wp_footer();
?>
</body>
</html>