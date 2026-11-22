<?php
/**
 * Template Name: 独立开发
 * Description: 独立开发者应用专区。
 *
 * @package Appverse
 */

get_header();

$indie_apps = new WP_Query(
	array(
		'post_type'      => 'app',
		'posts_per_page' => 12,
		'post_status'    => 'publish',
		'tax_query'      => array(
			array(
				'taxonomy' => 'app_category',
				'field'    => 'slug',
				'terms'    => 'indie',
			),
		),
	)
);

$developers = array();
if ( $indie_apps->have_posts() ) {
	foreach ( $indie_apps->posts as $indie_post ) {
		$author_id = (int) $indie_post->post_author;
		if ( ! isset( $developers[ $author_id ] ) ) {
			$developers[ $author_id ] = array(
				'id'    => $author_id,
				'name'  => get_the_author_meta( 'display_name', $author_id ),
				'bio'   => get_the_author_meta( 'description', $author_id ),
				'level' => appverse_get_user_level( $author_id ),
				'count' => 0,
			);
		}
		++$developers[ $author_id ]['count'];
	}
	wp_reset_postdata();
}
?>
<main id="av-main" class="av-main av-main--indie">

	<section class="av-indie-hero">
		<p class="av-pagehead__eyebrow"><?php esc_html_e( 'INDIE MAKERS', 'appverse' ); ?></p>
		<h1 class="av-indie-hero__title"><?php esc_html_e( '小团队，也值得被看见。', 'appverse' ); ?></h1>
		<p class="av-indie-hero__text"><?php esc_html_e( '这里收录由个人或小团队独立完成的应用。没有买量，没有换皮，只有认真做出来的东西。', 'appverse' ); ?></p>
		<div class="av-indie-hero__actions">
			<a class="av-btn av-btn--primary" href="<?php echo esc_url( appverse_page_url( 'about' ) ); ?>"><?php esc_html_e( '了解独立开发计划', 'appverse' ); ?></a>
			<a class="av-btn av-btn--ghost" href="<?php echo esc_url( appverse_page_url( 'square' ) ); ?>"><?php esc_html_e( '看开发者动态', 'appverse' ); ?></a>
		</div>
	</section>

	<?php if ( $developers ) : ?>
		<section class="av-devs">
			<div class="av-section__head">
				<h2 class="av-section__title"><?php esc_html_e( '独立开发者', 'appverse' ); ?></h2>
			</div>
			<div class="av-devs__list">
				<?php foreach ( array_slice( $developers, 0, 4 ) as $developer ) : ?>
					<a class="av-devcard" href="<?php echo esc_url( get_author_posts_url( $developer['id'] ) ); ?>">
						<?php echo appverse_get_avatar( $developer['id'], 'md' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped builder. ?>
						<span class="av-devcard__name"><?php echo esc_html( $developer['name'] ); ?><?php if ( $developer['level'] ) : ?><span class="av-level">Lv.<?php echo esc_html( (string) $developer['level'] ); ?></span><?php endif; ?></span>
						<span class="av-devcard__meta"><?php echo esc_html( sprintf( /* translators: %d apps */ __( '%d 款作品', 'appverse' ), (int) $developer['count'] ) ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<section class="av-search-section">
		<div class="av-section__head">
			<h2 class="av-section__title"><?php esc_html_e( '独立作品', 'appverse' ); ?></h2>
			<span class="av-section__more"><?php echo esc_html( (string) $indie_apps->found_posts ); ?></span>
		</div>
		<?php if ( $indie_apps->have_posts() ) : ?>
			<div class="av-appgrid">
				<?php
				while ( $indie_apps->have_posts() ) :
					$indie_apps->the_post();
					get_template_part( 'template-parts/app-card' );
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		<?php else : ?>
			<?php get_template_part( 'template-parts/empty', null, array( 'icon' => 'code', 'title' => __( '独立专区正在筹备', 'appverse' ), 'text' => __( '把应用加入「独立开发」分类后就会显示在这里。', 'appverse' ) ) ); ?>
		<?php endif; ?>
	</section>

</main>
<?php
get_footer();
