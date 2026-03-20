/**
 * Chat Block Registration
 *
 * Registers the AI chat block for the Gutenberg editor.
 * Uses @extrachill/chat shared component library.
 */

import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import Edit from './edit';

import './editor.scss';
import './style.scss';

registerBlockType( metadata.name, {
	edit: Edit,
	save: () => null,
} );
