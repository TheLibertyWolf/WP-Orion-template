<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="orion-search"><?php echo esc_html( sprintf( __( 'Rechercher sur %s', 'orion26' ), get_bloginfo( 'name' ) ) ); ?></label>
	<div><input id="orion-search" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Pilote, écurie, championnat…', 'orion26' ); ?>"><button type="submit"><?php esc_html_e( 'Rechercher', 'orion26' ); ?></button></div>
</form>
