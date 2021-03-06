function add() {
    window.location = '/exchange/market/add';
}

$(document).ready(function() {
    var setup_table = function(object) {
        $('#ajaxtable').css('width', '100%');
        $('th.market_id').css('width', '40px');
        $('th.market_name').css('width', '30%');
        $('th.market_currency').css('width', '40px');
        $('th.market_commodities').css('width', '30%');
        $('th.actions').css('width', '50px');
    };

    var ajaxtable = setup_ajax_table("/exchange/market/browse",
                     [true, true, true, false, false],
                     {
                     },
                     true,
                     setup_table,
                     [[0, 'asc']]
                    );

});

