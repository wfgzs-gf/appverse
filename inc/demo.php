<?php
/**
 * Demo store importer.
 *
 * Creates a complete, removable demo store the first time the theme is
 * activated: apps, categories, community posts, pages, menus and banners.
 *
 * @package Appverse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import the demo store.
 *
 * @param bool $force Remove existing demo content first and rebuild.
 * @return bool
 */
function appverse_import_demo_content( $force = false ) {
	if ( get_option( 'appverse_demo_imported' ) && ! $force ) {
		return true;
	}
	if ( $force && get_option( 'appverse_demo_imported' ) ) {
		appverse_remove_demo_content();
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$ids = array(
		'apps'        => array(),
		'posts'       => array(),
		'pages'       => array(),
		'attachments' => array(),
	);

	$asset = function ( $relative ) {
		$file = APPVERSE_DIR . '/assets/demo/' . ltrim( $relative, '/' );
		return is_readable( $file ) ? $file : '';
	};

	/* ---------------------------------------------------------------------
	 * Taxonomy terms
	 * ------------------------------------------------------------------ */
	$terms = array(
		'education' => array( '学习教育', '背单词、刷题、查资料，学习路上的小工具。' ),
		'life'      => array( '便捷生活', '把城市服务、社区和日常琐事装进口袋。' ),
		'health'    => array( '医疗健康', '情绪、睡眠与身心健康，慢一点也没关系。' ),
		'tools'     => array( '系统工具', '文件、账号、设备与云端的效率工具。' ),
		'media'     => array( '影音娱乐', '音乐、影像与社区，给生活加点声音。' ),
		'office'    => array( '效率办公', '让记录、协作与灵感保持同步。' ),
		'games'     => array( '游戏', '随时打开，进入另一个世界。' ),
		'indie'     => array( '独立开发', '小团队、大作品，认真做产品的人。' ),
	);
	$term_ids = array();
	foreach ( $terms as $slug => $data ) {
		$existing = get_term_by( 'slug', $slug, 'app_category' );
		if ( $existing ) {
			$term_ids[ $slug ] = (int) $existing->term_id;
			continue;
		}
		$result = wp_insert_term( $data[0], 'app_category', array( 'slug' => $slug, 'description' => $data[1] ) );
		if ( ! is_wp_error( $result ) ) {
			$term_ids[ $slug ] = (int) $result['term_id'];
		}
	}

	$collection_defs = array(
		'editor-picks' => '编辑精选',
		'night-owl'    => '夜猫子专用',
		'indie-list'   => '独立开发者合集',
	);
	$collection_ids  = array();
	foreach ( $collection_defs as $slug => $name ) {
		$existing = get_term_by( 'slug', $slug, 'app_collection' );
		if ( $existing ) {
			$collection_ids[ $slug ] = (int) $existing->term_id;
			continue;
		}
		$result = wp_insert_term( $name, 'app_collection', array( 'slug' => $slug ) );
		if ( ! is_wp_error( $result ) ) {
			$collection_ids[ $slug ] = (int) $result['term_id'];
		}
	}

	$feed_defs = array(
		'dynamic'    => '动态',
		'attachment' => '附件',
		'developer'  => '开发者',
	);
	$feed_ids  = array();
	foreach ( $feed_defs as $slug => $name ) {
		$existing = get_term_by( 'slug', $slug, 'feed_type' );
		if ( $existing ) {
			$feed_ids[ $slug ] = (int) $existing->term_id;
			continue;
		}
		$result = wp_insert_term( $name, 'feed_type', array( 'slug' => $slug ) );
		if ( ! is_wp_error( $result ) ) {
			$feed_ids[ $slug ] = (int) $result['term_id'];
		}
	}

	/* ---------------------------------------------------------------------
	 * Media
	 * ------------------------------------------------------------------ */
	$import_image = function ( $relative, $title, $parent = 0 ) use ( $asset, &$ids ) {
		$file = $asset( $relative );
		if ( ! $file ) {
			return 0;
		}
		$bits = wp_upload_bits( basename( $relative ), null, file_get_contents( $file ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( ! empty( $bits['error'] ) ) {
			return 0;
		}
		$attachment_id = wp_insert_attachment(
			array(
				'post_mime_type' => function_exists( 'wp_get_image_mime' ) ? wp_get_image_mime( $bits['file'] ) : 'image/png',
				'post_title'     => $title,
				'post_content'   => '',
				'post_status'    => 'inherit',
			),
			$bits['file'],
			$parent
		);
		if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
			return 0;
		}
		wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $bits['file'] ) );
		$ids['attachments'][] = (int) $attachment_id;
		return (int) $attachment_id;
	};

	$icon_ids = array();
	foreach ( range( 1, 12 ) as $index ) {
		$icon_ids[ $index ] = $import_image( 'icons/app-' . str_pad( (string) $index, 2, '0', STR_PAD_LEFT ) . '.png', '应用图标 ' . $index );
	}
	$default_icon = $import_image( 'icons/app-default.png', '默认应用图标' );
	$shot_ids     = array();
	foreach ( range( 1, 3 ) as $index ) {
		$shot_ids[ $index ] = $import_image( 'screens/shot-' . str_pad( (string) $index, 2, '0', STR_PAD_LEFT ) . '.png', '应用截图 ' . $index );
	}
	$avatar_ids = array();
	foreach ( range( 1, 4 ) as $index ) {
		$avatar_ids[ $index ] = $import_image( 'avatars/user-' . str_pad( (string) $index, 2, '0', STR_PAD_LEFT ) . '.png', '用户头像 ' . $index );
	}
	$banner_ids = array();
	foreach ( range( 1, 3 ) as $index ) {
		$banner_ids[ $index ] = $import_image( 'banners/banner-' . str_pad( (string) $index, 2, '0', STR_PAD_LEFT ) . '.jpg', '首页轮播 ' . $index );
	}

	/* ---------------------------------------------------------------------
	 * Users
	 * ------------------------------------------------------------------ */
	$demo_user_id = username_exists( 'myszp' );
	if ( ! $demo_user_id ) {
		$demo_user_id = wp_insert_user(
			array(
				'user_login'   => 'myszp',
				'user_pass'    => wp_generate_password( 20 ),
				'user_email'   => 'myszp@appverse.local',
				'display_name' => 'myszp',
				'nickname'     => 'myszp',
				'role'         => 'author',
				'description'  => '喜欢折腾应用与云盘的普通用户，遇到问题会认真反馈。',
			)
		);
	}
	if ( ! is_wp_error( $demo_user_id ) ) {
		update_user_meta( $demo_user_id, 'appverse_level', 3 );
		update_user_meta( $demo_user_id, 'appverse_exp', 420 );
		update_user_meta( $demo_user_id, 'appverse_exp_max', 1000 );
		update_user_meta( $demo_user_id, 'appverse_points', 120 );
		update_user_meta( $demo_user_id, 'appverse_share_value', 35 );
		update_user_meta( $demo_user_id, 'appverse_traffic', '0MB' );
		update_user_meta( $demo_user_id, 'appverse_following', 8 );
		if ( ! empty( $avatar_ids[2] ) ) {
			update_user_meta( $demo_user_id, 'appverse_avatar', $avatar_ids[2] );
		}
	}
	$demo_user_id = is_wp_error( $demo_user_id ) ? 1 : (int) $demo_user_id;

	$developer_user_id = username_exists( 'lingverse' );
	if ( ! $developer_user_id ) {
		$developer_user_id = wp_insert_user(
			array(
				'user_login'   => 'lingverse',
				'user_pass'    => wp_generate_password( 20 ),
				'user_email'   => 'dev@appverse.local',
				'display_name' => '灵界工作室',
				'nickname'     => '灵界工作室',
				'role'         => 'author',
				'description'  => '两个人维护的文字修仙小世界。',
			)
		);
	}
	if ( ! is_wp_error( $developer_user_id ) ) {
		update_user_meta( $developer_user_id, 'appverse_level', 6 );
		update_user_meta( $developer_user_id, 'appverse_exp', 880 );
		update_user_meta( $developer_user_id, 'appverse_exp_max', 1200 );
		update_user_meta( $developer_user_id, 'appverse_points', 960 );
		update_user_meta( $developer_user_id, 'appverse_share_value', 512 );
		if ( ! empty( $avatar_ids[3] ) ) {
			update_user_meta( $developer_user_id, 'appverse_avatar', $avatar_ids[3] );
		}
	}
	$developer_user_id = is_wp_error( $developer_user_id ) ? 1 : (int) $developer_user_id;

	/* Demo profile for the current administrator, matching the preview. */
	$admin_id = get_current_user_id();
	if ( ! $admin_id ) {
		$administrators = get_users(
			array(
				'role'   => 'administrator',
				'number' => 1,
				'fields' => 'ID',
			)
		);
		$admin_id = $administrators ? (int) $administrators[0] : 0;
	}
	if ( $admin_id ) {
		update_user_meta( $admin_id, 'appverse_level', 0 );
		update_user_meta( $admin_id, 'appverse_exp', 843 );
		update_user_meta( $admin_id, 'appverse_exp_max', 1000 );
		update_user_meta( $admin_id, 'appverse_points', 0 );
		update_user_meta( $admin_id, 'appverse_share_value', 97 );
		update_user_meta( $admin_id, 'appverse_traffic', '0MB' );
		update_user_meta( $admin_id, 'appverse_following', 0 );
		update_user_meta( $admin_id, 'appverse_checkin', current_time( 'Y-m-d' ) );
		update_user_meta( $admin_id, 'appverse_checkin_streak', 7 );
		if ( ! empty( $avatar_ids[1] ) ) {
			update_user_meta( $admin_id, 'appverse_avatar', $avatar_ids[1] );
		}
		if ( 'admin' === get_userdata( $admin_id )->display_name ) {
			wp_update_user( array( 'ID' => $admin_id, 'display_name' => '娉悟', 'nickname' => '娉悟' ) );
		}
	}

	/* ---------------------------------------------------------------------
	 * Apps
	 * ------------------------------------------------------------------ */
	$apps = array(
		array(
			'slug'      => 'xiaokui',
			'title'     => '小葵',
			'icon'      => 1,
			'cats'      => array( 'education' ),
			'version'   => '3.7.5',
			'size'      => '28.4 MB',
			'downloads' => 6,
			'developer' => '小葵工作室',
			'rating'    => 4.8,
			'slogan'    => '安静的学习搭子，查词、组卷、背单词都在这里。',
			'description' => '<p>小葵是一款轻量学习工具，把词典、生词本和每日计划放进同一个安静的界面里。没有打扰式推送，打开就能进入专注状态。</p><h2>可以做什么</h2><ul><li>中英日韩多语词典，支持整句翻译与发音。</li><li>生词自动归档，按遗忘曲线安排复习。</li><li>每日学习计划与专注计时，数据只保存在本地和你的账号里。</li></ul><p>它适合通勤路上背十个词，也适合在图书馆坐一整个下午。</p>',
			'changelog' => "3.7.5\n· 修复部分设备查词闪退的问题\n· 生词本支持批量导出\n\n3.7.0\n· 新增日语释义与例句朗读\n· 优化暗色模式下的对比度",
		),
		array(
			'slug'      => 'lyg',
			'title'     => '我的连云港',
			'icon'      => 2,
			'cats'      => array( 'life' ),
			'version'   => '3.6.4',
			'size'      => '46.2 MB',
			'downloads' => 15,
			'developer' => '连云港市民卡服务中心',
			'rating'    => 4.6,
			'slogan'    => '一座城市的日常服务，装进一个应用。',
			'description' => '<p>我的连云港把社保、公积金、公交、景区预约和社区服务收进一个入口，减少在多个小程序之间来回跳转的麻烦。</p><h2>常用服务</h2><ul><li>电子市民卡与公交乘车码。</li><li>社保、医保、公积金查询。</li><li>本地景区预约与文化活动报名。</li></ul><p>首次使用需要完成实名认证，认证信息仅用于政务服务核验。</p>',
			'changelog' => "3.6.4\n· 修复部分安卓机型乘车码刷新的问题\n· 新增连云区社区服务入口\n\n3.6.0\n· 支持景区预约记录同步",
		),
		array(
			'slug'      => 'yixinli',
			'title'     => '壹心理',
			'icon'      => 3,
			'cats'      => array( 'health' ),
			'version'   => '11.0.41',
			'size'      => '62.8 MB',
			'downloads' => 9,
			'developer' => '壹心理',
			'rating'    => 4.9,
			'slogan'    => '在情绪低落的时候，给你一盏小灯。',
			'description' => '<p>壹心理提供心理测评、情绪日记与专业咨询预约。它不承诺立刻解决所有问题，而是陪你把感受说清楚。</p><h2>主要内容</h2><ul><li>情绪记录与每周回顾，看到自己的起伏。</li><li>由持证咨询师提供的在线咨询预约。</li><li>睡眠、亲密关系与职场压力的主题课程。</li></ul><p>如果你正处在危机中，请优先联系当地紧急援助热线。</p>',
			'changelog' => "11.0.41\n· 优化咨询师筛选体验\n· 修复日记图片上传失败的问题\n\n11.0.30\n· 新增睡眠声音库",
		),
		array(
			'slug'      => 'phone-manager',
			'title'     => '手机管家',
			'icon'      => 4,
			'cats'      => array( 'tools' ),
			'version'   => '12.1.74',
			'size'      => '18.6 MB',
			'downloads' => 128,
			'developer' => 'Flyme 系统工具',
			'rating'    => 4.7,
			'slogan'    => '清理、加速、找文件，一个足够克制的系统工具。',
			'description' => '<p>手机管家把垃圾清理、文件管理和权限检查做得足够简单，不塞广告，也不诱导安装其他应用。</p><h2>功能亮点</h2><ul><li>一键清理缓存与残留安装包。</li><li>按类型浏览图片、视频、文档与大文件。</li><li>检查敏感权限调用记录，给出可执行建议。</li></ul><p>建议每月做一次深度清理，平时使用快速清理即可。</p>',
			'changelog' => "12.1.74\n· 修复点击云盘备份显示空白的问题\n· 提升大文件扫描速度\n\n12.1.60\n· 新增安装包管理",
		),
		array(
			'slug'      => 'flyme-account',
			'title'     => 'Flyme 账号',
			'icon'      => 5,
			'cats'      => array( 'tools' ),
			'version'   => '12.0.15',
			'size'      => '9.4 MB',
			'downloads' => 86,
			'developer' => 'Flyme',
			'rating'    => 4.5,
			'slogan'    => '一处登录，同步你的设备与云服务。',
			'description' => '<p>Flyme 账号用于登录魅族设备与云服务，管理联系人、便签、相册与查找手机等功能。</p><h2>安全建议</h2><ul><li>开启两步验证，避免账号被盗。</li><li>定期检查登录设备，移除不再使用的终端。</li><li>找回手机功能需要保持网络与定位开启。</li></ul>',
			'changelog' => "12.0.15\n· 修复验证码偶尔收不到的问题\n· 优化登录设备列表展示\n\n12.0.10\n· 支持通行密钥登录",
		),
		array(
			'slug'      => 'songguo',
			'title'     => '松果倾诉',
			'icon'      => 6,
			'cats'      => array( 'health' ),
			'version'   => '5.2.0',
			'size'      => '44.1 MB',
			'downloads' => 3,
			'developer' => '松果心理健康',
			'rating'    => 4.8,
			'slogan'    => '有些话，说给愿意听的人。',
			'description' => '<p>松果倾诉提供匿名倾诉与倾听者匹配。你可以在不想暴露身份的时候，找一个愿意认真听你说话的人。</p><h2>使用方式</h2><ul><li>文字或语音匿名倾诉，按分钟计费。</li><li>倾听者经过基础培训与实名认证。</li><li>支持举报与拉黑，保护你的边界。</li></ul><p>平台不提供医疗诊断，紧急情况请及时就医。</p>',
			'changelog' => "5.2.0\n· 新增夜间倾听专区\n· 修复语音通话偶尔中断的问题\n\n5.1.8\n· 优化匿名匹配速度",
		),
		array(
			'slug'      => 'haofenshu',
			'title'     => '好分数',
			'icon'      => 7,
			'cats'      => array( 'education' ),
			'version'   => '9.8.2',
			'size'      => '51.6 MB',
			'downloads' => 12,
			'developer' => '好分数教育',
			'rating'    => 4.4,
			'slogan'    => '每次考试之后，知道自己错在哪里。',
			'description' => '<p>好分数面向学生与家长，提供成绩查询、错题整理与学情分析。数据由学校或考试机构授权后接入。</p><h2>核心功能</h2><ul><li>成绩单与单科排名趋势。</li><li>错题自动归档，支持按知识点复习。</li><li>为家长生成每周学情简报。</li></ul><p>成绩数据涉及隐私，请不要在公开场合分享账号信息。</p>',
			'changelog' => "9.8.2\n· 修复部分学校成绩单加载失败的问题\n· 错题本支持按章节筛选\n\n9.8.0\n· 新增学情周报",
		),
		array(
			'slug'      => 'xuanhuang',
			'title'     => '玄黄录',
			'icon'      => 8,
			'cats'      => array( 'games', 'indie' ),
			'version'   => '1.6.0',
			'size'      => '38.2 MB',
			'downloads' => 42,
			'developer' => '青简工作室',
			'rating'    => 4.9,
			'slogan'    => '一款只有文字，却有山川湖海的修仙游戏。',
			'description' => '<p>玄黄录是一款单机文字修仙游戏。没有抽卡，没有体力，也没有每日任务清单。你只需要做选择，然后承担结果。</p><h2>玩法</h2><ul><li>随机生成的灵根、出身与机缘。</li><li>超过两百个事件，选择会改变人物关系与结局。</li><li>支持多周目继承部分见闻。</li></ul><p>建议戴耳机游玩，配乐是这款游戏的一半。</p>',
			'changelog' => "1.6.0\n· 新增「北境」章节与十二个事件\n· 修复部分结局无法解锁的问题\n\n1.5.2\n· 优化文字排版与字号设置",
		),
		array(
			'slug'      => 'wanwu',
			'title'     => '晚悟',
			'icon'      => 9,
			'cats'      => array( 'tools', 'indie' ),
			'version'   => '2.3.1',
			'size'      => '22.7 MB',
			'downloads' => 8,
			'developer' => '夜航船',
			'rating'    => 4.7,
			'slogan'    => '睡前五分钟，把今天想明白一点。',
			'description' => '<p>晚悟是一款极简的每日复盘工具。每晚三个问题：今天做了什么、哪里可以更好、明天最重要的一件事。</p><h2>特点</h2><ul><li>没有社交，没有排行榜，只有你自己的记录。</li><li>支持指纹锁与本地加密备份。</li><li>每周生成一页复盘摘要，可导出为图片。</li></ul><p>开发者在关于页写下了这句话：希望它安静地陪你很多年。</p>',
			'changelog' => "2.3.1\n· 修复备份文件在部分设备上无法恢复的问题\n· 增加每周摘要导出\n\n2.2.0\n· 支持自定义三个问题",
		),
		array(
			'slug'      => 'oneplus-community',
			'title'     => '一加社区',
			'icon'      => 10,
			'cats'      => array( 'media', 'life' ),
			'version'   => '6.9.0',
			'size'      => '39.8 MB',
			'downloads' => 5,
			'developer' => '一加手机',
			'rating'    => 4.3,
			'slogan'    => '和一群喜欢折腾的人聊手机。',
			'description' => '<p>一加社区是加油们的交流阵地。这里有系统更新公告、摄影作品、刷机教程，也有大量真实的使用反馈。</p><h2>板块</h2><ul><li>产品公告与系统更新日志。</li><li>摄影与随手拍分享。</li><li>玩机教程与问题求助。</li></ul><p>刷机有风险，操作前请完整备份数据。</p>',
			'changelog' => "6.9.0\n· 优化图片加载与夜间模式\n· 修复部分帖子翻页重复的问题\n\n6.8.2\n· 新增话题关注",
		),
		array(
			'slug'      => 'lingjie',
			'title'     => '灵界 LingVerse',
			'icon'      => 11,
			'cats'      => array( 'games', 'indie' ),
			'version'   => '1.2.8',
			'size'      => '96.4 MB',
			'downloads' => 132,
			'developer' => '灵界工作室',
			'rating'    => 4.9,
			'slogan'    => '打开即玩的文字修仙世界。',
			'description' => '<p>灵界是一个持续更新的文字修仙世界。你可以在洞府里种灵草，在宗门里学功法，也可以独自去往无人踏足的秘境。</p><h2>世界特色</h2><ul><li>文字驱动的探索与战斗，不肝不氪。</li><li>宗门、师徒与交易行，和真实玩家一起生活。</li><li>每个季度推进一次主线，旧的存档不会作废。</li></ul><p>建议先完成新手三问，它会决定你的初始灵根。</p>',
			'changelog' => "1.2.8\n· 新增「洞府经营」玩法\n· 修复跨服交易行偶发卡顿\n\n1.2.0\n· 开启第五座宗门「听雨楼」\n· 重做战斗日志排版",
		),
		array(
			'slug'      => 'lanzou',
			'title'     => '蓝奏云',
			'icon'      => 12,
			'cats'      => array( 'tools' ),
			'version'   => '4.2.6',
			'size'      => '12.1 MB',
			'downloads' => 210,
			'developer' => '蓝奏科技',
			'rating'    => 4.5,
			'slogan'    => '轻量文件分享与云存储。',
			'description' => '<p>蓝奏云提供不限速的小文件上传与分享，常用于应用安装包、文档与素材的临时分发。</p><h2>说明</h2><ul><li>单个文件大小限制以账号等级为准。</li><li>分享链接支持密码与有效期设置。</li><li>请勿上传侵权、违法或含有恶意代码的文件。</li></ul><p>如遇链接无法打开，可尝试更换网络或稍后重试。</p>',
			'changelog' => "4.2.6\n· 修复部分安卓机型点击备份显示空白的问题\n· 优化上传进度提示\n\n4.2.0\n· 新增加密分享",
		),
	);

	$app_dates = array();
	$total      = count( $apps );
	foreach ( $apps as $position => &$app_data ) {
		$app_data['content'] = $app_data['description'] . '<h2>更新日志</h2><pre class="av-pre">' . esc_html( $app_data['changelog'] ) . '</pre>';
		/* First apps in the list are the newest, so they become the home list. */
		$app_dates[ $app_data['slug'] ] = gmdate( 'Y-m-d H:i:s', strtotime( current_time( 'mysql' ) . ' -' . ( ( $position + 1 ) * 2 ) . ' hours' ) );
	}
	unset( $app_data );

	// Create in reverse so 小葵 / 我的连云港 / 壹心理 end up with the newest dates.
	$apps_reversed = array_reverse( $apps );
	foreach ( $apps_reversed as $app_data ) {
		$cat_slugs = $app_data['cats'];
		$term_ids_for_app = array();
		foreach ( $cat_slugs as $cat_slug ) {
			if ( isset( $term_ids[ $cat_slug ] ) ) {
				$term_ids_for_app[] = $term_ids[ $cat_slug ];
			}
		}
		$app_id = wp_insert_post(
			array(
				'post_type'    => 'app',
				'post_status'  => 'publish',
				'post_title'   => $app_data['title'],
				'post_name'    => $app_data['slug'],
				'post_excerpt' => $app_data['slogan'],
				'post_content' => $app_data['content'],
				'post_author'  => $developer_user_id,
				'post_date'    => $app_dates[ $app_data['slug'] ],
				'meta_input'   => array(
					'_app_version'      => $app_data['version'],
					'_app_size'         => $app_data['size'],
					'_app_downloads'    => $app_data['downloads'],
					'_app_developer'    => $app_data['developer'],
					'_app_rating'       => $app_data['rating'],
					'_app_official'     => 1,
					'_app_badge'        => '正式版',
					'_app_download_url' => home_url( '/downloads/' ),
					'_app_package'      => 'com.appverse.' . $app_data['slug'],
					'_app_updated'      => gmdate( 'Y-m-d', strtotime( $app_dates[ $app_data['slug'] ] ) ),
					'_app_changelog'    => $app_data['changelog'],
					'_app_screenshots'  => implode( ',', array_filter( $shot_ids ) ),
				),
			)
		);

		if ( is_wp_error( $app_id ) ) {
			continue;
		}
		$ids['apps'][] = (int) $app_id;

		if ( $term_ids_for_app ) {
			wp_set_object_terms( $app_id, $term_ids_for_app, 'app_category' );
		}
		wp_set_object_terms( $app_id, array( $collection_ids['editor-picks'] ), 'app_collection' );
		if ( in_array( 'indie', $cat_slugs, true ) ) {
			wp_set_object_terms( $app_id, array( $collection_ids['indie-list'] ), 'app_collection', true );
		}

		if ( ! empty( $icon_ids[ $app_data['icon'] ] ) ) {
			set_post_thumbnail( $app_id, $icon_ids[ $app_data['icon'] ] );
		} elseif ( $default_icon ) {
			set_post_thumbnail( $app_id, $default_icon );
		}
	}

	/* Sticky picks for the home grid, matching the preview order. */
	$app_by_slug = array();
	foreach ( $ids['apps'] as $app_id ) {
		$app_by_slug[ get_post_field( 'post_name', $app_id ) ] = (int) $app_id;
	}

	$sticky_slugs = array( 'wanwu', 'oneplus-community', 'songguo', 'haofenshu', 'xuanhuang' );
	$sticky_ids   = array();
	foreach ( $sticky_slugs as $sticky_slug ) {
		if ( isset( $app_by_slug[ $sticky_slug ] ) ) {
			$sticky_ids[] = (int) $app_by_slug[ $sticky_slug ];
		}
	}
	if ( $sticky_ids ) {
		update_option( 'sticky_posts', array_values( $sticky_ids ) );
		// Comma-separated to preserve the curated order (sticky_posts gets sorted).
		update_option( 'appverse_featured_ids', implode( ',', array_values( $sticky_ids ) ) );
	}

	/* ---------------------------------------------------------------------
	 * Community posts
	 * ------------------------------------------------------------------ */
	$posts = array(
		array(
			'text'     => '43x5点击蓝奏显示空白，更换移动云盘链接哇 谢谢 好多都是这样',
			'device'   => 'MEIZU 21 Pro',
			'app'      => 'phone-manager',
			'time'     => '09:56',
			'likes'    => 1,
			'dislikes' => 0,
			'feed'     => 'dynamic',
			'comments' => 2,
		),
		array(
			'text'     => '点击蓝奏显示空白，更换移动云盘链接哇 谢谢',
			'device'   => 'MEIZU 21 Pro',
			'app'      => 'flyme-account',
			'time'     => '09:55',
			'likes'    => 1,
			'dislikes' => 0,
			'feed'     => 'attachment',
			'comments' => 1,
		),
		array(
			'text'     => '点击蓝奏显示空白，更换移动云盘链接哇 谢谢',
			'device'   => 'MEIZU 21 Pro',
			'app'      => '',
			'time'     => '09:54',
			'likes'    => 0,
			'dislikes' => 0,
			'feed'     => 'dynamic',
			'comments' => 0,
		),
		array(
			'text'     => '「灵界」1.2.8 已经推送，这次把洞府经营做完了。旧存档可以直接进，不用重开。遇到问题站内信或者评论区都可以，我会看。',
			'device'   => '灵界工作室',
			'app'      => 'lingjie',
			'time'     => '昨天 21:40',
			'likes'    => 23,
			'dislikes' => 0,
			'feed'     => 'developer',
			'author'   => $developer_user_id,
			'comments' => 2,
		),
		array(
			'text'     => '求一个安静一点的记账应用，不要开屏广告，最好能导出 CSV。目前用的小程序广告太多了。',
			'device'   => 'Xiaomi 14',
			'app'      => '',
			'time'     => '昨天 18:12',
			'likes'    => 4,
			'dislikes' => 0,
			'feed'     => 'dynamic',
			'author'   => $demo_user_id,
			'comments' => 1,
		),
	);

	foreach ( $posts as $post_data ) {
		$author = isset( $post_data['author'] ) ? $post_data['author'] : $demo_user_id;
		$time_raw = isset( $post_data['time'] ) ? $post_data['time'] : '';
		if ( 0 === strpos( $time_raw, '昨天' ) ) {
			$post_date = gmdate( 'Y-m-d', strtotime( current_time( 'Y-m-d' ) . ' -1 day' ) ) . ' ' . trim( str_replace( '昨天', '', $time_raw ) ) . ':00';
		} else {
			$post_date = current_time( 'Y-m-d' ) . ' ' . $time_raw . ':00';
		}
		$post_id = wp_insert_post(
			array(
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_title'   => wp_trim_words( $post_data['text'], 18, '…' ),
				'post_content' => $post_data['text'],
				'post_author'  => $author,
				'post_date'    => $post_date,
				'meta_input'   => array(
					'_feed_device'          => $post_data['device'],
					'_feed_app'             => isset( $app_by_slug[ $post_data['app'] ] ) ? $app_by_slug[ $post_data['app'] ] : 0,
					'_appverse_like_count'   => $post_data['likes'],
					'_appverse_dislike_count' => $post_data['dislikes'],
				),
			)
		);
		if ( is_wp_error( $post_id ) ) {
			continue;
		}
		$ids['posts'][] = (int) $post_id;
		if ( ! empty( $feed_ids[ $post_data['feed'] ] ) ) {
			wp_set_object_terms( $post_id, array( $feed_ids[ $post_data['feed'] ] ), 'feed_type' );
		}

		$comments = array(
			array( '娉悟', '试试把链接里的 lanzou 换成移动云盘，我这边可以打开。', $demo_user_id ),
			array( '夜航船', '已经记录，下个版本会统一换源。', $developer_user_id ),
		);
		$comments = array_slice( $comments, 0, max( 0, (int) $post_data['comments'] ) );
		foreach ( $comments as $comment ) {
			wp_insert_comment(
				array(
					'comment_post_ID'      => $post_id,
					'comment_author'       => $comment[0],
					'comment_author_email' => 'demo@appverse.local',
					'comment_content'      => $comment[1],
					'comment_approved'     => 1,
					'comment_date'         => current_time( 'mysql' ),
					'user_id'              => $comment[2],
				)
			);
		}
	}

	/* ---------------------------------------------------------------------
	 * Pages
	 * ------------------------------------------------------------------ */
	$pages = array(
		'home'        => array( '首页', '', '' ),
		'square'      => array( '广场', '', '' ),
		'me'          => array( '我的', 'page-profile.php', '' ),
		'ranking'     => array( '排行', 'page-ranking.php', '' ),
		'categories'  => array( '分类', 'page-categories.php', '' ),
		'checkin'     => array( '签到', 'page-checkin.php', '' ),
		'indie'       => array( '独立开发', 'page-indie.php', '' ),
		'member'      => array(
			'会员中心',
			'',
			'<p>Appverse 会员是一次轻量的支持计划：去掉页面广告位，解锁应用集收藏上限，并优先体验新功能。</p><h2>会员权益</h2><ul><li>专属会员标识与资料卡装饰。</li><li>应用集收藏数量从 5 个提升到 100 个。</li><li>每月 3 次应用推荐位申请。</li></ul><blockquote><p>会员收入的一部分会用于支持独立开发者。</p></blockquote>',
		),
		'tasks'       => array(
			'任务中心',
			'',
			'<p>完成日常任务可以获得积分，积分可以在积分商城兑换会员天数与小礼物。</p><h2>今日任务</h2><ul><li>签到 +5 积分</li><li>发布一条动态 +10 积分</li><li>收藏一个应用集 +5 积分</li><li>邀请一位朋友注册 +50 积分</li></ul>',
		),
		'points'      => array(
			'积分商城',
			'',
			'<p>用积分兑换会员、应用集名额和开发者周边。所有兑换均不支持退款，请确认后再提交。</p><h2>可兑换</h2><ul><li>7 天会员体验 · 300 积分</li><li>应用集扩容 10 个 · 200 积分</li><li>Appverse 贴纸包 · 500 积分</li></ul>',
		),
		'downloads'   => array(
			'下载管理',
			'',
			'<p>这里会显示你在本站点点击过下载的应用。演示内容中不包含真实安装包，下载按钮仅用于展示交互。</p><p>如需管理设备上的应用，请前往手机系统设置或使用应用商店客户端。</p>',
		),
		'apps-manage' => array(
			'应用管理',
			'',
			'<p>管理你收藏、安装与关注的应用。你可以在这里查看版本更新、打开应用详情，或取消关注。</p><p>当前演示站点没有接入真实安装数据，列表为空是正常的。</p>',
		),
		'collections' => array(
			'应用集',
			'',
			'<p>应用集由编辑或用户创建，把同一场景下的应用放在一起，方便一次装齐。</p><p>例如「新手机第一晚」「独立开发者合集」「夜猫子专用」。</p>',
		),
		'filter'      => array(
			'应用筛选',
			'',
			'<p>按分类、评分、安装包大小和更新频率筛选应用。筛选结果会随着应用库更新而变化。</p><p>你也可以直接在应用列表页使用顶部排序与分类筛选。</p>',
		),
		'about'       => array(
			'关于',
			'',
			'<p>Appverse 是一个用 WordPress 构建的应用商店主题演示站点。这里的应用信息、评分与下载数据均为演示内容，不代表真实产品。</p><h2>主题信息</h2><ul><li>主题：Appverse 1.0.0</li><li>运行环境：WordPress + PHP</li><li>演示数据可在「外观 → Appverse 演示内容」中重新导入或移除。</li></ul>',
		),
	);

	foreach ( $pages as $slug => $data ) {
		$page_id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $data[0],
				'post_name'    => $slug,
				'post_content' => $data[2],
				'meta_input'   => $data[1] ? array( '_wp_page_template' => $data[1] ) : array(),
			)
		);
		if ( ! is_wp_error( $page_id ) ) {
			$ids['pages'][] = (int) $page_id;
			if ( 'home' === $slug ) {
				$home_page_id = (int) $page_id;
			}
			if ( 'square' === $slug ) {
				$square_page_id = (int) $page_id;
			}
			if ( 'me' === $slug ) {
				$me_page_id = (int) $page_id;
			}
		}
	}

	update_option( 'show_on_front', 'page' );
	if ( ! empty( $home_page_id ) ) {
		update_option( 'page_on_front', $home_page_id );
	}
	if ( ! empty( $square_page_id ) ) {
		update_option( 'page_for_posts', $square_page_id );
	}
	update_option( 'blogdescription', '发现值得安装的应用' );

	/* Hide WordPress' bundled placeholder content on a fresh demo install. */
	$default_post = get_post( 1 );
	if ( $default_post && 'post' === $default_post->post_type ) {
		$default_content = (string) $default_post->post_content;
		if ( false !== strpos( $default_content, 'Welcome to WordPress' ) || false !== strpos( $default_content, '欢迎使用 WordPress' ) ) {
			wp_trash_post( 1 );
		}
	}
	$sample_page = get_page_by_path( 'sample-page' );
	if ( ! $sample_page ) {
		$sample_page = get_page_by_path( '示例页面' );
	}
	if ( $sample_page ) {
		wp_trash_post( $sample_page->ID );
	}

	/* ---------------------------------------------------------------------
	 * Menu
	 * ------------------------------------------------------------------ */
	$menu_name = '底部导航';
	$menu      = wp_get_nav_menu_object( $menu_name );
	if ( ! $menu ) {
		$menu_id = wp_create_nav_menu( $menu_name );
	} else {
		$menu_id = $menu->term_id;
	}
	if ( ! is_wp_error( $menu_id ) && $menu_id ) {
		$existing_items = wp_get_nav_menu_items( $menu_id );
		if ( ! $existing_items ) {
			$menu_pages = array( $home_page_id ?? 0, $square_page_id ?? 0, $me_page_id ?? 0 );
			foreach ( $menu_pages as $page_id ) {
				if ( $page_id ) {
					wp_update_nav_menu_item(
						$menu_id,
						0,
						array(
							'menu-item-object-id' => $page_id,
							'menu-item-object'    => 'page',
							'menu-item-type'      => 'post_type',
							'menu-item-status'    => 'publish',
						)
					);
				}
			}
		}
		$locations            = (array) get_theme_mod( 'nav_menu_locations', array() );
		$locations['tabbar']  = (int) $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}

	/* ---------------------------------------------------------------------
	 * Banners
	 * ------------------------------------------------------------------ */
	$banner_defaults = array(
		array( 'AppShare 出品', '灵界', 'LingVerse', '打开即玩的文字修仙世界', '立即入世', $app_by_slug['lingjie'] ?? 0 ),
		array( '本周精选', '夜色电台', 'Night Radio', '把深夜留给声音与故事', '去看看', 0 ),
		array( '独立开发者计划', '小团队，大作品', 'Indie Makers', '发现那些认真做产品的人', '逛独立区', 0 ),
	);
	foreach ( $banner_defaults as $index => $banner ) {
		$n = $index + 1;
		if ( ! empty( $banner_ids[ $n ] ) ) {
			set_theme_mod( 'appverse_banner_' . $n . '_image', wp_get_attachment_url( $banner_ids[ $n ] ) );
		}
		set_theme_mod( 'appverse_banner_' . $n . '_eyebrow', $banner[0] );
		set_theme_mod( 'appverse_banner_' . $n . '_title', $banner[1] );
		set_theme_mod( 'appverse_banner_' . $n . '_latin', $banner[2] );
		set_theme_mod( 'appverse_banner_' . $n . '_subtitle', $banner[3] );
		set_theme_mod( 'appverse_banner_' . $n . '_cta', $banner[4] );
		set_theme_mod(
			'appverse_banner_' . $n . '_url',
			$banner[5] ? get_permalink( $banner[5] ) : ( 3 === $n ? appverse_page_url( 'indie' ) : appverse_apps_url() )
		);
	}
	set_theme_mod( 'appverse_notification_count', 62 );
	set_theme_mod( 'appverse_footer_text', 'Appverse · 发现值得安装的应用' );

	update_option( 'appverse_demo_imported', 1 );
	update_option( 'appverse_demo_ids', $ids );
	return true;
}

/**
 * Remove demo content created by the importer.
 */
function appverse_remove_demo_content() {
	$ids = get_option( 'appverse_demo_ids', array() );
	if ( is_array( $ids ) ) {
		foreach ( array( 'posts', 'pages', 'apps' ) as $group ) {
			foreach ( (array) ( $ids[ $group ] ?? array() ) as $post_id ) {
				wp_delete_post( (int) $post_id, true );
			}
		}
		foreach ( (array) ( $ids['attachments'] ?? array() ) as $attachment_id ) {
			wp_delete_attachment( (int) $attachment_id, true );
		}
	}

	delete_option( 'appverse_demo_ids' );
	delete_option( 'appverse_demo_imported' );
	delete_option( 'appverse_featured_ids' );
	delete_option( 'sticky_posts' );

	if ( (int) get_option( 'page_on_front' ) ) {
		update_option( 'page_on_front', 0 );
	}
	if ( (int) get_option( 'page_for_posts' ) ) {
		update_option( 'page_for_posts', 0 );
	}
	update_option( 'show_on_front', 'posts' );

	$menu = wp_get_nav_menu_object( '底部导航' );
	if ( $menu ) {
		wp_delete_nav_menu( $menu->term_id );
	}
}
