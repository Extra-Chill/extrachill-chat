/**
 * WordPress REST Adapter for @extrachill/chat
 *
 * Bridges the Extra Chill chat REST endpoints to the
 * generic ChatAdapter contract.
 */

import apiFetch from '@wordpress/api-fetch';

let messageIdCounter = 0;
function generateId() {
	return `ec_${ Date.now() }_${ ++messageIdCounter }`;
}

/**
 * Create a ChatAdapter that talks to the Extra Chill chat REST API.
 *
 * @param {Object} config
 * @param {Array}  config.initialMessages - Server-rendered chat history
 * @param {number} config.userId          - Current user ID (0 if logged out)
 * @return {import('@extrachill/chat').ChatAdapter}
 */
export function createExtraChillChatAdapter( config = {} ) {
	const { initialMessages = [], userId = 0 } = config;

	return {
		capabilities: {
			sessions: false,
			history: false,
			streaming: false,
			tools: true,
			availabilityStates: true,
		},

		async loadInitialState() {
			if ( ! userId ) {
				return {
					availability: {
						status: 'login-required',
						loginUrl: '/wp-login.php?redirect_to=' + encodeURIComponent( window.location.href ),
					},
				};
			}

			// Normalize server-rendered messages to @extrachill/chat format
			const messages = normalizeMessages( initialMessages );

			return {
				availability: { status: 'ready' },
				messages,
			};
		},

		async sendMessage( input ) {
			const response = await apiFetch( {
				path: 'message',
				method: 'POST',
				data: { message: input.content },
			} );

			const messages = [];

			// Add the assistant's text response
			if ( response.message ) {
				messages.push( {
					id: generateId(),
					role: 'assistant',
					content: response.message,
					timestamp: response.timestamp || new Date().toISOString(),
					toolCalls: response.tool_calls?.length
						? response.tool_calls.map( ( tc ) => ( {
							id: generateId(),
							name: tc.tool || tc.name,
							parameters: tc.parameters || {},
						} ) )
						: undefined,
				} );
			}

			return {
				sessionId: 'default',
				messages,
				completed: true,
			};
		},

		async clearSession() {
			await apiFetch( {
				path: 'history',
				method: 'DELETE',
			} );
		},
	};
}

/**
 * Normalize server-rendered chat history messages.
 *
 * The EC backend stores messages as { role, content, timestamp }
 * without IDs. Map them to the normalized format.
 *
 * @param {Array} rawMessages
 * @return {Array}
 */
function normalizeMessages( rawMessages ) {
	if ( ! Array.isArray( rawMessages ) ) {
		return [];
	}

	return rawMessages
		.filter( ( msg ) => ( msg.role === 'user' || msg.role === 'assistant' ) && msg.content )
		.map( ( msg ) => ( {
			id: generateId(),
			role: msg.role,
			content: msg.content,
			timestamp: msg.timestamp || new Date().toISOString(),
		} ) );
}
