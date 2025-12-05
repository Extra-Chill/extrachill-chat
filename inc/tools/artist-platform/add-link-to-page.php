<?php
/**
 * Artist Platform Chat Tool: Add Link to Page
 *
 * Multisite-aware tool for adding links to artist link pages.
 * Uses network-wide functions from extrachill-users plugin and switches
 * to artist.extrachill.com to access artist platform functionality.
 *
 * @package ExtraChillChat\Tools\ArtistPlatform
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register add_link_to_page tool for chat
 *
 * @param array $tools Existing tools array
 * @return array Tools array with add_link_to_page tool added
 */
function ec_chat_register_add_link_tool( $tools ) {
	$tools['add_link_to_page'] = array(
		'function' => array(
			'name'        => 'add_link_to_page',
			'description' => 'Add a new link button to the user\'s artist link page. Use this when the user asks to add a link, button, or URL to their link page.',
			'parameters'  => array(
				'type'       => 'object',
				'properties' => array(
					'link_text' => array(
						'type'        => 'string',
						'description' => 'Text to display on the button (e.g., "Pre-save my new single", "Listen on Spotify", "Buy tickets")',
					),
					'link_url'  => array(
						'type'        => 'string',
						'description' => 'Full URL where the button should link to (must include http:// or https://)',
					),
					'position'  => array(
						'type'        => 'integer',
						'description' => 'Position in the list (1-based). Use 1 for first position, 3 for third position, etc. Omit this parameter to add the link to the end of the list.',
					),
				),
				'required'   => array( 'link_text', 'link_url' ),
			),
		),
		'callback' => 'ec_chat_tool_add_link',
	);

	return $tools;
}
add_filter( 'ec_chat_tools', 'ec_chat_register_add_link_tool' );

/**
 * Chat tool callback for adding links to link pages
 *
 * Uses multisite blog switching to access artist platform functions
 * on artist.extrachill.com from chat.extrachill.com.
 *
 * @param array $parameters Tool parameters from AI
 * @param array $tool_def   Tool definition
 * @return array Success/error response
 */
function ec_chat_tool_add_link( $parameters, $tool_def ) {
	$user_id = get_current_user_id();

	// Check if users plugin function exists (network-activated)
	if ( ! function_exists( 'ec_get_artists_for_user' ) ) {
		return array(
			'error' => 'User artist functions are not available. The extrachill-users plugin must be network-activated.',
		);
	}

	// Get user's artist profiles (network-wide function)
	$artist_ids = ec_get_artists_for_user( $user_id );

	if ( empty( $artist_ids ) ) {
		return array(
			'error'      => 'You don\'t have any artist profiles yet.',
			'suggestion' => 'Create an artist profile at artist.extrachill.com to get started with your link page.',
		);
	}

	// Uses first artist profile (multi-artist support not yet implemented)
	$artist_id = $artist_ids[0];

	// Switch to artist site to access artist platform functions
	switch_to_blog( 4 );
	try {
		// Check if artist platform functions exist on artist site
		if ( ! function_exists( 'ec_get_link_page_for_artist' ) ) {
			return array(
				'error' => 'Link page system is not available on artist platform.',
			);
		}

		// Get link page for this artist
		$link_page_id = ec_get_link_page_for_artist( $artist_id );

		if ( ! $link_page_id ) {
			return array(
				'error'      => 'No link page found for your artist profile.',
				'suggestion' => 'Link pages are created automatically when you set up your artist profile.',
			);
		}

		// Prepare link data
		$link_data = array(
			'link_text'     => $parameters['link_text'],
			'link_url'      => $parameters['link_url'],
			'section_index' => 0, // Always add to first section
		);

		// Convert 1-based position to 0-based array index if provided
		if ( isset( $parameters['position'] ) ) {
			$link_data['position'] = max( 0, intval( $parameters['position'] ) - 1 );
		}

		// Call the artist platform action
		// Action handles all validation, permissions, and saving
		$result = null;
		add_action(
			'ec_artist_add_link',
			function ( $lp_id, $data, $uid ) use ( &$result ) {
				if ( function_exists( 'ec_action_artist_add_link' ) ) {
					$result = ec_action_artist_add_link( $lp_id, $data, $uid );
				} else {
					$result = new WP_Error( 'function_missing', 'Artist platform add link function not available.' );
				}
			},
			10,
			3
		);

		do_action( 'ec_artist_add_link', $link_page_id, $link_data, $user_id );

		// Check result from action
		if ( is_wp_error( $result ) ) {
			return array(
				'error'      => $result->get_error_message(),
				'error_code' => $result->get_error_code(),
			);
		}

		// Success! Build response message
		$artist_name   = get_the_title( $artist_id );
		$link_page_url = get_permalink( $link_page_id );
		$position_text = isset( $result['position'] ) ? ' at position ' . ( $result['position'] + 1 ) : '';

		return array(
			'success'       => true,
			'message'       => sprintf(
				'Added "%s" to %s\'s link page%s. View it here: %s',
				$parameters['link_text'],
				$artist_name,
				$position_text,
				$link_page_url
			),
			'link_page_url' => $link_page_url,
			'link_id'       => $result['link_id'] ?? null,
			'position'      => isset( $result['position'] ) ? $result['position'] + 1 : null,
		);

	} finally {
		restore_current_blog();
	}
}
