import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import Save from './save';

registerBlockType( 'competitive-scheduling/client', {
	edit: Edit,
	save: Save,
} );
