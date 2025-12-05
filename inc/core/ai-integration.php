<?php
/**
 * OpenAI integration via extrachill-ai-client with DM-Multisite tool support
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array Array of tool definitions formatted for AI request
 */
function ec_chat_get_available_tools() {
	global $ec_chat_tools;

	if ( ! $ec_chat_tools ) {
		return array();
	}

	return $ec_chat_tools->get_tools_for_ai();
}

/**
 * Send AI message with tool support and 20-message conversation history window.
 *
 * System prompts injected via directive filters during chubes_ai_request:
 * - Priority 10: ChatCoreDirective (agent identity + HTML requirement)
 * - Priority 20: ChatSystemPromptDirective (custom prompt from site settings)
 * - Priority 30: ChatUserContextDirective (user identity and membership)
 *
 * @param string $user_message  User's message
 * @param int    $chat_post_id  Chat post ID for conversation history
 * @return array|WP_Error Array with 'content', 'tool_calls', 'messages' keys, or WP_Error on failure
 */
function ec_chat_send_ai_message( $user_message, $chat_post_id = 0 ) {
	$messages = array();

	if ( $chat_post_id ) {
		$history = ec_chat_get_messages( $chat_post_id );
		$recent_history = array_slice( $history, -20 );

		foreach ( $recent_history as $msg ) {
			$message = array(
				'role'    => $msg['role'],
				'content' => $msg['content']
			);

			if ( isset( $msg['tool_calls'] ) ) {
				$message['tool_calls'] = $msg['tool_calls'];
			}

			if ( isset( $msg['tool_call_id'] ) ) {
				$message['tool_call_id'] = $msg['tool_call_id'];
			}

			$messages[] = $message;
		}
	}

	$messages[] = array(
		'role'    => 'user',
		'content' => $user_message
	);

	$tools = ec_chat_get_available_tools();

	return ec_chat_conversation_loop( $messages, $tools );
}
