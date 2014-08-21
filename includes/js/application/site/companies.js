function add() {
    window.location = '/company/add';
}

$(document).ready(function() {
    var setup_table = function(object) {
        $('#ajaxtable').css('width', '100%');
        $('th.company_id').css('width', '40px');
        $('th.company_code').css('width', '40px');
        $('th.company_name').css('width', '160px');
        $('th.company_type').css('width', '40px');
        $('th.company_role').css('width', '40px');
        $('th.company_email').css('width', '160px');
        // $('th.company_address_country_id').css('width', '120px');
        $('th.actions').css('width', '40px');
    };

    var ajaxtable = setup_ajax_table("/company/index",
                     [true, true, true, true, true, true, false, false],
                     {
                         combo: 'combo',
                         company_type: 'select',
                         company_role: 'select'
                     },
                     true,
                     setup_table,
                     [[0, 'desc']]
                    );

});

