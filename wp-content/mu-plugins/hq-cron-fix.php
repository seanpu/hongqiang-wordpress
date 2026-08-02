<?php
/**
 * Plugin Name: 宏强后台任务修复
 * Description: 禁用 Action Scheduler 队列与外部更新检查，修复 SQLite 下 cron 报错导致的间歇性加载慢。
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. 禁用 Action Scheduler 队列运行（SQLite 不兼容其 claim SQL）
add_filter( 'action_scheduler_should_process_queue', '__return_false' );

// 2. 阻止 Action Scheduler 注册 cron 事件
add_filter( 'action_scheduler_queue_runner_should_run', '__return_false' );

// 3. 移除 action_scheduler_run_queue 的 cron 注册
add_action( 'init', 'hq_remove_as_cron', 1 );
function hq_remove_as_cron() {
	wp_clear_scheduled_hook( 'action_scheduler_run_queue' );
}

// 4. 禁用所有外部 HTTP 请求（更新检查等），配合 WP_HTTP_BLOCK_EXTERNAL
add_filter( 'pre_http_request', 'hq_block_external_requests', 10, 3 );
function hq_block_external_requests( $pre, $args, $url ) {
	if ( defined( 'WP_HTTP_BLOCK_EXTERNAL' ) && WP_HTTP_BLOCK_EXTERNAL ) {
		// 仅阻止外部请求，允许同站请求
		$host = wp_parse_url( $url, PHP_URL_HOST );
		$site = wp_parse_url( home_url(), PHP_URL_HOST );
		if ( $host && $host !== $site ) {
			return new WP_Error( 'http_request_blocked', __( 'External HTTP requests blocked.' ) );
		}
	}
	return $pre;
}

// 5. 清掉积压的 Action Scheduler 待处理任务（防止反复触发）
add_action( 'init', 'hq_cleanup_as_actions', 5 );
function hq_cleanup_as_actions() {
	if ( defined( 'WP_CLI' ) || is_admin() ) {
		return;
	}
	global $wpdb;
	// 将遗留的 pending 任务标记为 failed，避免队列反复尝试
	$wpdb->query( "UPDATE {$wpdb->prefix}actionscheduler_actions SET status='failed' WHERE status='pending' OR status='in-progress'" );
}
