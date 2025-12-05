# Conversation Loop API

Multi-turn conversation loop enabling AI tool chaining and iterative problem solving.

## Function Reference

### ec_chat_conversation_loop()

Execute conversation loop with multi-turn tool calling support.

**File**: `inc/core/conversation-loop.php`

**Purpose**: Enables AI to chain multiple tool calls until reaching final text response

**Signature**:
```php
function ec_chat_conversation_loop(
    array $messages,
    array $tools = array(),
    int $max_iterations = 10
)
```

**Parameters**:

- **$messages** (array, required)
  - Initial conversation messages
  - Format: Array of message objects with `role` and `content`
  - Must not be empty
  - Includes conversation history and new user message

- **$tools** (array, optional)
  - Available tools for AI to use
  - Format: Array of tool definitions from `ec_chat_get_available_tools()`
  - Default: Empty array (no tools available)

- **$max_iterations** (int, optional)
  - Maximum loop iterations before error
  - Default: 10
  - Prevents infinite tool calling loops

**Return**:

Success returns array with three keys:
```php
array(
    'content'    => string,  // Final AI response text (HTML)
    'tool_calls' => array,   // All tool calls made during conversation
    'messages'   => array    // Complete message history including tool calls
)
```

Error returns `WP_Error` object with error code and message

**Error Codes**:
- `invalid_messages` - Messages array empty or invalid
- `ai_request_failed` - AI API request failed
- `invalid_response` - AI response missing required content
- `tool_not_found` - AI requested tool that doesn't exist
- `max_iterations_reached` - Exceeded max iteration limit

## Loop Execution Flow

### Iteration Process

Each iteration:

1. **Send Request**
   - Messages array sent to AI via `ai_request` filter
   - Model: `gpt-5-mini` (hardcoded line 39)
   - Provider: `openai` (hardcoded line 42)
   - Tools: All available tools included

2. **Check Response Type**
   - **Has text content**: Loop complete, return response
   - **Has tool calls**: Continue to tool execution

3. **Execute Tool Calls**
   - Add assistant message with tool calls to messages array
   - Execute each tool via `$ec_chat_tools->call_tool()`
   - Add tool result messages to messages array
   - Increment iteration counter

4. **Repeat or Terminate**
   - If under max iterations: Return to step 1
   - If at max iterations: Return error

### Example Flow

**User**: "Find and summarize the latest Bonobo interview"

**Iteration 1**:
- AI returns tool call: `local_search` with query "Bonobo interview"
- Execute search, get 3 results
- Add search results to messages

**Iteration 2**:
- AI returns tool call: `wordpress_post_reader` for post ID 12345
- Execute reader, get full post content
- Add post content to messages

**Iteration 3**:
- AI returns text response: Summary of interview
- Loop complete, return response

**Total**: 3 iterations, 2 tool calls

## Message Structure

### User Message Format

```php
array(
    'role'    => 'user',
    'content' => 'What are the latest posts about techno?'
)
```

### Assistant Message with Tool Calls

```php
array(
    'role'       => 'assistant',
    'content'    => null,  // Null when making tool calls
    'tool_calls' => array(
        array(
            'id'         => 'call_abc123',
            'name'       => 'local_search',
            'parameters' => array(
                'query' => 'techno'
            )
        )
    )
)
```

### Tool Result Message

```php
array(
    'role'         => 'tool',
    'tool_call_id' => 'call_abc123',
    'content'      => '{"results": [...]}' // JSON string
)
```

### Final Assistant Message

```php
array(
    'role'    => 'assistant',
    'content' => '<p>I found 5 recent posts about techno...</p>'
)
```

## Tool Execution

### Tool Registry

**Global Variable**: `$ec_chat_tools`

**Type**: `EC_Chat_DM_Tools` instance

**Initialization**: Loaded in `inc/tools/dm-tools.php`

**Purpose**: Manages tool discovery and execution

### Tool Call Process

**Check Tool Exists**:
```php
if (!$ec_chat_tools->has_tool($tool_id)) {
    return new WP_Error('tool_not_found', "Tool \"{$tool_id}\" not found");
}
```

**Execute Tool**:
```php
$result = $ec_chat_tools->call_tool($tool_id, $parameters);
```

**Handle Result**:
```php
if (is_wp_error($result)) {
    return $result;  // Propagate error up
}

// Add result to messages
$messages[] = array(
    'role'         => 'tool',
    'tool_call_id' => $tool_call['id'] ?? $tool_id,
    'content'      => wp_json_encode($result)
);
```

## AI Request Integration

### Filter Hook

**Filter**: `ai_request`

**Priority**: 99 (actual execution - after all directive injections)

**Handler**: ExtraChill AI Client plugin

**Parameters**:
```php
apply_filters(
    'ai_request',
    $request_data,    // Request configuration
    'openai',         // Provider (hardcoded)
    null,             // Streaming callback (not used)
    $tools            // Available tools
);
```

### Request Data Format

```php
$request_data = array(
    'messages' => $messages,  // Complete conversation
    'model'    => 'gpt-5-mini' // Hardcoded model
);
```

### Response Format

**Success**:
```php
array(
    'success' => true,
    'data'    => array(
        'content'    => '<p>Response text</p>',  // If text response
        'tool_calls' => array(...)                // If tool calls
    )
)
```

**Error**:
```php
array(
    'success' => false,
    'error'   => 'Error message description'
)
```

## Iteration Limits

### Max Iterations Purpose

**Prevent Infinite Loops**: AI might repeatedly call tools without reaching conclusion

**Default**: 10 iterations

**Typical Usage**: Most conversations complete in 1-4 iterations

**Error Handling**: Returns descriptive error if limit reached

### Reaching Limit

**Error Code**: `max_iterations_reached`

**Error Message**:
```
Conversation loop exceeded maximum iterations (10).
AI may be stuck in tool calling loop.
```

**User Impact**: Receives error message instead of AI response

**Logging**: Logged to PHP error log for debugging

**Resolution**: Indicates AI directive or tool issue requiring investigation

## Performance Considerations

### Iteration Cost

**Per Iteration**:
- 1 AI API request (2-5 seconds)
- 0-N tool executions (varies by tool)
- Message array growth
- Database queries for tools

**Total Time**: Multiply by actual iteration count

**Example**: 3 iterations × 3 seconds = 9 second response time

### Message Array Growth

**Growth Pattern**: Each iteration adds 2-4 messages

**Impact**: API token usage increases with each iteration

**Optimization**: 20-message history window limits context size

## Error Handling

### Tool Execution Errors

**Detection**: `is_wp_error($result)` check after each tool call

**Propagation**: Tool errors immediately return from loop

**User Impact**: User receives error message

**Example**:
```php
$result = $ec_chat_tools->call_tool($tool_id, $parameters);
if (is_wp_error($result)) {
    return $result;  // Stops loop, returns error
}
```

### AI Request Errors

**Detection**: Check `$response['success']` flag

**Error Extraction**: `$response['error']` contains message

**Default Message**: "AI request failed" if no specific error

**Code**: `ai_request_failed`

### Invalid Response Errors

**Detection**: Missing required `content` field in final response

**Code**: `invalid_response`

**Message**: "AI response missing content"

**Cause**: Usually indicates AI API issue

## Tool Call Metadata

### Collection Process

**Array**: `$all_tool_calls` accumulates all tool usage

**Format**:
```php
array(
    array(
        'tool'       => 'local_search',
        'parameters' => array('query' => 'techno')
    ),
    array(
        'tool'       => 'wordpress_post_reader',
        'parameters' => array('post_id' => 12345, 'blog_id' => 1)
    )
)
```

**Purpose**: UI display showing which tools AI used

**Return**: Included in success return array as `tool_calls` key

### UI Integration

**JavaScript**: Displays tool calls in gray info boxes

**Format**: Friendly names and parameter summaries

**Position**: Between user message and AI response

**Example Display**:
```
Searched Extra Chill network: techno
Read post content: post_id 12345
```

## Integration Points

### Called By

**Function**: `ec_chat_send_ai_message()`

**File**: `inc/core/ai-integration.php`

**Context**: AJAX message handler processes user input

### Calls To

**AI Client**: Via `ai_request` filter (extrachill-ai-client plugin)

**Tool Registry**: `$ec_chat_tools->call_tool()` for tool execution

**Error Logging**: `error_log()` for failures

### Returns To

**Handler**: `ec_chat_handle_message()` in `inc/core/ajax-handler.php`

**Processing**: Result saved to conversation history, returned to user
