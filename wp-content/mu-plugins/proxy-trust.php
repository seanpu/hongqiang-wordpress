<?php
/**
 * Plugin Name: 反向代理 HTTPS 识别
 * Description: 强制 HTTPS 上下文与 URL 输出（ALB 前端 https 终结）。
 * Version: 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 站点始终由 ALB 以 HTTPS 前端提供，强制 HTTPS 上下文
$_SERVER['HTTPS'] = 'on';
$_SERVER['SERVER_PORT'] = 443;

// 强制站点生成的 URL 使用 https
add_filter( 'script_loader_src', 'hq_force_https_resource', 99 );
add_filter( 'style_loader_src', 'hq_force_https_resource', 99 );
add_filter( 'wp_get_attachment_url', 'hq_force_https_resource', 99 );
add_filter( 'upload_dir', 'hq_force_upload_dir_https', 99 );
add_filter( 'home_url', 'hq_force_https_resource', 99 );
add_filter( 'site_url', 'hq_force_https_resource', 99 );

function hq_force_https_resource( $url ) {
	if ( is_string( $url ) && strpos( $url, 'http://' ) === 0 ) {
		$url = 'https://' . substr( $url, 7 );
	}
	return $url;
}

function hq_force_upload_dir_https( $arr ) {
	if ( isset( $arr['url'] ) ) {
		$arr['url'] = hq_force_https_resource( $arr['url'] );
	}
	if ( isset( $arr['baseurl'] ) ) {
		$arr['baseurl'] = hq_force_https_resource( $arr['baseurl'] );
	}
	return $arr;
}
