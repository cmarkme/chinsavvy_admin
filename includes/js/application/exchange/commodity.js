function add() {
    window.location = '/exchange/commodity/add';
}

$(document).ready(function() {
    var setup_table = function(object) {
        $('#ajaxtable').css('width', '100%');
        $('th.commodity_id').css('width', '40px');
        $('th.commodity_name').css('width', '80%');
        $('th.commodity_category').css('width', '40px');
        $('th.actions').css('width', '40px');
    };

    var ajaxtable = setup_ajax_table("/exchange/commodity/browse",
                     [true, true, true, false],
                     {
                     },
                     true,
                     setup_table,
                     [[0, 'asc']]
                    );

});

