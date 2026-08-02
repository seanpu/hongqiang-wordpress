<?php
/**
 * Plugin Name: 备案信息页脚 & 页面装饰
 * Description: 注入 ICP 备案号（深海军蓝页脚），隐藏首页标题，为内页标题栏添加科技风背景，为博客索引页添加标题栏。
 * Version: 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_footer', 'hq_icp_footer', 999 );

function hq_icp_footer() {
	echo '<div class="hq-footer-icp" style="text-align:center;padding:28px 0;font-size:14px;color:#b8c6dd;background:#071d40;border-top:3px solid #e8a33d;width:100%;clear:both;line-height:1.9;">
		<p style="margin:4px 0;">深圳宏强电子商务有限公司 &copy; ' . esc_html( gmdate( 'Y' ) ) . ' 版权所有</p>
		<p style="margin:4px 0;">
			<a href="https://beian.miit.gov.cn/" target="_blank" rel="nofollow noopener" style="color:#dbe7ff;text-decoration:none;">粤ICP备2026103852号-1</a>
		</p>
	</div>';
}

add_action( 'wp_head', 'hq_hide_front_title' );

function hq_hide_front_title() {
	if ( is_front_page() ) {
		echo '<style>.home .ast-article-single .entry-title { display:none !important; }</style>';
	}
}

// 为博客索引页（新闻中心）注入全宽标题栏
add_action( 'astra_primary_content_top', 'hq_blog_banner' );

function hq_blog_banner() {
	if ( is_admin() ) {
		return;
	}
	if ( is_home() || is_archive() ) {
		echo '<div class="hq-archive-banner"><h1>新闻中心</h1><p>行业动态、公司新闻与运营干货分享</p></div>';
	}
}
