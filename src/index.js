import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from "@wordpress/block-editor";
import '.style.scss';

registerBlockType('competitive-scheduling/client', {
    edit: function () {
        const blockProps = useBlockProps();
        return <p {...blockProps}>Edit JSX</p>;
    },
    save: function () {
        const blockProps = useBlockProps.save();
        return <p {...blockProps}>Save JSX</p>;
    },
});
