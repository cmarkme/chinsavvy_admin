$(document).ready(function() {

    var role_id = $.url.segment(3);
    var input = make_autocomplete_input('add_user_role', 'add_user_role', '', 'Add a user to this role', 'users/role/get_assignable_users/'+role_id,
        function(event, ui) { // Callback called when a value is entered
            var user_id = ui.item.value;
            window.location = '/users/role/add_role_to_user/'+role_id+'/'+user_id;
        }
    );
    $("#add_div").append(input.label);
    $("#add_div").append(input.input);
    $("#ajaxtable").dataTable( {
        "bLengthChange": false,
        "asStripClasses": ['odd', 'even'],
        "iDisplayLength": 20,
        "sDom": '<"top"ip>rt',
        "bJQueryUI": false,
        "sPaginationType": 'input'
    });
});
