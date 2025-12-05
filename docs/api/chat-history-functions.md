# Chat History Functions

WordPress functions for managing conversation persistence via custom post type.

## Custom Post Type

### Registration

**Function**: `ec_chat_register_post_type()`

**Hook**: `init` action

**Post Type**: `ec_chat`

**Configuration**:
```php
register_post_type('ec_chat', array(
    'public'              => false,
    'show_ui'             => true,
    'show_in_menu'        => true,
    'supports'            => array('title', 'author'),
    'capability_type'     => 'post',
    'hierarchical'        => false,
    'menu_position'       => 25,
    'menu_icon'           => 'dashicons-format-chat',
    'show_in_rest'        => false,
    'publicly_queryable'  => false,
    'exclude_from_search' => true,
    'show_in_nav_menus'   => false,
    'show_in_admin_bar'   => false,
    'can_export'          => true
));
```

**Admin Visibility**: Shows in WordPress admin Posts menu

**Frontend**: Not publicly queryable or searchable

## Core Functions

### ec_chat_get_or_create_chat()

Get existing chat post or create new one for user.

**File**: `inc/core/chat-history.php`

**Signature**:
```php
function ec_chat_get_or_create_chat(int $user_id)
```

**Parameters**:
- `$user_id` (int, required) - WordPress user ID

**Return**:
- `int` - Chat post ID on success
- `WP_Error` - Error object on failure

**Error Codes**:
- `invalid_user` - User ID invalid or user not found

**Behavior**:

1. **Validate User ID**:
```php
if (!$user_id) {
    return new WP_Error('invalid_user', 'Invalid user ID');
}
```

2. **Search Existing Chat**:
```php
$existing_chat = get_posts(array(
    'post_type'   => 'ec_chat',
    'post_status' => 'publish',
    'author'      => $user_id,
    'numberposts' => 1,
    'orderby'     => 'date',
    'order'       => 'DESC',
    'fields'      => 'ids'
));
```

3. **Return Existing or Create New**:
```php
if (!empty($existing_chat)) {
    return $existing_chat[0];  // Return existing
}

// Create new chat post
$chat_post_id = wp_insert_post(array(
    'post_title'  => sprintf('Chat - %s - %s', $user->display_name, current_time('Y-m-d H:i:s')),
    'post_type'   => 'ec_chat',
    'post_status' => 'publish',
    'post_author' => $user_id
));
```

4. **Initialize Post Meta**:
```php
update_post_meta($chat_post_id, '_ec_chat_messages', array());
update_post_meta($chat_post_id, '_ec_chat_last_updated', current_time('mysql'));
```

**Post Title Format**: `"Chat - {Display Name} - {Timestamp}"`

**Example**: `"Chat - John Smith - 2025-10-05 14:30:00"`

### ec_chat_get_messages()

Retrieve all messages from chat post.

**Signature**:
```php
function ec_chat_get_messages(int $chat_post_id)
```

**Parameters**:
- `$chat_post_id` (int, required) - Chat post ID

**Return**:
```php
array  // Array of message objects (empty array if none)
```

**Message Structure**:
```php
array(
    array(
        'role'      => 'user',
        'content'   => 'Hello',
        'timestamp' => '2025-10-05 14:30:00'
    ),
    array(
        'role'       => 'assistant',
        'content'    => '<p>Hi there!</p>',
        'timestamp'  => '2025-10-05 14:30:02',
        'tool_calls' => array(...)  // Optional
    ),
    array(
        'role'         => 'tool',
        'content'      => '{"results":[...]}',
        'timestamp'    => '2025-10-05 14:30:01',
        'tool_call_id' => 'call_abc123'  // Optional
    )
)
```

**Implementation**:
```php
if (!$chat_post_id) {
    return array();
}

$messages = get_post_meta($chat_post_id, '_ec_chat_messages', true);

if (!is_array($messages)) {
    return array();
}

return $messages;
```

**Safety**: Returns empty array if post ID invalid or meta not found

### ec_chat_add_message()

Add single message to chat history.

**Signature**:
```php
function ec_chat_add_message(
    int $chat_post_id,
    string $role,
    mixed $content,
    array $extra_data = array()
)
```

**Parameters**:

- `$chat_post_id` (int, required) - Chat post ID
- `$role` (string, required) - Message role: 'user', 'assistant', 'tool', 'system'
- `$content` (mixed, required) - Message content (string or null for assistant with tool_calls)
- `$extra_data` (array, optional) - Additional message data

**Extra Data Fields**:
- `tool_calls` - Array of tool calls made by assistant
- `tool_call_id` - ID linking tool result to tool call
- Any other custom fields

**Return**:
```php
bool  // True on success, false on failure
```

**Implementation**:
```php
// Build message object
$message = array(
    'role'      => $role,
    'content'   => $content,
    'timestamp' => current_time('mysql')
);

// Merge extra data
if (!empty($extra_data)) {
    $message = array_merge($message, $extra_data);
}

// Append to messages array
$messages[] = $message;

// Update post meta
$updated = update_post_meta($chat_post_id, '_ec_chat_messages', $messages);
update_post_meta($chat_post_id, '_ec_chat_last_updated', current_time('mysql'));

return $updated !== false;
```

**Timestamp**: Automatically added to each message

**Post Meta Update**: Updates `_ec_chat_last_updated` timestamp

### ec_chat_save_conversation()

Save complete conversation from conversation loop.

**Signature**:
```php
function ec_chat_save_conversation(int $chat_post_id, array $messages)
```

**Parameters**:
- `$chat_post_id` (int, required) - Chat post ID
- `$messages` (array, required) - Array of messages from conversation loop

**Return**:
```php
bool  // True on success, false on failure
```

**Purpose**: Save all messages from single conversation turn including:
- User message
- AI tool calls
- Tool results
- Final AI response

**Implementation**:
```php
foreach ($messages as $message) {
    if (!isset($message['role'])) {
        continue;  // Skip invalid messages
    }

    $role = $message['role'];
    $content = $message['content'] ?? null;

    // Extract tool call data
    $extra_data = array();
    if (isset($message['tool_calls'])) {
        $extra_data['tool_calls'] = $message['tool_calls'];
    }
    if (isset($message['tool_call_id'])) {
        $extra_data['tool_call_id'] = $message['tool_call_id'];
    }

    // Add message
    ec_chat_add_message($chat_post_id, $role, $content, $extra_data);
}
```

**Bulk Operation**: Calls `ec_chat_add_message()` for each message

**Validation**: Skips messages without `role` field

**Tool Data**: Preserves tool_calls and tool_call_id fields

### ec_chat_clear_history()

Clear all messages from chat post.

**Signature**:
```php
function ec_chat_clear_history(int $chat_post_id)
```

**Parameters**:
- `$chat_post_id` (int, required) - Chat post ID

**Return**:
```php
bool  // True on success, false on failure
```

**Implementation**:
```php
if (!$chat_post_id) {
    return false;
}

$updated = update_post_meta($chat_post_id, '_ec_chat_messages', array());
update_post_meta($chat_post_id, '_ec_chat_last_updated', current_time('mysql'));

return $updated !== false;
```

**Action**: Replaces message array with empty array

**Preserves**: Chat post remains, only messages cleared

**Timestamp**: Updates last updated timestamp

**Irreversible**: Cannot undo clear operation

## Post Meta Keys

### _ec_chat_messages

**Type**: Array (serialized in database)

**Structure**: Array of message objects

**Purpose**: Store complete conversation history

**Access**:
```php
$messages = get_post_meta($chat_post_id, '_ec_chat_messages', true);
```

**Update**:
```php
update_post_meta($chat_post_id, '_ec_chat_messages', $messages);
```

**Serialization**: WordPress automatically serializes/unserializes array

### _ec_chat_last_updated

**Type**: String (MySQL datetime)

**Format**: `Y-m-d H:i:s` (e.g., "2025-10-05 14:30:00")

**Purpose**: Track when conversation last modified

**Update Triggers**:
- New message added
- History cleared

**Access**:
```php
$last_updated = get_post_meta($chat_post_id, '_ec_chat_last_updated', true);
```

**Update**:
```php
update_post_meta($chat_post_id, '_ec_chat_last_updated', current_time('mysql'));
```

## Database Queries

### Optimized Queries

**Get Messages** (single query):
```php
$messages = get_post_meta($chat_post_id, '_ec_chat_messages', true);
```

**Find User's Chat** (single query):
```php
get_posts(array(
    'post_type'   => 'ec_chat',
    'author'      => $user_id,
    'numberposts' => 1,
    'fields'      => 'ids'  // Only return IDs, not full post objects
));
```

### Query Performance

**Object Cache**: WordPress object cache caches post meta

**Indexes**: Post author and post type indexed by WordPress

**Optimization**: `fields => 'ids'` reduces memory usage

## Usage Patterns

### Initial Page Load

```php
$user_id = get_current_user_id();
$chat_post_id = ec_chat_get_or_create_chat($user_id);

if (!is_wp_error($chat_post_id)) {
    $chat_history = ec_chat_get_messages($chat_post_id);
    // Pass to JavaScript for display
}
```

### Processing User Message

```php
// Get chat post
$chat_post_id = ec_chat_get_or_create_chat($user_id);

// Send to AI (loads history internally)
$ai_response = ec_chat_send_ai_message($user_message, $chat_post_id);

// Save complete conversation
if (!is_wp_error($ai_response) && !empty($ai_response['messages'])) {
    ec_chat_save_conversation($chat_post_id, $ai_response['messages']);
}
```

### Clearing History

```php
$chat_post_id = ec_chat_get_or_create_chat($user_id);

if (!is_wp_error($chat_post_id)) {
    $cleared = ec_chat_clear_history($chat_post_id);

    if ($cleared) {
        // Success
    }
}
```

## Error Handling

### Function Return Checks

**WP_Error Pattern**:
```php
$chat_post_id = ec_chat_get_or_create_chat($user_id);

if (is_wp_error($chat_post_id)) {
    error_log('Chat Error: ' . $chat_post_id->get_error_message());
    // Handle error
} else {
    // Use $chat_post_id
}
```

**Boolean Pattern**:
```php
$saved = ec_chat_save_conversation($chat_post_id, $messages);

if (!$saved) {
    error_log('Failed to save conversation');
}
```

### Validation

All functions validate inputs:

```php
if (!$chat_post_id || empty($role)) {
    return false;  // Or WP_Error
}
```

### Database Errors

WordPress `update_post_meta()` returns:
- `int` - Meta ID on success
- `bool` - True on update, false on failure

Functions check: `$updated !== false`

## Message Structure Standards

### Required Fields

Every message must have:
- `role` - Message role identifier
- `content` - Message content (or null)
- `timestamp` - When message added

### Optional Fields

Depending on role:
- `tool_calls` - For assistant making tool calls
- `tool_call_id` - For tool result messages

### Role Types

**user**: User messages
```php
array(
    'role'      => 'user',
    'content'   => 'User message text',
    'timestamp' => '2025-10-05 14:30:00'
)
```

**assistant**: AI responses (text or tool calls)
```php
// Text response
array(
    'role'      => 'assistant',
    'content'   => '<p>AI response HTML</p>',
    'timestamp' => '2025-10-05 14:30:02'
)

// Tool calls
array(
    'role'       => 'assistant',
    'content'    => null,
    'timestamp'  => '2025-10-05 14:30:01',
    'tool_calls' => array(...)
)
```

**tool**: Tool execution results
```php
array(
    'role'         => 'tool',
    'content'      => '{"results":[...]}',
    'timestamp'    => '2025-10-05 14:30:01',
    'tool_call_id' => 'call_abc123'
)
```

**system**: System directives (not stored in history)
```php
// Directives injected during AI request, not saved to history
```

## WordPress Integration

### Admin Interface

**Menu**: Posts → Chats

**List View**: Shows all chat posts with author and date

**Edit Screen**: Standard post editor

**Search**: Can search chat posts by title/author

**Bulk Actions**: Standard WordPress post bulk actions available

### Capabilities

**Create**: Users cannot create chat posts manually (auto-created)

**Edit**: Standard `edit_post` capability

**Delete**: Standard `delete_post` capability

**View**: Administrators can view all user chats

### Export

**WordPress Exporter**: Can export chat posts

**Format**: Standard WordPress XML export format

**Includes**: Post meta with messages array

## Future Enhancements

Structure supports additional features:

**Conversation Branching**: Could support multiple conversations per user

**Message Editing**: Could add edit history to messages

**Message Deletion**: Could implement soft delete for messages

**Statistics**: Could track message counts, tool usage, response times

**Archiving**: Could archive old conversations

**Sharing**: Could enable conversation sharing between users
