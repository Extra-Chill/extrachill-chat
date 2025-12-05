# Template System

Template override system replacing theme homepage with custom chat interface.

## Template Override

### Homepage Filter

**Filter**: `extrachill_template_homepage`

**File**: Main plugin file `extrachill-chat.php`

**Registration**:
```php
add_filter('extrachill_template_homepage', 'ec_chat_override_homepage_template');
```

**Function**:
```php
function ec_chat_override_homepage_template($template) {
    return EXTRACHILL_CHAT_PLUGIN_DIR . 'inc/templates/chat-interface.php';
}
```

**Priority**: Default (10)

**Scope**: Homepage only (theme controls filter execution)

## Chat Interface Template

### Main Template

**File**: `inc/templates/chat-interface.php`

**Structure**:
```php
<?php get_header(); ?>

<div class="full-width-breakout">
    <?php do_action('extrachill_above_chat'); ?>

    <div id="ec-chat-container">
        <div id="ec-chat-messages" class="ec-chat-messages">
            <div id="ec-chat-placeholder" class="ec-chat-placeholder">
                Welcome to Extra Chill Chat, your AI Powered Independent Music Assistant
            </div>
        </div>

        <div id="ec-chat-input-container" class="ec-chat-input-container">
            <textarea id="ec-chat-input" class="ec-chat-input"
                      placeholder="Type your message..." rows="1"></textarea>
            <button id="ec-chat-send" class="ec-chat-send-button">Send</button>
        </div>

        <div id="ec-chat-loading" class="ec-chat-loading" style="display:none;">
            <span class="ec-loading-spinner"></span>
            <span>Thinking...</span>
        </div>
    </div>

    <?php do_action('extrachill_below_chat'); ?>
</div>

<?php get_footer(); ?>
```

**Theme Integration**:
- Uses `get_header()` for site header
- Uses `get_footer()` for site footer
- Uses theme's `.full-width-breakout` class for layout
- Relies on theme CSS variables

### Template Structure

**Container**: `#ec-chat-container`
- Main chat wrapper
- JavaScript target for initialization

**Messages Area**: `#ec-chat-messages`
- Scrollable message display
- Min height: 500px, Max height: 600px
- Placeholder shown when empty

**Input Container**: `#ec-chat-input-container`
- Flexbox layout for input and button
- Auto-resizing textarea
- Send button

**Loading Indicator**: `#ec-chat-loading`
- Initially hidden
- Displays during AI processing
- Spinner and "Thinking..." text

## Template Hooks

### extrachill_above_chat

**Location**: Before chat container

**Usage**: Add header content above chat interface

**File**: `inc/templates/chat-header.php` (registered via chat-hooks.php)

**Content**:
```php
<div class="ec-chat-header">
    <h1>Extra Chill Chat</h1>
    <p class="ec-chat-subtitle">Your AI-powered independent music assistant</p>
    <button id="ec-chat-clear" class="ec-chat-clear-button">Clear History</button>
</div>
```

**Registration**:
```php
add_action('extrachill_above_chat', 'ec_chat_render_header');
```

### extrachill_below_chat

**Location**: After chat container

**Usage**: Add footer content below chat interface

**File**: `inc/templates/chat-footer.php` (registered via chat-hooks.php)

**Content**:
```php
<div class="ec-chat-footer">
    <p class="ec-chat-disclaimer">
        This AI assistant can make mistakes. Verify important information independently.
    </p>
</div>
```

**Registration**:
```php
add_action('extrachill_below_chat', 'ec_chat_render_footer');
```

## Template Rendering Functions

### ec_chat_render_header()

**File**: `inc/core/chat-hooks.php`

**Function**:
```php
function ec_chat_render_header() {
    include EXTRACHILL_CHAT_PLUGIN_DIR . 'inc/templates/chat-header.php';
}
```

**Hook**: `extrachill_above_chat`

**Purpose**: Render chat header with title and clear button

### ec_chat_render_footer()

**File**: `inc/core/chat-hooks.php`

**Function**:
```php
function ec_chat_render_footer() {
    include EXTRACHILL_CHAT_PLUGIN_DIR . 'inc/templates/chat-footer.php';
}
```

**Hook**: `extrachill_below_chat`

**Purpose**: Render chat footer with disclaimer

## Theme Integration

### Sticky Header Control

**Filter**: `extrachill_enable_sticky_header`

**Registration**:
```php
add_filter('extrachill_enable_sticky_header', '__return_false');
```

**Purpose**: Disable theme's sticky header behavior on chat page

**Scope**: Chat site only (where plugin activated)

### CSS Variables

**Dependency**: ExtraChill theme's CSS custom properties

**Variables Used**:
- `--background-color` - Background colors
- `--text-color` - Text color
- `--border-color` - Border colors
- `--button-bg` - Button background
- `--button-hover-bg` - Button hover state
- `--card-background` - Message bubble backgrounds
- `--link-color` - Link colors
- `--muted-text` - Muted text color
- `--focus-border-color` - Focus state borders
- `--focus-box-shadow` - Focus state shadow

**Automatic Dark Mode**: CSS variables handle dark mode automatically

### Layout Classes

**full-width-breakout**: Theme class for full-width content sections

**Purpose**: Break out of theme's content width constraints

**Applied To**: Main chat container wrapper

## Asset Loading

### Conditional Loading

**Hook**: `wp_enqueue_scripts`

**Condition**: `is_front_page()`

**File**: `inc/core/assets.php`

**Function**:
```php
function ec_chat_enqueue_assets() {
    if (!is_front_page()) {
        return;  // Only load on homepage
    }

    // Enqueue CSS and JS
}
```

**Scope**: Assets only load when chat template active

### CSS Enqueuing

**Handle**: `extrachill-chat`

**File**: `assets/css/chat.css`

**Dependencies**: None (relies on theme variables)

**Version**: File modification time (`filemtime()`) for cache busting

**Code**:
```php
wp_enqueue_style(
    'extrachill-chat',
    EXTRACHILL_CHAT_PLUGIN_URL . 'assets/css/chat.css',
    array(),
    filemtime($css_file)
);
```

### JavaScript Enqueuing

**Handle**: `extrachill-chat`

**File**: `assets/js/chat.js`

**Dependencies**: `array('jquery')`

**Version**: File modification time for cache busting

**Footer**: `true` (loads in footer)

**Code**:
```php
wp_enqueue_script(
    'extrachill-chat',
    EXTRACHILL_CHAT_PLUGIN_URL . 'assets/js/chat.js',
    array('jquery'),
    filemtime($js_file),
    true
);
```

### Localized Data

**Script Handle**: `extrachill-chat`

**Object Name**: `ecChatData`

**Data**:
```php
array(
    'ajaxUrl'     => admin_url('admin-ajax.php'),
    'nonce'       => wp_create_nonce('ec_chat_nonce'),
    'clearNonce'  => wp_create_nonce('ec_chat_clear_nonce'),
    'userId'      => $user_id,
    'chatHistory' => $chat_history  // Array of messages
)
```

**Purpose**: Provide JavaScript with:
- AJAX endpoint URL
- Security nonces
- Current user ID
- Existing conversation history

## Template Customization

### Modifying Templates

**Direct Edit**: Edit template files in plugin directory

**Child Plugin**: Create child plugin with higher filter priority

**Example Override**:
```php
// In custom plugin
add_filter('extrachill_template_homepage', 'my_custom_chat_template', 20);

function my_custom_chat_template($template) {
    return plugin_dir_path(__FILE__) . 'templates/my-chat.php';
}
```

### Adding Content to Hooks

**Before Chat**:
```php
add_action('extrachill_above_chat', 'my_custom_header', 20);

function my_custom_header() {
    echo '<div class="my-custom-content">Custom content</div>';
}
```

**After Chat**:
```php
add_action('extrachill_below_chat', 'my_custom_footer', 20);

function my_custom_footer() {
    echo '<div class="my-custom-footer">Custom footer</div>';
}
```

**Priority**: Use priority 20+ to run after default content

### Modifying Placeholder

Change welcome message:

```php
add_filter('ec_chat_placeholder_text', function($text) {
    return 'My custom welcome message';
});
```

Then update template to use filter:

```php
<div id="ec-chat-placeholder" class="ec-chat-placeholder">
    <?php echo apply_filters('ec_chat_placeholder_text',
        'Welcome to Extra Chill Chat, your AI Powered Independent Music Assistant'
    ); ?>
</div>
```

## Template Dependencies

### Required Theme Support

**get_header()**: Theme must have header.php

**get_footer()**: Theme must have footer.php

**CSS Variables**: Theme must define required CSS custom properties

**full-width-breakout**: Theme must support or define this class

### ExtraChill Theme Specific

**Designed For**: ExtraChill theme specifically

**Compatibility**: May require CSS adjustments for other themes

**Variables**: Relies on theme's root.css variable definitions

**Layout**: Uses theme's content width and breakout patterns

## Responsive Behavior

### Mobile Breakpoint

**Breakpoint**: 768px

**Mobile Styles**:
- Reduced padding
- Smaller message max-width (85% vs 70%)
- Adjusted container margins
- Touch-optimized button sizes

**Template**: Same HTML for all screen sizes

**CSS Only**: Responsive behavior via media queries in chat.css

## Template Loading Order

### Complete Flow

1. **Request**: User visits chat.extrachill.com
2. **Authentication**: `template_redirect` priority 5 checks login
3. **Template Selection**: WordPress determines homepage template
4. **Filter Execution**: `extrachill_template_homepage` filter runs
5. **Override Return**: Plugin returns custom template path
6. **Template Load**: WordPress loads chat-interface.php
7. **Header**: `get_header()` loads theme header
8. **Above Hook**: `extrachill_above_chat` action fires
9. **Chat Interface**: Main chat HTML renders
10. **Below Hook**: `extrachill_below_chat` action fires
11. **Footer**: `get_footer()` loads theme footer
12. **Assets**: CSS and JavaScript enqueued in footer
13. **JavaScript Init**: Chat.init() executes on DOM ready

### Asset Loading in Flow

**CSS**: Enqueued during `wp_enqueue_scripts` hook

**JavaScript**: Enqueued during `wp_enqueue_scripts` hook

**Localized Data**: Attached to script via `wp_localize_script()`

**Execution**: JavaScript runs after DOM ready

## Template Debugging

### Template Path

Check which template loading:

```php
add_action('template_include', function($template) {
    error_log('Loading template: ' . $template);
    return $template;
}, 999);
```

### Hook Execution

Verify hooks firing:

```php
add_action('extrachill_above_chat', function() {
    error_log('Above chat hook fired');
}, 1);

add_action('extrachill_below_chat', function() {
    error_log('Below chat hook fired');
}, 1);
```

### Asset Loading

Check if assets enqueued:

```php
add_action('wp_footer', function() {
    global $wp_scripts, $wp_styles;
    error_log('Styles: ' . print_r($wp_styles->queue, true));
    error_log('Scripts: ' . print_r($wp_scripts->queue, true));
});
```
