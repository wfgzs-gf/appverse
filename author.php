<?php
/**
 * Author archive.
 *
 * @package Appverse
 */

get_header();
$author_id = (int) get_queried_object_id();
$level     = appverse_get_user_level( $author_id );
$bio       = get_the_author_meta( 'description', $author_id );
?>
<main id="av-main" class="av-main av-main--author">

	<section class="av-author-card av-card">
		<?php echo appverse_get_avatar( $author_id, 'lg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped builder. ?>
		<div class="av-author-card__info">
			<h1 class="av-author-card__name"><?php echo esc_html( get_the_author_meta( 'display_name', $author_id ) ); ?><?php if ( $level ) : ?><span class="av-level">Lv.<?php echo esc_html( (string) $level ); ?></span><?php endif; ?></h1>
			<p class="av-author-card__bio"><?php echo esc_html( $bio ? $bio : __( '这个人还没有写简介。', 'appverse' ) ); ?></p>
		</div>
	</section>

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
		<?php get_template_part( 'template-parts/empty', null, array( 'icon' => 'user', 'title' => __( '还没有发布内容', 'appverse' ) ) ); ?>
	<?php endif; ?>

</main>
<?php
get_footer();
