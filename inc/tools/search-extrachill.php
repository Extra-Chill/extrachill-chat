<?php
/**
 * ExtraChill Network Search Tool for Chat
 *
 * Provides access to the native extrachill_multisite_search() function
 * with superior relevance scoring and rich metadata.
 *
 * Features weighted algorithm prioritizing exact matches
 * and comprehensive metadata including thumbnails, taxonomies, and site context.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register ExtraChill network search tool
 *
 * @param array $tools Existing tools array
 * @return array Tools array with search_extrachill added
 */
function ec_chat_register_search_tool( $tools ) {
	$tools['search_extrachill'] = array(
		'function' => array(
			'name'        => 'search_extrachill',
			'description' => 'Search across all Extra Chill network sites (extrachill.com, community, shop, artist, events, stream, chat, app). Returns highly relevant results with weighted scoring that prioritizes exact matches. Use this to find posts, pages, forum topics, products, and other content across the entire network.',
			'parameters'  => array(
				'type'       => 'object',
				'properties' => array(
					'query' => array(
						'type'        => 'string',
						'description' => 'Search query to find content across the network',
					),
					'limit' => array(
						'type'        => 'integer',
						'description' => 'Maximum number of results to return (default: 10, max: 50)',
					),
					'sites' => array(
						'type'        => 'array',
						'items'       => array( 'type' => 'string' ),
						'description' => 'Optional: specific sites to search. Use domain format: ["community.extrachill.com", "extrachill.com"]. If omitted, searches all sites.',
					),
				),
				'required'   => array( 'query' ),
			),
		),
		'callback' => 'ec_chat_tool_search_extrachill',
	);

	return $tools;
}
add_filter( 'ec_chat_tools', 'ec_chat_register_search_tool' );

/**
 * Chat tool callback for ExtraChill network search
 *
 * @param array $parameters Tool parameters from AI
 * @param array $tool_def   Tool definition
 * @return array Search results or error
 */
function ec_chat_tool_search_extrachill( $parameters, $tool_def ) {
	// Check if extrachill-search plugin function exists
	if ( ! function_exists( 'extrachill_multisite_search' ) ) {
		return array(
			'error'   => 'Search functionality is not available. The extrachill-search plugin must be network activated.',
			'success' => false,
		);
	}

	// Validate query
	if ( empty( $parameters['query'] ) ) {
		return array(
			'error'   => 'Search query is required',
			'success' => false,
		);
	}

	$query = $parameters['query'];
	$limit = isset( $parameters['limit'] ) ? min( absint( $parameters['limit'] ), 50 ) : 10;
	$sites = isset( $parameters['sites'] ) && is_array( $parameters['sites'] ) ? $parameters['sites'] : array();

	// Call native ExtraChill multisite search
	$search_args = array(
		'limit'        => $limit,
		'return_count' => true,
	);

	$results = extrachill_multisite_search( $query, $sites, $search_args );

	// Handle error case
	if ( empty( $results ) || ! isset( $results['results'] ) ) {
		return array(
			'success'          => true,
			'query'            => $query,
			'total_results'    => 0,
			'results_returned' => 0,
			'message'          => 'No results found for "' . $query . '"',
			'results'          => array(),
		);
	}

	// Format results for AI consumption
	$formatted_results = array();
	foreach ( $results['results'] as $result ) {
		$formatted_results[] = array(
			'title'     => $result['post_title'],
			'excerpt'   => $result['post_excerpt'],
			'url'       => $result['permalink'],
			'post_type' => $result['post_type'],
			'date'      => $result['post_date'],
			'site_name' => $result['site_name'],
			'site_url'  => $result['site_url'],
			'author'    => isset( $result['post_author'] ) ? get_the_author_meta( 'display_name', $result['post_author'] ) : '',
		);
	}

	return array(
		'success'          => true,
		'query'            => $query,
		'total_results'    => $results['total'],
		'results_returned' => count( $formatted_results ),
		'sites_searched'   => ! empty( $sites ) ? implode( ', ', $sites ) : 'all network sites',
		'results'          => $formatted_results,
	);
}
