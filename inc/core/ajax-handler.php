<?php
/**
 * AJAX handlers for chat messages and history clearing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_ajax_ec_chat_message', 'ec_chat_handle_message' );
add_action( 'wp_ajax_ec_chat_clear_history', 'ec_chat_handle_clear_history' );

function ec_chat_handle_message() {
	check_ajax_referer( 'ec_chat_nonce', 'nonce' );

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => 'You must be logged in to use chat.' ), 401 );
	}

	$user_message = isset( $_POST['message'] ) ? wp_unslash( $_POST['message'] ) : '';
	$user_message = sanitize_textarea_field( $user_message );

	if ( empty( $user_message ) ) {
		wp_send_json_error( array( 'message' => 'Message cannot be empty.' ), 400 );
	}

	$user_id = get_current_user_id();
	$chat_post_id = ec_chat_get_or_create_chat( $user_id );

	if ( is_wp_error( $chat_post_id ) ) {
		error_log( 'ExtraChill Chat History Error: ' . $chat_post_id->get_error_message() );
		wp_send_json_error( array( 'message' => 'Sorry, I encountered an error with chat history. Please try again.' ), 500 );
	}

	$ai_response = ec_chat_send_ai_message( $user_message, $chat_post_id );

	if ( is_wp_error( $ai_response ) ) {
		error_log( 'ExtraChill Chat AI Error: ' . $ai_response->get_error_message() );
		wp_send_json_error( array( 'message' => 'Sorry, I encountered an error processing your message. Please try again.' ), 500 );
	}

	$response_content = $ai_response['content'];
	$tool_calls = $ai_response['tool_calls'] ?? array();
	$messages = $ai_response['messages'] ?? array();

	// Save complete conversation: user message, assistant tool calls, tool results, final response
	if ( ! empty( $messages ) ) {
		ec_chat_save_conversation( $chat_post_id, $messages );
	}

	wp_send_json_success( array(
		'message'    => $response_content,
		'tool_calls' => $tool_calls,  // Metadata for UI display (which tools were used)
		'timestamp'  => current_time( 'mysql' )
	) );
}

function ec_chat_handle_clear_history() {
	check_ajax_referer( 'ec_chat_clear_nonce', 'nonce' );

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => 'You must be logged in to clear chat history.' ), 401 );
	}

	$user_id = get_current_user_id();
	$chat_post_id = ec_chat_get_or_create_chat( $user_id );

	if ( is_wp_error( $chat_post_id ) ) {
		error_log( 'ExtraChill Chat Clear History Error: ' . $chat_post_id->get_error_message() );
		wp_send_json_error( array( 'message' => 'Sorry, I encountered an error clearing chat history.' ), 500 );
	}

	$cleared = ec_chat_clear_history( $chat_post_id );

	if ( ! $cleared ) {
		wp_send_json_error( array( 'message' => 'Failed to clear chat history.' ), 500 );
	}

	wp_send_json_success( array(
		'message' => 'Chat history cleared successfully.'
	) );
}