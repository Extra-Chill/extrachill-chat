<?php
/**
 * Chat Homepage Template
 *
 * Renders either the chat block or login-register block based on auth status.
 * Hooked via extrachill_homepage_content action.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

extrachill_breadcrumbs();
?>

<h2><?php esc_html_e( 'Extra Chill Chat', 'extrachill-chat' ); ?></h2>

<?php
if ( is_user_logged_in() ) {
	echo do_blocks( '<!-- wp:extrachill/chat /-->' );
} else {
	echo do_blocks( '<!-- wp:extrachill/login-register /-->' );
}
