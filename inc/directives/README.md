# ExtraChill Chat AI Directives

This directory contains AI directive classes that inject system messages into AI requests via WordPress filters.

## Directive Priority System

Directives run in priority order (lowest first) before the main AI request executes at priority 50:

- **Priority 10**: `ChatCoreDirective` - Establishes agent identity and HTML formatting requirement
- **Priority 20**: `ChatSystemPromptDirective` - Injects user's custom system prompt from settings
- **Priority 30**: `MultisiteSiteContextDirective` - From dm-multisite plugin (if active) - provides site context
- **Priority 50**: Main `ai_request` filter executes the actual API call

## What Are Directives?

Directives are system messages that provide foundational context to the AI before it receives user input. They shape the AI's understanding of:
- Its role and identity
- The system architecture it's operating within
- Response format requirements
- Available tools and capabilities
- User customizations

## Integration with dm-multisite

When the dm-multisite plugin is active and network-activated, its `MultisiteSiteContextDirective` runs at priority 30, providing the AI with:
- Current site context (site name, URL, blog ID)
- Network topology information (all 6 sites in the Extra Chill network)
- Cross-site data access patterns
- Available multisite tools

This allows the chat agent to understand which site the user is on and intelligently access data across the entire network using the `local_search` and `wordpress_post_reader` tools.

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
Priority 30: MultisiteSiteContextDirective injects (if dm-multisite active)
    - Current site: chat.extrachill.com
    - Available sites in network
    - Multisite tool availability
    ↓
Priority 50: Main ai_request filter
    - Sends complete message stack to AI provider
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
   - **21-40**: Context and environment information
   - **41-49**: Final modifications before execution

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
