<?php
/**
 * Chat Breadcrumb Integration
 *
 * Integrates with theme's breadcrumb system to provide chat-specific
 * breadcrumbs with "Extra Chill → Chat" root link.
 *
 * @package ExtraChillChat
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Change breadcrumb root to "Extra Chill → Chat" on chat pages
 *
 * Uses theme's extrachill_breadcrumbs_root filter to override the root link.
 * Only applies on blog ID 5 (chat.extrachill.com).
 *
 * @param string $root_link Default root breadcrumb link HTML
 * @return string Modified root link
 * @since 1.0.0
 */
function ec_chat_breadcrumb_root( $root_link ) {
	// Only apply on chat.extrachill.com (blog ID 5)
	if ( get_current_blog_id() !== 5 ) {
		return $root_link;
	}

	// On homepage, just "Extra Chill" (trail will add "Chat")
	if ( is_front_page() ) {
		return '<a href="https://extrachill.com">Extra Chill</a>';
	}

	// On other pages, include "Chat" in root
	return '<a href="https://extrachill.com">Extra Chill</a> › <a href="' . esc_url( home_url() ) . '">Chat</a>';
}
add_filter( 'extrachill_breadcrumbs_root', 'ec_chat_breadcrumb_root' );

/**
 * Override breadcrumb trail for chat homepage
 *
 * Displays just "Chat" (no link) on the homepage to prevent "Archives" suffix.
 *
 * @param string $custom_trail Existing custom trail from other plugins
 * @return string Breadcrumb trail HTML
 * @since 1.0.0
 */
function ec_chat_breadcrumb_trail_homepage( $custom_trail ) {
	// Only apply on chat.extrachill.com (blog ID 5)
	if ( get_current_blog_id() !== 5 ) {
		return $custom_trail;
	}

	// Only on front page (homepage)
	if ( is_front_page() ) {
		return '<span>Chat</span>';
	}

	return $custom_trail;
}
add_filter( 'extrachill_breadcrumbs_override_trail', 'ec_chat_breadcrumb_trail_homepage' );
