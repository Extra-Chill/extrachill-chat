# Conversation History

Persistent conversation storage using WordPress custom post type with automatic history loading.

## Storage System

### Custom Post Type

**Post Type**: `ec_chat`

**One Per User**: Each user has single chat post containing all conversation history

**Post Author**: Links chat to specific user via WordPress `post_author` field

**Post Meta Storage**: Messages stored in `_ec_chat_messages` post meta array

**Auto-Creation**: Chat post created automatically when user sends first message

### Message Structure

Each message stored with:

**Core Fields**:
- `role`: Message role (user, assistant, tool, system)
- `content`: Message content (string or null for tool calls)
- `timestamp`: WordPress timestamp when message added

**Optional Fields**:
- `tool_calls`: Array of tool calls made by AI
- `tool_call_id`: ID linking tool result to specific tool call

**Complete Conversation**: All messages including intermediate tool calls and results stored

## History Loading

### Page Load Behavior

**Automatic Display**: Full conversation history loads when you visit chat page

**JavaScript Processing**: History passed from PHP to JavaScript via `wp_localize_script()`

**Visual Rendering**: All previous messages display in chronological order

**Scroll Position**: Interface auto-scrolls to most recent message

### History Window in AI Requests

**Last 20 Messages**: AI receives recent conversation context

**Purpose**: Balance context awareness with API token limits

**Includes**:
- User messages
- AI responses
- Tool calls
- Tool results

**Reconstructed Conversation**: Proper message structure maintained for AI understanding

## Message Saving

### Automatic Saving

**Trigger**: Every user message and AI response saves immediately

**Complete Capture**: Saves entire conversation turn including:
1. User message
2. AI tool calls (if any)
3. Tool results
4. Final AI response

**Transaction**: All messages from single conversation loop saved together

### Save Function

**Function**: `ec_chat_save_conversation()`

**Input**: Array of messages from conversation loop

**Process**: Iterates through messages, extracting tool calls and IDs

**Storage**: Appends to existing message array in post meta

## Clearing History

### User-Initiated Clear

**Interface**: "Clear History" button in chat header

**Confirmation**: JavaScript confirms before clearing: "Are you sure you want to clear your chat history? This cannot be undone."

**AJAX Request**: Sends request to `ec_chat_clear_history` action

**Nonce**: Separate clear history nonce for security

### Clear Process

**Function**: `ec_chat_clear_history()`

**Action**: Replaces message array with empty array in post meta

**Timestamp Update**: Updates `_ec_chat_last_updated` meta

**UI Reset**: JavaScript displays welcome message after successful clear

**Permanent**: Cannot undo clear operation

## Data Persistence

### Cross-Session

**Browser Independence**: History persists across browser sessions

**Device Independence**: Same history on desktop and mobile

**Login Required**: Must be logged in to any multisite site to access

### Cross-Device

**Multisite Authentication**: Same user account across all Extra Chill sites

**Shared History**: Conversation continues regardless of login site

**Real-Time Sync**: WordPress database provides immediate consistency

## Database Queries

### Get or Create Chat

**Function**: `ec_chat_get_or_create_chat()`

**Query**: Searches for existing `ec_chat` post by user ID

**Order**: Most recent chat post (by date)

**Fallback**: Creates new chat post if none exists

**Return**: Chat post ID or WP_Error

### Get Messages

**Function**: `ec_chat_get_messages()`

**Query**: Retrieves `_ec_chat_messages` post meta

**Return**: Array of messages (empty array if none)

**No Limit**: Returns complete conversation history

### Add Message

**Function**: `ec_chat_add_message()`

**Process**:
1. Load existing messages array
2. Build new message object with timestamp
3. Merge extra data (tool calls, tool IDs)
4. Append to messages array
5. Update post meta
6. Update last updated timestamp

**Return**: Boolean success status

## Admin Visibility

### WordPress Admin

**Menu Location**: Posts → Chats

**List View**: Shows all user chat posts with:
- Post title (format: "Chat - Display Name - Timestamp")
- Author
- Date created

**Edit Screen**: Standard WordPress post editor with:
- Title field
- Author selection
- Post meta (hidden from UI)

**Capability**: Standard post capabilities apply

**Export**: Can export chat posts via WordPress export

## Privacy Considerations

**User-Specific**: Each user only sees their own conversation

**No Sharing**: Chats not publicly visible or searchable

**Database Storage**: Stored in WordPress database on server

**Admin Access**: Site administrators can view all chat posts

**Export**: Users cannot self-export (admin-only via WordPress export)

## Performance

### Message Array Size

**Unbounded Growth**: No automatic message limit or pruning

**Performance Impact**: Large conversations increase post meta size

**Load Time**: Full history loads on page load (may slow with 1000+ messages)

**Recommendation**: Manually clear history periodically for optimal performance

### Caching

**WordPress Object Cache**: Post meta cached by WordPress

**No Custom Caching**: No additional caching layer implemented

**Database Queries**: Each page load queries chat post and messages

## Metadata Tracking

### Last Updated Timestamp

**Meta Key**: `_ec_chat_last_updated`

**Update Triggers**:
- New message added
- History cleared

**Format**: MySQL datetime format

**Purpose**: Track conversation activity

### Future Metadata

Structure supports additional metadata:
- Conversation topics
- Message counts
- Tool usage statistics
- User satisfaction ratings
