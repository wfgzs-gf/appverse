<?php
/**
 * Community feed (posts page: 广场).
 *
 * @package Appverse
 */

get_header();

$tabs   = appverse_get_feed_tabs();
$active = appverse_current_feed_tab();
$base   = appverse_page_url( 'square' );
?>
<main id="av-main" class="av-main av-main--feed">

	<nav class="av-feedtabs" aria-label="<?php esc_attr_e( '动态分类', 'appverse' ); ?>">
		<?php foreach ( $tabs as $key => $label ) : ?>
			<?php
			$url = 'all' === $key ? $base : add_query_arg( 'tab', $key, $base );
			?>
			<a class="av-feedtab<?php echo $active === $key ? ' is-active' : ''; ?>" href="<?php echo esc_url( $url ); ?>" <?php echo $active === $key ? 'aria-current="page"' : ''; ?>>
				<?php echo esc_html( $label ); ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<?php if ( have_posts() ) : ?>
		<div class="av-feed">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/post-card' );
			endwhile;
			?>
		</div>
		<?php appverse_pagination(); ?>
	<?php else : ?>
		<?php
		get_template_part(
			'template-parts/empty',
			null,
			array(
				'icon'  => 'comment',
				'title' => __( '还没有动态', 'appverse' ),
				'text'  => __( '去应用详情页看看，或者发布第一条使用体验。', 'appverse' ),
				'cta'   => __( '逛逛应用库', 'appverse' ),
				'url'   => appverse_apps_url(),
			)
		);
		?>
	<?php endif; ?>

	<a class="av-fab" href="<?php echo esc_url( is_user_logged_in() ? admin_url( 'post-new.php' ) : wp_login_url( appverse_page_url( 'square' ) ) ); ?>" aria-label="<?php esc_attr_e( '发布动态', 'appverse' ); ?>">
		<?php appverse_the_icon( 'plus', 26 ); ?>
	</a>

</main>
<?php
get_footer();
