/**
 * Chat Block - Frontend Entry
 *
 * Mounts the @extrachill/chat component with the Extra Chill
 * WordPress REST adapter.
 */

import apiFetch, { createRootURLMiddleware, createNonceMiddleware } from '@wordpress/api-fetch';
import { createRoot, createElement } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Chat } from '@extrachill/chat';
import { createExtraChillChatAdapter } from './adapter';

// Configure apiFetch with REST root and nonce from localized config
const config = window.ecChatConfig || {};
if ( config.restUrl ) {
	apiFetch.use( createRootURLMiddleware( config.restUrl ) );
}
if ( config.nonce ) {
	apiFetch.use( createNonceMiddleware( config.nonce ) );
}

window.addEventListener( 'DOMContentLoaded', () => {
	const container = document.getElementById( 'ec-chat-root' );
	if ( ! container ) {
		return;
	}

	const adapter = createExtraChillChatAdapter( {
		initialMessages: config.chatHistory || [],
		userId: config.userId || 0,
	} );

	const root = createRoot( container );
	root.render(
		createElement( Chat, {
			adapter,
			contentFormat: 'html',
			showTools: true,
			toolNames: {
				search_extrachill: __( 'Searched Extra Chill network', 'extrachill-chat' ),
				add_link_to_page: __( 'Added link to artist page', 'extrachill-chat' ),
			},
			placeholder: __( 'Type your message...', 'extrachill-chat' ),
			emptyState: createElement(
				'div',
				{ className: 'ec-chat-welcome' },
				__( 'Welcome to Extra Chill Chat, your AI Powered Independent Music Assistant', 'extrachill-chat' ),
			),
			onError: ( error ) => {
				console.error( 'Chat error:', error );
			},
		} ),
	);
} );
