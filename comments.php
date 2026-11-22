<?php
/**
 * Comments template.
 *
 * @package Appverse
 */

if ( post_password_required() ) {
	return;
}
?>
<div id="comments" class="av-comments">
	<?php if ( have_comments() ) : ?>
		<div class="av-comments__head">
			<h2 class="av-comments__title"><?php esc_html_e( '评论', 'appverse' ); ?> <span class="av-comments__count"><?php echo esc_html( (string) get_comments_number() ); ?></span></h2>
		</div>

		<ol class="av-comments__list">
			<?php
			wp_list_comments(
				array(
					'callback'   => 'appverse_comment_callback',
					'style'      => 'ol',
					'short_ping' => true,
				)
			);
			?>
		</ol>

		<?php
		the_comments_pagination(
			array(
				'prev_text' => appverse_icon( 'chevron-left', 16 ),
				'next_text' => appverse_icon( 'chevron-right', 16 ),
				'class'     => 'av-pager',
			)
		);
		?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
		<p class="av-comments__closed"><?php esc_html_e( '评论已关闭。', 'appverse' ); ?></p>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'title_reply'        => __( '说点什么', 'appverse' ),
			'title_reply_before' => '<h3 class="av-comments__form-title">',
			'title_reply_after'  => '</h3>',
			'class_form'         => 'av-form',
			'class_submit'       => 'av-btn av-btn--primary',
			'label_submit'       => __( '发表评论', 'appverse' ),
			'comment_field'      => '<p class="av-form__field"><label for="comment" class="av-form__label">' . esc_html__( '评论内容', 'appverse' ) . '</label><textarea id="comment" name="comment" class="av-form__textarea" rows="4" required></textarea></p>',
			'fields'             => array(
				'author' => '<p class="av-form__field"><label for="author" class="av-form__label">' . esc_html__( '昵称', 'appverse' ) . '</label><input id="author" name="author" type="text" class="av-form__input" value="' . esc_attr( wp_get_current_commenter()['comment_author'] ) . '"></p>',
				'email'  => '<p class="av-form__field"><label for="email" class="av-form__label">' . esc_html__( '邮箱', 'appverse' ) . '</label><input id="email" name="email" type="email" class="av-form__input" value="' . esc_attr( wp_get_current_commenter()['comment_author_email'] ) . '"></p>',
			),
			'comment_notes_before' => '<p class="av-form__note">' . esc_html__( '邮箱不会公开。', 'appverse' ) . '</p>',
		)
	);
	?>
</div>
