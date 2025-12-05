<?php
/**
 * Plugin Name: Extra Chill Chat
 * Plugin URI: https://extrachill.com
 * Description: AI chatbot for chat.extrachill.com with conversation history and tool calling
 * Version: 0.2.0
 * Author: Chris Huber
 * Author URI: https://chubes.net
 * Requires Plugins: extrachill-ai-client
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Text Domain: extrachill-chat
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EXTRACHILL_CHAT_VERSION', '0.2.0' );
define( 'EXTRACHILL_CHAT_PLUGIN_FILE', __FILE__ );
define( 'EXTRACHILL_CHAT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'EXTRACHILL_CHAT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

add_action( 'plugins_loaded', 'extrachill_chat_init' );

function extrachill_chat_init() {
	require_once EXTRACHILL_CHAT_PLUGIN_DIR . 'inc/core/chat-history.php';
	require_once EXTRACHILL_CHAT_PLUGIN_DIR . 'inc/tools/chat-tools.php';
	require_once EXTRACHILL_CHAT_PLUGIN_DIR . 'inc/tools/artist-platform/add-link-to-page.php';
	require_once EXTRACHILL_CHAT_PLUGIN_DIR . 'inc/tools/search-extrachill.php';
	require_once EXTRACHILL_CHAT_PLUGIN_DIR . 'inc/directives/ChatCoreDirective.php';
	require_once EXTRACHILL_CHAT_PLUGIN_DIR . 'inc/directives/ChatSystemPromptDirective.php';
	require_once EXTRACHILL_CHAT_PLUGIN_DIR . 'inc/directives/ChatUserContextDirective.php';
	require_once EXTRACHILL_CHAT_PLUGIN_DIR . 'inc/directives/MultisiteSiteContextWrapper.php';
	require_once EXTRACHILL_CHAT_PLUGIN_DIR . 'inc/core/conversation-loop.php';
	require_once EXTRACHILL_CHAT_PLUGIN_DIR . 'inc/core/ai-integration.php';
	require_once EXTRACHILL_CHAT_PLUGIN_DIR . 'inc/core/assets.php';
	require_once EXTRACHILL_CHAT_PLUGIN_DIR . 'inc/core/chat-hooks.php';
	require_once EXTRACHILL_CHAT_PLUGIN_DIR . 'inc/core/breadcrumbs.php';

	if ( is_admin() ) {
		require_once EXTRACHILL_CHAT_PLUGIN_DIR . 'inc/admin/admin-settings.php';
	}
}

add_action( 'extrachill_homepage_content', 'ec_chat_render_homepage' );

function ec_chat_render_homepage() {
	include EXTRACHILL_CHAT_PLUGIN_DIR . 'inc/templates/chat-interface.php';
}

add_filter( 'extrachill_enable_sticky_header', '__return_false' );
