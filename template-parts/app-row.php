<?php
/**
 * App row used in home and lists.
 *
 * @package Appverse
 */

$post_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : get_the_ID();
if ( ! $post_id ) {
	return;
}
$meta     = appverse_get_app_meta( $post_id );
$terms    = get_the_terms( $post_id, 'app_category' );
$category = ( ! is_wp_error( $terms ) && $terms ) ? $terms[0]->name : __( '应用', 'appverse' );
?>
<div class="av-approw">
	<a class="av-approw__link" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
		<?php appverse_app_icon( $post_id, 'sm' ); ?>
		<span class="av-approw__body">
			<span class="av-approw__name"><?php echo esc_html( get_the_title( $post_id ) ); ?></span>
			<span class="av-approw__meta">
				<?php echo esc_html( $category ); ?>
				<span class="av-download-count">↓<?php echo esc_html( appverse_format_count( $meta['downloads'] ) ); ?></span>
			</span>
			<span class="av-approw__version"><?php echo esc_html( $meta['version'] ); ?></span>
		</span>
	</a>
	<?php echo appverse_get_download_button( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped builder. ?>
</div>
