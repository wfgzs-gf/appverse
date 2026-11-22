<?php
/**
 * Search form.
 *
 * @package Appverse
 */

$unique_id = wp_unique_id( 'search-form-' );
?>
<form role="search" method="get" class="av-searchbar av-searchbar--page" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<button class="av-searchbar__submit" type="submit" aria-label="<?php esc_attr_e( '搜索', 'appverse' ); ?>">
		<?php appverse_the_icon( 'search', 22 ); ?>
	</button>
	<label class="screen-reader-text" for="<?php echo esc_attr( $unique_id ); ?>"><?php esc_html_e( '搜索', 'appverse' ); ?></label>
	<input class="av-searchbar__input" id="<?php echo esc_attr( $unique_id ); ?>" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( '搜索应用、动态或开发者', 'appverse' ); ?>">
</form>
