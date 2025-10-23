# ExtraChill Chat AI Directives

This directory contains AI directive classes that inject system messages into AI requests via WordPress filters.

## Directive Priority System

Directives run in priority order (lowest first) before the main AI request executes at priority 99:

- **Priority 10**: `ChatCoreDirective` - Establishes agent identity and HTML formatting requirement
- **Priority 20**: `ChatSystemPromptDirective` - Injects user's custom system prompt from settings
- **Priority 30**: `ChatUserContextDirective` - Injects current user identity and membership status
- **Priority 40**: `MultisiteSiteContextDirective` - From dm-multisite plugin (if active) - provides network context
- **Priority 99**: Main `ai_request` filter executes the actual API call via ai-http-client

## What Are Directives?

Directives are system messages that provide foundational context to the AI before it receives user input. They shape the AI's understanding of:
- Its role and identity
- The system architecture it's operating within
- Response format requirements
- Available tools and capabilities
- User customizations

## Integration with dm-multisite

When the dm-multisite plugin is active and network-activated, extrachill-chat hooks its `MultisiteSiteContextDirective` to the `ai_request` filter at priority 40 via the `MultisiteSiteContextWrapper.php` file.

This provides the AI with comprehensive network context:
- Current site context (site name, URL, blog ID)
- Network topology information (all 8 sites in the Extra Chill network)
- Available post types and taxonomies per site
- Cross-site data access patterns via JSON-formatted metadata

The wrapper pattern allows extrachill-chat to:
- Use dm-multisite's directive without requiring Data Machine installation
- Gracefully degrade if dm-multisite is not network-activated
- Maintain clean separation between plugins while sharing context

This enables the chat agent to understand which site the user is on and intelligently access data across the entire network using the `search_extrachill` and `wordpress_post_reader` tools.

## Directive Execution Flow

```
User sends message
    ↓
Priority 10: ChatCoreDirective injects
    - Agent identity as Extra Chill music assistant
    - HTML formatting requirement (CRITICAL)
    - Platform architecture overview
    ↓
Priority 20: ChatSystemPromptDirective injects
    - User's custom system prompt from settings (optional)
    - Allows site admin to customize behavior
    ↓
Priority 30: ChatUserContextDirective injects
    - Current user display name and username
    - User's role on current site
    - Team member status, artist profiles, community membership
    ↓
Priority 40: MultisiteSiteContextDirective injects (if dm-multisite active)
    - Current site: chat.extrachill.com
    - All 8 network sites with URLs and metadata
    - Post types and taxonomies per site
    - JSON-formatted network topology
    ↓
Priority 99: ai-http-client executes request
    - Sends complete message stack to AI provider (OpenAI)
    - Returns formatted HTML response
```

## Creating New Directives

To create a new directive:

1. **Create class file** in `inc/directives/`
2. **Implement static inject() method**:
   ```php
   public static function inject($request, $provider_name, $streaming_callback, $tools, $conversation_data = null): array {
       if (!isset($request['messages']) || !is_array($request['messages'])) {
           return $request;
       }

       $directive = self::generate_directive();

       array_push($request['messages'], [
           'role' => 'system',
           'content' => $directive
       ]);

       return $request;
   }
   ```

3. **Register the filter** at appropriate priority:
   ```php
   add_filter('ai_request', [ClassName::class, 'inject'], PRIORITY, 5);
   ```

4. **Choose priority** based on purpose:
   - **1-10**: Core identity and foundational requirements
   - **11-20**: User customizations and preferences
   - **21-40**: Context and environment information (user context at 30, site context at 40)
   - **41-98**: Final modifications before execution
   - **99**: Reserved for ai-http-client actual API execution

## Why HTML Formatting Requirement?

The `ChatCoreDirective` instructs the AI to return HTML instead of markdown because:
- **No dependencies**: Eliminates need for markdown parsing libraries
- **AI-native**: Modern LLMs excel at HTML generation
- **Clean output**: More reliable than client-side markdown conversion
- **Rich formatting**: Supports paragraphs, lists, links, code blocks natively
- **Performance**: No client-side parsing overhead

The JavaScript simply uses `.html()` to render the AI's response directly, trusting the AI to generate clean, safe HTML.

## Directive Best Practices

1. **Be concise**: Directives add to token count - every word matters
2. **Be specific**: Clear instructions produce better results
3. **Use hierarchy**: Critical instructions first, context second
4. **Test thoroughly**: Changes to directives affect all conversations
5. **Document changes**: Update this README when adding directives

## Current Directive Contents

### ChatCoreDirective (Priority 10)
- Agent identity: AI music assistant for Extra Chill
- Platform architecture: WordPress multisite network overview
- Response format: **HTML requirement** (critical for formatting)
- Available tools: Overview of search and content access capabilities

### ChatSystemPromptDirective (Priority 20)
- Loads custom prompt from: `get_option('extrachill_chat_system_prompt')`
- Only injects if user has configured a custom prompt
- Allows site admin to customize agent behavior without modifying core code

### ChatUserContextDirective (Priority 30)
- Current user display name and username
- User's role on current site
- Team member status (via extrachill-multisite `ec_is_team_member()`)
- Artist profiles count (via extrachill-artist-platform `ec_get_user_artist_ids()`)
- Community membership (via WordPress native multisite functions)
- Graceful degradation: Uses `function_exists()` checks for optional plugin integrations

### MultisiteSiteContextDirective (Priority 40)
- Provided by dm-multisite plugin, hooked via `MultisiteSiteContextWrapper.php`
- Current site metadata (blog ID, name, URL)
- All 8 Extra Chill network sites with complete metadata
- Post types and taxonomies available on each site
- JSON-formatted network topology for structured AI understanding
- Only active when dm-multisite is network-activated
- Enables AI to intelligently navigate and search across entire multisite network
