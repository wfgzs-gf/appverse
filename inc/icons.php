<?php
/**
 * Inline SVG icon set.
 *
 * @package Appverse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return a list of icon definitions.
 *
 * @return array<string, array{paths: string, filled?: bool}>
 */
function appverse_icon_definitions() {
	static $icons = null;
	if ( null !== $icons ) {
		return $icons;
	}

	$stroke = 'fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"';

	$icons = array(
		'search'          => array( 'paths' => '<circle cx="11" cy="11" r="6.5" ' . $stroke . '/><path d="m16 16 4.2 4.2" ' . $stroke . '/>' ),
		'bell'            => array( 'paths' => '<path d="M18.4 9.8a6.4 6.4 0 0 0-12.8 0c0 5-2 6.2-2 6.2h16.8s-2-1.2-2-6.2Z" ' . $stroke . '/><path d="M10 19.2a2.2 2.2 0 0 0 4 0" ' . $stroke . '/>' ),
		'bell-fill'       => array( 'paths' => '<path fill="currentColor" d="M12 2.4a7.4 7.4 0 0 0-7.4 7.4c0 4.8-1.7 6.4-2.3 6.9a1 1 0 0 0 .6 1.8h18.2a1 1 0 0 0 .6-1.8c-.6-.5-2.3-2.1-2.3-6.9A7.4 7.4 0 0 0 12 2.4Zm-2.1 18.2a2.4 2.4 0 0 0 4.2 0Z"/>' ),
		'settings'        => array( 'paths' => '<circle cx="12" cy="12" r="3.1" ' . $stroke . '/><path d="M19.4 14.2a7.8 7.8 0 0 0 0-4.4l2-1.5-2-3.4-2.3 1a8 8 0 0 0-3.8-2.2L13 1.5h-4l-.3 2.2A8 8 0 0 0 4.9 5.9l-2.3-1-2 3.4 2 1.5a7.8 7.8 0 0 0 0 4.4l-2 1.5 2 3.4 2.3-1a8 8 0 0 0 3.8 2.2l.3 2.2h4l.3-2.2a8 8 0 0 0 3.8-2.2l2.3 1 2-3.4Z" ' . $stroke . '/>' ),
		'download'        => array( 'paths' => '<path d="M12 3.5v11" ' . $stroke . '/><path d="m7.2 10 4.8 4.8 4.8-4.8" ' . $stroke . '/><path d="M4 17.5v2.2c0 .5.4.9.9.9h14.2c.5 0 .9-.4.9-.9v-2.2" ' . $stroke . '/>' ),
		'cloud-download'  => array( 'paths' => '<path d="M7.4 17.4a4.4 4.4 0 0 1-.7-8.7 5.6 5.6 0 0 1 10.8-1.2 4.1 4.1 0 0 1 1.1 8" ' . $stroke . '/><path d="M12 11.5v8" ' . $stroke . '/><path d="m8.7 16.4 3.3 3.3 3.3-3.3" ' . $stroke . '/>' ),
		'arrow-left'      => array( 'paths' => '<path d="M20 12H4.5" ' . $stroke . '/><path d="m10.5 5.5-6.5 6.5 6.5 6.5" ' . $stroke . '/>' ),
		'arrow-right'     => array( 'paths' => '<path d="M4 12h15.5" ' . $stroke . '/><path d="m13.5 5.5 6.5 6.5-6.5 6.5" ' . $stroke . '/>' ),
		'chevron-left'    => array( 'paths' => '<path d="m15 5-7 7 7 7" ' . $stroke . '/>' ),
		'chevron-right'   => array( 'paths' => '<path d="m9 5 7 7-7 7" ' . $stroke . '/>' ),
		'chevron-down'    => array( 'paths' => '<path d="m5 9 7 7 7-7" ' . $stroke . '/>' ),
		'chevron-up'      => array( 'paths' => '<path d="m5 15 7-7 7 7" ' . $stroke . '/>' ),
		'home'            => array( 'paths' => '<path d="m3.5 10.2 8.5-7 8.5 7v9.1a1.2 1.2 0 0 1-1.2 1.2H4.7a1.2 1.2 0 0 1-1.2-1.2Z" ' . $stroke . '/><path d="M9.3 20.5v-6.3h5.4v6.3" ' . $stroke . '/>' ),
		'home-fill'       => array( 'paths' => '<path fill="currentColor" d="M11.3 2.3a1.1 1.1 0 0 1 1.4 0l8.5 7a1.1 1.1 0 0 1 .4.85v9.05a2.3 2.3 0 0 1-2.3 2.3H4.7a2.3 2.3 0 0 1-2.3-2.3v-9.05c0-.33.15-.64.4-.85ZM9.2 13.1a1 1 0 0 0-1 1v6.4h7.6v-6.4a1 1 0 0 0-1-1Z"/>' ),
		'compass'         => array( 'paths' => '<circle cx="12" cy="12" r="9" ' . $stroke . '/><path d="m15.6 8.4-2.1 5.1-5.1 2.1 2.1-5.1Z" ' . $stroke . '/>' ),
		'compass-fill'    => array( 'paths' => '<path fill="currentColor" d="M12 2.2A9.8 9.8 0 1 0 21.8 12 9.8 9.8 0 0 0 12 2.2Zm4.5 5.3-2.7 6.3-6.3 2.7 2.7-6.3Z"/>' ),
		'user'            => array( 'paths' => '<circle cx="12" cy="8" r="4" ' . $stroke . '/><path d="M4 20.3c.7-4 3.9-6.2 8-6.2s7.3 2.2 8 6.2" ' . $stroke . '/>' ),
		'user-fill'       => array( 'paths' => '<path fill="currentColor" d="M12 2.8A5.2 5.2 0 1 0 17.2 8 5.2 5.2 0 0 0 12 2.8Zm0 12.2c-4.5 0-8.2 2.4-9 6.2a1 1 0 0 0 1 1.2h16a1 1 0 0 0 1-1.2c-.8-3.8-4.5-6.2-9-6.2Z"/>' ),
		'users'           => array( 'paths' => '<circle cx="9" cy="8.5" r="3.6" ' . $stroke . '/><path d="M2.8 20c.6-3.6 3.3-5.7 6.7-5.7s6.1 2.1 6.7 5.7" ' . $stroke . '/><path d="M16 5.4a3.4 3.4 0 0 1 0 6.6" ' . $stroke . '/><path d="M17.6 14.6c2.1.5 3.5 2.2 3.9 5" ' . $stroke . '/>' ),
		'grid'            => array( 'paths' => '<rect x="3.5" y="3.5" width="7" height="7" rx="2" ' . $stroke . '/><rect x="13.5" y="3.5" width="7" height="7" rx="2" ' . $stroke . '/><rect x="3.5" y="13.5" width="7" height="7" rx="2" ' . $stroke . '/><rect x="13.5" y="13.5" width="7" height="7" rx="2" ' . $stroke . '/>' ),
		'grid-fill'       => array( 'paths' => '<rect x="2.8" y="2.8" width="8" height="8" rx="2.4" fill="currentColor"/><rect x="13.2" y="2.8" width="8" height="8" rx="2.4" fill="currentColor"/><rect x="2.8" y="13.2" width="8" height="8" rx="2.4" fill="currentColor"/><rect x="13.2" y="13.2" width="8" height="8" rx="2.4" fill="currentColor"/>' ),
		'trophy'          => array( 'paths' => '<path d="M7 4h10v4.2a5 5 0 0 1-10 0Z" ' . $stroke . '/><path d="M7 5.5H3.8v1.4A3.3 3.3 0 0 0 7 10.1M17 5.5h3.2v1.4a3.3 3.3 0 0 1-3.2 3.2" ' . $stroke . '/><path d="M12 13.2v3.2M8.4 20.4h7.2M9.6 16.4h4.8l.6 4H9Z" ' . $stroke . '/>' ),
		'calendar'        => array( 'paths' => '<rect x="3.5" y="5" width="17" height="16" rx="3" ' . $stroke . '/><path d="M3.5 9.5h17M8 3v4M16 3v4" ' . $stroke . '/><path d="M8 13.5h.01M12 13.5h.01M16 13.5h.01M8 17h.01M12 17h.01" ' . $stroke . '/>' ),
		'calendar-check'  => array( 'paths' => '<rect x="3.5" y="5" width="17" height="16" rx="3" ' . $stroke . '/><path d="M3.5 9.5h17M8 3v4M16 3v4" ' . $stroke . '/><path d="m8.5 15 2.3 2.3 4.7-4.7" ' . $stroke . '/>' ),
		'code'            => array( 'paths' => '<path d="m8.5 7-5 5 5 5M15.5 7l5 5-5 5M13.5 4l-3 16" ' . $stroke . '/>' ),
		'check'           => array( 'paths' => '<path d="m5 12.5 4.5 4.5L19.5 7" ' . $stroke . '/>' ),
		'check-circle'    => array( 'paths' => '<circle cx="12" cy="12" r="9" ' . $stroke . '/><path d="m8 12.3 2.7 2.7 5.3-5.5" ' . $stroke . '/>' ),
		'star'            => array( 'paths' => '<path d="m12 3.2 2.6 5.3 5.9.9-4.2 4.1 1 5.9-5.3-2.8-5.3 2.8 1-5.9-4.2-4.1 5.9-.9Z" ' . $stroke . '/>' ),
		'star-fill'       => array( 'paths' => '<path fill="currentColor" d="m12 2.6 3 6 6.7 1-4.9 4.7 1.2 6.7-6-3.2-6 3.2 1.2-6.7L2.3 9.6l6.7-1Z"/>' ),
		'folder'          => array( 'paths' => '<path d="M3.5 7.2A2.2 2.2 0 0 1 5.7 5h3.1l2 2.4h7.5a2.2 2.2 0 0 1 2.2 2.2v8.2a2.2 2.2 0 0 1-2.2 2.2H5.7a2.2 2.2 0 0 1-2.2-2.2Z" ' . $stroke . '/>' ),
		'folder-fill'     => array( 'paths' => '<path fill="currentColor" d="M3.2 7.1a2.5 2.5 0 0 1 2.5-2.4h3.1c.5 0 1 .2 1.3.6l1.7 1.9h6.3a2.5 2.5 0 0 1 2.5 2.5v8.2a2.5 2.5 0 0 1-2.5 2.4H5.7a2.5 2.5 0 0 1-2.5-2.4Z"/>' ),
		'filter'          => array( 'paths' => '<path d="M3.5 5h17l-6.5 7.7v5.9l-4 2.2v-8.1Z" ' . $stroke . '/>' ),
		'plus'            => array( 'paths' => '<path d="M12 4v16M4 12h16" ' . $stroke . '/>' ),
		'heart'           => array( 'paths' => '<path d="M12 20.2S3.8 15.3 3.8 9.6a4.6 4.6 0 0 1 8.2-2.8 4.6 4.6 0 0 1 8.2 2.8c0 5.7-8.2 10.6-8.2 10.6Z" ' . $stroke . '/>' ),
		'heart-fill'      => array( 'paths' => '<path fill="currentColor" d="M12 20.8S3 15.5 3 9.5a5.2 5.2 0 0 1 9-3.5 5.2 5.2 0 0 1 9 3.5c0 6-9 11.3-9 11.3Z"/>' ),
		'comment'         => array( 'paths' => '<path d="M20.5 11.5a8.5 8.5 0 0 1-12.4 7.6L3.5 20.5l1.5-4.4a8.5 8.5 0 1 1 15.5-4.6Z" ' . $stroke . '/>' ),
		'comment-fill'    => array( 'paths' => '<path fill="currentColor" d="M12 2.5A9.5 9.5 0 0 0 4.2 17L2.5 21.5 7 19.9A9.5 9.5 0 1 0 12 2.5Z"/>' ),
		'thumb-up'        => array( 'paths' => '<path d="M7 10.5v9H3.8a.8.8 0 0 1-.8-.8v-7.4a.8.8 0 0 1 .8-.8Z" ' . $stroke . '/><path d="M7 10.5 11 3a2.2 2.2 0 0 1 2.1 3l-1 3.3h5a2 2 0 0 1 2 2.4l-1.2 6a2 2 0 0 1-2 1.7H7" ' . $stroke . '/>' ),
		'thumb-down'      => array( 'paths' => '<path d="M17 13.5v-9h3.2a.8.8 0 0 1 .8.8v7.4a.8.8 0 0 1-.8.8Z" ' . $stroke . '/><path d="M17 13.5 13 21a2.2 2.2 0 0 1-2.1-3l1-3.3h-5a2 2 0 0 1-2-2.4l1.2-6a2 2 0 0 1 2-1.7H17" ' . $stroke . '/>' ),
		'more'            => array( 'paths' => '<circle cx="5" cy="12" r="1.7" fill="currentColor"/><circle cx="12" cy="12" r="1.7" fill="currentColor"/><circle cx="19" cy="12" r="1.7" fill="currentColor"/>' ),
		'close'           => array( 'paths' => '<path d="m6 6 12 12M18 6 6 18" ' . $stroke . '/>' ),
		'shield'          => array( 'paths' => '<path d="M12 2.8 20 6v5.2c0 5-3.3 8.4-8 10-4.7-1.6-8-5-8-10V6Z" ' . $stroke . '/><path d="m8.6 11.8 2.4 2.4 4.4-4.8" ' . $stroke . '/>' ),
		'clock'           => array( 'paths' => '<circle cx="12" cy="12" r="9" ' . $stroke . '/><path d="M12 7v5.3l3.3 2" ' . $stroke . '/>' ),
		'share'           => array( 'paths' => '<circle cx="18" cy="5.5" r="2.7" ' . $stroke . '/><circle cx="6" cy="12" r="2.7" ' . $stroke . '/><circle cx="18" cy="18.5" r="2.7" ' . $stroke . '/><path d="m8.4 10.7 7.2-3.9M8.4 13.3l7.2 3.9" ' . $stroke . '/>' ),
		'logout'          => array( 'paths' => '<path d="M9.5 4H5.2A1.2 1.2 0 0 0 4 5.2v13.6A1.2 1.2 0 0 0 5.2 20h4.3" ' . $stroke . '/><path d="M14 8.5 17.5 12 14 15.5M9 12h8.5" ' . $stroke . '/>' ),
		'lock'            => array( 'paths' => '<rect x="4.5" y="10" width="15" height="10.5" rx="2.4" ' . $stroke . '/><path d="M8 10V7.4a4 4 0 0 1 8 0V10M12 14.5v2.2" ' . $stroke . '/>' ),
		'smartphone'      => array( 'paths' => '<rect x="6.5" y="2.8" width="11" height="18.4" rx="2.6" ' . $stroke . '/><path d="M10.5 5.4h3M12 18h.01" ' . $stroke . '/>' ),
		'collection'      => array( 'paths' => '<rect x="3.5" y="3.5" width="7" height="7" rx="2" ' . $stroke . '/><rect x="13.5" y="3.5" width="7" height="7" rx="2" ' . $stroke . '/><rect x="3.5" y="13.5" width="7" height="7" rx="2" ' . $stroke . '/><path d="M17 13.5v7M13.5 17h7" ' . $stroke . '/>' ),
		'list'            => array( 'paths' => '<path d="M9 6h11M9 12h11M9 18h11" ' . $stroke . '/><path d="M4.5 6h.01M4.5 12h.01M4.5 18h.01" ' . $stroke . '/>' ),
		'play'            => array( 'paths' => '<path d="M8 4.8v14.4L20 12Z" ' . $stroke . '/>' ),
		'fire'            => array( 'paths' => '<path d="M13.3 2.5c.6 4-1.3 5.5-2.7 7.2-1.1 1.3-2 2.6-2 4.6a5.4 5.4 0 0 0 10.8 0c0-2.6-1.1-4.6-2.3-6.2-1.7 1.2-2.7 2.4-3 3.7-.9-2.6-.5-5.9-.8-9.3Z" ' . $stroke . '/><path d="M8.5 10.5C5.5 12 4 14.2 4 16.8a6.8 6.8 0 0 0 3 5.7" ' . $stroke . '/>' ),
		'crown'           => array( 'paths' => '<path d="m3.5 7 4.2 3.2L12 4l4.3 6.2L20.5 7l-1.3 11.2H4.8Z" ' . $stroke . '/><path d="M5 21h14" ' . $stroke . '/>' ),
		'gift'            => array( 'paths' => '<rect x="3.5" y="8" width="17" height="4.5" rx="1.2" ' . $stroke . '/><path d="M5 12.5v7a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-7M12 8v12.5" ' . $stroke . '/><path d="M12 8H8.6a2.3 2.3 0 1 1 0-4.6C11 3.4 12 8 12 8ZM12 8h3.4a2.3 2.3 0 1 0 0-4.6C13 3.4 12 8 12 8Z" ' . $stroke . '/>' ),
		'task'            => array( 'paths' => '<rect x="4" y="4.5" width="16" height="16" rx="3" ' . $stroke . '/><path d="m8.2 12 2.5 2.5 5.1-5.2" ' . $stroke . '/>' ),
		'vip'             => array( 'paths' => '<path d="M4.5 8.5 8 11l4-5 4 5 3.5-2.5-1.2 10H5.7Z" ' . $stroke . '/><path d="M8 20.5h8" ' . $stroke . '/>' ),
		'chart'           => array( 'paths' => '<path d="M4 20V4M4 20h16" ' . $stroke . '/><path d="M8 16v-4M12 16V8M16 16v-6" ' . $stroke . '/>' ),
		'link'            => array( 'paths' => '<path d="m10 14 4-4" ' . $stroke . '/><path d="M8.5 17.5 7 19a3.5 3.5 0 0 1-5-5l3-3a3.5 3.5 0 0 1 5 0" ' . $stroke . '/><path d="M15.5 6.5 17 5a3.5 3.5 0 0 1 5 5l-3 3a3.5 3.5 0 0 1-5 0" ' . $stroke . '/>' ),
		'external'        => array( 'paths' => '<path d="M14 4h6v6M20 4l-9 9" ' . $stroke . '/><path d="M18 14v4.5A1.5 1.5 0 0 1 16.5 20h-11A1.5 1.5 0 0 1 4 18.5v-11A1.5 1.5 0 0 1 5.5 6H10" ' . $stroke . '/>' ),
		'apps'            => array( 'paths' => '<rect x="3" y="3" width="6" height="6" rx="1.8" ' . $stroke . '/><rect x="15" y="3" width="6" height="6" rx="1.8" ' . $stroke . '/><rect x="3" y="15" width="6" height="6" rx="1.8" ' . $stroke . '/><rect x="15" y="15" width="6" height="6" rx="1.8" ' . $stroke . '/>' ),
		'edit'            => array( 'paths' => '<path d="M14.5 4.5 19.5 9.5 8 21H3v-5Z" ' . $stroke . '/><path d="m12.5 6.5 5 5" ' . $stroke . '/>' ),
		'eye'             => array( 'paths' => '<path d="M2.5 12S6 5.8 12 5.8 21.5 12 21.5 12 18 18.2 12 18.2 2.5 12 2.5 12Z" ' . $stroke . '/><circle cx="12" cy="12" r="2.8" ' . $stroke . '/>' ),
		'mail'            => array( 'paths' => '<rect x="3" y="5" width="18" height="14" rx="2.5" ' . $stroke . '/><path d="m4 7 8 6 8-6" ' . $stroke . '/>' ),
		'refresh'         => array( 'paths' => '<path d="M20 7.5V3.8l-2.2 2.2A8.4 8.4 0 1 0 20.4 14" ' . $stroke . '/>' ),
		'sparkles'        => array( 'paths' => '<path d="m12 3 1.7 4.7L18.5 9.5l-4.8 1.8L12 16l-1.7-4.7L5.5 9.5l4.8-1.8Z" ' . $stroke . '/><path d="m18.5 15 .9 2.4 2.4.9-2.4.9-.9 2.4-.9-2.4-2.4-.9 2.4-.9Z" ' . $stroke . '/>' ),
		'trash'           => array( 'paths' => '<path d="M4 6.5h16M9 6.5V4.2A1.2 1.2 0 0 1 10.2 3h3.6A1.2 1.2 0 0 1 15 4.2v2.3M6.5 6.5l.9 13a1.5 1.5 0 0 0 1.5 1.4h6.2a1.5 1.5 0 0 0 1.5-1.4l.9-13" ' . $stroke . '/><path d="M10 10.5v6M14 10.5v6" ' . $stroke . '/>' ),
	);

	return $icons;
}

/**
 * Render an inline SVG icon.
 *
 * @param string $name  Icon name.
 * @param int    $size  Pixel size.
 * @param string $class Extra CSS classes.
 * @param bool   $echo  Echo or return.
 * @return string
 */
function appverse_icon( $name, $size = 24, $class = '', $echo = false ) {
	$defs = appverse_icon_definitions();
	if ( ! isset( $defs[ $name ] ) ) {
		$name = 'grid';
	}

	$classes = trim( 'av-icon av-icon--' . sanitize_html_class( $name ) . ' ' . $class );
	$svg     = sprintf(
		'<svg class="%1$s" width="%2$d" height="%2$d" viewBox="0 0 24 24" aria-hidden="true" focusable="false">%3$s</svg>',
		esc_attr( $classes ),
		(int) $size,
		$defs[ $name ]['paths']
	);

	if ( $echo ) {
		echo $svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted inline SVG.
	}
	return $svg;
}

/**
 * Echo helper.
 *
 * @param string $name  Icon.
 * @param int    $size  Size.
 * @param string $class Class.
 */
function appverse_the_icon( $name, $size = 24, $class = '' ) {
	appverse_icon( $name, $size, $class, true );
}
