# Chat Interface

The chat interface provides a ChatGPT-style conversational experience with AI responses displayed as HTML.

## Accessing Chat

**Authentication Required**: All users must be logged in to any Extra Chill multisite site.

**Access URL**: Visit chat.extrachill.com

**404 Behavior**: Non-logged-in users receive a 404 error page.

## Interface Components

### Message Display Area

**Location**: Main scrollable container showing conversation history

**Message Types**:
- User messages: Right-aligned blue bubbles
- AI responses: Left-aligned gray bubbles with HTML-formatted content
- Tool usage info: Gray info boxes showing which tools AI used

**Auto-Scroll**: Interface automatically scrolls to latest message

**Height**: 500-600px scrollable area (400-500px on mobile)

### Input Area

**Text Input**:
- Auto-resizing textarea (50px min, 150px max height)
- Grows with content as you type
- Placeholder: "Type your message..."

**Send Button**:
- Blue button labeled "Send"
- Disabled during AI response processing

**Keyboard Shortcuts**:
- Enter: Send message
- Shift+Enter: New line in message

### Welcome Message

**Initial State**: "Welcome to Extra Chill Chat, your AI Powered Independent Music Assistant"

**Behavior**: Disappears when first message sent or history loaded

### Loading States

**Typing Indicator**: Three animated dots appear while AI processes response

**Input Disabled**: Input and send button disabled during processing

## Message Flow

### Sending Messages

1. Type message in textarea
2. Click Send button or press Enter
3. Message appears immediately in chat as blue bubble
4. Input clears and typing indicator appears
5. AI response appears when ready
6. Input re-enabled for next message

### Receiving Responses

**HTML Formatting**: AI responses render as HTML with proper formatting:
- Paragraphs: `<p>` tags
- Lists: `<ul>`, `<ol>`, `<li>` tags
- Links: `<a href="">` tags (clickable)
- Emphasis: `<strong>`, `<em>` tags
- Code: `<code>` tags with monospace font

**Tool Usage Display**: If AI uses tools, gray info boxes appear between user message and AI response showing:
- Tool name (friendly name like "Searched Extra Chill network")
- Tool parameters (query, URL, or JSON)

### Example Interaction

```
You: What are the latest electronic music posts?

[Tool info box]
Searched Extra Chill network: electronic music

AI: I found 3 recent posts about electronic music:

• Bonobo's New Album Review - Released last month
• Interview with Four Tet - Discussing production techniques
• Festival Preview: Movement Detroit - Electronic music lineup
```

## Conversation History

**Persistent Storage**: All conversations saved automatically to WordPress database

**History Window**: Last 20 messages included in each AI request for context

**Page Load**: Full conversation history displays when you visit chat page

**Cross-Session**: History persists across browser sessions and devices

## Responsive Design

### Desktop (>768px)

- Message bubbles: 70% max width
- Full padding and spacing
- Larger text and buttons

### Mobile (≤768px)

- Message bubbles: 85% max width
- Reduced padding for more content space
- Font size maintained at 16px to prevent iOS zoom
- Touch-optimized button sizes

## Visual Feedback

**Message Animation**: New messages fade in with slight upward motion

**Typing Indicator**: Three dots animate in sequence while AI thinks

**Focus States**: Input border highlights blue when focused

**Button States**: Send button changes color on hover, appears disabled when processing

## Accessibility Features

**Keyboard Navigation**: Full keyboard support for input and sending

**Focus Management**: Input automatically focused after sending message

**Color Contrast**: Uses ExtraChill theme CSS variables for consistent, accessible colors

**Semantic HTML**: AI responses use proper HTML elements for screen readers
