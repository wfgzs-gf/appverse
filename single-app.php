<?php
/**
 * Single app detail.
 *
 * @package Appverse
 */

get_header();

while ( have_posts() ) :
	the_post();
	$post_id   = get_the_ID();
	$meta      = appverse_get_app_meta( $post_id );
	$terms     = get_the_terms( $post_id, 'app_category' );
	$cat       = ( ! is_wp_error( $terms ) && $terms ) ? $terms[0]->name : __( '应用', 'appverse' );
	$shots     = $meta['screenshots'];
	$comments  = (int) get_comments_number( $post_id );
	$related   = appverse_related_apps( $post_id, 4 );
	$dev_posts = get_posts(
		array(
			'post_type'      => 'post',
			'posts_per_page' => 2,
			'post_status'    => 'publish',
			'meta_key'       => '_feed_app',
			'meta_value'     => $post_id,
		)
	);
	?>
	<main id="av-main" class="av-main av-main--app">

		<section class="av-card av-apphead">
			<div class="av-apphead__top">
				<?php appverse_app_icon( $post_id, 'xl', array( 'badge' => false ) ); ?>
				<div class="av-apphead__info">
					<div class="av-apphead__name-row">
						<h1 class="av-apphead__name"><?php the_title(); ?></h1>
						<span class="av-chip av-chip--level"><?php echo esc_html( $meta['badge'] ); ?></span>
					</div>
					<p class="av-apphead__dev">
						<?php echo esc_html( $meta['developer'] ); ?>
						<span class="av-post__dot" aria-hidden="true">·</span>
						<?php echo esc_html( $cat ); ?>
					</p>
					<div class="av-apphead__rating">
						<?php echo appverse_get_stars( $meta['rating'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped builder. ?>
						<span class="av-apphead__score"><?php echo esc_html( number_format_i18n( $meta['rating'], 1 ) ); ?></span>
						<span class="av-apphead__downloads">↓<?php echo esc_html( appverse_format_count( $meta['downloads'] ) ); ?></span>
					</div>
				</div>
			</div>

			<div class="av-apphead__actions">
				<?php echo appverse_get_download_button( $post_id, __( '下载安装', 'appverse' ), 'av-btn--primary av-btn--block av-apphead__download' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped builder. ?>
				<button class="av-btn av-btn--ghost av-btn--icon av-apphead__fav" type="button" data-toast="<?php esc_attr_e( '已加入收藏（演示）', 'appverse' ); ?>" aria-label="<?php esc_attr_e( '收藏', 'appverse' ); ?>">
					<?php appverse_the_icon( 'heart', 21 ); ?>
				</button>
				<a class="av-btn av-btn--ghost av-btn--icon" href="<?php echo esc_url( appverse_share_url( $post_id ) ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( '分享', 'appverse' ); ?>">
					<?php appverse_the_icon( 'share', 20 ); ?>
				</a>
			</div>
		</section>

		<div class="av-infobar">
			<div class="av-infobar__item">
				<span class="av-infobar__label"><?php esc_html_e( '版本', 'appverse' ); ?></span>
				<strong class="av-infobar__value"><?php echo esc_html( $meta['version'] ); ?></strong>
			</div>
			<div class="av-infobar__item">
				<span class="av-infobar__label"><?php esc_html_e( '大小', 'appverse' ); ?></span>
				<strong class="av-infobar__value"><?php echo esc_html( $meta['size'] ); ?></strong>
			</div>
			<div class="av-infobar__item">
				<span class="av-infobar__label"><?php esc_html_e( '更新', 'appverse' ); ?></span>
				<strong class="av-infobar__value"><?php echo esc_html( $meta['updated'] ); ?></strong>
			</div>
		</div>

		<div class="av-tabs" data-tabs>
			<button class="av-tab av-tabs__tab is-active" type="button" data-tab="intro"><?php esc_html_e( '简介', 'appverse' ); ?></button>
			<button class="av-tab av-tabs__tab" type="button" data-tab="shots"><?php esc_html_e( '截图', 'appverse' ); ?></button>
			<button class="av-tab av-tabs__tab" type="button" data-tab="changelog"><?php esc_html_e( '更新日志', 'appverse' ); ?></button>
			<button class="av-tab av-tabs__tab" type="button" data-tab="comments"><?php esc_html_e( '评论', 'appverse' ); ?> <span class="av-tabs__count"><?php echo esc_html( (string) $comments ); ?></span></button>
		</div>

		<section class="av-card av-card--pad av-tabpanel is-active" data-panel="intro">
			<div class="av-prose">
				<?php the_content(); ?>
			</div>
			<div class="av-trust">
				<span><?php appverse_the_icon( 'shield', 16 ); ?><?php esc_html_e( '安全检测', 'appverse' ); ?></span>
				<span><?php appverse_the_icon( 'check-circle', 16 ); ?><?php esc_html_e( '无广告', 'appverse' ); ?></span>
				<span><?php appverse_the_icon( 'smartphone', 16 ); ?><?php esc_html_e( 'Android 8.0+', 'appverse' ); ?></span>
			</div>
		</section>

		<section class="av-card av-card--pad av-tabpanel" data-panel="shots" hidden>
			<div class="av-section__head">
				<h2 class="av-section__title"><?php esc_html_e( '应用截图', 'appverse' ); ?></h2>
			</div>
			<?php if ( $shots ) : ?>
				<div class="av-shots">
					<?php foreach ( $shots as $shot_id ) : ?>
						<a class="av-shot" href="<?php echo esc_url( wp_get_attachment_image_url( $shot_id, 'full' ) ); ?>" target="_blank" rel="noopener noreferrer">
							<?php echo wp_get_attachment_image( $shot_id, 'appverse-shot', false, array( 'loading' => 'lazy' ) ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<?php
				$demo_shots = array(
					appverse_demo_asset( 'screens/shot-01.png' ),
					appverse_demo_asset( 'screens/shot-02.png' ),
					appverse_demo_asset( 'screens/shot-03.png' ),
				);
				?>
				<div class="av-shots">
					<?php foreach ( array_filter( $demo_shots ) as $shot_url ) : ?>
						<span class="av-shot"><img src="<?php echo esc_url( $shot_url ); ?>" alt="<?php esc_attr_e( '应用截图', 'appverse' ); ?>" loading="lazy"></span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</section>

		<section class="av-card av-card--pad av-tabpanel" data-panel="changelog" hidden>
			<div class="av-section__head">
				<h2 class="av-section__title"><?php esc_html_e( '更新日志', 'appverse' ); ?></h2>
				<span class="av-section__more"><?php echo esc_html( $meta['version'] ); ?></span>
			</div>
			<div class="av-changelog">
				<?php
				$lines = preg_split( '/\r\n|\r|\n/', (string) $meta['changelog'] );
				foreach ( $lines as $line ) {
					$line = trim( $line );
					if ( '' === $line ) {
						continue;
					}
					if ( preg_match( '/^[vV]?[\d.]+/', $line ) ) {
						echo '<h3 class="av-changelog__version">' . esc_html( $line ) . '</h3>';
					} else {
						echo '<p class="av-changelog__line">' . esc_html( $line ) . '</p>';
					}
				}
				?>
			</div>
		</section>

		<section class="av-card av-card--pad av-tabpanel" data-panel="comments" hidden>
			<?php comments_template(); ?>
		</section>
		<?php if ( $related->have_posts() ) : ?>
			<section class="av-related">
				<div class="av-section__head">
					<h2 class="av-section__title"><?php esc_html_e( '相关应用', 'appverse' ); ?></h2>
					<a class="av-section__more" href="<?php echo esc_url( appverse_apps_url() ); ?>"><?php esc_html_e( '全部', 'appverse' ); ?><?php appverse_the_icon( 'chevron-right', 14 ); ?></a>
				</div>
				<div class="av-related__list">
					<?php
					while ( $related->have_posts() ) :
						$related->the_post();
						get_template_part( 'template-parts/app-row' );
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( $dev_posts ) : ?>
			<section class="av-related">
				<div class="av-section__head">
					<h2 class="av-section__title"><?php esc_html_e( '开发者动态', 'appverse' ); ?></h2>
				</div>
				<div class="av-feed">
					<?php
					global $post;
					foreach ( $dev_posts as $dev_post ) :
						$post = $dev_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
						setup_postdata( $post );
						get_template_part( 'template-parts/post-card' );
					endforeach;
					wp_reset_postdata();
					?>
				</div>
			</section>
		<?php endif; ?>

		<div class="av-downloadbar">
			<?php appverse_app_icon( $post_id, 'xs', array( 'badge' => false ) ); ?>
			<div class="av-downloadbar__body">
				<span class="av-downloadbar__name"><?php the_title(); ?></span>
				<span class="av-downloadbar__meta"><?php echo esc_html( appverse_app_version_line( $meta ) ); ?></span>
			</div>
			<?php echo appverse_get_download_button( $post_id, __( '下载', 'appverse' ), 'av-btn--primary' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped builder. ?>
		</div>

	</main>
	<?php
endwhile;

get_footer();
