<?php
/**
 * Priority 30 AI directive providing user context and identity.
 *
 * Injects current user information for personalized AI responses.
 * Gracefully handles missing plugins via function_exists() checks.
 *
 * Filter execution order: Runs last (after ChatCoreDirective at 10, ChatSystemPromptDirective at 20)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChatUserContextDirective {

	/**
	 * Inject user context directive at priority 30.
	 *
	 * @param array $request AI request array
	 * @return array Modified request with user context directive injected
	 */
	public static function inject( $request, $provider_name = null, $streaming_callback = null, $tools = null, $conversation_data = null ) {
		if ( ! isset( $request['messages'] ) || ! is_array( $request['messages'] ) ) {
			return $request;
		}

		$directive = self::generate_user_context_directive();

		if ( empty( $directive ) ) {
			return $request;
		}

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
	 * Generate user context directive.
	 *
	 * @return string User context directive text
	 */
	private static function generate_user_context_directive() {
		$user = wp_get_current_user();

		if ( ! $user || ! $user->ID ) {
			return '';
		}

		$directive  = "USER CONTEXT:\n";
		$directive .= '- Display Name: ' . $user->display_name . "\n";
		$directive .= '- Username: @' . $user->user_login . "\n";

		$role = self::get_user_role( $user );
		if ( $role ) {
			$directive .= '- Current Site Role: ' . ucfirst( $role ) . "\n";
		}

		$is_team = self::check_team_member_status( $user->ID );
		if ( $is_team !== null ) {
			$directive .= '- Team Member: ' . ( $is_team ? 'Yes' : 'No' ) . "\n";
		}

		$artist_status = self::check_artist_status( $user->ID );
		if ( $artist_status ) {
			$directive .= '- Artist: ' . $artist_status . "\n";
		}

		$is_community = self::check_community_member_status( $user->ID );
		if ( $is_community !== null ) {
			$directive .= '- Community Member: ' . ( $is_community ? 'Yes' : 'No' ) . "\n";
		}

		return trim( $directive );
	}

	/**
	 * @param WP_User $user User object
	 * @return string|null Role name or null if no role
	 */
	private static function get_user_role( $user ) {
		if ( empty( $user->roles ) ) {
			return null;
		}

		return $user->roles[0];
	}

	/**
	 * Uses extrachill-multisite ec_is_team_member() if available.
	 *
	 * @param int $user_id User ID
	 * @return bool|null True if team member, false if not, null if function unavailable
	 */
	private static function check_team_member_status( $user_id ) {
		if ( ! function_exists( 'ec_is_team_member' ) ) {
			return null;
		}

		return ec_is_team_member( $user_id );
	}

	/**
	 * Uses extrachill-users ec_get_artists_for_user() if available.
	 *
	 * @param int $user_id User ID
	 * @return string|null Artist status string or null if function unavailable
	 */
	private static function check_artist_status( $user_id ) {
		if ( ! function_exists( 'ec_get_artists_for_user' ) ) {
			return null;
		}

		$artist_ids = ec_get_artists_for_user( $user_id );

		if ( empty( $artist_ids ) ) {
			return 'No';
		}

		$count = count( $artist_ids );
		return 'Yes (' . $count . ( $count === 1 ? ' profile' : ' profiles' ) . ')';
	}

	/**
	 * @param int $user_id User ID
	 * @return bool|null True if community member, false if not, null if site not found
	 */
	private static function check_community_member_status( $user_id ) {
		$community_blog_id = function_exists( 'ec_get_blog_id' ) ? ec_get_blog_id( 'community' ) : null;
		if ( ! $community_blog_id ) {
			return null;
		}

		return is_user_member_of_blog( $user_id, $community_blog_id );
	}
}

// Register directive at priority 30 - runs AFTER custom system prompt, BEFORE site context
add_filter( 'chubes_ai_request', array( 'ChatUserContextDirective', 'inject' ), 30, 5 );
