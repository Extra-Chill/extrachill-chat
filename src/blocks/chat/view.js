/**
 * Chat Block - Frontend Entry
 *
 * Mounts the React chat app when the block is rendered on the frontend.
 */

import apiFetch, { createRootURLMiddleware, createNonceMiddleware } from '@wordpress/api-fetch';
import { createRoot } from '@wordpress/element';
import ChatApp from './components/ChatApp';

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

	const root = createRoot( container );
	root.render( <ChatApp /> );
} );
