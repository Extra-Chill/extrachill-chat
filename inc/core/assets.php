<?php
/**
 * Conditional loading of CSS and JavaScript for chat interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'ec_chat_enqueue_assets' );

function ec_chat_enqueue_assets() {
	if ( ! is_front_page() ) {
		return;
	}

	$css_file = EXTRACHILL_CHAT_PLUGIN_DIR . 'assets/css/chat.css';
	$js_file  = EXTRACHILL_CHAT_PLUGIN_DIR . 'assets/js/chat.js';

	wp_enqueue_style(
		'extrachill-chat',
		EXTRACHILL_CHAT_PLUGIN_URL . 'assets/css/chat.css',
		array(),
		file_exists( $css_file ) ? filemtime( $css_file ) : EXTRACHILL_CHAT_VERSION
	);

	wp_enqueue_script(
		'extrachill-chat',
		EXTRACHILL_CHAT_PLUGIN_URL . 'assets/js/chat.js',
		array(),
		file_exists( $js_file ) ? filemtime( $js_file ) : EXTRACHILL_CHAT_VERSION,
		true
	);

	$user_id = get_current_user_id();
	$chat_history = array();

	if ( $user_id ) {
		$chat_post_id = ec_chat_get_or_create_chat( $user_id );
		if ( ! is_wp_error( $chat_post_id ) ) {
			$chat_history = ec_chat_get_messages( $chat_post_id );
		}
	}

	wp_localize_script(
		'extrachill-chat',
		'ecChatData',
		array(
			'restUrl'     => rest_url( 'extrachill/v1/chat/' ),
			'nonce'       => wp_create_nonce( 'wp_rest' ),
			'userId'      => $user_id,
			'chatHistory' => $chat_history
		)
	);
}