import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit.js';
import Save from './save.js';

registerBlockType( 'competitive-scheduling/client', {
	edit: Edit,
	save: Save,
} );
