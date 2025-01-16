var registerBlockType = wp.blocks.registerBlockType;

registerBlockType("competitive-scheduling/client", {
    edit: function () {
        return "Edit";
    },
    save: function () {
        return "Save";
    },
});