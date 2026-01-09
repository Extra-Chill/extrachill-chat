/**
 * ChatApp Component
 *
 * Main container for the chat interface.
 */

import { __ } from '@wordpress/i18n';
import useChat from '../hooks/useChat';
import MessageList from './MessageList';
import ChatInput from './ChatInput';
import TypingIndicator from './TypingIndicator';
import ToolCallsInfo from './ToolCallsInfo';

export default function ChatApp() {
	const {
		messages,
		isLoading,
		toolCalls,
		hasMessages,
		sendMessage,
		clearHistory,
		messagesEndRef,
	} = useChat();

	return (
		<div className="ec-chat-container">
			<div className="ec-chat-messages">
				{ ! hasMessages && (
					<div className="ec-chat-placeholder">
						{ __( 'Welcome to Extra Chill Chat, your AI Powered Independent Music Assistant', 'extrachill-chat' ) }
					</div>
				) }

				<MessageList messages={ messages } />

				{ toolCalls.length > 0 && <ToolCallsInfo toolCalls={ toolCalls } /> }

				{ isLoading && <TypingIndicator /> }

				<div ref={ messagesEndRef } />
			</div>

			<ChatInput onSend={ sendMessage } disabled={ isLoading } />

			<div className="ec-chat-footer">
				<button
					type="button"
					className="button-3 button-small"
					onClick={ clearHistory }
				>
					{ __( 'Clear Chat History', 'extrachill-chat' ) }
				</button>
			</div>
		</div>
	);
}
