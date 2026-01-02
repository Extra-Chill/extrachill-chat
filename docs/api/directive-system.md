# AI Directive System

Directive classes inject system messages into chat AI requests.

## Filter hook

Directives attach to the `chubes_ai_request` filter.

This filter is called from the conversation loop:

- `inc/core/conversation-loop.php`

## Priority order

Directives apply in priority order (lowest to highest):

1. **Priority 10**: `ChatCoreDirective`
2. **Priority 20**: `ChatSystemPromptDirective`
3. **Priority 30**: `ChatUserContextDirective`
4. **Priority 40**: `MultisiteSiteContextWrapper` (conditional)

After directives run, a downstream handler attached to `chubes_ai_request` performs the actual provider request.

## Directive classes

### `ChatCoreDirective` (priority 10)

**File**: `inc/directives/ChatCoreDirective.php`

Establishes:
- assistant identity / platform context
- HTML response requirement
- tool usage behavior guidance

### `ChatSystemPromptDirective` (priority 20)

**File**: `inc/directives/ChatSystemPromptDirective.php`

Injects the optional site setting:

- `get_option( 'extrachill_chat_system_prompt', '' )`

### `ChatUserContextDirective` (priority 30)

**File**: `inc/directives/ChatUserContextDirective.php`

Adds current user context (capabilities depend on which network plugins are active).

### `MultisiteSiteContextWrapper` (priority 40)

**File**: `inc/directives/MultisiteSiteContextWrapper.php`

Conditionally registers a directive provided by another plugin:

```php
if ( class_exists( 'DMMultisite\\MultisiteSiteContextDirective' ) ) {
	add_filter( 'chubes_ai_request', array( 'DMMultisite\\MultisiteSiteContextDirective', 'inject' ), 40, 5 );
}
```

## Signature

Directives use the 5-arg signature (they primarily modify and return the request array):

```php
public static function inject(
	$request,
	$provider_name = null,
	$streaming_callback = null,
	$tools = null,
	$conversation_data = null
)
```
