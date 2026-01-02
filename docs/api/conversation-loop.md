# Conversation Loop API

Multi-turn conversation loop enabling AI tool chaining and iterative problem solving.

## `ec_chat_conversation_loop()`

**File**: `inc/core/conversation-loop.php`

Executes a loop that:
1. Sends messages + tools to the AI request filter.
2. If the response contains tool calls, executes them via the tool registry.
3. Feeds tool results back into the message list.
4. Repeats until the AI returns final `content`.

### Signature

```php
function ec_chat_conversation_loop( $messages, $tools = array(), $max_iterations = 10 )
```

### Return

On success:

```php
array(
	'content'    => string, // HTML
	'tool_calls' => array,
	'messages'   => array,
)
```

On error: `WP_Error`.

### Error codes

- `invalid_messages`
- `chubes_ai_request_failed`
- `invalid_response`
- `tool_not_found`
- `max_iterations_reached`

## AI request integration

The loop calls:

```php
$response = apply_filters( 'chubes_ai_request', $request_data, 'openai', null, $tools );
```

`$request_data` includes:

- `messages`
- `model` (hardcoded to `gpt-5-mini` in this plugin)

## Tool execution

The loop uses the global tool registry created in `inc/tools/chat-tools.php`:

- global: `$ec_chat_tools`
- class: `EC_Chat_Tool_Registry`

Tool calls returned by the AI are executed via:

- `$ec_chat_tools->has_tool( $tool_id )`
- `$ec_chat_tools->call_tool( $tool_id, $parameters )`

Tool results are appended to the conversation as messages with:

- `role` = `tool`
- `tool_call_id` = AI call id (or tool id)
- `content` = `wp_json_encode( $result )`
