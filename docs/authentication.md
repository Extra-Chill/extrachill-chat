# Authentication System

WordPress multisite authentication providing network-wide access to chat interface.

## Authentication Requirements

### Access Control

**Login Required**: All pages on chat.extrachill.com require authentication

**Multisite Native**: Any logged-in user from any Extra Chill network site can access

**No Domain Detection**: Authentication applies to all pages on the site where plugin activated

**404 for Guests**: Non-logged-in users receive 404 error page

## Implementation

### Authentication Check

**File**: `inc/core/authentication.php`

**Hook**: `template_redirect` action at priority 5

**Function**: `ec_chat_check_authentication()`

**Code**:
```php
add_action('template_redirect', 'ec_chat_check_authentication', 5);

function ec_chat_check_authentication() {
    if (!is_user_logged_in()) {
        wp_die(
            '<h1>404 Not Found</h1><p>The page you are looking for does not exist.</p>',
            '404 Not Found',
            array('response' => 404)
        );
    }
}
```

### Early Execution

**Priority**: 5 (runs early in template_redirect)

**Purpose**: Block access before theme templates load

**Prevents**: Unnecessary processing for unauthenticated requests

## User Experience Flow

### Authenticated Users

**Access Path**:
1. User logs into any Extra Chill network site
2. Visits chat.extrachill.com
3. WordPress recognizes multisite authentication
4. Chat interface loads normally

**Session**: WordPress maintains single session across all subdomains

**Cookie Domain**: `.extrachill.com` allows session sharing

### Unauthenticated Users

**Access Path**:
1. User visits chat.extrachill.com without login
2. `template_redirect` hook fires at priority 5
3. `ec_chat_check_authentication()` checks `is_user_logged_in()`
4. Returns false
5. `wp_die()` displays 404 error page
6. WordPress theme templates never load

**Error Display**:
```
404 Not Found

The page you are looking for does not exist.
```

**HTTP Status**: 404 (not 401/403)

**Design Choice**: 404 prevents revealing chat exists to unauthorized users

## WordPress Multisite Integration

### Native Authentication

**WordPress Function**: `is_user_logged_in()`

**Multisite Aware**: Checks if user authenticated to any site in network

**Cookie Sharing**: WordPress handles cookie domain configuration

**No Custom Code**: Uses WordPress core authentication system

### Cross-Site Access

**User on extrachill.com**:
- Logs in via extrachill.com
- Visits chat.extrachill.com
- Automatically authenticated (same cookie domain)

**User on community.extrachill.com**:
- Logs in via community.extrachill.com
- Visits chat.extrachill.com
- Automatically authenticated (same cookie domain)

**Any Network Site**: Works from any of the 7 Extra Chill network sites

### No Site-Specific Membership

**No blog_id Check**: Does not verify user is member of chat site

**Network-Wide**: Any multisite network user can access

**Simplicity**: No need to add users to chat site specifically

## Security Considerations

### Session Security

**WordPress Sessions**: Standard WordPress session handling

**Secure Cookies**: Respects WordPress `COOKIE_SECURE` constant

**HTTP Only**: Session cookies marked HTTP only

**SameSite**: WordPress default SameSite cookie policy

### AJAX Security

**Separate Layer**: AJAX requests have additional nonce verification

**Authentication Check**: AJAX handlers independently verify `is_user_logged_in()`

**Two-Layer Security**:
1. Template redirect blocks page access
2. AJAX handlers block unauthenticated requests

### No Capability Checks

**Access Level**: Any authenticated user (all roles)

**No Restrictions**: Subscribers, Contributors, Authors, Editors, Admins all have access

**Rationale**: Chat designed for all logged-in users

**Admin Functions**: Only admin settings page requires `manage_options` capability

## Login Flow

### Recommended Login Path

**Option 1**: Login via community.extrachill.com
- User navigates to community login
- Enters credentials
- Redirects to chat.extrachill.com

**Option 2**: Login via main site
- User logs into extrachill.com
- Navigates to chat.extrachill.com
- Already authenticated

**Option 3**: Direct WordPress login
- Navigate to chat.extrachill.com/wp-login.php
- Enter credentials
- Redirect to chat homepage

### Login Redirects

**No Custom Redirects**: Uses WordPress default login redirect behavior

**After Login**: WordPress redirects to originally requested URL or dashboard

**Logout**: Standard WordPress logout process

## Error Handling

### 404 Display

**Function**: `wp_die()`

**Parameters**:
- Message: HTML with 404 heading and text
- Title: "404 Not Found"
- Args: `array('response' => 404)`

**Styling**: WordPress default error page styling

**No Theme**: Error page loads before theme, uses WordPress core styles

### Custom Error Pages

**Not Implemented**: Could customize via `wp_die_handler` filter

**Current**: Standard WordPress 404 error display

**Example Customization**:
```php
add_filter('wp_die_handler', function($handler) {
    return function($message, $title, $args) {
        // Custom 404 page
        include EXTRACHILL_CHAT_PLUGIN_DIR . 'templates/404.php';
        die();
    };
});
```

## Integration with Theme

### Template Override

**Filter**: `extrachill_template_homepage`

**Priority**: After authentication check

**Flow**:
1. `template_redirect` priority 5: Authentication check
2. WordPress continues template loading
3. Theme executes homepage template filter
4. Chat plugin returns custom template

**Protection**: Template never loads for unauthenticated users

### Assets Loading

**Conditional**: Assets only enqueue if `is_front_page()`

**After Auth**: Assets enqueue after authentication verified

**JavaScript Data**: Localized script includes user ID

## Debugging Authentication

### Check User Status

Add to authentication function temporarily:

```php
function ec_chat_check_authentication() {
    if (!is_user_logged_in()) {
        error_log('Chat access denied - user not logged in');
        error_log('User ID: ' . get_current_user_id());
        error_log('Cookie: ' . print_r($_COOKIE, true));
        wp_die(...);
    } else {
        error_log('Chat access granted - user ID: ' . get_current_user_id());
    }
}
```

### Common Issues

**Cookie Domain Mismatch**:
- Check WordPress `COOKIE_DOMAIN` constant
- Should be `.extrachill.com` for subdomain sharing
- Verify in wp-config.php

**Session Expiration**:
- WordPress sessions expire after inactivity
- Default: 48 hours for "Remember Me", 2 days otherwise
- User must re-login

**Multisite Configuration**:
- Verify multisite network configured correctly
- Check `wp_blogs` table for correct domain mapping
- Confirm DNS resolves chat.extrachill.com

### Testing Authentication

**Test Logged In**:
1. Login to any network site
2. Visit chat.extrachill.com
3. Should see chat interface

**Test Logged Out**:
1. Logout from all sites
2. Visit chat.extrachill.com
3. Should see 404 error

**Test Different Sites**:
1. Login via extrachill.com
2. Visit chat.extrachill.com - should work
3. Login via community.extrachill.com
4. Visit chat.extrachill.com - should work

## Performance Impact

### Minimal Overhead

**Single Check**: One `is_user_logged_in()` call per request

**Early Exit**: Blocks further processing for unauthenticated users

**No Database Queries**: WordPress user check uses cookies only

### Page Load Time

**Authenticated**: No measurable impact (microseconds)

**Unauthenticated**: Faster than normal page (exits early)

## Future Enhancements

Structure supports additional authentication features:

**Role-Based Access**: Could restrict to specific user roles

**Capability Checks**: Could require custom capability

**IP Restrictions**: Could limit access by IP address

**Rate Limiting**: Could limit requests per user

**Login Required Message**: Could show login form instead of 404

**Example Role Check**:
```php
function ec_chat_check_authentication() {
    if (!is_user_logged_in()) {
        wp_die('404 Not Found', ...);
    }

    $user = wp_get_current_user();
    $allowed_roles = array('subscriber', 'administrator');

    if (!array_intersect($allowed_roles, $user->roles)) {
        wp_die('Access Denied', ...);
    }
}
```
