<?php
/**
 * Single community post.
 *
 * @package Appverse
 */

get_header();

while ( have_posts() ) :
	the_post();
	$post_id   = get_the_ID();
	$author_id = (int) get_post_field( 'post_author', $post_id );
	$level     = appverse_get_user_level( $author_id );
	$reaction  = appverse_get_reaction_state( $post_id );
	$comments  = (int) get_comments_number( $post_id );
	$related   = new WP_Query(
		array(
			'post_type'           => 'post',
			'posts_per_page'      => 2,
			'post__not_in'        => array( $post_id ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
	?>
	<main id="av-main" class="av-main av-main--single">

		<article class="av-card av-card--pad av-article" data-post-id="<?php echo esc_attr( (string) $post_id ); ?>">
			<header class="av-post__head">
				<a class="av-post__avatar" href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>">
					<?php echo appverse_get_avatar( $author_id, 'sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped builder. ?>
				</a>
				<div class="av-post__id">
					<div class="av-post__name-row">
						<a class="av-post__name" href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>"><?php echo esc_html( get_the_author_meta( 'display_name', $author_id ) ); ?></a>
						<?php if ( $level ) : ?><span class="av-level">Lv.<?php echo esc_html( (string) $level ); ?></span><?php endif; ?>
					</div>
					<div class="av-post__meta">
						<span><?php echo esc_html( get_the_date( 'Y-m-d H:i' ) ); ?></span>
						<span class="av-post__dot" aria-hidden="true">·</span>
						<span><?php echo esc_html( appverse_get_feed_device( $post_id ) ); ?></span>
					</div>
				</div>
			</header>

			<h1 class="av-article__title"><?php the_title(); ?></h1>
			<div class="av-post__text av-prose"><?php the_content(); ?></div>

			<?php if ( appverse_get_attached_app( $post_id ) ) : ?>
				<?php get_template_part( 'template-parts/app-embed', null, array( 'app' => appverse_get_attached_app( $post_id ) ) ); ?>
			<?php endif; ?>

			<footer class="av-post__actions av-article__actions">
				<button class="av-action<?php echo $reaction['liked'] ? ' is-liked' : ''; ?>" type="button" data-like data-post-id="<?php echo esc_attr( (string) $post_id ); ?>" data-type="like" aria-pressed="<?php echo $reaction['liked'] ? 'true' : 'false'; ?>">
					<?php appverse_the_icon( 'thumb-up', 19 ); ?><span class="av-action__count" data-like-count><?php echo esc_html( (string) $reaction['likeCount'] ); ?></span>
				</button>
				<button class="av-action<?php echo $reaction['disliked'] ? ' is-disliked' : ''; ?>" type="button" data-like data-post-id="<?php echo esc_attr( (string) $post_id ); ?>" data-type="dislike" aria-pressed="<?php echo $reaction['disliked'] ? 'true' : 'false'; ?>">
					<?php appverse_the_icon( 'thumb-down', 19 ); ?><span class="av-action__count" data-dislike-count><?php echo esc_html( (string) $reaction['dislikeCount'] ); ?></span>
				</button>
				<span class="av-action"><?php appverse_the_icon( 'comment', 19 ); ?><span class="av-action__count"><?php echo esc_html( (string) $comments ); ?></span></span>
			</footer>
		</article>

		<section class="av-card av-card--pad">
			<?php comments_template(); ?>
		</section>

		<?php if ( $related->have_posts() ) : ?>
			<section class="av-related">
				<div class="av-section__head">
					<h2 class="av-section__title"><?php esc_html_e( '更多动态', 'appverse' ); ?></h2>
				</div>
				<div class="av-feed">
					<?php
					global $post;
					foreach ( $related->posts as $related_post ) :
						$post = $related_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
						setup_postdata( $post );
						get_template_part( 'template-parts/post-card' );
					endforeach;
					wp_reset_postdata();
					?>
				</div>
			</section>
		<?php endif; ?>

	</main>
	<?php
endwhile;

get_footer();
