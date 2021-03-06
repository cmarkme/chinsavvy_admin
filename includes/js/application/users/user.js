function add() {
    window.location = '/users/user/add';
}

$(document).ready(function() {
    var setup_table = function(object) {
        $('#ajaxtable').css('width', '100%');
        $('th.user_id').css('width', '40px');
        $('th.user_name').css('width', '140px');
        $('th.user_email').css('width', '140px');
        $('th.user_roles').css('width', '140px');
        $('th.actions').css('width', '40px');
    };

    var ajaxtable = setup_ajax_table("/users/user",
                     [true, true, true, false, false],
                     {
                         combo: 'combo',
                         role_id: 'select'
                     },
                     true,
                     setup_table,
                     [[1, 'asc']]
                    );

});

