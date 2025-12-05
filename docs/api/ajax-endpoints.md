# AJAX Endpoints

WordPress AJAX endpoints for chat message processing and history management.

## ec_chat_message

Handles user messages and returns AI responses.

### Endpoint Configuration

**Action**: `ec_chat_message`

**Hook**: `wp_ajax_ec_chat_message`

**Method**: POST

**URL**: `admin-ajax.php`

**Authentication**: Logged-in users only

### Request Parameters

**action** (string, required)
- Value: `"ec_chat_message"`
- Identifies which AJAX handler to execute

**nonce** (string, required)
- Nonce for security verification
- Generated: `wp_create_nonce('ec_chat_nonce')`
- Verified: `check_ajax_referer('ec_chat_nonce', 'nonce')`

**message** (string, required)
- User's chat message content
- Sanitization: `sanitize_textarea_field(wp_unslash($_POST['message']))`
- Validation: Cannot be empty

### JavaScript Example

```javascript
$.ajax({
    url: ecChatData.ajaxUrl,
    type: 'POST',
    data: {
        action: 'ec_chat_message',
        nonce: ecChatData.nonce,
        message: 'What are the latest electronic music posts?'
    },
    success: function(response) {
        if (response.success) {
            console.log(response.data.message);
            console.log(response.data.tool_calls);
        }
    }
});
```

### Response Format

**Success Response** (HTTP 200):

```json
{
    "success": true,
    "data": {
        "message": "<p>I found 3 recent posts...</p>",
        "tool_calls": [
            {
                "tool": "local_search",
                "parameters": {
                    "query": "electronic music"
                }
            }
        ],
        "timestamp": "2025-10-05 14:32:15"
    }
}
```

**Error Response** (HTTP 400/401/500):

```json
{
    "success": false,
    "data": {
        "message": "Error message description"
    }
}
```

### Response Fields

**message** (string)
- AI response content
- Format: HTML (not plain text or markdown)
- Ready for `innerHTML` insertion

**tool_calls** (array)
- Metadata about which tools AI used
- Each element contains `tool` and `parameters`
- Used for UI display, not execution
- Empty array if no tools used

**timestamp** (string)
- MySQL datetime format
- When response generated
- Format: `Y-m-d H:i:s`

### Error Codes

**401 Unauthorized**
- User not logged in
- Message: "You must be logged in to use chat."

**400 Bad Request**
- Empty message submitted
- Message: "Message cannot be empty."

**500 Internal Server Error**
- Chat post creation failed
- AI request failed
- Message: "Sorry, I encountered an error processing your message. Please try again."

### Processing Flow

1. Verify nonce and authentication
2. Sanitize user message
3. Get or create chat post for user
4. Send message to AI with conversation history
5. Execute conversation loop with tool calling
6. Save complete conversation to database
7. Return AI response and tool metadata

### Performance

**Average Response Time**: 2-5 seconds depending on:
- AI model processing speed
- Number of tool calls required
- Network latency to OpenAI

**Timeout**: No explicit timeout (uses WordPress/server defaults)

**Concurrent Requests**: Each user can have one active request at a time (UI disables input)

## ec_chat_clear_history

Clears user's conversation history.

### Endpoint Configuration

**Action**: `ec_chat_clear_history`

**Hook**: `wp_ajax_ec_chat_clear_history`

**Method**: POST

**URL**: `admin-ajax.php`

**Authentication**: Logged-in users only

### Request Parameters

**action** (string, required)
- Value: `"ec_chat_clear_history"`
- Identifies which AJAX handler to execute

**nonce** (string, required)
- Nonce for security verification
- Generated: `wp_create_nonce('ec_chat_clear_nonce')`
- Verified: `check_ajax_referer('ec_chat_clear_nonce', 'nonce')`

**Note**: Separate nonce from message endpoint for security isolation

### JavaScript Example

```javascript
if (confirm('Are you sure you want to clear your chat history? This cannot be undone.')) {
    $.ajax({
        url: ecChatData.ajaxUrl,
        type: 'POST',
        data: {
            action: 'ec_chat_clear_history',
            nonce: ecChatData.clearNonce
        },
        success: function(response) {
            if (response.success) {
                $('#ec-chat-messages').empty();
                // Show welcome message
            }
        }
    });
}
```

### Response Format

**Success Response** (HTTP 200):

```json
{
    "success": true,
    "data": {
        "message": "Chat history cleared successfully."
    }
}
```

**Error Response** (HTTP 401/500):

```json
{
    "success": false,
    "data": {
        "message": "Error message description"
    }
}
```

### Error Codes

**401 Unauthorized**
- User not logged in
- Message: "You must be logged in to clear chat history."

**500 Internal Server Error**
- Chat post retrieval failed
- Clear operation failed
- Message: "Sorry, I encountered an error clearing chat history." or "Failed to clear chat history."

### Processing Flow

1. Verify nonce and authentication
2. Get or create chat post for user
3. Clear messages array in post meta
4. Update last updated timestamp
5. Return success confirmation

### Performance

**Response Time**: <500ms (simple database update)

**Irreversible**: Cannot undo clear operation

**UI Update**: JavaScript handles clearing visual display

## Security Implementation

### Nonce Verification

**Purpose**: Prevent CSRF attacks

**Generation**: Server-side via `wp_create_nonce()`

**Validation**: `check_ajax_referer()` on each request

**Expiration**: WordPress default (24 hours)

**Failure**: Dies with 403 error if nonce invalid

### Authentication Checks

**Function**: `is_user_logged_in()`

**Enforcement**: Every AJAX handler checks before processing

**Failure**: Returns 401 error JSON response

**Session**: WordPress session management handles authentication

### Input Sanitization

**User Message**: `sanitize_textarea_field(wp_unslash())`

**Process**:
1. `wp_unslash()` removes magic quote slashes
2. `sanitize_textarea_field()` sanitizes content
3. Validation checks for empty string

**Protection**: Prevents XSS and SQL injection

### Output Escaping

**AI Response**: Trusted HTML (from OpenAI) inserted via `innerHTML`

**Note**: AI instructed to return clean HTML, not markdown

**Risk**: Minimal as AI provider trusted, but user input sanitized before AI sees it

## Error Logging

### Server-Side Logging

**Function**: `error_log()`

**Logged Errors**:
- Chat post creation failures
- AI request failures
- Clear history failures

**Format**: `"ExtraChill Chat [Type] Error: {message}"`

**Location**: PHP error log (configured in php.ini)

### Client-Side Logging

**Console Errors**: JavaScript logs AJAX failures to browser console

**Format**: `console.error('Chat error:', error)`

**Purpose**: Debugging and troubleshooting

## Rate Limiting

**Current Implementation**: None

**Browser Limitation**: UI disables input during processing

**User Limitation**: One active request per user (UI enforced)

**Future Consideration**: Could implement server-side rate limiting if needed

## Localized Data

### ecChatData Object

Available to JavaScript after page load:

```javascript
{
    ajaxUrl: "https://chat.extrachill.com/wp-admin/admin-ajax.php",
    nonce: "abc123def456",
    clearNonce: "xyz789uvw012",
    userId: 42,
    chatHistory: [
        {
            role: "user",
            content: "Hello",
            timestamp: "2025-10-05 14:30:00"
        },
        {
            role: "assistant",
            content: "<p>Hi! How can I help you?</p>",
            timestamp: "2025-10-05 14:30:02"
        }
    ]
}
```

**Generation**: `wp_localize_script()` in assets.php

**Scope**: Available to chat.js only

**Security**: Nonces regenerated on each page load
