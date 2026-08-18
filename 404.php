<?php
/** Page introuvable. @package Orion26 */
get_header();
?>
<main id="main-content" class="orion-container">
	<div class="empty-state"><span class="archive-header__eyebrow">Erreur 404</span><h1><?php esc_html_e( 'Hors trajectoire', 'orion26' ); ?></h1><p><?php esc_html_e( 'Cette page n’existe plus ou son adresse a changé. Retrouvez la piste depuis l’accueil ou lancez une recherche.', 'orion26' ); ?></p><?php get_search_form(); ?><p><a href="<?php echo esc_url( home_url( '/' ) ); ?>">← <?php esc_html_e( 'Retour à l’accueil', 'orion26' ); ?></a></p></div>
</main>
<?php get_footer(); ?>
