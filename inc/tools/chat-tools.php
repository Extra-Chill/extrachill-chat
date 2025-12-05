<?php
/**
 * Extensible Chat Tools Registry
 *
 * Core tool discovery and management system that merges tools from multiple sources.
 * Provides unified interface for all chat tools regardless of source.
 *
 * Tool sources:
 * - ec_chat_tools: Chat-specific tools (artist platform, content creation, etc.)
 * - dm_chubes_ai_tools_multisite: External DM-Multisite plugin tools
 * - Future: ec_chat_admin_tools (admin-only tools)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Unified tool registry with multi-source discovery
 */
class EC_Chat_Tools {

	/**
	 * @var array All discovered tools from all sources
	 */
	private $tools = array();

	public function __construct() {
		$this->discover_tools();
	}

	/**
	 * Discover tools from all registered filter sources
	 */
	private function discover_tools() {
		// Discover from chat-specific tools filter
		$chat_tools = apply_filters( 'ec_chat_tools', array() );

		// Discover from external DM-Multisite plugin
		$dm_tools = apply_filters( 'dm_chubes_ai_tools_multisite', array() );

		// Exclude DM's local_search - we use ExtraChill's superior search_extrachill instead
		if ( isset( $dm_tools['local_search'] ) ) {
			unset( $dm_tools['local_search'] );
		}

		// Merge all sources
		$this->tools = array_merge( $chat_tools, $dm_tools );

		// Future expansion point:
		// $admin_tools = apply_filters('ec_chat_admin_tools', array());
		// $this->tools = array_merge($this->tools, $admin_tools);
	}

	/**
	 * Get all discovered tools
	 *
	 * @return array All tools from all sources
	 */
	public function get_tools() {
		return $this->tools;
	}

	/**
	 * Check if tool exists
	 *
	 * @param string $tool_id Tool identifier
	 * @return bool True if tool exists
	 */
	public function has_tool( $tool_id ) {
		return isset( $this->tools[ $tool_id ] );
	}

	/**
	 * Get specific tool definition
	 *
	 * @param string $tool_id Tool identifier
	 * @return array|null Tool definition or null if not found
	 */
	public function get_tool( $tool_id ) {
		return $this->tools[ $tool_id ] ?? null;
	}

	/**
	 * Execute tool with parameters
	 *
	 * @param string $tool_id    Tool identifier
	 * @param array  $parameters Tool parameters
	 * @return array|WP_Error Tool result or error
	 */
	public function call_tool( $tool_id, array $parameters ) {
		if ( ! $this->has_tool( $tool_id ) ) {
			return new WP_Error(
				'tool_not_found',
				sprintf( 'Tool %s not available', $tool_id )
			);
		}

		$tool = $this->tools[ $tool_id ];

		// Handle DM-Multisite tool format (class/method)
		if ( ! empty( $tool['class'] ) && ! empty( $tool['method'] ) ) {
			try {
				return call_user_func(
					array( $tool['class'], $tool['method'] ),
					$parameters,
					$tool
				);
			} catch ( Exception $e ) {
				return new WP_Error(
					'tool_execution_failed',
					sprintf( 'Tool %s execution failed: %s', $tool_id, $e->getMessage() )
				);
			}
		}

		// Handle chat tool format (callback function)
		if ( ! empty( $tool['callback'] ) && is_callable( $tool['callback'] ) ) {
			try {
				return call_user_func( $tool['callback'], $parameters, $tool );
			} catch ( Exception $e ) {
				return new WP_Error(
					'tool_execution_failed',
					sprintf( 'Tool %s execution failed: %s', $tool_id, $e->getMessage() )
				);
			}
		}

		return new WP_Error(
			'tool_invalid',
			sprintf( 'Tool %s missing valid callback or class/method', $tool_id )
		);
	}

	/**
	 * Format tools for AI request
	 *
	 * @return array Array of tool definitions in AI format
	 */
	public function get_tools_for_ai() {
		$formatted_tools = array();

		foreach ( $this->tools as $tool_id => $tool_def ) {
			// Check if tool has function definition (required for AI)
			if ( isset( $tool_def['function'] ) ) {
				$formatted_tools[] = $tool_def['function'];
			} else {
				// Build from available metadata
				$formatted_tools[] = array(
					'name'        => $tool_id,
					'description' => $tool_def['description'] ?? '',
					'parameters'  => $tool_def['parameters'] ?? array(),
				);
			}
		}

		return $formatted_tools;
	}
}

// Initialize global tool registry
global $ec_chat_tools;
$ec_chat_tools = new EC_Chat_Tools();
