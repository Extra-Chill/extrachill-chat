# Chat endpoints (REST)

The chat UI uses the Extra Chill REST API under the `extrachill/v1` namespace.

## POST `/wp-json/extrachill/v1/chat/message`

Send a user message and receive the assistant response.

### Implementation

- Route file: `extrachill-api/inc/routes/chat/message.php`
- Delegates to: `ec_chat_send_ai_message()` in `extrachill-chat`

### Authentication

- Requires a logged-in user (`permission_callback` checks `is_user_logged_in()`).
- Frontend requests include `X-WP-Nonce` using a `wp_rest` nonce.

### Request body (JSON)

```json
{ "message": "What are the latest electronic music posts?" }
```

### Response (JSON)

```json
{
  "message": "<p>…HTML response…</p>",
  "tool_calls": [
    {
      "tool": "search_extrachill",
      "parameters": { "query": "techno" }
    }
  ],
  "timestamp": "2025-12-23 14:32:15"
}
```

Notes:
- `message` is **HTML** (rendered in the UI via `innerHTML`).
- `tool_calls` is UI metadata collected by the conversation loop.

## DELETE `/wp-json/extrachill/v1/chat/history`

Clear the current user's stored chat history.

### Implementation

- Route file: `extrachill-api/inc/routes/chat/history.php`
- Delegates to: `ec_chat_clear_history()` in `extrachill-chat`

### Authentication

Logged-in users only (`is_user_logged_in()`).

### Response (JSON)

```json
{ "message": "Chat history cleared successfully." }
```

## Frontend usage

The chat frontend calls these endpoints from:

- `extrachill-chat/assets/js/chat.js`

It uses:

- `ecChatData.restUrl` = `rest_url( 'extrachill/v1/chat/' )`
- `ecChatData.nonce` = `wp_create_nonce( 'wp_rest' )`
