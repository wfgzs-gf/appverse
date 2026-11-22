<?php
/**
 * Embedded app card inside a community post.
 *
 * @package Appverse
 */

$app = isset( $args['app'] ) ? $args['app'] : appverse_get_attached_app( get_the_ID() );
if ( ! $app ) {
	return;
}
$meta = appverse_get_app_meta( $app->ID );
?>
<a class="av-post__app" href="<?php echo esc_url( get_permalink( $app ) ); ?>">
	<?php appverse_app_icon( $app->ID, 'xs', array( 'badge' => false ) ); ?>
	<span class="av-post__app-body">
		<span class="av-post__app-name"><?php echo esc_html( get_the_title( $app ) ); ?></span>
		<span class="av-post__app-meta"><?php echo esc_html( $meta['version'] ); ?></span>
	</span>
	<span class="av-post__app-arrow"><?php appverse_the_icon( 'chevron-right', 18 ); ?></span>
</a>
