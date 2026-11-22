<?php
/**
 * Template Name: 应用分类
 * Description: 浏览应用分类。
 *
 * @package Appverse
 */

get_header();

$categories = appverse_get_app_categories();
$icon_map   = array(
	'education' => 'edit',
	'life'      => 'home',
	'health'    => 'heart',
	'tools'     => 'settings',
	'media'     => 'play',
	'office'    => 'task',
	'games'     => 'trophy',
	'indie'     => 'code',
);
?>
<main id="av-main" class="av-main av-main--categories">

	<?php
	appverse_page_heading(
		__( '应用分类', 'appverse' ),
		__( '按使用场景找到你需要的那一款。', 'appverse' ),
		__( 'CATEGORIES', 'appverse' )
	);
	?>

	<?php if ( $categories ) : ?>
		<div class="av-catgrid">
			<?php foreach ( $categories as $category ) : ?>
				<?php
				$icon = isset( $icon_map[ $category->slug ] ) ? $icon_map[ $category->slug ] : 'grid';
				$url  = get_term_link( $category );
				if ( is_wp_error( $url ) ) {
					continue;
				}
				?>
				<a class="av-catcard" href="<?php echo esc_url( $url ); ?>">
					<span class="av-catcard__icon"><?php appverse_the_icon( $icon, 24 ); ?></span>
					<span class="av-catcard__name"><?php echo esc_html( $category->name ); ?></span>
					<span class="av-catcard__count"><?php echo esc_html( sprintf( /* translators: %d apps */ __( '%d 款应用', 'appverse' ), (int) $category->count ) ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<?php get_template_part( 'template-parts/empty', null, array( 'icon' => 'grid', 'title' => __( '还没有分类', 'appverse' ) ) ); ?>
	<?php endif; ?>

	<a class="av-catfeature" href="<?php echo esc_url( appverse_page_url( 'indie' ) ); ?>">
		<span class="av-catfeature__icon"><?php appverse_the_icon( 'code', 28 ); ?></span>
		<span class="av-catfeature__body">
			<span class="av-catfeature__eyebrow"><?php esc_html_e( '独立开发者计划', 'appverse' ); ?></span>
			<span class="av-catfeature__title"><?php esc_html_e( '小团队，也值得被看见', 'appverse' ); ?></span>
			<span class="av-catfeature__text"><?php esc_html_e( '发现那些认真做产品的人。', 'appverse' ); ?></span>
		</span>
		<?php appverse_the_icon( 'arrow-right', 20, 'av-catfeature__arrow' ); ?>
	</a>

</main>
<?php
get_footer();
