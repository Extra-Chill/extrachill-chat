<?php
/**
 * Action hooks for chat header and footer templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'extrachill_above_chat', 'ec_chat_render_header' );
add_action( 'extrachill_below_chat', 'ec_chat_render_footer' );

function ec_chat_render_header() {
	require EXTRACHILL_CHAT_PLUGIN_DIR . 'inc/templates/chat-header.php';
}

function ec_chat_render_footer() {
	require EXTRACHILL_CHAT_PLUGIN_DIR . 'inc/templates/chat-footer.php';
}
