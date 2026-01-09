/**
 * useChat Hook
 *
 * Manages chat state and API interactions.
 * apiFetch middleware configured in view.js sets base URL and nonce.
 */

import { useState, useCallback, useRef, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

const config = window.ecChatConfig || {};

export default function useChat() {
	const [ messages, setMessages ] = useState( config.chatHistory || [] );
	const [ isLoading, setIsLoading ] = useState( false );
	const [ toolCalls, setToolCalls ] = useState( [] );
	const messagesEndRef = useRef( null );

	const scrollToBottom = useCallback( () => {
		messagesEndRef.current?.scrollIntoView( { behavior: 'smooth' } );
	}, [] );

	useEffect( () => {
		scrollToBottom();
	}, [ messages, scrollToBottom ] );

	const sendMessage = useCallback( async ( content ) => {
		if ( ! content.trim() || isLoading ) {
			return;
		}

		const userMessage = {
			role: 'user',
			content: content.trim(),
			timestamp: new Date().toISOString(),
		};

		setMessages( ( prev ) => [ ...prev, userMessage ] );
		setIsLoading( true );
		setToolCalls( [] );

		try {
			const response = await apiFetch( {
				path: 'message',
				method: 'POST',
				data: { message: content.trim() },
			} );

			if ( response.tool_calls && response.tool_calls.length > 0 ) {
				setToolCalls( response.tool_calls );
			}

			const assistantMessage = {
				role: 'assistant',
				content: response.message || 'Sorry, I encountered an error. Please try again.',
				timestamp: response.timestamp || new Date().toISOString(),
			};

			setMessages( ( prev ) => [ ...prev, assistantMessage ] );
		} catch ( error ) {
			console.error( 'Chat error:', error );
			const errorMessage = {
				role: 'assistant',
				content: 'Sorry, I encountered a connection error. Please try again.',
				timestamp: new Date().toISOString(),
			};
			setMessages( ( prev ) => [ ...prev, errorMessage ] );
		} finally {
			setIsLoading( false );
		}
	}, [ isLoading ] );

	const clearHistory = useCallback( async () => {
		if ( ! window.confirm( 'Are you sure you want to clear your chat history? This cannot be undone.' ) ) {
			return;
		}

		try {
			await apiFetch( {
				path: 'history',
				method: 'DELETE',
			} );

			setMessages( [] );
			setToolCalls( [] );
		} catch ( error ) {
			console.error( 'Clear history error:', error );
			window.alert( 'An error occurred while clearing chat history. Please try again.' );
		}
	}, [] );

	const hasMessages = messages.length > 0;

	return {
		messages,
		isLoading,
		toolCalls,
		hasMessages,
		sendMessage,
		clearHistory,
		messagesEndRef,
	};
}
