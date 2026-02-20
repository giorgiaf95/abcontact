# Struttura
- style.css — header del tema
- index.php, header.php, footer.php, functions.php, ecc.
- assets/ — css, js, immagini
- template-parts/ — parti riutilizzabili
- inc/ — funzioni e bootstrap del tema
- languages/ — traduzioni

# Git
- Inizializza repo:
  git init
  git add .
  git commit -m "Initial commit - theme structure"

# Contatti
Autore: Giorgia
___________________________________________________________

FOOTER
per mostrare più colonne con menù distinti (ex. 3 colonne di link con gestione diversa) registrare più location in inc/setup.php:

register_nav_menus( array(
  'primary'  => __( 'Primary Menu', 'theme-abcontact' ),
  'footer'   => __( 'Footer Menu (colonna 1)', 'theme-abcontact' ),
  'footer_2' => __( 'Footer Menu (colonna 2)', 'theme-abcontact' ),
  'footer_3' => __( 'Footer Menu (colonna 3)', 'theme-abcontact' ),
) );

dopo in footer.php mostra ogni menù nella sua colonna:

<nav class="footer-col footer-col-1" aria-label="<?php esc_attr_e( 'Footer column 1', 'theme-abcontact' ); ?>">
  <?php
    if ( has_nav_menu( 'footer' ) ) {
      wp_nav_menu( array(
        'theme_location' => 'footer',
        'menu_class'     => 'footer-menu',
        'container'      => false,
      ) );
    }
  ?>
</nav>

<nav class="footer-col footer-col-2" aria-label="<?php esc_attr_e( 'Footer column 2', 'theme-abcontact' ); ?>">
  <?php
    if ( has_nav_menu( 'footer_2' ) ) {
      wp_nav_menu( array(
        'theme_location' => 'footer_2',
        'menu_class'     => 'footer-menu',
        'container'      => false,
      ) );
    }
  ?>
</nav>

<nav class="footer-col footer-col-3" aria-label="<?php esc_attr_e( 'Footer column 3', 'theme-abcontact' ); ?>">
  <?php
    if ( has_nav_menu( 'footer_3' ) ) {
      wp_nav_menu( array(
        'theme_location' => 'footer_3',
        'menu_class'     => 'footer-menu',
        'container'      => false,
      ) );
    }
  ?>
</nav>

Poi in Aspetto → Menu assegnare tre menu diversi alle rispettive location.
____________________________________________________________________________

icone card why-us-cards
grandezza 44x44, colorazione: bianco, preferibile SVG, va bene anche PNG

____________________________________________________________________________

MENU

assegna a "Servizi" css -> mega
Sottomenu servizi - Privati - > menu-heading menu-heading--privati
                    Aziende -> menu-heading menu-heading--aziende
Titoli secondari - column-title

__________________________________________________________________________