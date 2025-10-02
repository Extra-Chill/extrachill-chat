<?php
/**
 * Returns 404 for non-logged-in users on all pages.
 * Access granted for any logged-in multisite user.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'template_redirect', 'ec_chat_check_authentication', 5 );

function ec_chat_check_authentication() {
	if ( ! is_user_logged_in() ) {
		wp_die(
			'<h1>404 Not Found</h1><p>The page you are looking for does not exist.</p>',
			'404 Not Found',
			array( 'response' => 404 )
		);
	}
}