<?php
/**
 * Plugin Name: Extra Chill Chat
 * Plugin URI: https://extrachill.com
 * Description: Simple AI chatbot for chat.extrachill.com with network-wide authentication
 * Version: 1.0.0
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

define( 'EXTRACHILL_CHAT_VERSION', '1.0.0' );
define( 'EXTRACHILL_CHAT_PLUGIN_FILE', __FILE__ );
define( 'EXTRACHILL_CHAT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'EXTRACHILL_CHAT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

add_action( 'plugins_loaded', 'extrachill_chat_init' );

function extrachill_chat_init() {
	require_once EXTRACHILL_CHAT_PLUGIN_DIR . 'inc/core/chat-history.php';
	require_once EXTRACHILL_CHAT_PLUGIN_DIR . 'inc/tools/dm-tools.php';
	require_once EXTRACHILL_CHAT_PLUGIN_DIR . 'inc/directives/ChatCoreDirective.php';
	require_once EXTRACHILL_CHAT_PLUGIN_DIR . 'inc/directives/ChatSystemPromptDirective.php';
	require_once EXTRACHILL_CHAT_PLUGIN_DIR . 'inc/directives/ChatUserContextDirective.php';
	require_once EXTRACHILL_CHAT_PLUGIN_DIR . 'inc/directives/MultisiteSiteContextWrapper.php';
	require_once EXTRACHILL_CHAT_PLUGIN_DIR . 'inc/core/authentication.php';
	require_once EXTRACHILL_CHAT_PLUGIN_DIR . 'inc/core/conversation-loop.php';
	require_once EXTRACHILL_CHAT_PLUGIN_DIR . 'inc/core/ajax-handler.php';
	require_once EXTRACHILL_CHAT_PLUGIN_DIR . 'inc/core/ai-integration.php';
	require_once EXTRACHILL_CHAT_PLUGIN_DIR . 'inc/core/assets.php';
	require_once EXTRACHILL_CHAT_PLUGIN_DIR . 'inc/core/chat-hooks.php';

	if ( is_admin() ) {
		require_once EXTRACHILL_CHAT_PLUGIN_DIR . 'inc/admin/admin-settings.php';
	}
}

add_filter( 'extrachill_template_homepage', 'ec_chat_override_homepage_template' );

function ec_chat_override_homepage_template( $template ) {
	return EXTRACHILL_CHAT_PLUGIN_DIR . 'inc/templates/chat-interface.php';
}

add_filter( 'extrachill_enable_sticky_header', '__return_false' );