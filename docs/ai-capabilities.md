# AI Capabilities

The chat AI agent powered by OpenAI GPT-5-mini with multi-turn tool calling and Extra Chill platform integration.

## Core AI Features

### Model Configuration

**Provider**: OpenAI (hardcoded)

**Model**: gpt-5-mini (hardcoded)

**Not Configurable**: Provider and model cannot be changed in settings

### Response Format

**HTML Output**: AI returns all responses as clean, semantic HTML (not markdown)

**Supported HTML Elements**:
- `<p>` - Paragraphs
- `<strong>`, `<em>` - Text emphasis
- `<ul>`, `<ol>`, `<li>` - Lists
- `<a href="">` - Links
- `<code>` - Inline code
- `<pre>` - Code blocks

**JavaScript Rendering**: Responses inserted via `innerHTML` for proper HTML display

### Multi-Turn Conversation Loop

**Purpose**: Enables AI to chain multiple tool calls together

**Flow Example**:
1. AI searches for content → receives results
2. AI reads full post → receives content
3. AI searches related topics → receives results
4. AI summarizes findings → returns final response to user

**Max Iterations**: 10 loops before error to prevent infinite tool calling

**Automatic**: All tool chaining happens behind the scenes before user sees response

## Available Tools

When DM-Multisite plugin is network-activated, AI has access to these tools:

### google_search

**Purpose**: Search Google for external information

**Use Cases**:
- Current events and news
- Artist information not in database
- Venue details
- Festival lineups

**Parameters**:
- `query` (string): Search terms

**AI Behavior**: Uses automatically when asked about current information

### webfetch

**Purpose**: Fetch and extract content from web pages

**Use Cases**:
- Reading artist websites
- Extracting venue information
- Parsing festival pages
- Gathering external content

**Parameters**:
- `url` (string): Full URL to fetch

**AI Behavior**: Chains with google_search to read external sources

### local_search

**Purpose**: Search ALL Extra Chill network sites simultaneously

**Power**: Single tool searches across all post types on all sites

**Post Types Searched**:
- Posts (blog content)
- Pages
- Products (WooCommerce)
- Forums (bbPress)
- Events
- Custom post types

**Site Coverage**:
- extrachill.com
- community.extrachill.com
- shop.extrachill.com
- artist.extrachill.com
- events.extrachill.com

**Parameters**:
- `query` (string): Search terms
- `post_type` (optional): Filter to specific post types

**AI Behavior**: First tool used for any content request about Extra Chill

### wordpress_post_reader

**Purpose**: Read full content of posts from any network site

**Use Cases**:
- Detailed post analysis
- Content summarization
- Extracting specific information
- Answering questions about post details

**Parameters**:
- `post_id` (integer): WordPress post ID
- `blog_id` (integer): Site ID in network

**AI Behavior**: Chains with local_search to read full content after finding posts

## Tool Chaining Examples

### Example 1: Content Discovery

**User**: "Find posts about Bonobo"

**AI Tool Flow**:
1. `local_search` → Finds 5 posts about Bonobo across network
2. Returns formatted list with links

### Example 2: Deep Content Analysis

**User**: "Summarize the latest Four Tet interview"

**AI Tool Flow**:
1. `local_search` → Finds Four Tet interview posts
2. `wordpress_post_reader` → Reads full interview content
3. Summarizes key points from interview

### Example 3: External Research

**User**: "What's happening at Movement Detroit this year?"

**AI Tool Flow**:
1. `local_search` → Checks Extra Chill posts about Movement
2. `google_search` → Searches for current lineup information
3. `webfetch` → Reads festival website
4. Combines internal and external information

## Context Awareness

### Platform Architecture Context

AI understands the 7-site Extra Chill network:
- Main site structure
- Community forums
- Shop integration
- Artist profiles
- Event calendar

### User Context

AI receives information about current user:
- Display name and username
- Current site role
- Team member status (if extrachill-multisite active)
- Artist profile count (if extrachill-artist-platform active)
- Community membership status

**Personalization**: AI uses user context for personalized responses

### Network Topology Context

When dm-multisite is active, AI receives:
- All 6 network sites with metadata (name, URL, blog ID)
- Available post types per site
- Available taxonomies per site
- JSON-formatted network structure

**Intelligent Navigation**: AI understands where to find different content types

## Conversation Memory

**History Window**: Last 20 messages included in each AI request

**Context Retention**: AI remembers entire conversation within session

**Tool Call Memory**: Previous tool results available for reference

**Message Structure**: Includes user messages, AI responses, tool calls, and tool results

## Custom System Prompt

**Configuration**: Site Admin → ExtraChill Chat → System Prompt

**Purpose**: Modify AI behavior and personality

**Scope**: Site-level (each site can have different prompt)

**Priority**: Custom prompt injected after core platform directive

**Example Use Cases**:
- Add domain expertise
- Modify tone and style
- Add specific instructions
- Define response format preferences

## AI Directive System

Four-layer system builds complete AI context:

**Priority 10**: Platform architecture and HTML format requirement

**Priority 20**: Custom system prompt from site settings

**Priority 30**: Current user identity and membership status

**Priority 40**: Network topology and site metadata (if dm-multisite active)

All directives inject before AI processes each message.

## Response Characteristics

**Conversational**: Natural language responses

**Informative**: Provides detailed information when available

**Tool-First**: Uses tools rather than describing what tools could do

**Network-Aware**: Understands Extra Chill platform structure

**Personalized**: Acknowledges user identity and context

## Limitations

**No Streaming**: Responses appear complete (no token-by-token display)

**Max 10 Tool Calls**: Conversation loop prevents infinite tool chaining

**20 Message Window**: Older conversation history not included in AI context

**No Image Understanding**: Text-only interactions

**No Voice Input**: Text-based interface only

**Site-Specific Settings**: Each site must configure system prompt independently
