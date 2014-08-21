$(document).ready(function() {
    var setup_table = function(object) {
        $('#ajaxtable').css('width', '100%');
        $('th.name').css('width', '300px');
        $('th.description').css('width', '500px');
        $('th.actions').css('width', '100px');
    };

    var ajaxtable = setup_ajax_table("/autoemails/browse",
                     [true, true, true, true, true, false],
                     {},
                     true,
                     setup_table,
                     [[0, 'asc']]
                    );
});

