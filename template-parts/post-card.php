<?php
/**
 * Community post card.
 *
 * @package Appverse
 */

$post_id  = isset( $args['post_id'] ) ? (int) $args['post_id'] : get_the_ID();
if ( ! $post_id ) {
	return;
}
$author_id = (int) get_post_field( 'post_author', $post_id );
$level     = appverse_get_user_level( $author_id );
$reaction  = appverse_get_reaction_state( $post_id );
$comments  = (int) get_comments_number( $post_id );
$avatar    = appverse_get_avatar( $author_id, 'sm' );
?>
<article class="av-post" data-post-id="<?php echo esc_attr( (string) $post_id ); ?>">
	<header class="av-post__head">
		<a class="av-post__avatar" href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>" aria-label="<?php echo esc_attr( get_the_author_meta( 'display_name', $author_id ) ); ?>">
			<?php echo $avatar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped builder. ?>
		</a>
		<div class="av-post__id">
			<div class="av-post__name-row">
				<a class="av-post__name" href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>"><?php echo esc_html( get_the_author_meta( 'display_name', $author_id ) ); ?></a>
				<?php if ( $level ) : ?>
					<span class="av-level">Lv.<?php echo esc_html( (string) $level ); ?></span>
				<?php endif; ?>
			</div>
			<div class="av-post__meta">
				<span><?php echo esc_html( appverse_post_time( $post_id ) ); ?></span>
				<span class="av-post__dot" aria-hidden="true">·</span>
				<span><?php echo esc_html( appverse_get_feed_device( $post_id ) ); ?></span>
			</div>
		</div>
		<button class="av-post__menu" type="button" data-toast="<?php esc_attr_e( '更多操作即将上线', 'appverse' ); ?>" aria-label="<?php esc_attr_e( '更多', 'appverse' ); ?>">
			<?php appverse_the_icon( 'more', 20 ); ?>
		</button>
	</header>

	<div class="av-post__text"><?php echo wp_kses_post( apply_filters( 'the_content', get_post_field( 'post_content', $post_id ) ) ); ?></div>

	<?php if ( appverse_get_attached_app( $post_id ) ) : ?>
		<?php get_template_part( 'template-parts/app-embed', null, array( 'app' => appverse_get_attached_app( $post_id ) ) ); ?>
	<?php endif; ?>

	<footer class="av-post__actions">
		<button class="av-action<?php echo $reaction['liked'] ? ' is-liked' : ''; ?>" type="button" data-like data-post-id="<?php echo esc_attr( (string) $post_id ); ?>" data-type="like" aria-pressed="<?php echo $reaction['liked'] ? 'true' : 'false'; ?>">
			<?php appverse_the_icon( 'thumb-up', 19 ); ?>
			<span class="av-action__count" data-like-count><?php echo esc_html( (string) $reaction['likeCount'] ); ?></span>
		</button>
		<button class="av-action<?php echo $reaction['disliked'] ? ' is-disliked' : ''; ?>" type="button" data-like data-post-id="<?php echo esc_attr( (string) $post_id ); ?>" data-type="dislike" aria-pressed="<?php echo $reaction['disliked'] ? 'true' : 'false'; ?>">
			<?php appverse_the_icon( 'thumb-down', 19 ); ?>
			<span class="av-action__count" data-dislike-count><?php echo esc_html( (string) $reaction['dislikeCount'] ); ?></span>
		</button>
		<a class="av-action" href="<?php echo esc_url( get_permalink( $post_id ) . '#comments' ); ?>">
			<?php appverse_the_icon( 'comment', 19 ); ?>
			<span class="av-action__count"><?php echo esc_html( (string) $comments ); ?></span>
		</a>
	</footer>
</article>
