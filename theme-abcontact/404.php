<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<!-- Blue top -->
<div class="page-404-topbar" aria-hidden="true"></div>

<main id="main" class="site-main container page-404-wrapper" role="main" aria-label="<?php esc_attr_e( 'Pagina non trovata', 'theme-abcontact' ); ?>">
  <div class="page-404-inner">
    <section class="error-404 not-found">
      <header class="page-header">
        <h1 class="page-404-title"><?php esc_html_e( 'Pagina non disponibile', 'theme-abcontact' ); ?></h1>
      </header>

      <div class="page-content page-404-desc">
        <p><?php esc_html_e( 'Siamo spiacenti, la pagina che cerchi non è disponibile o è stata spostata.', 'theme-abcontact' ); ?></p>

        <div class="page-404-actions" aria-hidden="false" style="margin-top:18px;">
          <!-- Single button -->
          <a class="button page-404-back" href="<?php echo esc_url( home_url( '/' ) ); ?>"
             onclick="(function(u){ try{ if (history.length>1){ history.back(); return false; } }catch(e){} window.location.href=u; })(<?php echo json_encode( esc_url( home_url( '/' ) ) ); ?>); return false;">
            <?php esc_html_e( 'Torna indietro', 'theme-abcontact' ); ?>
          </a>
        </div>
      </div>
    </section>
  </div>
</main>

<?php
get_footer();