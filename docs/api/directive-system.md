# AI Directive System

Four-layer system injecting context and instructions into AI requests via WordPress filters.

## System Architecture

### Filter Hook Integration

**Filter**: `ai_request`

**Purpose**: Modify AI request before sending to OpenAI

**Execution Point**: Before actual API call to OpenAI

**Filter Chain**: Multiple directives at different priorities

### Priority Order

Directives execute in priority order (lowest to highest):

1. **Priority 10**: ChatCoreDirective - Platform architecture + HTML requirement
2. **Priority 20**: ChatSystemPromptDirective - Custom system prompt from settings
3. **Priority 30**: ChatUserContextDirective - User identity and membership
4. **Priority 40**: MultisiteSiteContextDirective - Network topology (if dm-multisite active)
5. **Priority 99**: AI Client - Actual API execution

## Directive Classes

### ChatCoreDirective (Priority 10)

**File**: `inc/directives/ChatCoreDirective.php`

**Purpose**: Establish AI agent identity and response format requirements

**Registration**:
```php
add_filter('ai_request', array('ChatCoreDirective', 'inject'), 10, 5);
```

**Injection Method**:
```php
public static function inject(
    $request,
    $provider_name = null,
    $streaming_callback = null,
    $tools = null,
    $conversation_data = null
)
```

**Content Injected**:

1. **Agent Identity**
   - "You are an AI assistant for Extra Chill"
   - Platform mission statement

2. **Platform Architecture**
   - 7-site multisite network description
   - Site purposes and URLs
   - Network relationships

3. **Tool Usage Instructions**
   - CRITICAL requirement to USE tools, not describe them
   - Prohibition on generating fake HTML forms/buttons
   - local_search tool capabilities

4. **Response Format Requirements**
   - CRITICAL requirement for HTML (not markdown)
   - Specific HTML tag usage instructions
   - Examples of correct formatting

**Message Format**:
```php
array(
    'role'    => 'system',
    'content' => $directive  // Full directive text
)
```

**Key Directive Sections**:

```
PLATFORM ARCHITECTURE:
- WordPress multisite network with 7 interconnected sites
- Main site: extrachill.com (music journalism and content)
...

TOOL USAGE:
CRITICAL: You have function tools available. When users ask you to find,
search, or read content, USE your tools.
...

RESPONSE FORMAT REQUIREMENTS:
CRITICAL: Always return your responses formatted as clean, semantic HTML.
- Use <p> tags for paragraphs (NOT markdown)
...
```

### ChatSystemPromptDirective (Priority 20)

**File**: `inc/directives/ChatSystemPromptDirective.php`

**Purpose**: Inject user-customizable system prompt from site settings

**Registration**:
```php
add_filter('ai_request', array('ChatSystemPromptDirective', 'inject'), 20, 5);
```

**Settings Source**:
```php
$custom_prompt = get_option('extrachill_chat_system_prompt', '');
```

**Conditional Injection**: Only injects if custom prompt configured (not empty)

**Message Format**:
```php
array(
    'role'    => 'system',
    'content' => $custom_prompt  // From site settings
)
```

**Example Custom Prompt**:
```
You are an expert in electronic dance music with deep knowledge of
techno and house music history, DJ mixing techniques, and festival culture.

Provide detailed, knowledgeable responses about electronic music topics.
Keep responses concise - aim for 2-3 paragraphs maximum unless the user
asks for detailed information.
```

### ChatUserContextDirective (Priority 30)

**File**: `inc/directives/ChatUserContextDirective.php`

**Purpose**: Provide AI with current user identity and membership information

**Registration**:
```php
add_filter('ai_request', array('ChatUserContextDirective', 'inject'), 30, 5);
```

**User Information Collected**:

1. **Basic Identity**
   - Display name
   - Username
   - Current site role

2. **Platform Membership** (if functions available)
   - Team member status (via `ec_is_team_member()`)
   - Artist profile count (via `ec_get_user_artist_ids()`)
   - Community membership (via `is_user_member_of_blog()`)

**Message Format**:
```php
array(
    'role'    => 'system',
    'content' => $directive  // User context text
)
```

**Example Directive Content**:
```
USER CONTEXT:
- Display Name: John Smith
- Username: @jsmith
- Current Site Role: Subscriber
- Team Member: No
- Artist: Yes (2 profiles)
- Community Member: Yes
```

**Graceful Degradation**:
```php
if (!function_exists('ec_is_team_member')) {
    return null;  // Skip if plugin not active
}
```

**Helper Methods**:

- `get_user_role()` - Extract primary role from user object
- `check_team_member_status()` - Query extrachill-multisite plugin
- `check_artist_status()` - Query extrachill-artist-platform plugin
- `check_community_member_status()` - Query WordPress multisite

### MultisiteSiteContextWrapper (Priority 40)

**File**: `inc/directives/MultisiteSiteContextWrapper.php`

**Purpose**: Hook dm-multisite directive if plugin is network-activated

**Registration**:
```php
if (class_exists('DMMultisite\MultisiteSiteContextDirective')) {
    add_filter('ai_request', array('DMMultisite\MultisiteSiteContextDirective', 'inject'), 40, 5);
}
```

**Conditional**: Only registers if dm-multisite plugin active

**Content Provided** (by dm-multisite):
- Current site metadata (blog ID, name, URL)
- All network sites with metadata
- Available post types per site
- Available taxonomies per site
- JSON-formatted network topology

**Format**: Injected as system message at priority 40

**Purpose**: Enables AI to understand network structure and navigate intelligently

## Message Injection Process

### Request Modification

Each directive modifies `$request['messages']` array:

```php
// Before directives
$request = array(
    'messages' => array(
        array('role' => 'user', 'content' => 'Hello'),
        // ... conversation history
    ),
    'model' => 'gpt-5-mini'
);

// After all directives (simplified)
$request = array(
    'messages' => array(
        // Original conversation history
        array('role' => 'user', 'content' => 'Hello'),

        // Priority 10: Core directive
        array('role' => 'system', 'content' => 'You are an AI assistant...'),

        // Priority 20: Custom prompt (if configured)
        array('role' => 'system', 'content' => 'You are an expert in EDM...'),

        // Priority 30: User context
        array('role' => 'system', 'content' => 'USER CONTEXT:\n- Display Name: John...'),

        // Priority 40: Network context (if dm-multisite active)
        array('role' => 'system', 'content' => 'NETWORK TOPOLOGY:\n{...}')
    ),
    'model' => 'gpt-5-mini'
);
```

### Array Push Pattern

All directives use `array_push()` to append system messages:

```php
array_push($request['messages'], array(
    'role'    => 'system',
    'content' => $directive
));

return $request;
```

## Filter Parameters

### Standard Filter Signature

```php
function inject(
    $request,                // AI request array
    $provider_name = null,   // Provider identifier (e.g., 'openai')
    $streaming_callback = null,  // Streaming callback (not used)
    $tools = null,           // Available tools array
    $conversation_data = null    // Additional conversation data
)
```

**Used Parameters**:
- `$request` - Modified and returned by each directive

**Unused Parameters** (in current implementation):
- `$provider_name` - Could be used for provider-specific directives
- `$streaming_callback` - Not used (no streaming support)
- `$tools` - Not used in directives (used by conversation loop)
- `$conversation_data` - Not used currently

## Directive Content Guidelines

### Core Directive Requirements

**CRITICAL Sections**: Use "CRITICAL:" prefix for absolute requirements

**Structured Format**: Organize with clear section headers

**Examples**: Include concrete examples of correct behavior

**Prohibitions**: Explicitly state what AI should NOT do

### Custom Prompt Best Practices

**Specificity**: Clear, specific instructions work better than vague guidance

**Conciseness**: Shorter prompts generally more effective

**Consistency**: Don't contradict core directive requirements

**Testing**: Iterate based on actual AI responses

### User Context Format

**Structured Data**: Key-value format for easy parsing

**Conditional Fields**: Only include available information

**Privacy**: User can see this context affects responses but not the exact directive text

## Integration with Conversation Loop

### Filter Execution Point

Directives run inside `ec_chat_conversation_loop()` on each iteration:

```php
$request_data = array(
    'messages' => $messages,
    'model'    => 'gpt-5-mini'
);

// All directives execute here via filter chain
$response = apply_filters('ai_request', $request_data, 'openai', null, $tools);
```

**Frequency**: Every AI request (including tool call iterations)

**Context Continuity**: Same directives apply throughout conversation loop

### Message Array Growth

Each iteration adds:
- User messages
- AI responses
- Tool calls
- Tool results
- System directives (re-injected each time)

**Optimization**: History window limits message array size

## Debugging Directives

### Viewing Injected Content

Not directly visible to end users. To inspect:

1. **Add Logging**:
```php
public static function inject($request, ...) {
    $directive = self::generate_core_directive();
    error_log('Core Directive: ' . $directive);
    // ... rest of method
}
```

2. **Check Request Array**:
```php
// In conversation-loop.php after filter
error_log('Request after directives: ' . print_r($request, true));
```

3. **AI Response Analysis**: Observe if AI follows directive instructions

### Common Issues

**Directive Not Applied**:
- Check filter registration hook
- Verify priority correct
- Confirm conditional logic passes

**AI Ignores Directive**:
- Directive may be too vague
- Conflicting directives
- AI model limitations

**Performance Impact**:
- Very long directives increase token usage
- Multiple system messages add context overhead

## Extension Points

### Adding New Directives

Choose appropriate priority based on dependency:

```php
// Priority 15: Between core and custom prompt
add_filter('ai_request', array('MyCustomDirective', 'inject'), 15, 5);

class MyCustomDirective {
    public static function inject($request, ...) {
        if (!isset($request['messages'])) {
            return $request;
        }

        $directive = self::generate_directive();

        array_push($request['messages'], array(
            'role'    => 'system',
            'content' => $directive
        ));

        return $request;
    }

    private static function generate_directive() {
        return 'Your custom directive content';
    }
}
```

### Priority Recommendations

- **1-9**: Reserved for critical platform-level directives
- **10-19**: Platform architecture and core requirements
- **20-29**: Site-level customization
- **30-39**: User-specific context
- **40-49**: Network/environment context
- **50+**: Feature-specific directives
- **99**: AI client execution (do not use)

### Conditional Directives

Pattern for optional functionality:

```php
// Only inject if condition met
if ($some_condition) {
    add_filter('ai_request', array('ConditionalDirective', 'inject'), 25, 5);
}
```

Or check within method:

```php
public static function inject($request, ...) {
    if (!self::should_inject()) {
        return $request;  // Skip injection
    }
    // ... inject directive
}
```
