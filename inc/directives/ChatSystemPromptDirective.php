<?php
/**
 * Priority 20 AI directive for user-customizable system prompt.
 *
 * Loads custom system prompt from site-level plugin settings.
 *
 * Filter execution order: Runs after ChatCoreDirective (10), before ChatUserContextDirective (30)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChatSystemPromptDirective {

	/**
	 * Inject custom system prompt at priority 20.
	 *
	 * Uses get_option() for site-level storage (each site can have its own prompt).
	 *
	 * @param array $request AI request array
	 * @return array Modified request with custom system prompt injected
	 */
	public static function inject( $request, $provider_name = null, $streaming_callback = null, $tools = null, $conversation_data = null ) {
		if ( ! isset( $request['messages'] ) || ! is_array( $request['messages'] ) ) {
			return $request;
		}

		$custom_prompt = get_option( 'extrachill_chat_system_prompt', '' );

		if ( empty( $custom_prompt ) ) {
			return $request;
		}

		array_push( $request['messages'], array(
			'role'    => 'system',
			'content' => $custom_prompt
		) );

		return $request;
	}
}

// Register directive at priority 20 - runs AFTER core directive but BEFORE site context
add_filter( 'ai_request', array( 'ChatSystemPromptDirective', 'inject' ), 20, 5 );
