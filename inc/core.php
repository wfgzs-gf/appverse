<?php
/**
 * Appverse core: content types, fields, customizer, AJAX and admin tools.
 *
 * @package Appverse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * Content types
 * ---------------------------------------------------------------------- */

/**
 * Register app post type and taxonomies.
 */
function appverse_register_content_types() {
	register_post_type(
		'app',
		array(
			'labels'        => array(
				'name'               => __( '应用', 'appverse' ),
				'singular_name'      => __( '应用', 'appverse' ),
				'add_new'            => __( '上架应用', 'appverse' ),
				'add_new_item'       => __( '上架新应用', 'appverse' ),
				'edit_item'          => __( '编辑应用', 'appverse' ),
				'new_item'           => __( '新应用', 'appverse' ),
				'view_item'          => __( '查看应用', 'appverse' ),
				'search_items'       => __( '搜索应用', 'appverse' ),
				'not_found'          => __( '还没有应用', 'appverse' ),
				'not_found_in_trash' => __( '回收站里没有应用', 'appverse' ),
				'all_items'          => __( '全部应用', 'appverse' ),
				'menu_name'          => __( '应用商店', 'appverse' ),
			),
			'public'        => true,
			'has_archive'   => 'apps',
			'rewrite'       => array(
				'slug'       => 'app',
				'with_front' => false,
			),
			'menu_icon'     => 'dashicons-screenoptions',
			'menu_position' => 5,
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'comments', 'revisions' ),
			'show_in_rest'  => true,
			'taxonomies'    => array( 'app_category', 'app_tag', 'app_collection' ),
		)
	);

	register_taxonomy(
		'app_category',
		array( 'app' ),
		array(
			'labels'            => array(
				'name'          => __( '应用分类', 'appverse' ),
				'singular_name' => __( '分类', 'appverse' ),
				'add_new_item'  => __( '添加分类', 'appverse' ),
				'edit_item'     => __( '编辑分类', 'appverse' ),
				'search_items'  => __( '搜索分类', 'appverse' ),
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'app-category', 'with_front' => false ),
		)
	);

	register_taxonomy(
		'app_tag',
		array( 'app' ),
		array(
			'labels'            => array(
				'name'          => __( '应用标签', 'appverse' ),
				'singular_name' => __( '标签', 'appverse' ),
			),
			'hierarchical'      => false,
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'app-tag', 'with_front' => false ),
		)
	);

	register_taxonomy(
		'app_collection',
		array( 'app' ),
		array(
			'labels'            => array(
				'name'          => __( '应用集', 'appverse' ),
				'singular_name' => __( '应用集', 'appverse' ),
				'add_new_item'  => __( '新建应用集', 'appverse' ),
			),
			'hierarchical'      => false,
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'collection', 'with_front' => false ),
		)
	);

	register_taxonomy(
		'feed_type',
		array( 'post' ),
		array(
			'labels'            => array(
				'name'          => __( '动态类型', 'appverse' ),
				'singular_name' => __( '动态类型', 'appverse' ),
			),
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => false,
			'hierarchical'      => true,
		)
	);
}
add_action( 'init', 'appverse_register_content_types' );

/**
 * Make app archives sortable by download count.
 *
 * @param WP_Query $query Query object.
 */
function appverse_pre_get_posts( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	$post_type = $query->get( 'post_type' );
	if ( ! $query->is_post_type_archive( 'app' ) && 'app' !== $post_type && ! $query->is_tax( array( 'app_category', 'app_tag', 'app_collection' ) ) ) {
		return;
	}

	$sort = isset( $_GET['sort'] ) ? sanitize_key( wp_unslash( $_GET['sort'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( 'hot' === $sort ) {
		$query->set( 'meta_key', '_app_downloads' );
		$query->set( 'orderby', 'meta_value_num' );
		$query->set( 'order', 'DESC' );
	} elseif ( 'rating' === $sort ) {
		$query->set( 'meta_key', '_app_rating' );
		$query->set( 'orderby', 'meta_value_num' );
		$query->set( 'order', 'DESC' );
	} else {
		$query->set( 'orderby', 'date' );
		$query->set( 'order', 'DESC' );
	}

	$badge = isset( $_GET['badge'] ) ? sanitize_key( wp_unslash( $_GET['badge'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( 'official' === $badge ) {
		$query->set(
			'meta_query',
			array(
				array(
					'key'   => '_app_official',
					'value' => '1',
				),
			)
		);
	}
}
add_action( 'pre_get_posts', 'appverse_pre_get_posts' );

/* -------------------------------------------------------------------------
 * Post meta
 * ---------------------------------------------------------------------- */

/**
 * App field definitions.
 *
 * @return array<string, array{label:string, type:string, default:mixed}>
 */
function appverse_app_fields() {
	return array(
		'version'    => array(
			'label'   => __( '版本号', 'appverse' ),
			'type'    => 'text',
			'default' => '1.0.0',
		),
		'size'       => array(
			'label'   => __( '安装包大小', 'appverse' ),
			'type'    => 'text',
			'default' => '32.6 MB',
		),
		'downloads'  => array(
			'label'   => __( '下载量', 'appverse' ),
			'type'    => 'number',
			'default' => 0,
		),
		'developer'  => array(
			'label'   => __( '开发者', 'appverse' ),
			'type'    => 'text',
			'default' => '',
		),
		'rating'     => array(
			'label'   => __( '评分（0-5）', 'appverse' ),
			'type'    => 'number',
			'default' => 4.8,
		),
		'official'   => array(
			'label'   => __( '官方版本', 'appverse' ),
			'type'    => 'checkbox',
			'default' => 1,
		),
		'badge'      => array(
			'label'   => __( '角标文字', 'appverse' ),
			'type'    => 'text',
			'default' => __( '正式版', 'appverse' ),
		),
		'download_url' => array(
			'label'   => __( '下载地址', 'appverse' ),
			'type'    => 'url',
			'default' => '',
		),
		'package'    => array(
			'label'   => __( '应用包名', 'appverse' ),
			'type'    => 'text',
			'default' => '',
		),
		'updated'    => array(
			'label'   => __( '更新时间', 'appverse' ),
			'type'    => 'date',
			'default' => '',
		),
		'changelog'  => array(
			'label'   => __( '更新日志', 'appverse' ),
			'type'    => 'textarea',
			'default' => '',
		),
	);
}

/**
 * Register app meta for REST and the block editor.
 */
function appverse_register_meta() {
	foreach ( appverse_app_fields() as $key => $field ) {
		$type = 'number' === $field['type'] ? 'number' : ( 'checkbox' === $field['type'] ? 'boolean' : 'string' );
		register_post_meta(
			'app',
			'_app_' . $key,
			array(
				'type'              => $type,
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'appverse_sanitize_app_meta_value',
				'auth_callback'     => function ( $allowed, $meta_key, $post_id ) {
					return current_user_can( 'edit_post', $post_id );
				},
			)
		);
	}

	register_post_meta(
		'app',
		'_app_screenshots',
		array(
			'type'          => 'string',
			'single'        => true,
			'show_in_rest'  => false,
			'auth_callback' => function ( $allowed, $meta_key, $post_id ) {
				return current_user_can( 'edit_post', $post_id );
			},
		)
	);

	register_post_meta(
		'post',
		'_feed_device',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	register_post_meta(
		'post',
		'_feed_app',
		array(
			'type'              => 'integer',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'absint',
		)
	);
}
add_action( 'init', 'appverse_register_meta' );

/**
 * Sanitize an app meta value.
 *
 * @param mixed $value Raw value.
 * @return mixed
 */
function appverse_sanitize_app_meta_value( $value ) {
	if ( is_bool( $value ) ) {
		return $value;
	}
	if ( is_numeric( $value ) ) {
		return 0 + $value;
	}
	return sanitize_textarea_field( (string) $value );
}

/**
 * Add app details meta box.
 */
function appverse_add_meta_boxes() {
	add_meta_box(
		'appverse-app-details',
		__( '应用信息', 'appverse' ),
		'appverse_app_meta_box',
		'app',
		'normal',
		'high'
	);
	add_meta_box(
		'appverse-feed-app',
		__( '动态关联', 'appverse' ),
		'appverse_feed_meta_box',
		'post',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'appverse_add_meta_boxes' );

/**
 * Render app details meta box.
 *
 * @param WP_Post $post Post object.
 */
function appverse_app_meta_box( $post ) {
	wp_nonce_field( 'appverse_save_app_meta', 'appverse_app_nonce' );

	$fields      = appverse_app_fields();
	$screenshots = appverse_get_screenshot_ids( $post->ID );
	?>
	<div class="av-admin-grid">
		<?php foreach ( $fields as $key => $field ) : ?>
			<?php
			$meta_key = '_app_' . $key;
			$value    = get_post_meta( $post->ID, $meta_key, true );
			if ( '' === $value || null === $value ) {
				$value = $field['default'];
			}
			?>
			<p class="av-admin-field av-admin-field--<?php echo esc_attr( $field['type'] ); ?>">
				<label for="<?php echo esc_attr( $meta_key ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
				<?php if ( 'textarea' === $field['type'] ) : ?>
					<textarea class="widefat" rows="4" id="<?php echo esc_attr( $meta_key ); ?>" name="<?php echo esc_attr( $meta_key ); ?>"><?php echo esc_textarea( (string) $value ); ?></textarea>
				<?php elseif ( 'checkbox' === $field['type'] ) : ?>
					<label class="av-admin-check">
						<input type="checkbox" id="<?php echo esc_attr( $meta_key ); ?>" name="<?php echo esc_attr( $meta_key ); ?>" value="1" <?php checked( $value, 1 ); ?>>
						<?php esc_html_e( '标记为官方版本', 'appverse' ); ?>
					</label>
				<?php else : ?>
					<input
						class="widefat"
						type="<?php echo esc_attr( 'number' === $field['type'] ? 'number' : ( 'date' === $field['type'] ? 'date' : ( 'url' === $field['type'] ? 'url' : 'text' ) ) ); ?>"
						id="<?php echo esc_attr( $meta_key ); ?>"
						name="<?php echo esc_attr( $meta_key ); ?>"
						value="<?php echo esc_attr( (string) $value ); ?>"
						<?php echo 'number' === $field['type'] ? 'step="0.1" min="0"' : ''; ?>
					>
				<?php endif; ?>
			</p>
		<?php endforeach; ?>
	</div>

	<div class="av-admin-shots">
		<p><strong><?php esc_html_e( '应用截图', 'appverse' ); ?></strong></p>
		<p class="description"><?php esc_html_e( '选择 3–6 张截图，建议尺寸 720×1280。', 'appverse' ); ?></p>
		<input type="hidden" id="appverse_screenshots" name="appverse_screenshots" value="<?php echo esc_attr( implode( ',', $screenshots ) ); ?>">
		<div class="av-admin-shots__list" id="appverse-shots-list">
			<?php foreach ( $screenshots as $attachment_id ) : ?>
				<?php $thumb = wp_get_attachment_image_url( $attachment_id, 'thumbnail' ); ?>
				<?php if ( $thumb ) : ?>
					<figure data-id="<?php echo esc_attr( $attachment_id ); ?>">
						<img src="<?php echo esc_url( $thumb ); ?>" alt="">
						<button type="button" class="button-link av-admin-shots__remove" aria-label="<?php esc_attr_e( '移除截图', 'appverse' ); ?>">×</button>
					</figure>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
		<button type="button" class="button" id="appverse-shots-add"><?php esc_html_e( '选择截图', 'appverse' ); ?></button>
	</div>
	<?php
}

/**
 * Render post feed meta box.
 *
 * @param WP_Post $post Post object.
 */
function appverse_feed_meta_box( $post ) {
	wp_nonce_field( 'appverse_save_feed_meta', 'appverse_feed_nonce' );
	$device = get_post_meta( $post->ID, '_feed_device', true );
	$app_id = (int) get_post_meta( $post->ID, '_feed_app', true );
	$apps   = get_posts(
		array(
			'post_type'      => 'app',
			'posts_per_page' => 100,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		)
	);
	?>
	<p>
		<label for="feed_device"><strong><?php esc_html_e( '发布设备', 'appverse' ); ?></strong></label>
		<input class="widefat" type="text" id="feed_device" name="feed_device" value="<?php echo esc_attr( $device ); ?>" placeholder="MEIZU 21 Pro">
	</p>
	<p>
		<label for="feed_app"><strong><?php esc_html_e( '关联应用', 'appverse' ); ?></strong></label>
		<select class="widefat" id="feed_app" name="feed_app">
			<option value="0"><?php esc_html_e( '不关联', 'appverse' ); ?></option>
			<?php foreach ( $apps as $app ) : ?>
				<option value="<?php echo esc_attr( $app->ID ); ?>" <?php selected( $app_id, $app->ID ); ?>><?php echo esc_html( $app->post_title ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<?php
}

/**
 * Save app meta.
 *
 * @param int $post_id Post ID.
 */
function appverse_save_app_meta( $post_id ) {
	if ( ! isset( $_POST['appverse_app_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['appverse_app_nonce'] ) ), 'appverse_save_app_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach ( appverse_app_fields() as $key => $field ) {
		$name = '_app_' . $key;
		if ( 'checkbox' === $field['type'] ) {
			update_post_meta( $post_id, $name, isset( $_POST[ $name ] ) ? 1 : 0 );
			continue;
		}
		if ( ! isset( $_POST[ $name ] ) ) {
			continue;
		}
		$raw = wp_unslash( $_POST[ $name ] );
		switch ( $field['type'] ) {
			case 'number':
				$value = (float) $raw;
				if ( 'downloads' === $key ) {
					$value = absint( $value );
				}
				break;
			case 'url':
				$value = esc_url_raw( (string) $raw );
				break;
			case 'date':
				$value = sanitize_text_field( (string) $raw );
				break;
			case 'textarea':
				$value = sanitize_textarea_field( (string) $raw );
				break;
			default:
				$value = sanitize_text_field( (string) $raw );
				break;
		}
		update_post_meta( $post_id, $name, $value );
	}

	if ( isset( $_POST['appverse_screenshots'] ) ) {
		$ids = array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_POST['appverse_screenshots'] ) ) ) ) );
		update_post_meta( $post_id, '_app_screenshots', implode( ',', array_unique( $ids ) ) );
	}
}
add_action( 'save_post_app', 'appverse_save_app_meta' );

/**
 * Save feed meta.
 *
 * @param int $post_id Post ID.
 */
function appverse_save_feed_meta( $post_id ) {
	if ( ! isset( $_POST['appverse_feed_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['appverse_feed_nonce'] ) ), 'appverse_save_feed_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['feed_device'] ) ) {
		update_post_meta( $post_id, '_feed_device', sanitize_text_field( wp_unslash( $_POST['feed_device'] ) ) );
	}
	if ( isset( $_POST['feed_app'] ) ) {
		update_post_meta( $post_id, '_feed_app', absint( wp_unslash( $_POST['feed_app'] ) ) );
	}
}
add_action( 'save_post_post', 'appverse_save_feed_meta' );

/* -------------------------------------------------------------------------
 * User profile fields
 * ---------------------------------------------------------------------- */

/**
 * User field definitions.
 *
 * @return array<string, array{label:string, type:string, default:mixed}>
 */
function appverse_user_fields() {
	return array(
		'level'       => array(
			'label'   => __( '等级', 'appverse' ),
			'type'    => 'number',
			'default' => 0,
		),
		'exp'         => array(
			'label'   => __( '当前经验', 'appverse' ),
			'type'    => 'number',
			'default' => 0,
		),
		'exp_max'     => array(
			'label'   => __( '升级所需经验', 'appverse' ),
			'type'    => 'number',
			'default' => 1000,
		),
		'points'      => array(
			'label'   => __( '积分', 'appverse' ),
			'type'    => 'number',
			'default' => 0,
		),
		'share_value' => array(
			'label'   => __( '分享值', 'appverse' ),
			'type'    => 'number',
			'default' => 0,
		),
		'traffic'     => array(
			'label'   => __( '流量（如 0MB）', 'appverse' ),
			'type'    => 'text',
			'default' => '0MB',
		),
		'following'   => array(
			'label'   => __( '关注数', 'appverse' ),
			'type'    => 'number',
			'default' => 0,
		),
	);
}

/**
 * Default user meta for new accounts.
 *
 * @param int $user_id User ID.
 */
function appverse_new_user_defaults( $user_id ) {
	foreach ( appverse_user_fields() as $key => $field ) {
		if ( '' === get_user_meta( $user_id, 'appverse_' . $key, true ) ) {
			update_user_meta( $user_id, 'appverse_' . $key, $field['default'] );
		}
	}
}
add_action( 'user_register', 'appverse_new_user_defaults' );

/**
 * Render profile fields.
 *
 * @param WP_User $user User object.
 */
function appverse_render_user_fields( $user ) {
	if ( ! current_user_can( 'edit_user', $user->ID ) ) {
		return;
	}
	wp_nonce_field( 'appverse_save_user_meta', 'appverse_user_nonce' );
	?>
	<h2><?php esc_html_e( 'Appverse 应用商店资料', 'appverse' ); ?></h2>
	<table class="form-table" role="presentation">
		<tbody>
		<?php foreach ( appverse_user_fields() as $key => $field ) : ?>
			<?php
			$meta_key = 'appverse_' . $key;
			$value    = get_user_meta( $user->ID, $meta_key, true );
			if ( '' === $value ) {
				$value = $field['default'];
			}
			?>
			<tr>
				<th><label for="<?php echo esc_attr( $meta_key ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
				<td>
					<input
						type="<?php echo esc_attr( 'number' === $field['type'] ? 'number' : 'text' ); ?>"
						class="regular-text"
						id="<?php echo esc_attr( $meta_key ); ?>"
						name="<?php echo esc_attr( $meta_key ); ?>"
						value="<?php echo esc_attr( $value ); ?>"
					>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php
}
add_action( 'show_user_profile', 'appverse_render_user_fields' );
add_action( 'edit_user_profile', 'appverse_render_user_fields' );

/**
 * Save user profile fields.
 *
 * @param int $user_id User ID.
 */
function appverse_save_user_fields( $user_id ) {
	if ( ! isset( $_POST['appverse_user_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['appverse_user_nonce'] ) ), 'appverse_save_user_meta' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return;
	}
	foreach ( appverse_user_fields() as $key => $field ) {
		$name = 'appverse_' . $key;
		if ( ! isset( $_POST[ $name ] ) ) {
			continue;
		}
		$value = 'number' === $field['type'] ? absint( wp_unslash( $_POST[ $name ] ) ) : sanitize_text_field( wp_unslash( $_POST[ $name ] ) );
		update_user_meta( $user_id, $name, $value );
	}
}
add_action( 'personal_options_update', 'appverse_save_user_fields' );
add_action( 'edit_user_profile_update', 'appverse_save_user_fields' );

/**
 * Custom avatar from user meta.
 *
 * @param string $url         Default URL.
 * @param mixed  $id_or_email User identifier.
 * @param array  $args        Avatar args.
 * @return string
 */
function appverse_custom_avatar_url( $url, $id_or_email, $args ) {
	$user = false;
	if ( $id_or_email instanceof WP_User ) {
		$user = $id_or_email;
	} elseif ( is_numeric( $id_or_email ) ) {
		$user = get_user_by( 'id', (int) $id_or_email );
	} elseif ( $id_or_email instanceof WP_Comment ) {
		$user = get_user_by( 'email', $id_or_email->comment_author_email );
	} elseif ( is_string( $id_or_email ) && is_email( $id_or_email ) ) {
		$user = get_user_by( 'email', $id_or_email );
	}

	if ( $user ) {
		$attachment_id = (int) get_user_meta( $user->ID, 'appverse_avatar', true );
		if ( $attachment_id ) {
			$custom = wp_get_attachment_image_url( $attachment_id, array( 200, 200 ) );
			if ( $custom ) {
				return $custom;
			}
		}
	}
	return $url;
}
add_filter( 'get_avatar_url', 'appverse_custom_avatar_url', 10, 3 );

/* -------------------------------------------------------------------------
 * Customizer
 * ---------------------------------------------------------------------- */

/**
 * Register customizer settings.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function appverse_customize_register( $wp_customize ) {
	$wp_customize->add_panel(
		'appverse_panel',
		array(
			'title'       => __( 'Appverse 主题设置', 'appverse' ),
			'description' => __( '首页轮播、通知角标与页脚信息。', 'appverse' ),
			'priority'    => 20,
		)
	);

	$wp_customize->add_section(
		'appverse_home',
		array(
			'title' => __( '首页轮播', 'appverse' ),
			'panel' => 'appverse_panel',
		)
	);

	for ( $i = 1; $i <= 3; $i++ ) {
		$wp_customize->add_setting(
			'appverse_banner_' . $i . '_image',
			array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Image_Control(
				$wp_customize,
				'appverse_banner_' . $i . '_image',
				array(
					'label'   => sprintf( /* translators: %d banner number */ __( '第 %d 张轮播图', 'appverse' ), $i ),
					'section' => 'appverse_home',
				)
			)
		);

		$text_fields = array(
			'eyebrow'  => __( '出品方小字', 'appverse' ),
			'title'    => __( '主标题', 'appverse' ),
			'latin'    => __( '英文副标', 'appverse' ),
			'subtitle' => __( '描述', 'appverse' ),
			'cta'      => __( '按钮文字', 'appverse' ),
			'url'      => __( '按钮链接', 'appverse' ),
		);
		foreach ( $text_fields as $key => $label ) {
			$wp_customize->add_setting(
				'appverse_banner_' . $i . '_' . $key,
				array(
					'default'           => '',
					'sanitize_callback' => 'url' === $key ? 'esc_url_raw' : 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				'appverse_banner_' . $i . '_' . $key,
				array(
					'label'   => $label,
					'section' => 'appverse_home',
					'type'    => 'url' === $key ? 'url' : 'text',
				)
			);
		}
	}

	$wp_customize->add_section(
		'appverse_general',
		array(
			'title' => __( '通用设置', 'appverse' ),
			'panel' => 'appverse_panel',
		)
	);

	$general = array(
		'appverse_notification_count' => array(
			'label'   => __( '通知角标数字', 'appverse' ),
			'type'    => 'number',
			'default' => 62,
		),
		'appverse_icp'                => array(
			'label'   => __( 'ICP 备案号', 'appverse' ),
			'type'    => 'text',
			'default' => '',
		),
		'appverse_footer_text'        => array(
			'label'   => __( '页脚说明', 'appverse' ),
			'type'    => 'text',
			'default' => __( 'Appverse · 发现值得安装的应用', 'appverse' ),
		),
		'appverse_download_tip'       => array(
			'label'   => __( '下载弹窗提示', 'appverse' ),
			'type'    => 'textarea',
			'default' => __( '下载即表示同意《用户协议》与《隐私政策》，应用由开发者提供，请自行甄别。', 'appverse' ),
		),
	);

	foreach ( $general as $key => $field ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $field['default'],
				'sanitize_callback' => 'number' === $field['type'] ? 'absint' : 'sanitize_textarea_field',
			)
		);
		$wp_customize->add_control(
			$key,
			array(
				'label'   => $field['label'],
				'section' => 'appverse_general',
				'type'    => $field['type'],
			)
		);
	}
}
add_action( 'customize_register', 'appverse_customize_register' );

/* -------------------------------------------------------------------------
 * AJAX
 * ---------------------------------------------------------------------- */

/**
 * JSON error helper.
 *
 * @param string $message Message.
 * @param int    $status  HTTP status.
 * @param array  $extra   Extra data.
 */
function appverse_ajax_error( $message, $status = 400, $extra = array() ) {
	wp_send_json_error(
		array_merge(
			array(
				'message'  => $message,
				'loginUrl' => wp_login_url( appverse_current_url() ),
			),
			$extra
		),
		$status
	);
}

/**
 * Toggle like / dislike.
 */
function appverse_ajax_like() {
	check_ajax_referer( 'appverse_nonce', 'nonce' );

	if ( ! is_user_logged_in() ) {
		appverse_ajax_error( __( '登录后才能点赞', 'appverse' ), 401, array( 'code' => 'login' ) );
	}

	$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	$type    = isset( $_POST['type'] ) && 'dislike' === sanitize_key( wp_unslash( $_POST['type'] ) ) ? 'dislike' : 'like';
	if ( ! $post_id || 'publish' !== get_post_status( $post_id ) ) {
		appverse_ajax_error( __( '内容不存在', 'appverse' ), 404 );
	}

	$user_id = get_current_user_id();
	$likes   = array_map( 'absint', (array) get_user_meta( $user_id, 'appverse_liked_posts', true ) );
	$dislikes = array_map( 'absint', (array) get_user_meta( $user_id, 'appverse_disliked_posts', true ) );

	$is_liked    = in_array( $post_id, $likes, true );
	$is_disliked = in_array( $post_id, $dislikes, true );

	$like_delta    = 0;
	$dislike_delta = 0;

	if ( 'like' === $type ) {
		if ( $is_liked ) {
			$likes      = array_values( array_diff( $likes, array( $post_id ) ) );
			$like_delta = -1;
		} else {
			$likes[]    = $post_id;
			$likes      = array_values( array_unique( $likes ) );
			$like_delta = 1;
			if ( $is_disliked ) {
				$dislikes      = array_values( array_diff( $dislikes, array( $post_id ) ) );
				$dislike_delta = -1;
				update_user_meta( $user_id, 'appverse_disliked_posts', $dislikes );
			}
		}
	} elseif ( $is_disliked ) {
		$dislikes      = array_values( array_diff( $dislikes, array( $post_id ) ) );
		$dislike_delta = -1;
	} else {
		$dislikes[]    = $post_id;
		$dislikes      = array_values( array_unique( $dislikes ) );
		$dislike_delta = 1;
		if ( $is_liked ) {
			$likes      = array_values( array_diff( $likes, array( $post_id ) ) );
			$like_delta = -1;
			update_user_meta( $user_id, 'appverse_liked_posts', $likes );
		}
	}

	update_user_meta( $user_id, 'appverse_liked_posts', $likes );
	update_user_meta( $user_id, 'appverse_disliked_posts', $dislikes );

	$like_count    = max( 0, (int) get_post_meta( $post_id, '_appverse_like_count', true ) + $like_delta );
	$dislike_count = max( 0, (int) get_post_meta( $post_id, '_appverse_dislike_count', true ) + $dislike_delta );
	update_post_meta( $post_id, '_appverse_like_count', $like_count );
	update_post_meta( $post_id, '_appverse_dislike_count', $dislike_count );

	wp_send_json_success(
		array(
			'liked'        => in_array( $post_id, $likes, true ),
			'disliked'     => in_array( $post_id, $dislikes, true ),
			'likeCount'    => $like_count,
			'dislikeCount' => $dislike_count,
		)
	);
}
add_action( 'wp_ajax_appverse_like', 'appverse_ajax_like' );

/**
 * Count reactions from user meta (demo scale; bounded).
 *
 * @param int    $post_id Post ID.
 * @param string $type    like|dislike.
 * @return int
 */
function appverse_recount_reaction( $post_id, $type ) {
	$meta_key = 'like' === $type ? 'appverse_liked_posts' : 'appverse_disliked_posts';
	$users    = get_users(
		array(
			'meta_key'     => $meta_key,
			'meta_compare' => 'EXISTS',
			'fields'       => 'ID',
			'number'       => 500,
		)
	);
	$count = 0;
	foreach ( $users as $user_id ) {
		if ( in_array( $post_id, array_map( 'absint', (array) get_user_meta( $user_id, $meta_key, true ) ), true ) ) {
			++$count;
		}
	}
	return $count;
}

/**
 * Count a download and return the target URL.
 */
function appverse_ajax_download() {
	check_ajax_referer( 'appverse_nonce', 'nonce' );

	$app_id = isset( $_POST['app_id'] ) ? absint( $_POST['app_id'] ) : 0;
	if ( ! $app_id || 'app' !== get_post_type( $app_id ) || 'publish' !== get_post_status( $app_id ) ) {
		appverse_ajax_error( __( '应用不存在', 'appverse' ), 404 );
	}

	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0';
	$key = 'appverse_dl_' . md5( $ip . '|' . $app_id );
	if ( ! get_transient( $key ) ) {
		$count = (int) get_post_meta( $app_id, '_app_downloads', true );
		update_post_meta( $app_id, '_app_downloads', $count + 1 );
		set_transient( $key, 1, 30 );
	}

	$url = get_post_meta( $app_id, '_app_download_url', true );
	if ( ! $url ) {
		$url = get_permalink( $app_id );
	}

	wp_send_json_success(
		array(
			'url'   => esc_url_raw( $url ),
			'count' => (int) get_post_meta( $app_id, '_app_downloads', true ),
		)
	);
}
add_action( 'wp_ajax_appverse_download', 'appverse_ajax_download' );
add_action( 'wp_ajax_nopriv_appverse_download', 'appverse_ajax_download' );

/**
 * Daily check-in.
 */
function appverse_ajax_checkin() {
	check_ajax_referer( 'appverse_nonce', 'nonce' );

	if ( ! is_user_logged_in() ) {
		appverse_ajax_error( __( '登录后才能签到', 'appverse' ), 401, array( 'code' => 'login' ) );
	}

	$user_id = get_current_user_id();
	$today   = current_time( 'Y-m-d' );
	$last    = get_user_meta( $user_id, 'appverse_checkin', true );

	if ( $today === $last ) {
		appverse_ajax_error( __( '今天已经签到啦', 'appverse' ), 409, array( 'code' => 'checked' ) );
	}

	$yesterday = gmdate( 'Y-m-d', strtotime( $today . ' -1 day' ) );
	$streak    = (int) get_user_meta( $user_id, 'appverse_checkin_streak', true );
	$streak    = ( $last === $yesterday ) ? $streak + 1 : 1;

	update_user_meta( $user_id, 'appverse_checkin', $today );
	update_user_meta( $user_id, 'appverse_checkin_streak', $streak );

	$points = (int) get_user_meta( $user_id, 'appverse_points', true ) + 5;
	$exp    = min( (int) get_user_meta( $user_id, 'appverse_exp_max', true ), (int) get_user_meta( $user_id, 'appverse_exp', true ) + 10 );
	update_user_meta( $user_id, 'appverse_points', $points );
	update_user_meta( $user_id, 'appverse_exp', $exp );

	wp_send_json_success(
		array(
			'streak' => $streak,
			'points' => $points,
			'exp'    => $exp,
			'message' => __( '签到成功，积分 +5', 'appverse' ),
		)
	);
}
add_action( 'wp_ajax_appverse_checkin', 'appverse_ajax_checkin' );

/* -------------------------------------------------------------------------
 * Demo content admin screen
 * ---------------------------------------------------------------------- */

/**
 * Add demo content management page.
 */
function appverse_admin_menu() {
	add_theme_page(
		__( 'Appverse 演示内容', 'appverse' ),
		__( 'Appverse 演示内容', 'appverse' ),
		'edit_theme_options',
		'appverse-demo',
		'appverse_demo_page'
	);
}
add_action( 'admin_menu', 'appverse_admin_menu' );

/**
 * Handle demo import/remove actions.
 */
function appverse_handle_demo_actions() {
	if ( ! isset( $_GET['page'] ) || 'appverse-demo' !== $_GET['page'] ) {
		return;
	}
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	if ( ! isset( $_POST['appverse_demo_action'] ) || ! isset( $_POST['appverse_demo_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['appverse_demo_nonce'] ) ), 'appverse_demo_action' ) ) {
		return;
	}

	$action = sanitize_key( wp_unslash( $_POST['appverse_demo_action'] ) );
	if ( 'import' === $action ) {
		appverse_import_demo_content( true );
		wp_safe_redirect( admin_url( 'themes.php?page=appverse-demo&appverse_notice=imported' ) );
		exit;
	}
	if ( 'remove' === $action ) {
		appverse_remove_demo_content();
		wp_safe_redirect( admin_url( 'themes.php?page=appverse-demo&appverse_notice=removed' ) );
		exit;
	}
}
add_action( 'admin_init', 'appverse_handle_demo_actions' );

/**
 * Render demo management screen.
 */
function appverse_demo_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$imported = (bool) get_option( 'appverse_demo_imported' );
	$ids      = get_option( 'appverse_demo_ids', array() );
	$counts   = array(
		'apps'  => isset( $ids['apps'] ) ? count( (array) $ids['apps'] ) : 0,
		'posts' => isset( $ids['posts'] ) ? count( (array) $ids['posts'] ) : 0,
		'pages' => isset( $ids['pages'] ) ? count( (array) $ids['pages'] ) : 0,
	);
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Appverse 演示内容', 'appverse' ); ?></h1>
		<?php if ( isset( $_GET['appverse_notice'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible"><p>
				<?php
				$notice = sanitize_key( wp_unslash( $_GET['appverse_notice'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				echo 'removed' === $notice ? esc_html__( '演示内容已移除。', 'appverse' ) : esc_html__( '演示内容已导入。', 'appverse' );
				?>
			</p></div>
		<?php endif; ?>
		<p><?php esc_html_e( '主题首次启用时会自动创建一套应用商店演示数据：应用、分类、动态、个人中心页面与轮播图。你可以随时重新导入或移除它们，不会影响你之后创建的内容。', 'appverse' ); ?></p>
		<table class="widefat striped" style="max-width:640px">
			<tbody>
				<tr><td><?php esc_html_e( '演示状态', 'appverse' ); ?></td><td><strong><?php echo $imported ? esc_html__( '已导入', 'appverse' ) : esc_html__( '未导入', 'appverse' ); ?></strong></td></tr>
				<tr><td><?php esc_html_e( '应用', 'appverse' ); ?></td><td><?php echo esc_html( (string) $counts['apps'] ); ?></td></tr>
				<tr><td><?php esc_html_e( '动态', 'appverse' ); ?></td><td><?php echo esc_html( (string) $counts['posts'] ); ?></td></tr>
				<tr><td><?php esc_html_e( '页面', 'appverse' ); ?></td><td><?php echo esc_html( (string) $counts['pages'] ); ?></td></tr>
			</tbody>
		</table>
		<form method="post" style="margin-top:20px">
			<?php wp_nonce_field( 'appverse_demo_action', 'appverse_demo_nonce' ); ?>
			<button class="button button-primary" name="appverse_demo_action" value="import"><?php echo $imported ? esc_html__( '重新导入演示内容', 'appverse' ) : esc_html__( '导入演示内容', 'appverse' ); ?></button>
			<?php if ( $imported ) : ?>
				<button class="button" name="appverse_demo_action" value="remove" onclick="return confirm('确定移除全部演示内容吗？');"><?php esc_html_e( '移除演示内容', 'appverse' ); ?></button>
			<?php endif; ?>
		</form>
	</div>
	<?php
}
