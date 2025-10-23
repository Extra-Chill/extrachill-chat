<?php
/**
 * Homepage template for chat.extrachill.com.
 * Overrides theme homepage via extrachill_template_homepage filter.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="full-width-breakout">
	<?php do_action( 'extrachill_above_chat' ); ?>

	<?php if ( is_user_logged_in() ) : ?>
		<div id="ec-chat-container">
			<div id="ec-chat-messages" class="ec-chat-messages">
				<div id="ec-chat-placeholder" class="ec-chat-placeholder">
					Welcome to Extra Chill Chat, your AI Powered Independent Music Assistant
				</div>
			</div>

			<div id="ec-chat-input-container" class="ec-chat-input-container">
				<textarea id="ec-chat-input" class="ec-chat-input" placeholder="Type your message..." rows="1"></textarea>
				<button id="ec-chat-send" class="ec-chat-send-button">Send</button>
			</div>

			<div id="ec-chat-loading" class="ec-chat-loading" style="display:none;">
				<span class="ec-loading-spinner"></span>
				<span>Thinking...</span>
			</div>
		</div>
	<?php else : ?>
		<h3><?php _e( 'Login or Register to Chat', 'extrachill-chat' ); ?></h3>
		<?php echo do_blocks( '<!-- wp:extrachill/login-register /-->' ); ?>
	<?php endif; ?>

	<?php do_action( 'extrachill_below_chat' ); ?>
</div>

<?php
get_footer();
?>