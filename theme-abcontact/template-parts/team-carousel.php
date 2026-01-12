<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $post;
$post_id = isset( $post->ID ) ? $post->ID : 0;
$raw = $post_id ? get_post_meta( $post_id, '_cs_team', true ) : '';
$items = array();
if ( $raw ) {
    $decoded = json_decode( $raw, true );
    if ( is_array( $decoded ) ) $items = $decoded;
}

if ( empty( $items ) ) {
    return;
}
?>

<section class="cs-team" aria-label="<?php esc_attr_e( 'Il nostro team', 'abcontact' ); ?>">
  <div class="cs-team-inner">
    <header class="cs-team-header">
      <h2 class="cs-team-title"><?php esc_html_e( 'Il nostro team', 'abcontact' ); ?></h2>
      <p class="cs-team-sub"><?php esc_html_e( 'Persone che ogni giorno contribuiscono al nostro lavoro', 'abcontact' ); ?></p>
    </header>
  </div>

  <div class="cs-team-viewport" aria-hidden="false">
    <div class="cs-team-track" role="list">
      <?php foreach ( $items as $i => $it ) :
          $name = isset( $it['name'] ) ? $it['name'] : '';
          $role = isset( $it['role'] ) ? $it['role'] : '';
          $place = isset( $it['place'] ) ? $it['place'] : '';
          $img_id = isset( $it['image_id'] ) ? intval( $it['image_id'] ) : 0;
      ?>
        <div class="cs-team-item" role="listitem" data-index="<?php echo esc_attr( $i ); ?>">
          <!-- photo positioned absolutely so scaling doesn't affect layout -->
          <div class="cs-team-photo-wrap" aria-hidden="false">
            <?php if ( $img_id ) : ?>
              <?php
                // medium_large for better resolution without full weight; change if needed
                echo wp_get_attachment_image( $img_id, 'medium_large', false, array(
                  'class' => 'cs-team-photo',
                  'loading' => 'lazy',
                  'alt' => esc_attr( $name )
                ) );
              ?>
            <?php else : ?>
              <div class="cs-team-photo-placeholder" aria-hidden="true"></div>
            <?php endif; ?>
          </div>

          <!-- compact card: fixed small padding, three lines -->
          <div class="cs-team-card" aria-hidden="false">
            <?php if ( $name ) : ?><div class="cs-team-name"><?php echo esc_html( $name ); ?></div><?php endif; ?>
            <?php if ( $role ) : ?><div class="cs-team-role"><?php echo esc_html( $role ); ?></div><?php endif; ?>
            <?php if ( $place ) : ?><div class="cs-team-place"><?php echo esc_html( $place ); ?></div><?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="cs-team-inner">
    <div class="cs-team-controls">
      <button class="cs-team-prev" aria-label="<?php esc_attr_e( 'Precedente', 'abcontact' ); ?>">‹</button>
      <button class="cs-team-next" aria-label="<?php esc_attr_e( 'Successivo', 'abcontact' ); ?>">›</button>
    </div>
  </div>
</section>