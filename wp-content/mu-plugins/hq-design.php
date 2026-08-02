<?php
/**
 * Plugin Name: 宏强站点设计系统
 * Description: 全局视觉设计 token 与样式覆盖，提升站点设计感。
 * Version: 1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'hq_enqueue_design_assets' );

function hq_enqueue_design_assets() {
	wp_enqueue_style(
		'hq-design',
		plugin_dir_url( __FILE__ ) . 'design.css',
		array(),
		filemtime( __DIR__ . '/design.css' )
	);
}
