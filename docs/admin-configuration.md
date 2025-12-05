# Admin Configuration

Site-level settings for customizing AI behavior through system prompts.

## Settings Location

**Menu Path**: WordPress Admin → ExtraChill Chat

**Icon**: Chat bubble icon (dashicons-format-chat)

**Menu Position**: Position 30 (below Comments, above Appearance)

**Capability Required**: `manage_options` (typically Administrators only)

## Settings Page

### Page Title

"ExtraChill Chat Settings"

### Settings Scope

**Site-Level**: Settings apply to current site only (not network-wide)

**Multisite Note**: Each site in Extra Chill network can have different configuration

**Storage**: WordPress `options` table via `get_option()` and `update_option()`

## Available Settings

### System Prompt

**Field Type**: Textarea

**Rows**: 10 rows

**Width**: Large (50 columns)

**Character Limit**: None enforced

**Field ID**: `extrachill_chat_system_prompt`

**Default Value**: Empty string (no custom prompt)

**Description**: "Define the AI's behavior and personality. Leave empty for no system prompt."

### Setting Purpose

**AI Customization**: Modifies how AI responds to users

**Behavior Definition**: Controls tone, style, and approach

**Domain Expertise**: Add specialized knowledge or focus areas

**Response Guidelines**: Define preferred response formats or patterns

## Configuration Examples

### Domain-Specific Focus

```
You are an expert in electronic dance music with deep knowledge of:
- Techno and house music history
- DJ mixing techniques
- Festival culture and lineups
- Producer equipment and software

Provide detailed, knowledgeable responses about electronic music topics.
```

### Tone and Style

```
Respond in a friendly, enthusiastic tone that reflects Extra Chill's
independent music journalism mission. Be conversational but informative.

Keep responses concise - aim for 2-3 paragraphs maximum unless the user
asks for detailed information.
```

### Response Formatting

```
When listing multiple items, always use HTML unordered lists for readability.

When referencing Extra Chill posts, always include clickable links using
<a href="URL">title</a> format.

Prioritize recent content over older posts when multiple results exist.
```

### Combined Approach

```
You are Extra Chill's music discovery assistant specializing in independent
and underground electronic music.

GUIDELINES:
- Be enthusiastic about discovering new music
- Always include links to relevant Extra Chill posts
- Prioritize recent content and upcoming events
- Use bullet points for multiple recommendations
- Keep tone friendly and conversational

When users ask about artists, provide:
1. Brief artist background
2. Recent Extra Chill coverage
3. Upcoming shows or releases
4. Similar artists they might enjoy
```

## Settings Management

### Saving Settings

**Button**: "Save Settings" button at bottom of form

**Action**: Submits to `options.php` (WordPress settings API)

**Validation**: `sanitize_textarea_field()` applied to input

**Success Message**: "Settings saved successfully." (green admin notice)

**Error Handling**: WordPress settings API handles validation errors

### Setting Updates

**Immediate Effect**: Changes apply to next AI conversation

**No Cache**: No caching layer - reads directly from options table

**Active Conversations**: Users in active conversation see changes on next message

### Retrieving Settings

**Function**: `get_option('extrachill_chat_system_prompt', '')`

**Default**: Empty string if not set

**Injection**: Loaded during AI request processing

**Filter Priority**: Priority 20 (after core directive, before user context)

## Settings Impact

### AI Directive System

Four-layer directive injection order:

1. **Priority 10**: Core platform architecture and HTML requirement (hardcoded)
2. **Priority 20**: Custom system prompt from settings ← Your configuration
3. **Priority 30**: User context and identity (automatic)
4. **Priority 40**: Network topology (if dm-multisite active)

**Custom Prompt Position**: Runs after platform basics, before user-specific context

### When Prompt Applied

**Every Message**: Custom prompt included in every AI request

**Conversation Context**: Works alongside conversation history

**Tool Access**: Does not affect which tools AI can use

**Format Requirement**: Cannot override HTML response format requirement

## Best Practices

### Effective Prompts

**Be Specific**: Clear instructions work better than vague guidance

**Use Examples**: Show desired response format with examples

**Set Priorities**: Tell AI what to prioritize when multiple options exist

**Define Tone**: Explicitly state desired conversational style

### What to Avoid

**Contradicting Core Directive**: Cannot override platform architecture context

**Markdown Requests**: AI must return HTML (core requirement cannot be changed)

**Tool Restrictions**: Cannot prevent AI from using available tools

**Response Length**: Very long prompts may impact AI performance

### Testing Prompts

**Iterative Approach**: Test prompts with various user questions

**User Feedback**: Monitor if responses match desired behavior

**Refinement**: Adjust based on actual AI responses

**Version Control**: Keep notes on what works and what doesn't

## Network-Wide Settings

### Current Limitation

**No Network Option**: Settings stored per-site, not network-wide

**Duplicate Configuration**: Must configure each site separately

**Different Prompts**: Can intentionally vary prompt per site

### Future Consideration

Structure allows for network-level settings if needed:
- Could use `get_site_option()` for network storage
- Would require code modification
- Current implementation is site-specific by design

## Settings Security

### Capability Check

**Required**: `manage_options` capability

**Enforcement**: WordPress settings API validates automatically

**Fallback**: Users without capability see "You do not have sufficient permissions"

### Input Sanitization

**Function**: `sanitize_textarea_field()`

**Process**:
- Removes invalid UTF-8
- Converts entities
- Strips tags
- Removes line breaks from single-line fields

**Additional**: `wp_unslash()` applied before sanitization

### Nonce Protection

**Automatic**: WordPress settings API adds nonce fields

**Verification**: Built into `options.php` submission handler

**CSRF Prevention**: Protects against cross-site request forgery

## Settings Storage

### Database Location

**Table**: `wp_options` (or `wp_{blog_id}_options` in multisite)

**Option Name**: `extrachill_chat_system_prompt`

**Autoload**: Yes (loaded on every page)

**Serialization**: Stored as plain text (not serialized)

### Direct Database Access

**Query Example**:
```sql
SELECT option_value
FROM wp_options
WHERE option_name = 'extrachill_chat_system_prompt';
```

**Update Example**:
```sql
UPDATE wp_options
SET option_value = 'Your custom prompt'
WHERE option_name = 'extrachill_chat_system_prompt';
```

**Caution**: Use WordPress functions instead of direct SQL when possible

## Troubleshooting

### Prompt Not Applied

**Check**: Verify setting saved (look for success message)

**Test**: Send new chat message (changes apply to new conversations)

**Verify**: Check browser console for JavaScript errors

**Database**: Confirm option exists in database

### Unexpected AI Behavior

**Conflict**: Custom prompt may conflict with core directives

**Clarity**: Vague prompts produce inconsistent results

**Length**: Extremely long prompts may be truncated

**Testing**: Try simplified prompt to isolate issue

### Settings Not Saving

**Permissions**: Verify `manage_options` capability

**PHP Errors**: Check error logs for PHP warnings

**Database**: Ensure database connection working

**Caching**: Clear object cache if using persistent cache

## Default Configuration

### Out of Box

**System Prompt**: Empty (no custom prompt)

**AI Behavior**: Uses core platform directive only

**Personality**: Helpful, informative music assistant

**Format**: HTML responses with tool usage

### Recommended Starting Point

```
You are Extra Chill's AI assistant for independent music discovery.

Keep responses friendly and conversational while being informative.
Always link to relevant Extra Chill posts when discussing music content.
Use your tools to find current information rather than making assumptions.
```

Then customize based on your specific needs and user feedback.
