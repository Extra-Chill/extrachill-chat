<?php
/**
 * Discovers and exposes Data Machine tools via dm_ai_tools_multisite filter.
 * Foundation for adding ExtraChill-Chat specific tools in the future.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tool registry managing discovery, validation, and execution for DM-Multisite tools.
 */
class EC_Chat_DM_Tools {

	/**
	 * @var array Available tools from DM-Multisite
	 */
	private $tools = array();

	public function __construct() {
		$this->discover_tools();
	}

	private function discover_tools() {
		$this->tools = apply_filters( 'dm_ai_tools_multisite', array() );
	}

	/**
	 * @return array Array of tool definitions
	 */
	public function get_tools() {
		return $this->tools;
	}

	/**
	 * @param string $tool_id Tool identifier (e.g., 'google_search')
	 * @return bool True if tool exists
	 */
	public function has_tool( $tool_id ) {
		return isset( $this->tools[ $tool_id ] );
	}

	/**
	 * @param string $tool_id Tool identifier
	 * @return array|null Tool definition or null if not found
	 */
	public function get_tool( $tool_id ) {
		return $this->tools[ $tool_id ] ?? null;
	}

	/**
	 * @param string $tool_id    Tool identifier
	 * @param array  $parameters Tool parameters
	 * @return array|WP_Error Tool execution result or WP_Error
	 */
	public function call_tool( $tool_id, array $parameters ) {
		if ( ! $this->has_tool( $tool_id ) ) {
			return new WP_Error(
				'tool_not_found',
				sprintf( 'Tool %s not available', $tool_id )
			);
		}

		$tool = $this->tools[ $tool_id ];

		if ( empty( $tool['class'] ) || empty( $tool['method'] ) ) {
			return new WP_Error(
				'tool_invalid',
				sprintf( 'Tool %s missing class or method', $tool_id )
			);
		}

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

	/**
	 * @return array Array of formatted tool definitions
	 */
	public function get_tools_for_ai() {
		$formatted_tools = array();

		foreach ( $this->tools as $tool_id => $tool_def ) {
			$formatted_tools[] = array(
				'name'        => $tool_id,
				'description' => $tool_def['description'] ?? '',
				'parameters'  => $tool_def['parameters'] ?? array()
			);
		}

		return $formatted_tools;
	}
}

// Initialize global tools registry
global $ec_chat_tools;
$ec_chat_tools = new EC_Chat_DM_Tools();
