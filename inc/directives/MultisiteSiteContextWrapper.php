<?php
/**
 * Wrapper for dm-multisite's MultisiteSiteContextDirective.
 *
 * Hooks the directive to ai_request filter at priority 40 when dm-multisite is active.
 * Provides comprehensive WordPress multisite network context to the AI agent.
 *
 * Gracefully degrades if dm-multisite plugin is not network-activated.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register MultisiteSiteContextDirective if dm-multisite is active.
 *
 * Priority 40 runs after all ExtraChill Chat directives:
 * - Priority 10: ChatCoreDirective (platform identity + HTML requirement)
 * - Priority 20: ChatSystemPromptDirective (custom prompt from settings)
 * - Priority 30: ChatUserContextDirective (user identity and membership)
 * - Priority 40: MultisiteSiteContextDirective (network context) ← This directive
 * - Priority 99: ai-http-client actual API execution
 */
if ( class_exists( 'DMMultisite\MultisiteSiteContextDirective' ) ) {
	add_filter( 'ai_request', array( 'DMMultisite\MultisiteSiteContextDirective', 'inject' ), 40, 5 );
}
