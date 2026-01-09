/**
 * TypingIndicator Component
 *
 * Shows animated dots while waiting for AI response.
 */

export default function TypingIndicator() {
	return (
		<div className="ec-chat-message ec-assistant-message ec-typing-indicator">
			<div className="ec-message-content">
				<p className="ec-typing-dots">
					<span></span>
					<span></span>
					<span></span>
				</p>
			</div>
		</div>
	);
}
