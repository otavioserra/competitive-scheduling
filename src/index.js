import { registerBlockType } from "@wordpress/blocks";

registerBlockType("competitive-scheduling/client", {
    edit: function () {
        return <p className="teste">Edit 2</p>;
    },
    save: function () {
        return <p className="teste">Save</p>;
    },
});