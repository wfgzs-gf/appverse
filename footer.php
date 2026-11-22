<?php
/**
 * Theme footer.
 *
 * @package Appverse
 */

?>
	<?php appverse_tabbar(); ?>

	<div class="av-modal" data-modal hidden>
		<div class="av-modal__backdrop" data-modal-close></div>
		<div class="av-modal__panel" role="dialog" aria-modal="true" aria-labelledby="av-modal-title">
			<button class="av-modal__close" type="button" data-modal-close aria-label="<?php esc_attr_e( '关闭', 'appverse' ); ?>">
				<?php appverse_the_icon( 'close', 20 ); ?>
			</button>
			<div class="av-modal__icon" data-modal-icon></div>
			<p class="av-modal__eyebrow"><?php esc_html_e( '准备下载', 'appverse' ); ?></p>
			<h2 class="av-modal__title" id="av-modal-title" data-modal-title><?php esc_html_e( '下载应用', 'appverse' ); ?></h2>
			<p class="av-modal__meta" data-modal-meta></p>
			<p class="av-modal__tip"><?php echo esc_html( get_theme_mod( 'appverse_download_tip', __( '下载即表示同意《用户协议》与《隐私政策》，应用由开发者提供，请自行甄别。', 'appverse' ) ) ); ?></p>
			<a class="av-btn av-btn--primary av-btn--block" data-modal-continue href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php esc_html_e( '继续下载', 'appverse' ); ?>
			</a>
			<button class="av-btn av-btn--ghost av-btn--block" type="button" data-modal-close>
				<?php esc_html_e( '取消', 'appverse' ); ?>
			</button>
		</div>
	</div>

	<div class="av-toast" data-toast role="status" aria-live="polite"></div>
</div><!-- .av-app -->
<?php wp_footer(); ?>
</body>
</html>
