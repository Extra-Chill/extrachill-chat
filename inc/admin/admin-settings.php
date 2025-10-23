<?php
/**
 * Site-level admin menu for configuring system prompt.
 * Provider (openai) and model (gpt-5-mini) are hardcoded in conversation-loop.php line 38.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', 'ec_chat_add_admin_menu' );
add_action( 'admin_init', 'ec_chat_register_settings' );

function ec_chat_add_admin_menu() {
	add_menu_page(
		'ExtraChill Chat Settings',
		'ExtraChill Chat',
		'manage_options',
		'extrachill-chat',
		'ec_chat_settings_page',
		'dashicons-format-chat',
		30
	);
}

function ec_chat_register_settings() {
	register_setting(
		'extrachill_chat_settings',
		'extrachill_chat_system_prompt',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'default'           => ''
		)
	);

	add_settings_section(
		'ec_chat_main_section',
		'AI Configuration',
		'ec_chat_section_callback',
		'extrachill-chat'
	);

	add_settings_field(
		'extrachill_chat_system_prompt',
		'System Prompt',
		'ec_chat_system_prompt_callback',
		'extrachill-chat',
		'ec_chat_main_section'
	);
}

function ec_chat_section_callback() {
	echo '<p>Configure the AI system prompt for the chat interface. This setting is site-level (not network-wide).</p>';
}

function ec_chat_system_prompt_callback() {
	$value = get_option( 'extrachill_chat_system_prompt', '' );
	?>
	<textarea
		name="extrachill_chat_system_prompt"
		id="extrachill_chat_system_prompt"
		rows="10"
		cols="50"
		class="large-text"><?php echo esc_textarea( $value ); ?></textarea>
	<p class="description">Define the AI's behavior and personality. Leave empty for no system prompt.</p>
	<?php
}

function ec_chat_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_GET['settings-updated'] ) ) {
		add_settings_error(
			'extrachill_chat_messages',
			'extrachill_chat_message',
			'Settings saved successfully.',
			'updated'
		);
	}

	settings_errors( 'extrachill_chat_messages' );
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'extrachill_chat_settings' );
			do_settings_sections( 'extrachill-chat' );
			submit_button( 'Save Settings' );
			?>
		</form>
	</div>
	<?php
}
