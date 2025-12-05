<?php
/**
 * Custom post type and helper functions for chat conversation history
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'ec_chat_register_post_type' );

function ec_chat_register_post_type() {
	register_post_type(
		'ec_chat',
		array(
			'labels'              => array(
				'name'               => __( 'Chats', 'extrachill-chat' ),
				'singular_name'      => __( 'Chat', 'extrachill-chat' ),
				'add_new'            => __( 'Add New', 'extrachill-chat' ),
				'add_new_item'       => __( 'Add New Chat', 'extrachill-chat' ),
				'edit_item'          => __( 'Edit Chat', 'extrachill-chat' ),
				'new_item'           => __( 'New Chat', 'extrachill-chat' ),
				'view_item'          => __( 'View Chat', 'extrachill-chat' ),
				'search_items'       => __( 'Search Chats', 'extrachill-chat' ),
				'not_found'          => __( 'No chats found', 'extrachill-chat' ),
				'not_found_in_trash' => __( 'No chats found in trash', 'extrachill-chat' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'supports'            => array( 'title', 'author' ),
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'menu_position'       => 25,
			'menu_icon'           => 'dashicons-format-chat',
			'show_in_rest'        => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => false,
			'can_export'          => true,
		)
	);
}

/**
 * @param int $user_id User ID
 * @return int|WP_Error Chat post ID or WP_Error
 */
function ec_chat_get_or_create_chat( $user_id ) {
	if ( ! $user_id ) {
		return new WP_Error( 'invalid_user', 'Invalid user ID' );
	}

	$existing_chat = get_posts(
		array(
			'post_type'   => 'ec_chat',
			'post_status' => 'publish',
			'author'      => $user_id,
			'numberposts' => 1,
			'orderby'     => 'date',
			'order'       => 'DESC',
			'fields'      => 'ids',
		)
	);

	if ( ! empty( $existing_chat ) ) {
		return $existing_chat[0];
	}

	$user = get_userdata( $user_id );
	if ( ! $user ) {
		return new WP_Error( 'invalid_user', 'User not found' );
	}

	$chat_post_id = wp_insert_post(
		array(
			'post_title'  => sprintf( 'Chat - %s - %s', $user->display_name, current_time( 'Y-m-d H:i:s' ) ),
			'post_type'   => 'ec_chat',
			'post_status' => 'publish',
			'post_author' => $user_id,
		)
	);

	if ( is_wp_error( $chat_post_id ) ) {
		return $chat_post_id;
	}

	update_post_meta( $chat_post_id, '_ec_chat_messages', array() );
	update_post_meta( $chat_post_id, '_ec_chat_last_updated', current_time( 'mysql' ) );

	return $chat_post_id;
}

/**
 * @param int $chat_post_id Chat post ID
 * @return array Array of messages
 */
function ec_chat_get_messages( $chat_post_id ) {
	if ( ! $chat_post_id ) {
		return array();
	}

	$messages = get_post_meta( $chat_post_id, '_ec_chat_messages', true );

	if ( ! is_array( $messages ) ) {
		return array();
	}

	return $messages;
}

/**
 * Supports all message roles: user, assistant, tool, system
 *
 * @param int    $chat_post_id Chat post ID
 * @param string $role         Message role
 * @param mixed  $content      Message content (string or null for assistant with tool_calls)
 * @param array  $extra_data   Additional message data (tool_calls, tool_call_id, etc.)
 * @return bool Success status
 */
function ec_chat_add_message( $chat_post_id, $role, $content, $extra_data = array() ) {
	if ( ! $chat_post_id || empty( $role ) ) {
		return false;
	}

	$messages = ec_chat_get_messages( $chat_post_id );

	$message = array(
		'role'      => $role,
		'content'   => $content,
		'timestamp' => current_time( 'mysql' ),
	);

	if ( ! empty( $extra_data ) ) {
		$message = array_merge( $message, $extra_data );
	}

	$messages[] = $message;

	$updated = update_post_meta( $chat_post_id, '_ec_chat_messages', $messages );
	update_post_meta( $chat_post_id, '_ec_chat_last_updated', current_time( 'mysql' ) );

	return $updated !== false;
}

/**
 * Save complete conversation from conversation loop (user message, tool calls, results, response).
 *
 * @param int   $chat_post_id Chat post ID
 * @param array $messages     Array of messages from conversation loop
 * @return bool Success status
 */
function ec_chat_save_conversation( $chat_post_id, $messages ) {
	if ( ! $chat_post_id || ! is_array( $messages ) || empty( $messages ) ) {
		return false;
	}

	foreach ( $messages as $message ) {
		if ( ! isset( $message['role'] ) ) {
			continue;
		}

		$role    = $message['role'];
		$content = $message['content'] ?? null;

		$extra_data = array();
		if ( isset( $message['tool_calls'] ) ) {
			$extra_data['tool_calls'] = $message['tool_calls'];
		}
		if ( isset( $message['tool_call_id'] ) ) {
			$extra_data['tool_call_id'] = $message['tool_call_id'];
		}

		ec_chat_add_message( $chat_post_id, $role, $content, $extra_data );
	}

	return true;
}

/**
 * @param int $chat_post_id Chat post ID
 * @return bool Success status
 */
function ec_chat_clear_history( $chat_post_id ) {
	if ( ! $chat_post_id ) {
		return false;
	}

	$updated = update_post_meta( $chat_post_id, '_ec_chat_messages', array() );
	update_post_meta( $chat_post_id, '_ec_chat_last_updated', current_time( 'mysql' ) );

	return $updated !== false;
}
