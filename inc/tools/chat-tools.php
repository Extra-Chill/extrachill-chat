<?php
/**
 * ExtraChill Chat Tool Registry
 *
 * Core tool discovery and management system for ExtraChill platform tools.
 * Provides unified interface for all chat tools.
 *
 * Tool sources:
 * - ec_chat_tools: Chat-specific tools (artist platform, content creation, etc.)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tool registry for ExtraChill platform tools
 */
class EC_Chat_Tool_Registry {

	/**
	 * @var array All discovered tools
	 */
	private $tools = array();

	public function __construct() {
		$this->discover_tools();
	}

	/**
	 * Discover tools from registered filter sources
	 */
	private function discover_tools() {
		$this->tools = apply_filters( 'ec_chat_tools', array() );
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
			sprintf( 'Tool %s missing valid callback', $tool_id )
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
$ec_chat_tools = new EC_Chat_Tool_Registry();
