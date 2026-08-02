<?php
/**
 * Plugin Name: 宏强性能优化
 * Description: PHP 输出压缩 + WordPress 冗余清理（emoji/embed/REST/XML-RPC）。
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------- PHP 输出 Gzip 压缩 ----------
if ( ! is_admin() && ! ini_get( 'zlib.output_compression' ) && ! ob_get_level() ) {
	ob_start( 'ob_gzhandler' );
}

// ---------- 移除 WordPress 冗余 ----------

// 1. 禁用 emoji 脚本和样式
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
add_filter( 'emoji_svg_url', '__return_false' );

// 2. 移除 REST API 的 link header 和 oEmbed
remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 10 );
remove_action( 'wp_head', 'wp_oembed_add_host_js' );
remove_action( 'template_redirect', 'rest_output_link_header', 11 );

// 3. 移除 RSD / WLW / shortlink / generator 标签
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'wp_generator' );

// 4. 移除区块库全局样式（前台不需要的 block-library 样式）
add_action( 'wp_enqueue_scripts', 'hq_deregister_styles', 100 );
function hq_deregister_styles() {
	// 保留当前主题和插件的必要样式，仅移除 WP 核心冗余
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'classic-theme-styles' );
	wp_dequeue_style( 'global-styles' );
}

// 5. 禁用 XML-RPC
add_filter( 'xmlrpc_enabled', '__return_false' );

// 6. 移除未使用的 dashicons（前台非登录用户）
add_action( 'wp_enqueue_scripts', 'hq_remove_dashicons', 100 );
function hq_remove_dashicons() {
	if ( ! is_user_logged_in() ) {
		wp_deregister_style( 'dashicons' );
	}
}
