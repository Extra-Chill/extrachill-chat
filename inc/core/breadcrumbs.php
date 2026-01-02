<?php
/**
 * Chat Breadcrumb Integration
 *
 * Integrates with theme's breadcrumb system to provide chat-specific
 * breadcrumbs with "Extra Chill → Chat" root link.
 *
 * @package ExtraChillChat
 * @since 0.1.0
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
 * @since 0.1.0
 */
function ec_chat_breadcrumb_root( $root_link ) {
	if ( is_front_page() ) {
		$main_site_url = ec_get_site_url( 'main' );
		return '<a href="' . esc_url( $main_site_url ) . '">Extra Chill</a>';
	}

	$main_site_url = ec_get_site_url( 'main' );
	return '<a href="' . esc_url( $main_site_url ) . '">Extra Chill</a> › <a href="' . esc_url( home_url() ) . '">Chat</a>';
}
add_filter( 'extrachill_breadcrumbs_root', 'ec_chat_breadcrumb_root' );

/**
 * Override breadcrumb trail for chat homepage
 *
 * Displays just "Chat" (no link) on the homepage to prevent "Archives" suffix.
 *
 * @param string $custom_trail Existing custom trail from other plugins
 * @return string Breadcrumb trail HTML
 * @since 0.1.0
 */
function ec_chat_breadcrumb_trail_homepage( $custom_trail ) {
	if ( is_front_page() ) {
		return '<span class="network-dropdown-target">Chat</span>';
	}

	return $custom_trail;
}
add_filter( 'extrachill_breadcrumbs_override_trail', 'ec_chat_breadcrumb_trail_homepage' );

/**
 * Override back-to-home link label for chat pages
 *
 * Changes "Back to Extra Chill" to "Back to Chat" on chat pages.
 * Uses theme's extrachill_back_to_home_label filter.
 * Only applies on blog ID 5 (chat.extrachill.com).
 *
 * @param string $label Default back-to-home link label
 * @param string $url   Back-to-home link URL
 * @return string Modified label
 * @since 0.1.0
 */
function ec_chat_back_to_home_label( $label, $url ) {
	if ( is_front_page() ) {
		return $label;
	}

	return '�0 Back to Chat';
}
add_filter( 'extrachill_back_to_home_label', 'ec_chat_back_to_home_label', 10, 2 );

