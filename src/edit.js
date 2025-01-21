import { useBlockProps } from '@wordpress/block-editor';
import './style.scss';
import './editor.scss';

// const x = 0;

export default function Edit() {
	const blockProps = useBlockProps();
	return <p {...blockProps}>Edit JSX</p>;
}