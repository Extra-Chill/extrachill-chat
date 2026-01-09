<?php
/**
 * Chat Block - Server-Side Render
 *
 * Renders the chat React app mount point on the frontend.
 * Handles config localization for the React app.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$user_id      = get_current_user_id();
$chat_history = array();

if ( $user_id && function_exists( 'ec_chat_get_or_create_chat' ) ) {
	$chat_post_id = ec_chat_get_or_create_chat( $user_id );
	if ( ! is_wp_error( $chat_post_id ) && function_exists( 'ec_chat_get_messages' ) ) {
		$chat_history = ec_chat_get_messages( $chat_post_id );
	}
}

$config = array(
	'restUrl'     => rest_url( 'extrachill/v1/chat/' ),
	'nonce'       => wp_create_nonce( 'wp_rest' ),
	'userId'      => $user_id,
	'chatHistory' => $chat_history,
);

$asset_file = include EXTRACHILL_CHAT_PLUGIN_DIR . 'build/blocks/chat/view.asset.php';

wp_enqueue_script(
	'ec-chat-view',
	EXTRACHILL_CHAT_PLUGIN_URL . 'build/blocks/chat/view.js',
	$asset_file['dependencies'],
	$asset_file['version'],
	true
);

wp_localize_script( 'ec-chat-view', 'ecChatConfig', $config );

$wrapper_attributes = get_block_wrapper_attributes( array(
	'class' => 'ec-chat-block',
) );

echo '<div ' . $wrapper_attributes . '>';
echo '<div id="ec-chat-root"></div>';
echo '</div>';
