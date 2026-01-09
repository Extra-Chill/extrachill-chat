/**
 * ChatInput Component
 *
 * Input textarea and send button for the chat interface.
 */

import { useState, useRef, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

export default function ChatInput( { onSend, disabled } ) {
	const [ value, setValue ] = useState( '' );
	const textareaRef = useRef( null );

	const handleSubmit = useCallback( () => {
		if ( value.trim() && ! disabled ) {
			onSend( value );
			setValue( '' );
			if ( textareaRef.current ) {
				textareaRef.current.style.height = 'auto';
			}
		}
	}, [ value, disabled, onSend ] );

	const handleKeyDown = useCallback( ( e ) => {
		if ( e.key === 'Enter' && ! e.shiftKey ) {
			e.preventDefault();
			handleSubmit();
		}
	}, [ handleSubmit ] );

	const handleInput = useCallback( () => {
		if ( textareaRef.current ) {
			textareaRef.current.style.height = 'auto';
			textareaRef.current.style.height = textareaRef.current.scrollHeight + 'px';
		}
	}, [] );

	return (
		<div className="ec-chat-input-container">
			<textarea
				ref={ textareaRef }
				className="ec-chat-input"
				value={ value }
				onChange={ ( e ) => setValue( e.target.value ) }
				onKeyDown={ handleKeyDown }
				onInput={ handleInput }
				placeholder={ __( 'Type your message...', 'extrachill-chat' ) }
				rows={ 1 }
				disabled={ disabled }
			/>
			<button
				type="button"
				className="ec-chat-send-button"
				onClick={ handleSubmit }
				disabled={ disabled || ! value.trim() }
			>
				{ __( 'Send', 'extrachill-chat' ) }
			</button>
		</div>
	);
}
