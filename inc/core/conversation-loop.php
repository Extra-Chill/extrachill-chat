<?php
/**
 * Multi-turn conversation loop handling AI tool calls until final text response.
 *
 * Enables chained tool usage: search → read → search again → summarize
 *
 * Hardcoded to OpenAI provider with gpt-5-mini model (line 41)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Execute conversation loop with multi-turn tool calling support.
 *
 * Max iteration limit prevents infinite loops.
 *
 * @param array $messages       Initial conversation messages
 * @param array $tools          Available tools for AI
 * @param int   $max_iterations Maximum loop iterations before error (default 10)
 * @return array|WP_Error Array with 'content', 'tool_calls', 'messages' keys, or WP_Error on failure
 */
function ec_chat_conversation_loop( $messages, $tools = array(), $max_iterations = 10 ) {
	global $ec_chat_tools;

	if ( ! is_array( $messages ) || empty( $messages ) ) {
		return new WP_Error( 'invalid_messages', 'Messages array is required' );
	}

	$all_tool_calls = array();
	$iteration      = 0;

	while ( $iteration < $max_iterations ) {
		++$iteration;

		$request_data = array(
			'messages' => $messages,
			'model'    => 'gpt-5-mini',
		);

		$response = apply_filters( 'chubes_ai_request', $request_data, 'openai', null, $tools );

		if ( ! isset( $response['success'] ) || ! $response['success'] ) {
			$error_message = isset( $response['error'] ) ? $response['error'] : 'AI request failed';
			return new WP_Error( 'chubes_ai_request_failed', $error_message );
		}

		$has_tool_calls = isset( $response['data']['tool_calls'] ) && ! empty( $response['data']['tool_calls'] );

		if ( ! $has_tool_calls ) {
			if ( ! isset( $response['data']['content'] ) ) {
				return new WP_Error( 'invalid_response', 'AI response missing content' );
			}

			return array(
				'content'    => $response['data']['content'],
				'tool_calls' => $all_tool_calls,
				'messages'   => $messages,
			);
		}

		$tool_calls = $response['data']['tool_calls'];

		$assistant_message = array(
			'role'    => 'assistant',
			'content' => null,
		);

		if ( ! empty( $tool_calls ) ) {
			$assistant_message['tool_calls'] = $tool_calls;
		}

		$messages[] = $assistant_message;

		foreach ( $tool_calls as $tool_call ) {
			$tool_id    = $tool_call['name'];
			$parameters = $tool_call['parameters'] ?? array();

			$all_tool_calls[] = array(
				'tool'       => $tool_id,
				'parameters' => $parameters,
			);

			if ( ! $ec_chat_tools || ! $ec_chat_tools->has_tool( $tool_id ) ) {
				return new WP_Error( 'tool_not_found', sprintf( 'Tool "%s" not found', $tool_id ) );
			}

			$result = $ec_chat_tools->call_tool( $tool_id, $parameters );

			if ( is_wp_error( $result ) ) {
				return $result;
			}

			$messages[] = array(
				'role'         => 'tool',
				'tool_call_id' => $tool_call['id'] ?? $tool_id,
				'content'      => wp_json_encode( $result ),
			);
		}
	}

	return new WP_Error(
		'max_iterations_reached',
		sprintf( 'Conversation loop exceeded maximum iterations (%d). AI may be stuck in tool calling loop.', $max_iterations )
	);
}
