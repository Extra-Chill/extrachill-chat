# ExtraChill Chat

AI chatbot plugin providing ChatGPT-style interface with multi-turn conversation loop, tool calling, conversation history, and three-layer AI directive system. Site-activated on chat.extrachill.com with network-wide authentication for all logged-in multisite users.

## Plugin Information

- **Name**: ExtraChill Chat
- **Version**: 0.2.1
- **Text Domain**: `extrachill-chat`
- **Author**: Chris Huber
- **Author URI**: https://chubes.net
- **License**: GPL v2 or later
- **Network**: false (site-activated on chat.extrachill.com only)
- **Requires at least**: 5.0
- **Tested up to**: 6.4
- **Requires PHP**: 7.4

## Architecture

### Plugin Loading Pattern
- **Procedural WordPress Pattern**: Uses direct `require_once` includes for all plugin functionality
- **Site-Activated Plugin**: Activated only on chat.extrachill.com site
- **Network-Wide Access**: Any logged-in multisite user can access chat interface
- **Minimal Dependencies**: Chat invokes the `chubes_ai_request` filter; the provider implementation lives outside this plugin (configured via extrachill-ai-client + ai-http-client).
- **Homepage Rendering**: Outputs UI via `extrachill_homepage_content` action hook

### Core Features

#### Authentication System
- **Multisite Native**: Any logged-in user from any site in network can access chat
- **No Domain Detection**: Applies to all pages on the site where plugin is activated
- **REST API Security**: `is_user_logged_in()` permission check on all REST endpoints

#### Conversation History System (`inc/core/chat-history.php`)
- **Custom Post Type**: `ec_chat` stores conversation history per user
- **User Association**: `post_author` field identifies chat owner
- **Message Storage**: Chat messages stored in `_ec_chat_messages` post meta
- **Automatic Creation**: Chat post created on first message if doesn't exist
- **Clear History**: REST endpoint allows users to clear their conversation history
- **Functions**:
  - `ec_chat_get_or_create_chat()` - Get or create chat post for user
  - `ec_chat_get_messages()` - Retrieve all messages from chat
  - `ec_chat_add_message()` - Add single message to chat history
  - `ec_chat_save_conversation()` - Save complete conversation from loop
  - `ec_chat_clear_history()` - Clear all messages from chat

#### Multi-Turn Conversation Loop (`inc/core/conversation-loop.php`)
- **Function**: `ec_chat_conversation_loop()`
- **Purpose**: Enables chained tool usage (search → read → search again)
- **Max Iterations**: 10 loops to prevent infinite tool calling
- **Flow**: AI returns tool calls → execute tools → send results → AI responds → repeat until text response
- **Hardcoded Model**: GPT-5-mini via OpenAI (not configurable in settings)
- **Return Value**: Array with `content`, `tool_calls`, and `messages` keys

#### Four-Layer AI Directive System (`inc/directives/`)
**Priority 10: ChatCoreDirective** (`ChatCoreDirective.php`)
- Agent identity and platform architecture overview
- 11-site active multisite network description (Blog IDs 1–5, 7–12) with docs at Blog ID 10; wire at Blog ID 11; horoscope at Blog ID 12
- HTML formatting requirement (NOT markdown)
- Tool usage instructions (use tools, don't describe them)

**Priority 20: ChatSystemPromptDirective** (`ChatSystemPromptDirective.php`)
- User-customizable system prompt from site settings
- Loaded from `get_option('extrachill_chat_system_prompt')`
- Optional - only injected if configured

**Priority 30: ChatUserContextDirective** (`ChatUserContextDirective.php`)
- Current user display name and username
- User's role on current site
- Team member status (via extrachill-multisite)
- Artist status and profile count (via extrachill-artist-platform)
- Community membership status (via native WordPress multisite functions)

**Priority 40: MultisiteSiteContextDirective** (`MultisiteSiteContextWrapper.php`)
- Provided by dm-multisite plugin, hooked via wrapper pattern
- Current site metadata (blog ID, name, URL)
- All 11 Extra Chill network sites with complete metadata
- Post types and taxonomies available on each site
- JSON-formatted network topology
- Only active when dm-multisite is network-activated
- Enables AI to understand network structure and navigate intelligently

#### Homepage Rendering (`inc/core/chat-hooks.php`, `inc/templates/`)
- **Homepage Content**: Uses `extrachill_homepage_content` action hook to inject chat interface
- **Full Template**: `inc/templates/chat-interface.php` - complete chat interface
- **Header Template**: `inc/templates/chat-header.php` - via `extrachill_above_chat` hook
- **Footer Template**: `inc/templates/chat-footer.php` - via `extrachill_below_chat` hook
- **Sticky Header Disabled**: `add_filter('extrachill_enable_sticky_header', '__return_false')`

#### REST API Integration (routes live in `extrachill-api`)
- **POST** `/wp-json/extrachill/v1/chat/message` (route file: `extrachill-api/inc/routes/chat/message.php`)\n  - Handles user messages\n  - Creates/loads conversation history\n  - Calls multi-turn conversation loop\n  - Saves complete conversation including tool calls\n  - Returns AI response + tool call metadata\n\n- **DELETE** `/wp-json/extrachill/v1/chat/history` (route file: `extrachill-api/inc/routes/chat/history.php`)\n  - Clears user's chat history\n  - Requires confirmation in JavaScript\n  - Returns success/error JSON response

#### AI Integration (`inc/core/ai-integration.php`)
- **Function**: `ec_chat_send_ai_message()`
- **Conversation History**: Loads last 20 messages from chat post
- **Message Structure**: Includes `tool_calls` and `tool_call_id` from history
- **Tool Discovery**: `ec_chat_get_available_tools()` via global `$ec_chat_tools`
- **Conversation Loop**: Uses `ec_chat_conversation_loop()` for multi-turn tool calling
- **System Prompts**: Injected via three directive filters (priorities 10, 20, 30)

#### Admin Settings (`inc/admin/admin-settings.php`)
- **Location**: Site Admin → ExtraChill Chat (top-level menu)
- **Storage**: Site-level via `get_option()` / `update_option()`
- **Capability**: `manage_options` required
- **Fields**: System prompt only (no provider/model selection)
- **AI Provider**: Hardcoded to OpenAI with GPT-5-mini model

#### Asset Management (`inc/core/assets.php`)
- **Conditional Loading**: Assets load on the front page only (`is_front_page()`)
- **Cache Busting**: `filemtime()` versioning for CSS/JS
- **Vanilla JS**: Frontend chat UI is implemented with vanilla JavaScript
- **Localized Data**:
  - REST base URL and nonce
  - Chat history for initial page load
  - User ID

## File Structure

```
extrachill-chat/
├── extrachill-chat.php          # Main plugin file
├── inc/
│   ├── core/
│   │   ├── chat-history.php     # Custom post type and history functions
│   │   ├── conversation-loop.php # Multi-turn AI tool calling loop
│   │   ├── ai-integration.php   # AI API integration with tool support
│   │   ├── assets.php           # CSS/JS enqueuing
│   │   ├── chat-hooks.php       # Template hooks management
│   │   └── breadcrumbs.php      # Breadcrumb navigation
│   ├── templates/
│   │   └── chat-interface.php   # Main chat template
│   ├── directives/
│   │   ├── ChatCoreDirective.php                # Priority 10: Agent identity + HTML requirement
│   │   ├── ChatSystemPromptDirective.php        # Priority 20: User custom prompt
│   │   ├── ChatUserContextDirective.php         # Priority 30: User identity and membership
│   │   └── MultisiteSiteContextWrapper.php      # Priority 40: Hooks dm-multisite site context
│   ├── admin/
│   │   └── admin-settings.php   # Site settings page
│   └── tools/
│       ├── chat-tools.php       # Tool registry and management
│       ├── search-extrachill.php # Native multisite search tool
│       └── artist-platform/
│           └── add-link-to-page.php # Artist platform tool with multisite blog switching
├── assets/
│   ├── css/
│   │   └── chat.css             # ChatGPT-style interface
│   └── js/
│       └── chat.js              # Chat functionality with history loading
├── build.sh                     # Production build script
├── .buildignore                 # Build exclusions
├── composer.json                # Dev dependencies only
└── CLAUDE.md                    # This documentation
```

## Technical Implementation

### Conversation History Flow
1. User sends message via REST (`POST /wp-json/extrachill/v1/chat/message`)
2. Route delegates to `ec_chat_send_ai_message()` and history functions in `extrachill-chat`
3. Backend calls `ec_chat_get_or_create_chat( $user_id )` to get/create chat post
4. Load last 20 messages from `_ec_chat_messages`
5. Execute conversation loop with multi-turn tool calling
6. Save complete conversation (including tool calls and results) to post meta
7. REST route returns JSON (`message`, `tool_calls`)

### Multi-Turn Conversation Loop Flow
1. Start with messages array (history + new user message)
2. Send to AI with available tools
3. **If AI returns text**: Done - return response
4. **If AI returns tool calls**:
   - Execute each tool via `$ec_chat_tools->call_tool()`
   - Add assistant message with `tool_calls` to conversation
   - Add tool result messages with `role: 'tool'` to conversation
   - Loop back to step 2 (max 10 iterations)
5. Return final response with all tool call metadata for UI display

### Four-Layer Directive System Flow
```
chubes_ai_request filter execution order:
1. Priority 10: ChatCoreDirective::inject()
   └─> Injects platform architecture + HTML requirement
2. Priority 20: ChatSystemPromptDirective::inject()
   └─> Injects custom system prompt (if configured)
3. Priority 30: ChatUserContextDirective::inject()
   └─> Injects user identity and membership context
4. Priority 40: MultisiteSiteContextDirective::inject() (if dm-multisite active)
   └─> Injects network topology and site metadata (JSON-formatted)
5. Priority 99: ai-http-client executes actual API request
   └─> Sends complete message stack to OpenAI with all context
```

### AI Request Format (Hardcoded)
```php
// In conversation-loop.php line 38-40
$request_data = array(
    'messages' => $messages,  // Includes history + system prompts via filters
    'model'    => 'gpt-5-mini'  // HARDCODED - not configurable
);

$response = apply_filters( 'chubes_ai_request', $request_data, 'openai', null, $tools );
```

### Chat Message Flow
1. User types message and clicks send or presses Enter
2. JavaScript: Display user message immediately
3. JavaScript: `fetch()` POST to `rest_url( 'extrachill/v1/chat/message' )`
4. REST route: `is_user_logged_in()` permission check and message sanitization
5. PHP: Get/create chat post for user
6. PHP: Load last 20 messages from history
7. PHP: Execute multi-turn conversation loop with tools
8. PHP: Save complete conversation to chat post meta
9. PHP: Return JSON with AI response + tool call metadata
10. JavaScript: Display tool calls in info boxes (if any)
11. JavaScript: Render assistant HTML response
12. JavaScript: Auto-scroll to bottom

### CSS Architecture
- **ExtraChill Variables**: Uses theme CSS custom properties from `root.css`
- **ChatGPT-Style Bubbles**: User messages right-aligned blue, assistant messages left-aligned gray
- **Tool Call Info Boxes**: Gray info boxes showing tool usage between messages
- **Full-Width Container**: Max-width 900px, centered with padding
- **Responsive Design**: Mobile breakpoint at 768px
- **Auto-Resize Input**: Textarea grows with content (max 150px height)
- **Dark Mode**: Automatic via CSS variables (no custom dark mode code needed)

### JavaScript Architecture
- **Vanilla JS Module Pattern**: Self-contained chat object
- **DOM Caching**: All elements cached on init
- **History Loading**: `loadChatHistory()` renders localized history on page load
- **Tool Display**: `addToolCallsInfo()` shows tool calls with friendly names
- **Event Binding**: Direct event listeners (no jQuery)
- **Auto-Resize**: Textarea height adjusts on input
- **Keyboard Support**: Enter to send, Shift+Enter for new line
- **Loading States**: Input disabled during REST request with typing indicator
- **Smooth Scrolling**: Scroll to bottom on new messages
- **Clear History**: Confirmation dialog before REST DELETE

## Development Standards

### Code Organization
- **Procedural Pattern**: Direct `require_once` includes throughout
- **WordPress Standards**: Full compliance with WordPress coding standards
- **Security First**: Nonces, capability checks, input sanitization, output escaping
- **Error Handling**: Comprehensive error logging and user-friendly messages

### Security Implementation
- **Authentication**: `is_user_logged_in()` check on every request
- **REST Nonce**: Frontend sends `X-WP-Nonce` header using `wp_create_nonce( 'wp_rest' )`
- **Input Sanitization**: `sanitize_textarea_field()` with `wp_unslash()`
- **Output Escaping**: `esc_html()`, `esc_attr()`, `esc_url()` throughout
- **Capability Checks**: `manage_options` for site admin settings
- **No Direct SQL**: All database operations via WordPress functions

### Build System
- **Shared Build Script**: Symlinked to universal build script at `../../.github/build.sh`
- **Production Build**: `./build.sh` creates non-versioned ZIP file only
- **Composer Integration**: Development dependencies only (PHPUnit, PHPCS)
- **File Exclusions**: `.buildignore` patterns exclude development files
- **Structure Validation**: Ensures plugin file exists in build
- **Output**: `/build/extrachill-chat.zip` file (non-versioned)

## Common Development Commands

### Building and Deployment
```bash
# Install dependencies
composer install

# Create production build
./build.sh

# Run PHP linting
composer run lint:php

# Fix PHP coding standards
composer run lint:fix

# Run tests
composer run test
```

### Build Output
- **Production ZIP**: `/build/extrachill-chat.zip` - Non-versioned deployment package
- **Activation**: Site-activate on chat.extrachill.com only
- **File Exclusions**: Development files, vendor/, .git/, build tools excluded via `.buildignore`

## Dependencies

### Required Plugins
- **extrachill-ai-client** - AI provider integration (must be network-activated for API key management)

### Optional Plugin Integrations
- **extrachill-multisite** - Provides `ec_is_team_member()` function for user context
- **extrachill-artist-platform** - Provides `ec_get_user_artist_ids()` function for user context

### Required Theme
- **extrachill** - Theme with `extrachill_template_homepage` filter and chat hooks

### WordPress Requirements
- WordPress 5.0+ multisite installation
- PHP 7.4+

## Integration Patterns

### With extrachill-ai-client
**AI Request** (in conversation-loop.php):
```php
$response = apply_filters( 'chubes_ai_request', $request_data, 'openai', null, $tools );
```

**Note**: Provider and model are hardcoded to OpenAI and GPT-5-mini respectively. AI client handles API key management and HTTP communication.

### With extrachill Theme
**Homepage Content** (in main plugin file):
```php
add_action( 'extrachill_homepage_content', 'ec_chat_render_homepage' );
```

**Disable Sticky Header** (in main plugin file):
```php
add_filter( 'extrachill_enable_sticky_header', '__return_false' );
```

**Template Hooks** (in chat-hooks.php):
- `extrachill_above_chat` - Renders chat header
- `extrachill_below_chat` - Renders chat footer

## Notes

- Requires a WordPress multisite network; the plugin is site-activated on chat.extrachill.com.
- Provider/model selection is not configurable; the conversation loop hardcodes `gpt-5-mini` and routes the request through the `chubes_ai_request` filter.
- The only admin setting is the optional system prompt (`extrachill_chat_system_prompt`).


### 2. Create Chat Site
- Network Admin → Sites → Add New
- Site URL: chat.extrachill.com
- Site Title: "ExtraChill Chat"
- Verify domain resolves correctly

### 3. Activate Plugin
- Visit chat.extrachill.com/wp-admin
- Plugins → Activate "ExtraChill Chat"
- **Note**: Site-activate only (NOT network activate)

### 4. Configure Settings (Optional)
- Site Admin → ExtraChill Chat
- Enter custom system prompt to modify AI behavior
- Save settings

### 5. Test Functionality
- Log in as any multisite user
- Visit chat.extrachill.com
- Verify chat interface renders
- Send test message
- Verify AI response appears
- Test clear history functionality


## Artist Platform Tools with Multisite Pattern

### Multisite Tool Architecture

Chat tools that need to access functionality on other sites in the multisite network use a specific pattern with blog switching.

**Key Insight**: The chat plugin runs on chat.extrachill.com, but artist platform functions exist on artist.extrachill.com. Tools must use WordPress multisite functions to access cross-site data.

### Artist Platform Tool: add_link_to_page

**File**: `inc/tools/artist-platform/add-link-to-page.php`

**Purpose**: Add links to user's artist link pages from chat interface

**Multisite Pattern Implementation**:

```php
// 1. Use network-wide function from extrachill-users plugin
$artist_ids = ec_get_artists_for_user( $user_id );

// 2. Get artist blog ID (hardcoded for performance)
$artist_blog_id = 4; // artist.extrachill.com

// 3. Switch to artist site
switch_to_blog( $artist_blog_id );
try {
    // 4. Call artist platform functions
    $link_page_id = ec_get_link_page_for_artist( $artist_id );
    do_action( 'ec_artist_add_link', $link_page_id, $link_data, $user_id );

    // 5. Get data for response
    $artist_name = get_the_title( $artist_id );
    $link_page_url = get_permalink( $link_page_id );
} finally {
    // 6. Always restore current blog
    restore_current_blog();
}
```

**Key Components**:

1. **Network-Wide Functions** (from extrachill-users):
   - `ec_get_artists_for_user()` - Get user's artist profiles
   - Available from any site (users plugin is network-activated)

2. **Blog Switching**:
   - Hardcoded blog IDs for performance (e.g., `$artist_blog_id = 4`)
   - `switch_to_blog()` - Switch database context
   - `restore_current_blog()` - Restore original context

3. **try/finally Pattern**:
   - Ensures blog is always restored, even if errors occur
   - Critical for multisite stability

4. **Artist Platform Functions** (only work on artist site):
   - `ec_get_link_page_for_artist()` - Get link page ID
   - `do_action('ec_artist_add_link')` - Add link via action hook

**Tool Registration**:
```php
add_filter( 'ec_chat_tools', 'ec_chat_register_add_link_tool' );
```

**JavaScript Display**:
```javascript
// In chat.js toolNames object:
'add_link_to_page': 'Added link to artist page'
```

### Pattern for Future Cross-Site Tools

When building tools that access other sites:

1. **Identify Data Source**: Which site has the functionality?
2. **Use Network Functions**: Check if extrachill-users or extrachill-multisite provide needed functions
3. **Implement Blog Switching**: Use try/finally with `switch_to_blog()` and `restore_current_blog()`
4. **Error Handling**: Return structured errors with `error` and `suggestion` keys
5. **Test Thoroughly**: Verify functions exist and work correctly from chat site

**Example Tool Structure**:
```php
function my_cross_site_tool( $parameters, $tool_def ) {
    // 1. Check network functions exist
    if ( ! function_exists( 'ec_network_function' ) ) {
        return array( 'error' => 'Network function not available' );
    }

    // 2. Get necessary IDs/data
    $data = ec_network_function( get_current_user_id() );

    // 3. Get target blog ID (hardcoded for performance)
    $target_blog_id = 5; // target.extrachill.com

    // 4. Switch and execute
    switch_to_blog( $target_blog_id );
    try {
        // Call target site functions
        $result = target_site_function( $data );
        return array( 'success' => true, 'result' => $result );
    } finally {
        restore_current_blog();
    }
}
```

## Current Implementation Status

### Implemented Features
- Multi-turn conversation loop with chained tool calling
- Conversation history system via custom post type
- Clear history functionality via REST endpoint
- Four-layer AI directive system (core, custom, user context, site context)
- Multisite site context directive (network topology and site metadata)
- Template override system via theme filters
- Tool usage display in UI with friendly names
- 20-message conversation history window
- HTML-formatted responses (not markdown)
- Network-wide multisite authentication
- User context injection (roles, team status, artist profiles)
- Network topology context (all 11 sites, post types, taxonomies)


### Hardcoded Limitations
- **AI Provider**: OpenAI only (hardcoded in conversation-loop.php)
- **AI Model**: GPT-5-mini only (hardcoded in conversation-loop.php)
- **No Admin Configuration**: Provider/model not configurable in settings
- **Site-Specific Settings**: System prompt stored per-site, not network-wide

### Not Yet Implemented
- RAG/vector search system
- Streaming responses
- User-specific settings or preferences
- Admin analytics dashboard
- Rate limiting per user
- Message editing or deletion
- Image upload and understanding
- Voice input capabilities
- Conversation branching or multiple chats
- Export conversation functionality

## User Info

- Name: Chris Huber
- Dev website: https://chubes.net
- GitHub: https://github.com/chubes4
- Founder & Editor: https://extrachill.com
- Creator: https://saraichinwag.com
