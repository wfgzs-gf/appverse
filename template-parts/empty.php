<?php
/**
 * Empty state.
 *
 * @package Appverse
 */

$icon  = isset( $args['icon'] ) ? $args['icon'] : 'sparkles';
$title = isset( $args['title'] ) ? $args['title'] : __( '这里还是空的', 'appverse' );
$text  = isset( $args['text'] ) ? $args['text'] : '';
$cta   = isset( $args['cta'] ) ? $args['cta'] : '';
$url   = isset( $args['url'] ) ? $args['url'] : '';
?>
<div class="av-empty">
	<span class="av-empty__icon"><?php appverse_the_icon( $icon, 30 ); ?></span>
	<h2 class="av-empty__title"><?php echo esc_html( $title ); ?></h2>
	<?php if ( $text ) : ?>
		<p class="av-empty__text"><?php echo esc_html( $text ); ?></p>
	<?php endif; ?>
	<?php if ( $cta && $url ) : ?>
		<a class="av-btn av-btn--primary" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $cta ); ?></a>
	<?php endif; ?>
</div>
