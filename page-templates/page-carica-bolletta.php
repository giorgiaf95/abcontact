<?php
/**
 * Template Name: Form Carica bolletta
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();

$post_id = get_the_ID();

/* enqueue styles & scripts */
$css = get_stylesheet_directory() . '/assets/css/carica-bolletta.css';
if ( file_exists( $css ) ) {
    wp_enqueue_style( 'ab-carica-bolletta', get_stylesheet_directory_uri() . '/assets/css/carica-bolletta.css', array(), filemtime( $css ) );
}
$js = get_stylesheet_directory() . '/assets/js/carica-bolletta.js';
if ( file_exists( $js ) ) {
    wp_enqueue_script( 'ab-carica-bolletta', get_stylesheet_directory_uri() . '/assets/js/carica-bolletta.js', array(), filemtime( $js ), true );
}

/* hero */
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

/* load helper (if available) */
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

if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['carica_bolletta_nonce'] ) ) {

    if ( ! wp_verify_nonce( $_POST['carica_bolletta_nonce'], 'carica_bolletta_submit' ) ) {
        $errors[] = __( 'Token di sicurezza non valido.', 'abcontact' );
    } else {
        $user_type = isset( $_POST['user_type'] ) ? sanitize_text_field( $_POST['user_type'] ) : '';
        $product = isset( $_POST['product'] ) ? sanitize_text_field( $_POST['product'] ) : '';

        $company = isset( $_POST['company_name'] ) ? sanitize_text_field( $_POST['company_name'] ) : '';
        $first_name = isset( $_POST['first_name'] ) ? sanitize_text_field( $_POST['first_name'] ) : '';
        $last_name = isset( $_POST['last_name'] ) ? sanitize_text_field( $_POST['last_name'] ) : '';

        $address = isset( $_POST['address'] ) ? sanitize_text_field( $_POST['address'] ) : '';
        $number = isset( $_POST['address_number'] ) ? sanitize_text_field( $_POST['address_number'] ) : '';
        $city = isset( $_POST['city'] ) ? sanitize_text_field( $_POST['city'] ) : '';
        $zip = isset( $_POST['zip'] ) ? sanitize_text_field( $_POST['zip'] ) : '';

        $phone = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';
        $email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';

        $gdpr = isset( $_POST['gdpr_confirm'] ) ? true : false;

        // Validation
        if ( empty( $user_type ) || ! in_array( $user_type, array( 'privato', 'azienda' ), true ) ) {
            $errors[] = __( 'Seleziona la tipologia utente.', 'abcontact' );
        }
        if ( empty( $product ) ) {
            $errors[] = __( 'Seleziona il prodotto.', 'abcontact' );
        }
        if ( $user_type === 'azienda' && empty( $company ) ) {
            $errors[] = __( 'Inserisci la Ragione Sociale.', 'abcontact' );
        }
        if ( $user_type === 'privato' && ( empty( $first_name ) || empty( $last_name ) ) ) {
            $errors[] = __( 'Inserisci Nome e Cognome.', 'abcontact' );
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

        $attachment_path = '';
        $attachment_url  = '';
        $attachment_id   = 0;

        // Handle bolletta upload (optional)
        if ( isset( $_FILES['bolletta_file'] ) && ! empty( $_FILES['bolletta_file']['name'] ) ) {
            $upload_attempted = true;
            if ( function_exists( 'abcontact_handle_upload' ) ) {
                // allowed extensions for helper: pdf, jpg, jpeg, png
                $res = abcontact_handle_upload( $_FILES['bolletta_file'], array( 'pdf', 'jpg', 'jpeg', 'png' ), 10 * 1024 * 1024 );
                if ( ! empty( $res['success'] ) ) {
                    $attachment_path = isset( $res['file'] ) ? $res['file'] : '';
                    $attachment_url  = isset( $res['url'] ) ? $res['url'] : '';
                    $attachment_id   = isset( $res['id'] ) ? (int) $res['id'] : 0;
                    $upload_success  = true;
                    $upload_name     = basename( $attachment_path );
                    error_log( '[BOLLETTA DEBUG] upload helper result: ' . print_r( $res, true ) );
                } else {
                    $errors[] = __( 'Errore upload file: ', 'abcontact' ) . $res['error'];
                    $upload_error_message = $res['error'];
                    error_log( '[BOLLETTA DEBUG] upload helper error: ' . $res['error'] );
                }
            } else {
                // fallback to wp_handle_upload if helper missing
                $file = $_FILES['bolletta_file'];
                $upload_name = $file['name'];
                // basic checks (mime checked below)
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

        if ( empty( $errors ) ) {

            $recipient = get_theme_mod( 'abcontact_recipient_email', '' );
            if ( ! is_email( $recipient ) ) {
                $recipient = get_option( 'admin_email' );
            }

            $subject = sprintf( '[Richiesta Bolletta] %s - %s', $user_type === 'azienda' ? $company : trim( $first_name . ' ' . $last_name ), $product );

            $body_lines = array();
            $body_lines[] = sprintf( __( 'Tipologia utente: %s', 'abcontact' ), $user_type );
            $body_lines[] = sprintf( __( 'Prodotto: %s', 'abcontact' ), $product );
            if ( $user_type === 'azienda' ) {
                $body_lines[] = sprintf( __( 'Ragione sociale: %s', 'abcontact' ), $company );
            } else {
                $body_lines[] = sprintf( __( 'Nome: %s', 'abcontact' ), $first_name );
                $body_lines[] = sprintf( __( 'Cognome: %s', 'abcontact' ), $last_name );
            }
            $body_lines[] = sprintf( __( 'Indirizzo: %s, %s, %s, %s', 'abcontact' ), $address, $number, $city, $zip );
            $body_lines[] = sprintf( __( 'Cellulare: %s', 'abcontact' ), $phone );
            $body_lines[] = sprintf( __( 'Email: %s', 'abcontact' ), $email );
            if ( $attachment_url ) {
                $body_lines[] = sprintf( __( 'File inviato (URL): %s', 'abcontact' ), $attachment_url );
            }
            $body_lines[] = '';
            $body_lines[] = __( 'Messaggio inviato dal form Carica Bolletta', 'abcontact' );
            $body = implode( "\n", $body_lines );

            $headers = array();
            if ( is_email( $email ) ) {
                $headers[] = 'Reply-To: ' . $email;
            }

            // Prepare attachments array for wp_mail (absolute paths)
            $attachments = array();

            error_log( '[MAIL DEBUG] carica-bolletta raw attachment_path: ' . print_r( $attachment_path, true ) );
            error_log( '[MAIL DEBUG] carica-bolletta raw attachment_url:  ' . print_r( $attachment_url, true ) );

            if ( ! empty( $attachment_path ) && file_exists( $attachment_path ) ) {
                $attachments[] = $attachment_path;
                error_log( '[MAIL DEBUG] carica-bolletta using absolute path: ' . $attachment_path );
            } elseif ( ! empty( $attachment_url ) ) {
                $upload = wp_upload_dir();
                $relative = str_replace( $upload['baseurl'], '', $attachment_url );
                $candidate = $upload['basedir'] . $relative;
                error_log( '[MAIL DEBUG] carica-bolletta candidate path: ' . $candidate . ' exists? ' . ( file_exists( $candidate ) ? 'YES' : 'NO' ) );
                if ( file_exists( $candidate ) ) {
                    $attachments[] = $candidate;
                } else {
                    error_log( '[MAIL DEBUG] carica-bolletta attachment not found on disk; will not attach.' );
                }
            } else {
                error_log( '[MAIL DEBUG] carica-bolletta no attachment info available' );
            }

            error_log( '[MAIL DEBUG] carica-bolletta final attachments array: ' . print_r( $attachments, true ) );

            $sent_mail = wp_mail( $recipient, $subject, $body, $headers, $attachments );

            error_log( '[MAIL DEBUG] carica-bolletta wp_mail returned: ' . var_export( $sent_mail, true ) );

            // Do not unlink/delete uploaded file: imported into uploads/media library by helper.
            if ( $sent_mail ) {
                $sent = true;
                wp_safe_redirect( add_query_arg( 'caricab_sent', '1', get_permalink() ) );
                exit;
            } else {
                $errors[] = __( 'Errore nell\'invio della email. Riprovare più tardi.', 'abcontact' );
            }
        }
    }
}


if ( isset( $_GET['caricab_sent'] ) && $_GET['caricab_sent'] == '1' ) {
    $sent = true;
}
?>

<div class="container page-carica-bolletta">
  <div class="carica-hero">
    <h1 class="carica-title"><?php echo esc_html__( 'Richiedi la valutazione della tua bolletta', 'abcontact' ); ?></h1>
    <p class="carica-sub"><?php echo esc_html__( 'Carica la bolletta per ricevere una proposta di risparmio su misura.', 'abcontact' ); ?></p>
  </div>

  <div class="carica-inner">

    <?php if ( $sent ) : ?>
      <div class="carica-success">
        <h2><?php esc_html_e( 'Richiesta inviata', 'abcontact' ); ?></h2>
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

      <form id="carica-bolletta-form" method="post" enctype="multipart/form-data" novalidate>
        <?php wp_nonce_field( 'carica_bolletta_submit', 'carica_bolletta_nonce' ); ?>

        <div class="grid two-cols">
          <div class="field">
            <label for="user_type"><?php esc_html_e( 'Tipologia Utente', 'abcontact' ); ?> *</label>
            <select id="user_type" name="user_type" required>
              <option value=""><?php esc_html_e( 'Seleziona tipologia', 'abcontact' ); ?></option>
              <option value="privato"><?php esc_html_e( 'Privato', 'abcontact' ); ?></option>
              <option value="azienda"><?php esc_html_e( 'Azienda', 'abcontact' ); ?></option>
            </select>
          </div>

          <div class="field">
            <label for="product"><?php esc_html_e( 'Prodotto', 'abcontact' ); ?> *</label>
            <select id="product" name="product" required>
              <option value=""><?php esc_html_e( 'Seleziona prodotto', 'abcontact' ); ?></option>
              <option value="Luce&Gas"><?php esc_html_e( 'Luce&Gas', 'abcontact' ); ?></option>
              <option value="Telefonia&Internet"><?php esc_html_e( 'Telefonia&Internet', 'abcontact' ); ?></option>
              <option value="Efficienza Energetica"><?php esc_html_e( 'Efficienza Energetica', 'abcontact' ); ?></option>
              <option value="Fotovoltaico"><?php esc_html_e( 'Fotovoltaico', 'abcontact' ); ?></option>
            </select>
          </div>
        </div>

        <div id="company_fields" style="display:none;">
          <div class="field">
            <label for="company_name"><?php esc_html_e( 'Ragione Sociale', 'abcontact' ); ?> *</label>
            <input type="text" id="company_name" name="company_name" placeholder="<?php esc_attr_e( 'Es. Rossi S.r.l.', 'abcontact' ); ?>">
          </div>
        </div>

        <div id="private_fields" style="display:none;">
          <div class="grid two-cols">
            <div class="field">
              <label for="first_name"><?php esc_html_e( 'Nome', 'abcontact' ); ?> *</label>
              <input type="text" id="first_name" name="first_name" placeholder="<?php esc_attr_e( 'Mario', 'abcontact' ); ?>">
            </div>
            <div class="field">
              <label for="last_name"><?php esc_html_e( 'Cognome', 'abcontact' ); ?> *</label>
              <input type="text" id="last_name" name="last_name" placeholder="<?php esc_attr_e( 'Rossi', 'abcontact' ); ?>">
            </div>
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

        <h3><?php esc_html_e( 'Caricamento Bolletta (opzionale)', 'abcontact' ); ?></h3>
        <div class="field">
          <label class="upload-drop" id="upload_drop">
            <!-- single file input + overlay -->
            <input type="file" id="bolletta_file" name="bolletta_file" accept=".pdf,image/jpeg,image/png">
            <div class="upload-instructions">
              <div class="upload-icon">⤴</div>
              <div class="upload-text"><?php esc_html_e( 'Clicca per caricare la tua bolletta (PDF/JPG/PNG max 10MB)', 'abcontact' ); ?></div>
            </div>
          </label>

          <?php
          if ( ! empty( $upload_attempted ) ) {
              if ( ! empty( $upload_name ) && ! empty( $upload_success ) ) {
                  echo '<div id="upload_filename" class="upload-filename upload-filename--success" aria-live="polite"><span class="upload-filename__icon">✓</span><span class="upload-filename__name">' . esc_html( $upload_name ) . '</span></div>';
              } else {
                  $note = ! empty( $upload_error_message ) ? $upload_error_message : __( 'Caricamento fallito', 'abcontact' );
                  echo '<div id="upload_filename" class="upload-filename upload-filename--error" aria-live="polite"><span class="upload-filename__icon">✕</span><span class="upload-filename__name">' . esc_html( $upload_name ) . '</span><div class="upload-filename__note">' . esc_html( $note ) . '</div></div>';
              }
          } else {
              echo '<div id="upload_filename" class="upload-filename" aria-live="polite"></div>';
          }
          ?>

        </div>

        <div class="field">
          <label class="checkbox">
            <input type="checkbox" id="gdpr_confirm" name="gdpr_confirm" value="1" required>
            <span><?php esc_html_e( 'Accetto il trattamento dei dati personali', 'abcontact' ); ?> *</span>
          </label>
          <p class="small"><?php esc_html_e( 'I tuoi dati saranno trattati in conformità al Regolamento UE 2016/679 (GDPR) e utilizzati esclusivamente per fornirti la consulenza richiesta.', 'abcontact' ); ?></p>
        </div>

        <div class="form-actions">
          <button type="button" class="btn btn-ghost" onclick="location.href='<?php echo esc_url( home_url() ); ?>'"><?php esc_html_e( 'Annulla', 'abcontact' ); ?></button>
          <button type="submit" class="btn btn-primary"><?php esc_html_e( 'Invia Richiesta', 'abcontact' ); ?></button>
        </div>

      </form>
    <?php endif; ?>

  </div>
</div>

<?php
get_footer();