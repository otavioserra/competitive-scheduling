import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from "@wordpress/block-editor";

registerBlockType('competitive-scheduling/client', {
    edit: function () {
        const blockProps = useBlockProps();
        return <p {...blockProps}>Edit 2</p>;
    },
    save: function () {
        return <p className="teste">Save</p>;
    },
});
