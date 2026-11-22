<?php
/**
 * App card used in archives and related lists.
 *
 * @package Appverse
 */

$post_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : get_the_ID();
if ( ! $post_id ) {
	return;
}
$meta  = appverse_get_app_meta( $post_id );
$terms = get_the_terms( $post_id, 'app_category' );
$cat   = ( ! is_wp_error( $terms ) && $terms ) ? $terms[0]->name : __( '应用', 'appverse' );
?>
<article class="av-appcard">
	<a class="av-appcard__link" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
		<?php appverse_app_icon( $post_id, 'lg' ); ?>
		<span class="av-appcard__body">
			<span class="av-appcard__name"><?php echo esc_html( get_the_title( $post_id ) ); ?></span>
			<span class="av-appcard__cat"><?php echo esc_html( $cat ); ?> · <?php echo esc_html( $meta['version'] ); ?></span>
			<span class="av-appcard__rating">
				<?php echo appverse_get_stars( $meta['rating'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped builder. ?>
				<span class="av-appcard__score"><?php echo esc_html( number_format_i18n( $meta['rating'], 1 ) ); ?></span>
			</span>
		</span>
	</a>
	<div class="av-appcard__foot">
		<span class="av-download-count">↓<?php echo esc_html( appverse_format_count( $meta['downloads'] ) ); ?></span>
		<?php echo appverse_get_download_button( $post_id, __( '下载', 'appverse' ), 'av-btn--sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped builder. ?>
	</div>
</article>
