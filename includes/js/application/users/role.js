function add() {
    window.location = '/users/role/add';
}

$(document).ready(function() {
    var setup_table = function(object) {
        $('#ajaxtable').css('width', '100%');
        $('th.role_id').css('width', '40px');
        $('th.role_name').css('width', '40px');
        $('th.role_description').css('width', '40px');
        $('th.actions').css('width', '40px');
    };

    var ajaxtable = setup_ajax_table("/users/role/browse",
                     [true, true, true, false],
                     {
                     },
                     true,
                     setup_table,
                     [[0, 'asc']]
                    );

});

