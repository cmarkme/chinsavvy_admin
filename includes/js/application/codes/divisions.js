function add() {
    window.location = '/codes/division/add';
}

$(document).ready(function() {
    var setup_table = function(object) {
        $('#ajaxtable').css('width', '100%');
        $('th.id').css('width', '40px');
        $('th.name').css('width', '140px');
        $('th.code').css('width', '40px');
        $('th.creation_date').css('width', '40px');
        $('th.actions').css('width', '50px');
    };

    var ajaxtable = setup_ajax_table("/codes/division/browse",
                     [true, true, true, true, false],
                     {
                         combo: 'combo'
                     },
                     true,
                     setup_table,
                     [[0, 'desc']]
                    );
});

