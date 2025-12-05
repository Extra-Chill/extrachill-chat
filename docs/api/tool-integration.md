# Tool Integration API

Tool discovery, registration, and execution system for AI function calling.

## Tool Registry

### EC_Chat_DM_Tools Class

**File**: `inc/tools/dm-tools.php`

**Purpose**: Manages tool discovery and execution from DM-Multisite plugin

**Instantiation**:
```php
global $ec_chat_tools;
$ec_chat_tools = new EC_Chat_DM_Tools();
```

**Initialization**: On plugin load via `plugins_loaded` hook

## Tool Discovery

### Discovery Filter

**Filter**: `dm_ai_tools_multisite`

**Purpose**: Discover tools registered by dm-multisite plugin

**Usage**:
```php
private function discover_tools() {
    $this->tools = apply_filters('dm_ai_tools_multisite', array());
}
```

**Return Format**:
```php
array(
    'google_search' => array(
        'name'        => 'google_search',
        'description' => 'Search Google for current information',
        'parameters'  => array(...),
        'class'       => 'DMMultisite\Tools\GoogleSearch',
        'method'      => 'execute'
    ),
    'local_search' => array(
        'name'        => 'local_search',
        'description' => 'Search all Extra Chill network sites',
        'parameters'  => array(...),
        'class'       => 'DMMultisite\Tools\LocalSearch',
        'method'      => 'execute'
    ),
    // ... more tools
)
```

**Conditional**: Only returns tools if dm-multisite plugin network-activated

## Tool Definition Structure

### Required Fields

**name** (string)
- Tool identifier used by AI
- Must be unique
- Example: `'google_search'`

**description** (string)
- Human-readable description for AI
- Explains when to use tool
- Example: `'Search Google for current information'`

**parameters** (object)
- JSON Schema object defining parameters
- Includes type, properties, required fields
- Used by AI to construct tool calls

**class** (string)
- Fully qualified class name
- Example: `'DMMultisite\\Tools\\GoogleSearch'`

**method** (string)
- Static method name to call
- Example: `'execute'`

### Parameter Schema Format

Example for google_search:

```php
'parameters' => array(
    'type'       => 'object',
    'properties' => array(
        'query' => array(
            'type'        => 'string',
            'description' => 'Search query terms'
        )
    ),
    'required' => array('query')
)
```

Example for wordpress_post_reader:

```php
'parameters' => array(
    'type'       => 'object',
    'properties' => array(
        'post_id' => array(
            'type'        => 'integer',
            'description' => 'WordPress post ID'
        ),
        'blog_id' => array(
            'type'        => 'integer',
            'description' => 'Site ID in multisite network'
        )
    ),
    'required' => array('post_id', 'blog_id')
)
```

## Public Methods

### get_tools()

Returns all discovered tools.

**Signature**:
```php
public function get_tools()
```

**Return**:
```php
array  // Associative array of tool definitions
```

**Usage**:
```php
global $ec_chat_tools;
$all_tools = $ec_chat_tools->get_tools();
```

### has_tool()

Check if specific tool exists.

**Signature**:
```php
public function has_tool(string $tool_id)
```

**Parameters**:
- `$tool_id` - Tool identifier (e.g., 'google_search')

**Return**:
```php
bool  // True if tool exists
```

**Usage**:
```php
if ($ec_chat_tools->has_tool('google_search')) {
    // Tool available
}
```

### get_tool()

Retrieve specific tool definition.

**Signature**:
```php
public function get_tool(string $tool_id)
```

**Parameters**:
- `$tool_id` - Tool identifier

**Return**:
```php
array|null  // Tool definition array or null if not found
```

**Usage**:
```php
$tool_def = $ec_chat_tools->get_tool('local_search');
if ($tool_def) {
    echo $tool_def['description'];
}
```

### call_tool()

Execute tool with parameters.

**Signature**:
```php
public function call_tool(string $tool_id, array $parameters)
```

**Parameters**:
- `$tool_id` - Tool identifier
- `$parameters` - Associative array of parameters

**Return**:
```php
array|WP_Error  // Tool result or error object
```

**Usage**:
```php
$result = $ec_chat_tools->call_tool('google_search', array(
    'query' => 'electronic music festivals 2025'
));

if (is_wp_error($result)) {
    error_log($result->get_error_message());
} else {
    // Process result
}
```

**Error Codes**:
- `tool_not_found` - Tool doesn't exist in registry
- `tool_invalid` - Tool missing class or method
- `tool_execution_failed` - Exception during execution

### get_tools_for_ai()

Format tools for AI request.

**Signature**:
```php
public function get_tools_for_ai()
```

**Return**:
```php
array  // Array of formatted tool definitions
```

**Format**:
```php
array(
    array(
        'name'        => 'google_search',
        'description' => 'Search Google for current information',
        'parameters'  => array(...)  // JSON Schema
    ),
    array(
        'name'        => 'local_search',
        'description' => 'Search all Extra Chill network sites',
        'parameters'  => array(...)
    )
)
```

**Usage**:
```php
$tools = $ec_chat_tools->get_tools_for_ai();
$response = apply_filters('ai_request', $request, 'openai', null, $tools);
```

## Tool Execution Flow

### Complete Execution Path

1. **AI Returns Tool Call**:
```php
$response['data']['tool_calls'] = array(
    array(
        'id'         => 'call_abc123',
        'name'       => 'local_search',
        'parameters' => array('query' => 'techno')
    )
);
```

2. **Conversation Loop Checks Tool Exists**:
```php
if (!$ec_chat_tools->has_tool($tool_id)) {
    return new WP_Error('tool_not_found', "Tool \"{$tool_id}\" not found");
}
```

3. **Execute Tool**:
```php
$result = $ec_chat_tools->call_tool($tool_id, $parameters);
```

4. **Inside call_tool()**:
```php
// Validate tool exists
if (!$this->has_tool($tool_id)) {
    return new WP_Error('tool_not_found', ...);
}

// Get tool definition
$tool = $this->tools[$tool_id];

// Validate definition
if (empty($tool['class']) || empty($tool['method'])) {
    return new WP_Error('tool_invalid', ...);
}

// Execute via call_user_func
try {
    return call_user_func(
        array($tool['class'], $tool['method']),
        $parameters,
        $tool
    );
} catch (Exception $e) {
    return new WP_Error('tool_execution_failed', $e->getMessage());
}
```

5. **Add Result to Messages**:
```php
$messages[] = array(
    'role'         => 'tool',
    'tool_call_id' => $tool_call['id'],
    'content'      => wp_json_encode($result)
);
```

### Error Propagation

Tool errors propagate up through call stack:

```
Tool Method
  ↓ (returns WP_Error or throws Exception)
call_tool()
  ↓ (returns WP_Error)
ec_chat_conversation_loop()
  ↓ (returns WP_Error)
ec_chat_send_ai_message()
  ↓ (returns WP_Error)
ec_chat_handle_message()
  ↓ (sends error JSON to client)
User sees error message
```

## Available Tools

### google_search

**Purpose**: Search Google for external information

**Class**: `DMMultisite\Tools\GoogleSearch`

**Parameters**:
- `query` (string, required) - Search terms

**Return Format**:
```php
array(
    'results' => array(
        array(
            'title'   => 'Result title',
            'url'     => 'https://example.com',
            'snippet' => 'Result description...'
        ),
        // ... more results
    )
)
```

### webfetch

**Purpose**: Fetch and extract content from web pages

**Class**: `DMMultisite\Tools\WebFetch`

**Parameters**:
- `url` (string, required) - Full URL to fetch

**Return Format**:
```php
array(
    'url'     => 'https://example.com',
    'title'   => 'Page title',
    'content' => 'Extracted text content...'
)
```

### local_search

**Purpose**: Search all Extra Chill network sites

**Class**: `DMMultisite\Tools\LocalSearch`

**Parameters**:
- `query` (string, required) - Search terms
- `post_type` (string, optional) - Filter to specific post type

**Return Format**:
```php
array(
    'results' => array(
        array(
            'post_id'    => 12345,
            'blog_id'    => 1,
            'site_name'  => 'Extra Chill',
            'site_url'   => 'https://extrachill.com',
            'title'      => 'Post title',
            'excerpt'    => 'Post excerpt...',
            'url'        => 'https://extrachill.com/post-slug',
            'post_type'  => 'post'
        ),
        // ... more results
    )
)
```

### wordpress_post_reader

**Purpose**: Read full post content from any network site

**Class**: `DMMultisite\Tools\WordPressPostReader`

**Parameters**:
- `post_id` (integer, required) - WordPress post ID
- `blog_id` (integer, required) - Site ID in network

**Return Format**:
```php
array(
    'post_id'   => 12345,
    'blog_id'   => 1,
    'title'     => 'Post title',
    'content'   => 'Full post content...',
    'excerpt'   => 'Post excerpt...',
    'author'    => 'Author name',
    'date'      => '2025-10-05',
    'url'       => 'https://extrachill.com/post-slug',
    'post_type' => 'post'
)
```

## Adding Custom Tools

### Registration Pattern

Not currently implemented but structure supports it:

```php
// In future: ec-chat specific tools
add_filter('ec_chat_tools', function($tools) {
    $tools['custom_tool'] = array(
        'name'        => 'custom_tool',
        'description' => 'Description of custom tool',
        'parameters'  => array(
            'type'       => 'object',
            'properties' => array(
                'param1' => array(
                    'type'        => 'string',
                    'description' => 'Parameter description'
                )
            ),
            'required' => array('param1')
        ),
        'class'       => 'MyCustomTool',
        'method'      => 'execute'
    );
    return $tools;
});
```

### Tool Class Pattern

```php
class MyCustomTool {
    public static function execute($parameters, $tool_definition) {
        // Validate parameters
        if (empty($parameters['param1'])) {
            return new WP_Error('invalid_params', 'param1 required');
        }

        try {
            // Execute tool logic
            $result = self::do_work($parameters['param1']);

            return array(
                'success' => true,
                'data'    => $result
            );
        } catch (Exception $e) {
            return new WP_Error('execution_failed', $e->getMessage());
        }
    }

    private static function do_work($param) {
        // Tool implementation
        return 'result';
    }
}
```

## Tool Call Format

### From AI to Plugin

AI returns tool calls in response:

```php
array(
    'id'         => 'call_abc123',  // Unique call ID from AI
    'name'       => 'local_search',  // Tool identifier
    'parameters' => array(           // Tool parameters
        'query' => 'electronic music'
    )
)
```

### To AI After Execution

Result sent back to AI:

```php
array(
    'role'         => 'tool',
    'tool_call_id' => 'call_abc123',  // Matches AI's ID
    'content'      => '{"results":[...]}'  // JSON string
)
```

## Performance Considerations

### Tool Discovery

**Timing**: Once per request during class initialization

**Cost**: Single filter execution

**Caching**: No caching - rediscovers each request

**Optimization**: Could cache tool definitions in future

### Tool Execution

**Blocking**: All tool calls block conversation loop

**Timeout**: Depends on individual tool implementation

**Chaining**: Multiple tools can execute in sequence

**Example Timing**:
- google_search: 1-3 seconds
- local_search: 0.5-2 seconds
- webfetch: 2-5 seconds
- wordpress_post_reader: 0.2-1 second

## Error Handling

### Discovery Errors

If dm-multisite not active:
```php
$this->tools = array();  // Empty array, no tools available
```

AI can still respond but without tool functionality.

### Execution Errors

**WP_Error Return**: Tool execution errors return WP_Error object

**Exception Handling**: Try-catch wraps execution

**Error Logging**: Logged to PHP error log

**User Impact**: User receives error message instead of AI response

### Validation

**Tool Exists**: Checked before execution

**Class/Method Exists**: Validated in call_tool()

**Parameter Validation**: Tool's responsibility to validate parameters

## Global Access

### Global Variable Pattern

```php
global $ec_chat_tools;
```

**Scope**: Available throughout plugin after initialization

**Usage Locations**:
- Conversation loop (tool execution)
- AI integration (getting tools for AI)
- Debugging and testing

**Type**: `EC_Chat_DM_Tools` instance

### Alternative Access

Could implement getter function:

```php
function ec_chat_get_tools_registry() {
    global $ec_chat_tools;
    return $ec_chat_tools;
}
```

Not currently implemented - direct global access used.
