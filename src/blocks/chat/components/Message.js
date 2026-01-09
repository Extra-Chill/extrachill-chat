/**
 * Message Component
 *
 * Renders a single chat message (user or assistant).
 */

export default function Message( { message } ) {
	const { role, content } = message;
	const isUser = role === 'user';
	const messageClass = isUser ? 'ec-user-message' : 'ec-assistant-message';

	return (
		<div className={ `ec-chat-message ${ messageClass }` }>
			<div className="ec-message-content">
				{ isUser ? (
					<p>{ content }</p>
				) : (
					<div dangerouslySetInnerHTML={ { __html: content } } />
				) }
			</div>
		</div>
	);
}
