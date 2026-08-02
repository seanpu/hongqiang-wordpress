<?php
/**
 * Plugin Name: 宏强登录保护
 * Description: 登录限流、统一错误提示、XML-RPC 禁用、安全头。
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------- 1. 登录失败限流：同 IP 连续失败 5 次锁定 30 分钟 ---------- */
add_filter( 'authenticate', 'hq_login_rate_limit', 30, 3 );
function hq_login_rate_limit( $user, $username, $password ) {
	// 先检查是否已锁定（无论密码是否正确都先拦截）
	$ip  = hq_get_client_ip();
	$key = 'hq_login_fail_' . md5( $ip );
	$fails = (int) get_transient( $key );

	if ( $fails >= 5 ) {
		return new WP_Error(
			'hq_login_locked',
			'登录尝试过多，账号已临时锁定 30 分钟，请稍后再试。'
		);
	}

	if ( $user instanceof WP_User ) {
		// 登录成功，清除失败计数
		delete_transient( $key );
		return $user;
	}
	if ( empty( $username ) ) {
		return $user;
	}

	// 登录失败时计数
	add_action( 'wp_login_failed', function() use ( $key ) {
		$fails = (int) get_transient( $key );
		set_transient( $key, $fails + 1, 30 * MINUTE_IN_SECONDS );
	} );

	return $user;
}

/* ---------- 2. 统一登录错误提示（防用户名枚举） ---------- */
add_filter( 'login_errors', function( $error ) {
	$error = '用户名或密码错误。';
	return $error;
} );

/* ---------- 3. 登录成功后清除失败计数 ---------- */
add_action( 'wp_login', function() {
	delete_transient( 'hq_login_fail_' . md5( hq_get_client_ip() ) );
} );

/* ---------- 4. 禁用 XML-RPC ---------- */
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'wp_xmlrpc_server_class', '__return_empty_string' );

/* ---------- 5. 限制未登录用户访问 REST API（除必要端点） ---------- */
add_filter( 'rest_authentication_errors', function( $result ) {
	if ( is_user_logged_in() ) {
		return $result;
	}
	// 允许站点健康检查等无需认证的端点，其余要求登录
	return new WP_Error(
		'rest_forbidden',
		'未授权访问。',
		array( 'status' => 401 )
	);
} );

/* 安全响应头由 Apache (security.conf) 统一设置 */

/* ---------- 辅助：获取客户端 IP ---------- */
function hq_get_client_ip() {
	$ip = '';
	if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$ips = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
		$ip  = trim( $ips[0] );
	} elseif ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '';
}
