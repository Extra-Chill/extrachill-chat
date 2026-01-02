<?php
/**
 * Priority 10 AI directive establishing chat agent identity and HTML formatting requirement.
 *
 * Filter execution order (all run during chubes_ai_request filter):
 * - Priority 10: ChatCoreDirective (this file) - agent identity + HTML requirement
 * - Priority 20: ChatSystemPromptDirective - custom prompt from site settings
 * - Priority 30: ChatUserContextDirective - user identity and membership
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChatCoreDirective {

	/**
	 * Inject core chat agent directive at priority 10.
	 *
	 * @param array $request AI request array
	 * @return array Modified request with core directive injected
	 */
	public static function inject( $request, $provider_name = null, $streaming_callback = null, $tools = null, $conversation_data = null ) {
		if ( ! isset( $request['messages'] ) || ! is_array( $request['messages'] ) ) {
			return $request;
		}

		$directive = self::generate_core_directive();

		array_push(
			$request['messages'],
			array(
				'role'    => 'system',
				'content' => $directive,
			)
		);

		return $request;
	}

	/**
	 * Generate core directive with agent identity and HTML formatting requirement.
	 *
	 * HTML required because JavaScript uses response.innerHTML (markdown wouldn't render).
	 *
	 * @return string Core directive text
	 */
	private static function generate_core_directive() {
		$directive = "You are an AI assistant for Extra Chill, an independent music journalism platform.\n\n";

		$directive .= "PLATFORM ARCHITECTURE:\n";
		$directive .= "- WordPress multisite network with 7 interconnected sites\n";
		$directive .= "- Main site: " . ec_get_site_url( 'main' ) . " (music journalism and content)\n";
		$directive .= "- Community: " . ec_get_site_url( 'community' ) . " (forums and user hub)\n";
		$directive .= "- Shop: " . ec_get_site_url( 'shop' ) . " (e-commerce)\n";
		$directive .= "- Chat: " . ec_get_site_url( 'chat' ) . " (this interface)\n";
		$directive .= "- Artist: " . ec_get_site_url( 'artist' ) . " (artist profiles)\n";
		$directive .= "- Events: " . ec_get_site_url( 'events' ) . " (event calendar)\n\n";

		$directive .= "TOOL USAGE:\n";
		$directive .= "CRITICAL: You have function tools available. When users ask you to find, search, or read content, USE your tools.\n";
		$directive .= "Do NOT generate HTML forms, buttons, or links that pretend to be tools.\n";
		$directive .= "Do NOT describe what tools you could use - just USE them.\n";
		$directive .= "Your search_extrachill tool searches ALL network sites simultaneously - use it for any content search request.\n\n";

		$directive .= "RESPONSE FORMAT REQUIREMENTS:\n";
		$directive .= "CRITICAL: Always return your responses formatted as clean, semantic HTML.\n\n";

		$directive .= "Required HTML formatting:\n";
		$directive .= "- Use <p> tags for paragraphs (NOT markdown)\n";
		$directive .= "- Use <strong> and <em> for emphasis (NOT markdown ** or *)\n";
		$directive .= "- Use <ul> and <li> for bulleted lists (NOT markdown -)\n";
		$directive .= "- Use <ol> and <li> for numbered lists (NOT markdown 1.)\n";
		$directive .= "- Use <a href=\"URL\">text</a> for links (NOT markdown [text](url))\n";
		$directive .= "- Use <code> for inline code (NOT markdown backticks)\n\n";

		$directive .= "CRITICAL: Do NOT use markdown syntax. Use HTML tags only.\n\n";

		$directive .= "Example correct HTML response:\n";
		$directive .= "<p>I found 3 posts about that topic:</p>\n";
		$directive .= "<ul>\n";
		$directive .= "<li><a href=\"" . ec_get_site_url( 'main' ) . "/post\">Post Title</a> - Brief description</li>\n";
		$directive .= "</ul>\n\n";

		return trim( $directive );
	}
}

// Register directive at priority 10 - runs FIRST before other directives
add_filter( 'chubes_ai_request', array( 'ChatCoreDirective', 'inject' ), 10, 5 );
