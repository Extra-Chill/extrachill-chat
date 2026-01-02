# Tool Integration API

Tool discovery, registration, and execution system for AI function calling in **extrachill-chat**.

## Tool registry

### `EC_Chat_Tool_Registry`

**File**: `inc/tools/chat-tools.php`

**Purpose**: Discovers chat tools and provides a unified execution API used by the conversation loop.

**Global instance**:

```php
global $ec_chat_tools;
$ec_chat_tools = new EC_Chat_Tool_Registry();
```

## Tool discovery

### Discovery filter: `ec_chat_tools`

Tools are registered via the `ec_chat_tools` filter.

```php
add_filter( 'ec_chat_tools', function ( $tools ) {
	$tools['my_tool'] = array(
		'name'        => 'my_tool',
		'description' => 'Describe what the tool does',
		'parameters'  => array(
			'type'       => 'object',
			'properties' => array(
				'query' => array(
					'type'        => 'string',
					'description' => 'Search query',
				),
			),
			'required'   => array( 'query' ),
		),
		'callback'    => 'my_tool_callback',
	);

	return $tools;
} );

function my_tool_callback( $parameters, $tool_def ) {
	return array( 'success' => true );
}
```

### Tool definition shape

Each tool is an array with:

- `callback` (required): callable executed as `call_user_func( $callback, $parameters, $tool_def )`
- `function` (optional): tool definition in the exact format passed to the AI provider. If omitted, the registry builds a tool definition from `name` / `description` / `parameters`.

## AI-facing tool formatting

### `get_tools_for_ai()`

`EC_Chat_Tool_Registry::get_tools_for_ai()` returns the list of tool definitions passed into the AI request.

- If a tool includes `function`, that value is used as-is.
- Otherwise the registry builds:

```php
array(
	'name'        => $tool_id,
	'description' => $tool_def['description'] ?? '',
	'parameters'  => $tool_def['parameters'] ?? array(),
)
```

## Execution

### `call_tool( $tool_id, $parameters )`

- Returns tool result (array) or `WP_Error`.
- Error codes:
  - `tool_not_found`
  - `tool_invalid`
  - `tool_execution_failed`

## Available tools (current)

These tools are registered by this plugin:

- `search_extrachill` (file: `inc/tools/search-extrachill.php`)
- `add_link_to_page` (file: `inc/tools/artist-platform/add-link-to-page.php`)

## Conversation loop integration

The conversation loop executes tool calls returned by the AI response via the global registry:

- file: `inc/core/conversation-loop.php`
- uses: `$ec_chat_tools->has_tool()` and `$ec_chat_tools->call_tool()`
