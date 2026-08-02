<?php
/**
 * Plugin Name: SLB 健康检查响应
 * Description: 让 SLB 健康检查请求返回 200，避免 301 循环导致后端被判不可用。
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 拦截 SLB 健康检查请求，直接返回 200
add_action( 'init', 'hq_slb_health_check', 0 );
function hq_slb_health_check() {
	$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
	if ( strpos( $ua, 'SLBHealthCheck' ) !== false ) {
		status_header( 200 );
		nocache_headers();
		header( 'Content-Type: text/plain' );
		echo 'OK';
		exit;
	}
}
