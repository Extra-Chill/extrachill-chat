/**
 * Chat Block - Editor Component
 *
 * Simple placeholder for the Gutenberg editor.
 */

import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';

export default function Edit() {
	const blockProps = useBlockProps();

	return (
		<div { ...blockProps }>
			<div className="ec-chat-editor-placeholder">
				<span className="dashicons dashicons-format-chat"></span>
				<p>{ __( 'AI Chat', 'extrachill-chat' ) }</p>
				<small>{ __( 'Chat interface will display on the frontend.', 'extrachill-chat' ) }</small>
			</div>
		</div>
	);
}
