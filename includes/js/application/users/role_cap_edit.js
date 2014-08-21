$(document).ready(function() {
    var role_id = $.url.segment(3);
    $("#assignable").treeview({
        animated: "fast",
        collapsed: true,
        unique: true,
        toggle: function() {
            // Toggle code here
            // console.log("%o was toggled", this);
            // No action is actually required when toggling, only item links are used
        }
    });
});
