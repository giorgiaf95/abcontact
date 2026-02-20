<?php
/**
 * Template Name: Form Unisciti a noi
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();

$post_id = get_the_ID();

/* enqueue styles & scripts */
$css = get_stylesheet_directory() . '/assets/css/carica-bolletta.css';
if ( file_exists( $css ) ) {
    wp_enqueue_style( 'ab-unisciti-style', get_stylesheet_directory_uri() . '/assets/css/carica-bolletta.css', array(), filemtime( $css ) );
}
$js = get_stylesheet_directory() . '/assets/js/unisciti.js';
if ( file_exists( $js ) ) {
    wp_enqueue_script( 'ab-unisciti-js', get_stylesheet_directory_uri() . '/assets/js/unisciti.js', array(), filemtime( $js ), true );
}

/* optional hero */
$hero_image = get_the_post_thumbnail_url( $post_id, 'full' ) ?: '';
$hero_args = array(
    'eyebrow'   => '',
    'title'     => get_the_title( $post_id ),
    'subtitle'  => '',
    'bg_url'    => $hero_image,
    'cta'       => '',
    'cta_label' => '',
);
set_query_var( 'args', $hero_args );
if ( locate_template( 'template-parts/news-hero.php' ) ) {
    get_template_part( 'template-parts/news-hero' );
} else {
    ?>
    <header class="sp-hero" style="<?php if ( $hero_image ) echo 'background-image: url(' . esc_url( $hero_image ) . ');'; ?>">
      <div class="sp-hero-inner container">
        <h1 class="sp-hero-title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h1>
      </div>
    </header>
    <?php
}
set_query_var( 'args', null );

/* load helper */
$upload_helper = get_stylesheet_directory() . '/inc/upload-helpers.php';
if ( file_exists( $upload_helper ) ) {
    require_once $upload_helper;
}

$errors = array();
$sent = false;

$upload_attempted = false;
$upload_success = false;
$upload_name = '';
$upload_error_message = '';

if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['unisciti_nonce'] ) ) {

    if ( ! wp_verify_nonce( $_POST['unisciti_nonce'], 'unisciti_submit' ) ) {
        $errors[] = __( 'Token di sicurezza non valido.', 'abcontact' );
    } else {
        // sanitize
        $position   = isset( $_POST['position'] ) ? sanitize_text_field( $_POST['position'] ) : '';
        $first_name = isset( $_POST['first_name'] ) ? sanitize_text_field( $_POST['first_name'] ) : '';
        $last_name  = isset( $_POST['last_name'] ) ? sanitize_text_field( $_POST['last_name'] ) : '';
        $address    = isset( $_POST['address'] ) ? sanitize_text_field( $_POST['address'] ) : '';
        $number     = isset( $_POST['address_number'] ) ? sanitize_text_field( $_POST['address_number'] ) : '';
        $city       = isset( $_POST['city'] ) ? sanitize_text_field( $_POST['city'] ) : '';
        $zip        = isset( $_POST['zip'] ) ? sanitize_text_field( $_POST['zip'] ) : '';
        $years      = isset( $_POST['years_experience'] ) ? intval( $_POST['years_experience'] ) : 0;
        $phone      = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';
        $email      = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
        $gdpr       = isset( $_POST['gdpr_confirm'] ) ? true : false;

        // Validation
        if ( empty( $position ) ) {
            $errors[] = __( 'Seleziona la posizione per cui ti candidi.', 'abcontact' );
        }
        if ( empty( $first_name ) ) {
            $errors[] = __( 'Inserisci il nome.', 'abcontact' );
        }
        if ( empty( $last_name ) ) {
            $errors[] = __( 'Inserisci il cognome.', 'abcontact' );
        }
        if ( empty( $phone ) ) {
            $errors[] = __( 'Inserisci il numero di cellulare.', 'abcontact' );
        }
        if ( empty( $email ) || ! is_email( $email ) ) {
            $errors[] = __( 'Inserisci una email valida.', 'abcontact' );
        }
        if ( ! $gdpr ) {
            $errors[] = __( 'Devi accettare il trattamento dei dati personali.', 'abcontact' );
        }

        // Make CV upload mandatory: check presence of file
        $cv_uploaded = ( isset( $_FILES['cv_file'] ) && ! empty( $_FILES['cv_file']['name'] ) && $_FILES['cv_file']['error'] !== UPLOAD_ERR_NO_FILE );
        if ( ! $cv_uploaded ) {
            $errors[] = __( 'Devi caricare il tuo curriculum (CV).', 'abcontact' );
        }

        $attachment_path = '';
        $attachment_url  = '';
        $attachment_id   = 0;

        // Use helper to upload & import the file (if helper exists)
        if ( $cv_uploaded ) {
            $upload_attempted = true;
            if ( function_exists( 'abcontact_handle_upload' ) ) {
                $allowed = array( 'pdf', 'doc', 'docx', 'rtf', 'txt' );
                $res = abcontact_handle_upload( $_FILES['cv_file'], $allowed, 10 * 1024 * 1024 );
                if ( ! empty( $res['success'] ) ) {
                    $attachment_path = isset( $res['file'] ) ? $res['file'] : '';
                    $attachment_url  = isset( $res['url'] ) ? $res['url'] : '';
                    $attachment_id   = isset( $res['id'] ) ? (int) $res['id'] : 0;
                    $upload_success  = true;
                    $upload_name     = basename( $attachment_path );
                    error_log( '[UNISCITI DEBUG] upload helper result: ' . print_r( $res, true ) );
                } else {
                    $errors[] = __( 'Errore upload CV: ', 'abcontact' ) . $res['error'];
                    $upload_error_message = $res['error'];
                    error_log( '[UNISCITI DEBUG] upload helper error: ' . $res['error'] );
                }
            } else {
                // fallback to previous behaviour if helper missing
                $file = $_FILES['cv_file'];
                $upload_name = $file['name'];
                require_once ABSPATH . 'wp-admin/includes/file.php';
                $overrides = array( 'test_form' => false );
                $move = wp_handle_upload( $file, $overrides );
                if ( isset( $move['error'] ) ) {
                    $errors[] = esc_html( $move['error'] );
                    $upload_error_message = $move['error'];
                } else {
                    $attachment_path = isset( $move['file'] ) ? $move['file'] : '';
                    $attachment_url  = isset( $move['url'] ) ? $move['url'] : '';
                    $upload_success = true;
                    $upload_name = basename( $attachment_path );
                }
            }
        }

        /* --- END UPLOAD HANDLER --- */

        // send if no errors
        if ( empty( $errors ) ) {
            // Determine recipient
            $recipient = get_theme_mod( 'abcontact_recipient_email', '' );
            if ( ! is_email( $recipient ) ) {
                $recipient = get_option( 'admin_email' );
                error_log( '[UNISCITI DEBUG] recipient invalid in theme mod, fallback to admin_email: ' . $recipient );
            } else {
                error_log( '[UNISCITI DEBUG] recipient from theme mod: ' . $recipient );
            }

            // Build subject/body
            $subject = sprintf( '[Candidatura] %s %s - %s', $first_name, $last_name, $position );

            $body_lines = array();
            $body_lines[] = sprintf( __( 'Posizione: %s', 'abcontact' ), $position );
            $body_lines[] = sprintf( __( 'Nome: %s', 'abcontact' ), $first_name );
            $body_lines[] = sprintf( __( 'Cognome: %s', 'abcontact' ), $last_name );
            $body_lines[] = sprintf( __( 'Indirizzo: %s, %s, %s, %s', 'abcontact' ), $address, $number, $city, $zip );
            $body_lines[] = sprintf( __( 'Anni di esperienza: %s', 'abcontact' ), $years );
            $body_lines[] = sprintf( __( 'Cellulare: %s', 'abcontact' ), $phone );
            $body_lines[] = sprintf( __( 'Email: %s', 'abcontact' ), $email );
            if ( $attachment_url ) {
                $body_lines[] = sprintf( __( 'CV (URL): %s', 'abcontact' ), $attachment_url );
            }
            $body_lines[] = '';
            $body_lines[] = __( 'Messaggio inviato dal form Unisciti a noi', 'abcontact' );
            $body = implode( "\n", $body_lines );

            // Headers
            $headers = array();
            $from_email = get_option( 'admin_email' );
            $from_name  = get_bloginfo( 'name' );
            if ( is_email( $from_email ) ) {
                $headers[] = 'From: ' . sanitize_text_field( $from_name ) . ' <' . $from_email . '>';
            }

            // Reply-To
            $reply_name = trim( $first_name . ' ' . $last_name );
            if ( is_email( $email ) ) {
                $headers[] = 'Reply-To: ' . sanitize_text_field( $reply_name ) . ' <' . $email . '>';
            }

            // Prepare attachments array for wp_mail (absolute paths)
            $attachments = array();
            if ( ! empty( $attachment_path ) && file_exists( $attachment_path ) ) {
                $attachments[] = $attachment_path;
            } elseif ( ! empty( $attachment_url ) ) {
                $upload = wp_upload_dir();
                $relative = str_replace( $upload['baseurl'], '', $attachment_url );
                $candidate = $upload['basedir'] . $relative;
                if ( file_exists( $candidate ) ) {
                    $attachments[] = $candidate;
                } else {
                    error_log( '[UNISCITI DEBUG] attachment candidate path not found: ' . $candidate );
                }
            }

            // Debug log full context before sending
            error_log( '[UNISCITI DEBUG] recipient: ' . $recipient );
            error_log( '[UNISCITI DEBUG] headers: ' . print_r( $headers, true ) );
            error_log( '[UNISCITI DEBUG] attachments: ' . print_r( $attachments, true ) );
            error_log( '[UNISCITI DEBUG] body (first 200 chars): ' . substr( $body, 0, 200 ) );

            // Send mail
            $sent_mail = wp_mail( $recipient, $subject, $body, $headers, $attachments );
            error_log( '[UNISCITI DEBUG] wp_mail returned: ' . var_export( $sent_mail, true ) );

            // NOTE: Do not unlink/delete uploaded file: it has been imported to uploads / media library.
            // If you want to delete attachments later, do it from WP admin or a scheduled cleanup.

            if ( $sent_mail ) {
                $sent = true;
                wp_safe_redirect( add_query_arg( 'unisciti_sent', '1', get_permalink() ) );
                exit;
            } else {
                $errors[] = __( 'Errore nell\'invio della email. Riprovare più tardi.', 'abcontact' );
            }
        }
    }
}

if ( isset( $_GET['unisciti_sent'] ) && $_GET['unisciti_sent'] == '1' ) {
    $sent = true;
}
?>

<div class="container page-unisciti">
  <div class="carica-hero">
    <h1 class="carica-title"><?php echo esc_html__( 'Unisciti a noi', 'abcontact' ); ?></h1>
    <p class="carica-sub"><?php echo esc_html__( 'Compila il modulo per candidarti alle nostre posizioni aperte.', 'abcontact' ); ?></p>
  </div>

  <div class="carica-inner">

    <?php if ( $sent ) : ?>
      <div class="carica-success">
        <h2><?php esc_html_e( 'Candidatura inviata', 'abcontact' ); ?></h2>
        <p><?php esc_html_e( 'Grazie — ti ricontatteremo al più presto.', 'abcontact' ); ?></p>
      </div>
    <?php else : ?>

      <?php if ( ! empty( $errors ) ) : ?>
        <div class="carica-errors" role="alert">
          <ul>
            <?php foreach ( $errors as $e ) : ?>
              <li><?php echo esc_html( $e ); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form id="unisciti-form" method="post" enctype="multipart/form-data" novalidate>
        <?php wp_nonce_field( 'unisciti_submit', 'unisciti_nonce' ); ?>

        <div class="grid two-cols">
          <div class="field">
            <label for="position"><?php esc_html_e( 'Posizione (Candidatura)', 'abcontact' ); ?> *</label>
            <select id="position" name="position" required>
              <option value=""><?php esc_html_e( 'Seleziona posizione', 'abcontact' ); ?></option>
              <option value="Operatore Front Office"><?php esc_html_e( 'Operatore Front Office', 'abcontact' ); ?></option>
              <option value="Operatore Back Office"><?php esc_html_e( 'Operatore Back Office', 'abcontact' ); ?></option>
              <option value="Agente di Commercio"><?php esc_html_e( 'Agente di Commercio', 'abcontact' ); ?></option>
              <option value="Business Promoter"><?php esc_html_e( 'Business Promoter', 'abcontact' ); ?></option>
            </select>
          </div>

          <div class="field">
            <label for="years_experience"><?php esc_html_e( 'Anni di esperienza', 'abcontact' ); ?></label>
            <input type="number" id="years_experience" name="years_experience" min="0" max="80" placeholder="0">
          </div>
        </div>

        <div class="grid two-cols">
          <div class="field">
            <label for="first_name"><?php esc_html_e( 'Nome', 'abcontact' ); ?> *</label>
            <input type="text" id="first_name" name="first_name" required>
          </div>
          <div class="field">
            <label for="last_name"><?php esc_html_e( 'Cognome', 'abcontact' ); ?> *</label>
            <input type="text" id="last_name" name="last_name" required>
          </div>
        </div>

        <hr>

        <h3><?php esc_html_e( 'Indirizzo', 'abcontact' ); ?></h3>
        <div class="grid two-cols">
          <div class="field">
            <label for="address"><?php esc_html_e( 'Via', 'abcontact' ); ?></label>
            <input type="text" id="address" name="address">
          </div>
          <div class="field" style="max-width:160px;">
            <label for="address_number"><?php esc_html_e( 'Civico', 'abcontact' ); ?></label>
            <input type="text" id="address_number" name="address_number">
          </div>
        </div>
        <div class="grid two-cols">
          <div class="field">
            <label for="city"><?php esc_html_e( 'Città', 'abcontact' ); ?></label>
            <input type="text" id="city" name="city">
          </div>
          <div class="field" style="max-width:160px;">
            <label for="zip"><?php esc_html_e( 'CAP', 'abcontact' ); ?></label>
            <input type="text" id="zip" name="zip">
          </div>
        </div>

        <hr>

        <h3><?php esc_html_e( 'Contatti', 'abcontact' ); ?></h3>
        <div class="grid two-cols">
          <div class="field">
            <label for="phone"><?php esc_html_e( 'Cellulare', 'abcontact' ); ?> *</label>
            <input type="tel" id="phone" name="phone" placeholder="+39 333 123 4567" required>
          </div>
          <div class="field">
            <label for="email"><?php esc_html_e( 'Email', 'abcontact' ); ?> *</label>
            <input type="email" id="email" name="email" placeholder="mario.rossi@email.it" required>
          </div>
        </div>

        <hr>

        <h3><?php esc_html_e( 'Carica il tuo CV (obbligatorio)', 'abcontact' ); ?></h3>
        <div class="field">
          <label class="upload-drop" id="cv_upload_drop" aria-labelledby="cv-upload-label">
            <!-- single file input + overlay -->
            <input type="file" id="cv_file" name="cv_file" accept=".pdf,.doc,.docx,.rtf,.txt" aria-required="true" required>
            <div class="upload-instructions" id="cv-upload-label">
              <div class="upload-icon">📎</div>
              <div class="upload-text"><?php esc_html_e( 'Clicca per caricare il tuo curriculum (PDF/DOC/DOCX/RTF max 10MB) - obbligatorio', 'abcontact' ); ?></div>
            </div>
          </label>

          <?php
          if ( ! empty( $upload_attempted ) ) {
              if ( ! empty( $upload_name ) && ! empty( $upload_success ) ) {
                  echo '<div id="cv_upload_filename" class="upload-filename upload-filename--success" aria-live="polite"><span class="upload-filename__icon">✓</span><span class="upload-filename__name">' . esc_html( $upload_name ) . '</span></div>';
              } else {
                  $note = ! empty( $upload_error_message ) ? $upload_error_message : __( 'Caricamento fallito', 'abcontact' );
                  echo '<div id="cv_upload_filename" class="upload-filename upload-filename--error" aria-live="polite"><span class="upload-filename__icon">✕</span><span class="upload-filename__name">' . esc_html( $upload_name ) . '</span><div class="upload-filename__note">' . esc_html( $note ) . '</div></div>';
              }
          } else {
              echo '<div id="cv_upload_filename" class="upload-filename" aria-live="polite"></div>';
          }
          ?>

        </div>

        <div class="field">
          <label class="checkbox">
            <input type="checkbox" id="gdpr_confirm" name="gdpr_confirm" value="1" required>
            <span><?php esc_html_e( 'Accetto il trattamento dei dati personali', 'abcontact' ); ?> *</span>
          </label>
          <p class="small"><?php esc_html_e( 'I tuoi dati saranno trattati in conformità al Regolamento UE 2016/679 (GDPR) e utilizzati esclusivamente per valutare la tua candidatura.', 'abcontact' ); ?></p>
        </div>

        <div class="form-actions">
          <button type="button" class="btn btn-ghost" onclick="location.href='<?php echo esc_url( home_url() ); ?>'"><?php esc_html_e( 'Annulla', 'abcontact' ); ?></button>
          <button type="submit" class="btn btn-primary"><?php esc_html_e( 'Invia Candidatura', 'abcontact' ); ?></button>
        </div>

      </form>
    <?php endif; ?>

  </div>
</div>

<?php
get_footer();