/**
 * MessageList Component
 *
 * Renders the list of chat messages.
 */

import Message from './Message';

export default function MessageList( { messages } ) {
	if ( ! messages || messages.length === 0 ) {
		return null;
	}

	// Filter to only show user and assistant messages with content
	const displayMessages = messages.filter(
		( msg ) => ( msg.role === 'user' || msg.role === 'assistant' ) && msg.content
	);

	return (
		<>
			{ displayMessages.map( ( message, index ) => (
				<Message key={ index } message={ message } />
			) ) }
		</>
	);
}
