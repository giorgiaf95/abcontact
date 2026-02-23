<?php
/**
 * Template Name: Lavora con noi
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! defined( 'ABCONTACT_LC_POSITIONS_META_V2' ) ) {
    define( 'ABCONTACT_LC_POSITIONS_META_V2', '_ab_lc_positions_v2' );
}

$post_id = get_the_ID();

/* Enqueue page-specific CSS */
$css_path = get_stylesheet_directory() . '/assets/css/lavora-con-noi.css';
if ( file_exists( $css_path ) ) {
    wp_enqueue_style( 'ab-lavora-con-noi', get_stylesheet_directory_uri() . '/assets/css/lavora-con-noi.css', array( 'abcontact-main' ), filemtime( $css_path ) );
}

/* Enqueue popup JS */
$js_path = get_stylesheet_directory() . '/assets/js/lavora-con-noi.js';
if ( file_exists( $js_path ) ) {
    wp_enqueue_script( 'ab-lavora-con-noi', get_stylesheet_directory_uri() . '/assets/js/lavora-con-noi.js', array(), filemtime( $js_path ), true );
}

get_header();

/* ---------------------------
   FORM HANDLER: candidatura da popup
   - CV obbligatorio
   - GDPR obbligatorio
   - dati contatto obbligatori
   - invio email con allegato
   --------------------------- */
$apply_errors = array();
$apply_sent = false;

$selected_job_id = '';
$selected_job_title = '';

if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['lc_apply_nonce'] ) ) {

    if ( ! wp_verify_nonce( $_POST['lc_apply_nonce'], 'lc_apply_submit' ) ) {
        $apply_errors[] = __( 'Token di sicurezza non valido.', 'abcontact' );
    } else {
        $selected_job_id    = isset( $_POST['job_id'] ) ? sanitize_text_field( $_POST['job_id'] ) : '';
        $selected_job_title = isset( $_POST['job_title'] ) ? sanitize_text_field( $_POST['job_title'] ) : '';

        $first_name = isset( $_POST['first_name'] ) ? sanitize_text_field( $_POST['first_name'] ) : '';
        $last_name  = isset( $_POST['last_name'] ) ? sanitize_text_field( $_POST['last_name'] ) : '';
        $phone      = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';
        $email      = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
        $about      = isset( $_POST['about'] ) ? sanitize_textarea_field( $_POST['about'] ) : '';
        $gdpr       = isset( $_POST['gdpr_confirm'] ) ? true : false;

        if ( empty( $selected_job_id ) || empty( $selected_job_title ) ) {
            $apply_errors[] = __( 'Seleziona una posizione valida.', 'abcontact' );
        }
        if ( empty( $first_name ) ) {
            $apply_errors[] = __( 'Inserisci il nome.', 'abcontact' );
        }
        if ( empty( $last_name ) ) {
            $apply_errors[] = __( 'Inserisci il cognome.', 'abcontact' );
        }
        if ( empty( $phone ) ) {
            $apply_errors[] = __( 'Inserisci il numero di telefono.', 'abcontact' );
        }
        if ( empty( $email ) || ! is_email( $email ) ) {
            $apply_errors[] = __( 'Inserisci una email valida.', 'abcontact' );
        }
        if ( ! $gdpr ) {
            $apply_errors[] = __( 'Devi accettare il trattamento dei dati personali.', 'abcontact' );
        }

        // CV upload mandatory
        $cv_uploaded = ( isset( $_FILES['cv_file'] ) && ! empty( $_FILES['cv_file']['name'] ) && $_FILES['cv_file']['error'] !== UPLOAD_ERR_NO_FILE );
        if ( ! $cv_uploaded ) {
            $apply_errors[] = __( 'Devi caricare il tuo curriculum (CV).', 'abcontact' );
        }

        $attachment_path = '';
        $attachment_url = '';

        // Upload CV (wp_handle_upload)
        if ( $cv_uploaded ) {
            $file = $_FILES['cv_file'];
            require_once ABSPATH . 'wp-admin/includes/file.php';

            $overrides = array( 'test_form' => false );
            $move = wp_handle_upload( $file, $overrides );

            if ( isset( $move['error'] ) ) {
                $apply_errors[] = esc_html( $move['error'] );
            } else {
                $attachment_path = isset( $move['file'] ) ? $move['file'] : '';
                $attachment_url  = isset( $move['url'] ) ? $move['url'] : '';
            }
        }

        if ( empty( $apply_errors ) ) {
            $recipient = get_theme_mod( 'abcontact_recipient_email', '' );
            if ( ! is_email( $recipient ) ) {
                $recipient = get_option( 'admin_email' );
            }

            $subject = sprintf(
                '[Candidatura] %s %s - %s',
                $first_name,
                $last_name,
                $selected_job_title
            );

            $body_lines = array();
            $body_lines[] = sprintf( __( 'Posizione: %s', 'abcontact' ), $selected_job_title );
            $body_lines[] = sprintf( __( 'Job ID: %s', 'abcontact' ), $selected_job_id );
            $body_lines[] = sprintf( __( 'Nome: %s', 'abcontact' ), $first_name );
            $body_lines[] = sprintf( __( 'Cognome: %s', 'abcontact' ), $last_name );
            $body_lines[] = sprintf( __( 'Telefono: %s', 'abcontact' ), $phone );
            $body_lines[] = sprintf( __( 'Email: %s', 'abcontact' ), $email );
            if ( $about !== '' ) {
                $body_lines[] = '';
                $body_lines[] = __( 'Parlaci di te:', 'abcontact' );
                $body_lines[] = $about;
            }
            if ( $attachment_url ) {
                $body_lines[] = '';
                $body_lines[] = sprintf( __( 'CV (URL): %s', 'abcontact' ), $attachment_url );
            }
            $body_lines[] = '';
            $body_lines[] = __( 'Messaggio inviato dal popup Lavora con noi', 'abcontact' );
            $body = implode( "\n", $body_lines );

            $headers = array();
            $from_email = get_option( 'admin_email' );
            $from_name  = get_bloginfo( 'name' );
            if ( is_email( $from_email ) ) {
                $headers[] = 'From: ' . sanitize_text_field( $from_name ) . ' <' . $from_email . '>';
            }

            $reply_name = trim( $first_name . ' ' . $last_name );
            if ( is_email( $email ) ) {
                $headers[] = 'Reply-To: ' . sanitize_text_field( $reply_name ) . ' <' . $email . '>';
            }

            $attachments = array();
            if ( ! empty( $attachment_path ) && file_exists( $attachment_path ) ) {
                $attachments[] = $attachment_path;
            }

            $sent_mail = wp_mail( $recipient, $subject, $body, $headers, $attachments );

            if ( $sent_mail ) {
                wp_safe_redirect( add_query_arg(
                    array(
                        'apply_sent' => '1',
                        'job_id'     => rawurlencode( $selected_job_id ),
                    ),
                    get_permalink()
                ) );
                exit;
            } else {
                $apply_errors[] = __( 'Errore nell\'invio della email. Riprovare più tardi.', 'abcontact' );
            }
        }
    }
}

if ( isset( $_GET['apply_sent'] ) && $_GET['apply_sent'] === '1' ) {
    $apply_sent = true;
    $selected_job_id = isset( $_GET['job_id'] ) ? sanitize_text_field( wp_unslash( $_GET['job_id'] ) ) : '';
}

/* hero-default/news-hero */
set_query_var( 'args', array(
    'eyebrow' => __( 'Lavora con noi', 'abcontact' ),
    'title'   => get_the_title( $post_id ),
    'subtitle' => '',
    'bg_url'  => get_the_post_thumbnail_url( $post_id, 'full' )
) );
get_template_part( 'template-parts/news-hero' );
set_query_var( 'args', null );

/* “Perché scegliere Abcontact” – non toccare */
if ( locate_template( 'template-parts/lavora-why.php' ) ) {
    get_template_part( 'template-parts/lavora-why' );
}

/* Positions v2 */
$positions = get_post_meta( $post_id, ABCONTACT_LC_POSITIONS_META_V2, true );
$positions = is_array( $positions ) ? $positions : array();

function abcontact_lc_label_employment_type( $v ) {
    return $v === 'part_time' ? __( 'Part-time', 'abcontact' ) : __( 'Full-time', 'abcontact' );
}
?>

<section class="lc-positions-v2" aria-label="<?php esc_attr_e( 'Posizioni Aperte', 'abcontact' ); ?>">
  <div class="lc-positions-inner container">
    <header class="lc-positions-header">
      <h2 class="lc-positions-title"><?php esc_html_e( 'Posizioni aperte', 'abcontact' ); ?></h2>
      <p class="lc-positions-leadin"><?php esc_html_e( 'Scopri le opportunità di carriera disponibili e trova quella più adatta a te', 'abcontact' ); ?></p>
    </header>

    <?php if ( ! empty( $positions ) ) : ?>
      <div class="lc-job-list" role="list">
        <?php foreach ( $positions as $pos ) :
          if ( ! is_array( $pos ) ) continue;
          $id = isset( $pos['id'] ) ? sanitize_text_field( $pos['id'] ) : '';
          $title = isset( $pos['title'] ) ? sanitize_text_field( $pos['title'] ) : '';
          $category = isset( $pos['category'] ) ? sanitize_text_field( $pos['category'] ) : '';
          $location = isset( $pos['location'] ) ? sanitize_text_field( $pos['location'] ) : '';
          $employment_type = isset( $pos['employment_type'] ) ? sanitize_text_field( $pos['employment_type'] ) : 'full_time';
          $description = isset( $pos['description'] ) ? (string) $pos['description'] : '';
          $description = wp_strip_all_tags( $description );

          if ( $id === '' || $title === '' ) continue;
          $is_selected = ( $selected_job_id && $selected_job_id === $id );
          ?>
          <button
            type="button"
            class="lc-job-card"
            role="listitem"
            data-job-id="<?php echo esc_attr( $id ); ?>"
            data-job-title="<?php echo esc_attr( $title ); ?>"
            data-job-category="<?php echo esc_attr( $category ); ?>"
            data-job-location="<?php echo esc_attr( $location ); ?>"
            data-job-type="<?php echo esc_attr( $employment_type ); ?>"
            data-job-description="<?php echo esc_attr( $description ); ?>"
            aria-haspopup="dialog"
            aria-controls="lc-apply-modal"
            <?php echo $is_selected ? 'data-job-selected="1"' : ''; ?>
          >
            <div class="lc-job-card__content">
              <span class="lc-job-pill"><?php echo esc_html( $category ?: __( 'Posizione', 'abcontact' ) ); ?></span>
              <span class="lc-job-title"><?php echo esc_html( $title ); ?></span>

              <span class="lc-job-meta">
                <span class="lc-job-meta__item">
                  <svg class="lc-ico" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path fill="currentColor" d="M12 2c3.9 0 7 3.1 7 7c0 5.3-7 13-7 13S5 14.3 5 9c0-3.9 3.1-7 7-7zm0 9.2c1.2 0 2.2-1 2.2-2.2S13.2 6.8 12 6.8S9.8 7.8 9.8 9S10.8 11.2 12 11.2z"/>
                  </svg>
                  <span><?php echo esc_html( $location ?: '—' ); ?></span>
                </span>

                <span class="lc-job-meta__item">
                  <svg class="lc-ico" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path fill="currentColor" d="M12 1a11 11 0 1011 11A11.013 11.013 0 0012 1zm0 20a9 9 0 119-9 9.01 9.01 0 01-9 9zm.5-13H13v6l5 2-.5.87L12.5 13V8z"/>
                  </svg>
                  <span><?php echo esc_html( abcontact_lc_label_employment_type( $employment_type ) ); ?></span>
                </span>
              </span>
            </div>

            <div class="lc-job-card__arrow" aria-hidden="true">
              <svg width="26" height="26" viewBox="0 0 24 24" focusable="false">
                <path fill="currentColor" d="M13.17 12L8.22 7.05L9.64 5.64L16 12l-6.36 6.36l-1.42-1.41z"/>
              </svg>
            </div>
          </button>
        <?php endforeach; ?>
      </div>
    <?php else : ?>
      <div class="lc-empty">
        <p><?php esc_html_e( 'Al momento non ci sono posizioni aperte.', 'abcontact' ); ?></p>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- Modal -->
<div id="lc-apply-modal" class="lc-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="lc-modal-title">
  <div class="lc-modal__overlay" data-lc-close></div>
  <div class="lc-modal__panel" role="document">
    <button type="button" class="lc-modal__close" data-lc-close aria-label="<?php esc_attr_e( 'Chiudi', 'abcontact' ); ?>">
      <svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
        <path fill="currentColor" d="M18.3 5.71L12 12l6.3 6.29l-1.41 1.42L10.59 13.4L4.29 19.71L2.88 18.29L9.17 12L2.88 5.71L4.29 4.29l6.3 6.31l6.3-6.31z"/>
      </svg>
    </button>

    <div class="lc-modal__header">
      <span class="lc-modal__pill" id="lc-modal-category"></span>
      <h3 class="lc-modal__title" id="lc-modal-title"></h3>

      <div class="lc-modal__meta">
        <span class="lc-modal__meta-item">
          <svg class="lc-ico" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path fill="currentColor" d="M12 2c3.9 0 7 3.1 7 7c0 5.3-7 13-7 13S5 14.3 5 9c0-3.9 3.1-7 7-7zm0 9.2c1.2 0 2.2-1 2.2-2.2S13.2 6.8 12 6.8S9.8 7.8 9.8 9S10.8 11.2 12 11.2z"/>
          </svg>
          <span id="lc-modal-location"></span>
        </span>

        <span class="lc-modal__meta-item">
          <svg class="lc-ico" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path fill="currentColor" d="M12 1a11 11 0 1011 11A11.013 11.013 0 0012 1zm0 20a9 9 0 119-9 9.01 9.01 0 01-9 9zm.5-13H13v6l5 2-.5.87L12.5 13V8z"/>
          </svg>
          <span id="lc-modal-type"></span>
        </span>
      </div>
    </div>

    <div class="lc-modal__desc" id="lc-modal-description"></div>

    <div class="lc-modal__form">
      <h4 class="lc-modal__form-title"><?php esc_html_e( 'Candidati ora', 'abcontact' ); ?></h4>

      <?php if ( $apply_sent ) : ?>
        <div class="carica-success">
          <h2><?php esc_html_e( 'Candidatura inviata', 'abcontact' ); ?></h2>
          <p><?php esc_html_e( 'Grazie — ti ricontatteremo al più presto.', 'abcontact' ); ?></p>
        </div>
      <?php else : ?>

        <?php if ( ! empty( $apply_errors ) ) : ?>
          <div class="carica-errors" role="alert">
            <ul>
              <?php foreach ( $apply_errors as $e ) : ?>
                <li><?php echo esc_html( $e ); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form id="lc-apply-form" method="post" enctype="multipart/form-data" novalidate>
          <?php wp_nonce_field( 'lc_apply_submit', 'lc_apply_nonce' ); ?>

          <input type="hidden" id="lc_job_id" name="job_id" value="<?php echo esc_attr( $selected_job_id ); ?>">
          <input type="hidden" id="lc_job_title" name="job_title" value="<?php echo esc_attr( $selected_job_title ); ?>">

          <div class="field">
            <label for="lc_fullname"><?php esc_html_e( 'Nome e Cognome', 'abcontact' ); ?> *</label>
            <div class="lc-fullname-grid">
              <input type="text" id="lc_first_name" name="first_name" placeholder="<?php esc_attr_e( 'Nome', 'abcontact' ); ?>" required>
              <input type="text" id="lc_last_name" name="last_name" placeholder="<?php esc_attr_e( 'Cognome', 'abcontact' ); ?>" required>
            </div>
          </div>

          <div class="field">
            <label for="lc_email"><?php esc_html_e( 'Email', 'abcontact' ); ?> *</label>
            <input type="email" id="lc_email" name="email" placeholder="<?php esc_attr_e( 'Email', 'abcontact' ); ?>" required>
          </div>

          <div class="field">
            <label for="lc_phone"><?php esc_html_e( 'Telefono', 'abcontact' ); ?> *</label>
            <input type="tel" id="lc_phone" name="phone" placeholder="<?php esc_attr_e( 'Telefono', 'abcontact' ); ?>" required>
          </div>

          <div class="field">
            <label for="lc_about"><?php esc_html_e( 'Parlaci di te e della tua esperienza', 'abcontact' ); ?></label>
            <textarea id="lc_about" name="about" rows="4" placeholder="<?php esc_attr_e( 'Parlaci di te e della tua esperienza...', 'abcontact' ); ?>"></textarea>
          </div>

          <div class="field">
            <label class="upload-drop" id="cv_upload_drop" aria-labelledby="cv-upload-label">
              <input type="file" id="cv_file" name="cv_file" accept=".pdf,.doc,.docx,.rtf,.txt" aria-required="true" required>
              <div class="upload-instructions" id="cv-upload-label">
                <div class="upload-icon">📎</div>
                <div class="upload-text"><?php esc_html_e( 'Carica il tuo CV (PDF/DOC/DOCX/RTF max 10MB) - obbligatorio', 'abcontact' ); ?></div>
              </div>
            </label>
            <div id="cv_upload_filename" class="upload-filename" aria-live="polite"></div>
          </div>

          <div class="field">
            <label class="checkbox">
              <input type="checkbox" id="lc_gdpr_confirm" name="gdpr_confirm" value="1" required>
              <span><?php esc_html_e( 'Accetto il trattamento dei dati personali', 'abcontact' ); ?> *</span>
            </label>
          </div>

          <button type="submit" class="lc-submit">
            <?php esc_html_e( 'Invia candidatura', 'abcontact' ); ?>
            <span class="lc-submit__icon" aria-hidden="true">
              <svg width="22" height="22" viewBox="0 0 24 24" focusable="false">
                <path fill="currentColor" d="M12 2a10 10 0 100 20a10 10 0 000-20zm1 5v5.59l4 2.33l-1 1.73L11 13V7h2z"/>
              </svg>
            </span>
          </button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php
$should_open = ( ! empty( $apply_errors ) || $apply_sent ) && ! empty( $selected_job_id );
if ( $should_open ) {
    echo '<script>window.__LC_OPEN_JOB_ID = ' . wp_json_encode( $selected_job_id ) . ';</script>';
}

get_footer();